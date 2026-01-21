<?php
require_once(dirname(__FILE__, 2) . "/User.php");
require_once(dirname(__FILE__, 2) . "/Ban.php");

function get_client_ip(): string
{
    // I found that REMOTE_ADDR is much harder to spoof than other way to get ip
    $ipaddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';


    if ($ipaddress !== 'UNKNOWN' && !filter_var($ipaddress, FILTER_VALIDATE_IP)) {
        $ipaddress = 'INVALID';
    }

    return $ipaddress;
}

function set_login_cookie(int $id, string $nickname): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id'] = $id;
    $_SESSION['user_nickname'] = $nickname;
    $_SESSION['login_time'] = time();

    $cookie_options = [
        'expires' => time() + 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    setcookie("session_active", "1", $cookie_options);
}

function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = array();
    session_destroy();
    $cookie_options = [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    setcookie("session_active", "", $cookie_options);
}

function get_login_cookie()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
        logout();
        return null;
    }

    if (isset($_SESSION['user_id']) && isset($_SESSION['user_nickname'])) {
        return [
            "id" => $_SESSION['user_id'],
            "nickname" => $_SESSION['user_nickname']
        ];
    }
    return null;
}

function get_nickname(): ?string
{
    $login_data = get_login_cookie();
    return $login_data["nickname"] ?? null;
}

function get_id(): ?int
{
    $login_data = get_login_cookie();
    return $login_data["id"] ?? null;
}

function is_client_banned(\pib\Ban $bans): bool
{
    return $bans->isUserBanned(get_id(), get_client_ip());
}

function get_role(PDO $conn): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = get_id();
    if (!$userId) return null;

    if (!isset($_SESSION['user_role'])) {
        $users = new \pib\User($conn);
        $user = $users->getUser($userId);
        $_SESSION['user_role'] = $user['Role'] ?? null;
    }

    return $_SESSION['user_role'];
}
