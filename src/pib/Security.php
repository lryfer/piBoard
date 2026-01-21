<?php
namespace pib;

class Security
{
    public static function escapeHtml(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function escapeUrl(?string $url): string
    {
        if ($url === null) {
            return '';
        }

        $url = trim($url);
        $dangerous_protocols = ['javascript:', 'data:', 'vbscript:', 'file:'];

        foreach ($dangerous_protocols as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return '';
            }
        }

        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        $input = str_replace(chr(0), '', $input);
        $input = trim($input);

        return $input;
    }

    public static function validateImageUrl(?string $url): bool
    {
        if ($url === null || strlen($url) === 0) {
            return true;
        }

        $dangerous_protocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
        $url_lower = strtolower(trim($url));

        foreach ($dangerous_protocols as $protocol) {
            if (strpos($url_lower, $protocol) === 0) {
                return false;
            }
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        return true;
    }
}
