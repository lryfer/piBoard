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
if(get_role($conn) !== "Founder" && ($_POST["role"] === "Founder" || $_POST["role"] === "Admin")) {
    die("Unauthorized action");
}

$users = new \pib\User($conn);
$users->updateUserRole($_POST["user_id"], $_POST["role"]);
header('Location: ../../admin.php');