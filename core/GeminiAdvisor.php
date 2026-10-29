<?php
/**
 * UniHelp - Gemini Career Advisor
 * Provides personalized advice to bridge skill gaps.
 */

class GeminiAdvisor {

    public static function getAdvice(string $goal, array $missingSkills): string {
        $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        if (empty($apiKey) || empty($missingSkills)) {
            return "Hamein aapke gap analysis ke liye kuch aur data chahiye. Please try again.";
        }

        $skillsList = implode(', ', $missingSkills);
        $prompt = <<<PROMPT
You are a career counselor and technical mentor. A student's career goal is "{$goal}".
They are missing the following key skills: {$skillsList}.

Provide exactly 3-4 highly actionable tips to bridge this gap. 
- Suggest specific modern projects they can build.
- Mention 1-2 top resources (courses, documentation, or certifications).
- Use a supportive, professional tone in a mix of English and Hindi (Hinglish) where appropriate for a friendly vibe.
- Keep the total response under 250 words.
- Format with simple bullet points. No bolding (**), no headers (#).
PROMPT;

        $payload = json_encode([
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800
            ]
        ]);

        $model = defined('GEMINI_MODEL') && !empty(GEMINI_MODEL) ? GEMINI_MODEL : 'gemini-2.0-flash';
        $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

        $ch = curl_init($baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $result = curl_exec($ch);

        if (!$result) return "Gemini API se response nahi mila. Please check your internet/API key.";

        $decoded = json_decode($result, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return !empty($text) ? $text : "Advice generate nahi ho payi. Please try again.";
    }
}
