<?php
/**
 * UniHelp - Gemini Resume Analyzer v2
 * Strategy:
 *   1. PDF/DOCX file seedha Gemini ko inline base64 mein bhejo (best quality)
 *   2. Fallback: extracted text bhejo
 *   3. Multiple models try karo in order
 */

class GeminiResumeAnalyzer {

    // Models to try in order — verified available for this API key
    private static $models = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-flash-latest',
        'gemini-pro-latest',
    ];

    /**
     * Main entry — try file-based then text-based approach
     */
    public static function analyze(string $resumeText, string $filePath = '', string $fileType = 'pdf'): array {
        $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        if (empty($apiKey)) {
            return self::fallbackFeedback('Gemini API key configure nahi hai.');
        }

        // Strategy 1: Send PDF file directly as base64 inline data
        if (!empty($filePath) && file_exists($filePath) && $fileType === 'pdf') {
            $result = self::analyzeWithFile($filePath, $apiKey);
            if ($result !== null) return $result;
        }

        // Strategy 2: Send extracted text
        if (!empty(trim($resumeText))) {
            $result = self::analyzeWithText($resumeText, $apiKey);
            if ($result !== null) return $result;
        }

        return self::fallbackFeedback('Gemini API se response nahi mila. API quota check karo ya thodi der baad try karo.');
    }

    /**
     * Send PDF file directly as base64 inline to Gemini (multimodal)
     */
    private static function analyzeWithFile(string $filePath, string $apiKey): ?array {
        $fileContent = file_get_contents($filePath);
        if (!$fileContent) return null;

        $base64 = base64_encode($fileContent);
        $prompt  = self::getPrompt('');

        $payload = json_encode([
            'contents' => [[
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => 'application/pdf',
                            'data'      => $base64
                        ]
                    ],
                    ['text' => $prompt]
                ]
            ]],
            'generationConfig' => [
                'temperature'      => 0.2,
                'maxOutputTokens'  => 2000,
                'responseMimeType' => 'application/json'
            ]
        ]);

        return self::callWithModels($payload, $apiKey);
    }

    /**
     * Send extracted text to Gemini
     */
    private static function analyzeWithText(string $text, string $apiKey): ?array {
        // Limit to 6000 chars
        $text = mb_substr($text, 0, 6000);
        $prompt = self::getPrompt($text);

        $payload = json_encode([
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]],
            'generationConfig' => [
                'temperature'      => 0.2,
                'maxOutputTokens'  => 2000,
                'responseMimeType' => 'application/json'
            ]
        ]);

        return self::callWithModels($payload, $apiKey);
    }

    /**
     * Try multiple Gemini models in order until one works
     */
    private static function callWithModels(string $payload, string $apiKey): ?array {
        $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

        $modelsToTry = self::$models;
        if (defined('GEMINI_MODEL') && !empty(GEMINI_MODEL)) {
            $modelsToTry = array_diff($modelsToTry, [GEMINI_MODEL]);
            array_unshift($modelsToTry, GEMINI_MODEL);
        }

        foreach ($modelsToTry as $model) {
            $url = $baseUrl . $model . ':generateContent?key=' . $apiKey;
            $result = self::curlPost($url, $payload);

            if ($result === null) continue;

            $decoded = json_decode($result, true);

            // Rate limit — try next model
            if (isset($decoded['error']['code']) && in_array($decoded['error']['code'], [429, 503])) {
                continue;
            }

            // Other API error — stop
            if (isset($decoded['error'])) {
                error_log("Gemini error on {$model}: " . json_encode($decoded['error']));
                continue;
            }

            $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (empty($text)) continue;

            // Clean JSON
            $text = trim($text);
            $text = preg_replace('/^```json\s*/i', '', $text);
            $text = preg_replace('/```\s*$/i', '', $text);
            $text = trim($text);

            $parsed = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                return self::sanitize($parsed);
            }
        }

        return null;
    }

    /**
     * cURL POST helper
     */
    private static function curlPost(string $url, string $payload): ?string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $result = curl_exec($ch);
        $err    = curl_error($ch);

        if ($err) {
            error_log("Gemini cURL error: " . $err);
            return null;
        }
        return $result ?: null;
    }

    /**
     * Build the analysis prompt
     */
    private static function getPrompt(string $resumeText): string {
        $textSection = !empty($resumeText)
            ? "Resume Text:\n" . $resumeText
            : "The resume is attached as a PDF file above. Analyze it carefully.";

        return <<<PROMPT
You are an expert resume reviewer and ATS specialist for Indian engineering students. Analyze the resume and return ONLY a raw valid JSON object (no markdown, no explanation, just JSON).

Required JSON structure:
{
  "overall_score": <integer 0-100>,
  "ats_score": <integer 0-100>,
  "experience_level": "<fresher|junior|mid-level|senior>",
  "summary_feedback": "<2-3 sentence overall assessment>",
  "skills_found": ["<skill1>", "<skill2>"],
  "missing_keywords": ["<keyword1>"],
  "strengths": ["<strength1>", "<strength2>", "<strength3>"],
  "weaknesses": ["<weakness1>", "<weakness2>"],
  "suggestions": ["<suggestion1>", "<suggestion2>", "<suggestion3>"],
  "sections_found": ["education", "skills", "projects"],
  "missing_sections": ["summary", "certifications"],
  "grammar_issues": ["<issue1>"],
  "formatting_notes": ["<note1>"]
}

Scoring guide:
- overall_score: holistic quality (80+ = excellent, 60-79 = good, 40-59 = average, below 40 = needs major work)
- ats_score: ATS-friendliness (single column=higher, tables/graphics=lower)

{$textSection}
PROMPT;
    }

    /**
     * Sanitize and fill defaults
     */
    private static function sanitize(array $data): array {
        $def = [
            'overall_score'    => 50,
            'ats_score'        => 50,
            'experience_level' => 'fresher',
            'summary_feedback' => 'Resume analysis complete.',
            'skills_found'     => [],
            'missing_keywords' => [],
            'strengths'        => [],
            'weaknesses'       => [],
            'suggestions'      => [],
            'sections_found'   => [],
            'missing_sections' => [],
            'grammar_issues'   => [],
            'formatting_notes' => [],
        ];
        foreach ($def as $k => $v) {
            if (!isset($data[$k])) $data[$k] = $v;
        }
        $data['overall_score'] = max(0, min(100, (int)$data['overall_score']));
        $data['ats_score']     = max(0, min(100, (int)$data['ats_score']));
        return $data;
    }

    /**
     * Fallback when all fails
     */
    private static function fallbackFeedback(string $reason): array {
        return array_merge(self::sanitize([]), [
            'overall_score'    => 0,
            'ats_score'        => 0,
            'summary_feedback' => $reason,
            'weaknesses'       => ['AI analysis complete nahi ho paya'],
            'suggestions'      => ['Thodi der baad dobara try karo', 'DOCX format mein upload karke try karo'],
            'error'            => true,
        ]);
    }
}
