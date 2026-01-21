<?php
namespace pib;

class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_TIME_NAME = 'csrf_token_time';
    private const TOKEN_LIFETIME = 3600; // i think that one hour is more than enough

    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_NAME] = $token;
        $_SESSION[self::TOKEN_TIME_NAME] = time();

        return $token;
    }

    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::TOKEN_NAME] ?? null;
    }

    public static function validateToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::TOKEN_NAME]) || !isset($_SESSION[self::TOKEN_TIME_NAME])) {
            return false;
        }

        // Check if token has expired
        if (time() - $_SESSION[self::TOKEN_TIME_NAME] > self::TOKEN_LIFETIME) {
            self::clearToken();
            return false;
        }

        // Use hash_equals to prevent timing attacks
        $valid = hash_equals($_SESSION[self::TOKEN_NAME], $token);

        if ($valid) {
            // Regenerate token after successful validation (one-time use)
            self::generateToken();
        }

        return $valid;
    }

    public static function clearToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION[self::TOKEN_NAME]);
        unset($_SESSION[self::TOKEN_TIME_NAME]);
    }

    public static function getTokenField(): string
    {
        $token = self::getToken() ?? self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateRequest(): bool
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;

        if ($token === null) {
            return false;
        }

        return self::validateToken($token);
    }
}
