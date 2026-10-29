<?php
/**
 * UniHelp - Resume Parser v2
 * PDF aur DOCX files se clean plain text extract karta hai
 */

class ResumeParser {

    /**
     * Main entry point
     */
    public static function extractText(string $filePath, string $ext): string {
        $ext = strtolower(trim($ext));
        try {
            if ($ext === 'docx') {
                return self::extractDocx($filePath);
            } else {
                return self::extractPdf($filePath);
            }
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * DOCX: unzip → word/document.xml → strip tags
     */
    private static function extractDocx(string $path): string {
        if (!class_exists('ZipArchive')) return '';
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return '';
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) return '';

        // Preserve paragraph breaks
        $xml = preg_replace('/<\/w:p>/i',  "\n", $xml);
        $xml = preg_replace('/<\/w:r>/i',  ' ',  $xml);
        $xml = preg_replace('/<w:br[^>]*>/i', "\n", $xml);
        $text = strip_tags($xml);
        return self::cleanText($text);
    }

    /**
     * PDF text extraction — multiple strategies
     */
    private static function extractPdf(string $path): string {
        // Strategy 1: pdftotext (xpdf/poppler) — best quality
        $text = self::tryPdfToText($path);
        if (strlen(trim($text)) > 100) {
            return self::cleanText($text);
        }

        // Strategy 2: Parse PDF stream objects properly
        $text = self::parsePdfStreams($path);
        if (strlen(trim($text)) > 100) {
            return self::cleanText($text);
        }

        // Strategy 3: Extract readable ASCII strings (fallback)
        $text = self::extractAsciiStrings($path);
        return self::cleanText($text);
    }

    /**
     * Strategy 1: pdftotext command
     */
    private static function tryPdfToText(string $path): string {
        if (!function_exists('exec')) return '';
        
        // Try common locations on Windows/XAMPP
        $commands = [
            'pdftotext',
            'C:\\Program Files\\xpdf-tools-win-4.05\\bin64\\pdftotext',
            'C:\\xpdf\\pdftotext',
        ];

        foreach ($commands as $cmd) {
            $output = [];
            $return = null;
            @exec('"' . $cmd . '" ' . escapeshellarg($path) . ' - 2>nul', $output, $return);
            if ($return === 0 && !empty($output)) {
                return implode("\n", $output);
            }
        }
        return '';
    }

    /**
     * Strategy 2: Parse PDF content streams properly
     * Looks for decoded text in PDF stream objects
     */
    private static function parsePdfStreams(string $path): string {
        $raw = file_get_contents($path);
        if (!$raw) return '';

        $text = '';

        // First, find all BT...ET text blocks (PDF text objects)
        // These contain the actual rendered text
        preg_match_all('/BT\b(.*?)\bET/s', $raw, $blocks);
        
        foreach ($blocks[1] as $block) {
            // Match Tj operator: (text)Tj
            preg_match_all('/\(([^)\\\\]*(?:\\\\.[^)\\\\]*)*)\)\s*Tj/s', $block, $tjMatches);
            foreach ($tjMatches[1] as $s) {
                $text .= self::decodePdfString($s) . ' ';
            }

            // Match TJ operator: [(text)-123(text)]TJ
            preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArrayMatches);
            foreach ($tjArrayMatches[1] as $arr) {
                preg_match_all('/\(([^)\\\\]*(?:\\\\.[^)\\\\]*)*)\)/', $arr, $strMatches);
                foreach ($strMatches[1] as $s) {
                    $decoded = self::decodePdfString($s);
                    if (strlen(trim($decoded)) > 0) {
                        $text .= $decoded;
                    }
                }
                $text .= ' ';
            }
        }

        // Also try to find text in uncompressed stream sections
        // Match strings like /Title (My Resume) or (John Doe)
        if (strlen(trim($text)) < 50) {
            preg_match_all('/\(([A-Za-z0-9 @.,\-:+#\/\(\)\[\]\'\"&]{3,})\)/', $raw, $simpleMatches);
            foreach ($simpleMatches[1] as $s) {
                $text .= $s . ' ';
            }
        }

        return $text;
    }

    /**
     * Strategy 3: Extract long ASCII strings (last resort)
     */
    private static function extractAsciiStrings(string $path): string {
        $raw = file_get_contents($path);
        if (!$raw) return '';

        // Skip the PDF header binary portion
        $startPos = strpos($raw, 'BT');
        if ($startPos === false) $startPos = 0;
        $portion = substr($raw, $startPos);

        // Match sequences of printable ASCII chars (length > 3)
        preg_match_all('/[ -~]{4,}/', $portion, $matches);
        $strings = array_filter($matches[0], function($s) {
            // Filter out PDF operators and junk
            if (preg_match('/^[\d\s.\/\[\]<>]+$/', $s)) return false;
            if (preg_match('/^(obj|endobj|stream|endstream|xref|trailer|startxref)$/', trim($s))) return false;
            // Must contain at least some letters
            return preg_match('/[A-Za-z]{2,}/', $s);
        });

        return implode(' ', $strings);
    }

    /**
     * Decode PDF string escape sequences
     */
    private static function decodePdfString(string $str): string {
        // Octal: \123
        $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function($m) {
            $char = chr(octdec($m[1]));
            // Map Windows-1252 high bytes to readable ASCII
            return (ord($char) > 127) ? ' ' : $char;
        }, $str);

        // Standard PDF escape sequences
        $map = [
            '\\n' => "\n",  '\\r' => "\r",  '\\t' => "\t",
            '\\b' => '',     '\\f' => '',
            '\\(' => '(',   '\\)' => ')',   '\\\\' => '\\',
        ];
        $str = str_replace(array_keys($map), array_values($map), $str);

        // Remove non-printable
        $str = preg_replace('/[^\x20-\x7E\n\r]/', '', $str);
        return $str;
    }

    /**
     * Final text cleanup
     */
    private static function cleanText(string $text): string {
        // Remove PDF keywords that leaked through
        $pdfKeywords = ['obj', 'endobj', 'stream', 'endstream', 'xref', 'trailer',
                        'startxref', 'BT', 'ET', 'Tf', 'Td', 'Tm', 'Tj', 'TJ',
                        'Re', 'rg', 'RG', 'Gs', 'gs', 'cm', 'Do', 'EMC', 'BMC'];
        foreach ($pdfKeywords as $kw) {
            $text = preg_replace('/\b' . preg_quote($kw, '/') . '\b/', ' ', $text);
        }

        // Remove hex strings like <abc123>
        $text = preg_replace('/<[0-9a-fA-F\s]+>/', ' ', $text);
        // Remove [...] arrays
        $text = preg_replace('/\[\s*\d[\d\s.]*\]/', ' ', $text);
        // Remove lines that look like PDF syntax (all numbers/slashes)
        $text = preg_replace('/^\s*[\d.\-\s\/]+\s*$/m', '', $text);
        // Collapse whitespace
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
