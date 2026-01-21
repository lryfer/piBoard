<?php
namespace pib;

class ErrorHandler
{
    private static bool $production_mode = false;

    public static function setProductionMode(bool $enabled): void
    {
        self::$production_mode = $enabled;
    }

    public static function handleError(string $user_message, string $technical_details = '', int $http_code = 400): void
    {
        http_response_code($http_code);

        if (self::$production_mode) {
            self::renderErrorPage($user_message);
        } else {
            $full_message = $user_message;
            if (!empty($technical_details)) {
                $full_message .= "\n\nTechnical details: " . $technical_details;
            }
            self::renderErrorPage($full_message);
        }

        exit;
    }

    public static function handleDatabaseError(\PDOException $e, string $context = ''): void
    {
        $user_message = "An error occurred. Please try again later.";
        $technical_details = "Database error in {$context}: " . $e->getMessage();
        error_log($technical_details);
        self::handleError($user_message, $technical_details, 500);
    }

    public static function handleUnauthorized(string $message = "Unauthorized action"): void
    {
        self::handleError($message, '', 403);
    }

    public static function handleBadRequest(string $message = "Invalid request"): void
    {
        self::handleError($message, '', 400);
    }

    private static function renderErrorPage(string $message): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - piBoard</title>
            <link rel="stylesheet" href="/styles/theme.css">
        </head>
        <body>
            <div class="error-container">
                <h1>Error</h1>
                <p><?= nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ?></p>
                <a href="/index.php">Return to home</a>
            </div>
        </body>
        </html>
        <?php
    }
}
