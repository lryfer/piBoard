<?php
require_once __DIR__ . '/../src/connection.php';
require_once __DIR__ . '/../src/pib/User.php';

if (php_sapi_name() !== 'cli') {
    die("ERROR: This script can only be run from command line.\n");
}

if ($argc < 3) {
    echo "Usage: php scripts/create_founder.php <username> <password>\n";
    exit(1);
}

$username = trim($argv[1]);
$password = trim($argv[2]);

if (empty($username) || empty($password)) {
    echo "ERROR: Username and password cannot be empty.\n";
    exit(1);
}

global $conn;
$users = new pib\User($conn);

try {
    $users->addUser($username, $password, "Founder");
    echo "✅ Founder user created: $username\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
