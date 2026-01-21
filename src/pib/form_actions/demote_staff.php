<?php
require_once "../ErrorHandler.php";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Error, method should be POST");
}

require_once "../../connection.php";
require_once "../User.php";
require_once "../CSRF.php";
require_once "utilities.php";

if (!\pib\CSRF::validateRequest()) {
    die("Error: Invalid CSRF token");
}

if (!isset($_POST['user_id'])) {
    die("Error, required data not provided");
}

global $conn;

$current_role = get_role($conn);
if ($current_role !== "Founder" && $current_role !== "Admin") {
    die("Error: Unauthorized action");
}

$users = new pib\User($conn);
$user_to_demote = $users->getUser($_POST['user_id']);

if (!$user_to_demote) {
    die("Error: User not found");
}


if ($current_role === "Admin" && $user_to_demote['Role'] !== "Mod") {
    die("Error: Admins can only demote Moderators");
}

if ($user_to_demote['Id'] == get_id()) {
    die("Error: You cannot demote yourself");
}

$users->updateUserRole($_POST['user_id'], null);

header('Location: ../../admin.php');
