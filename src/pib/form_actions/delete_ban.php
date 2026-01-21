<?php
require_once "../ErrorHandler.php";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Error, method should be POST");
}

require_once "../../connection.php";
require_once "../Ban.php";
require_once "../CSRF.php";
require_once "utilities.php";

if (!\pib\CSRF::validateRequest()) {
    die("Error: Invalid CSRF token");
}

if (!isset($_POST['ban_id'])) {
    die("Error, required data not provided");
}

global $conn;

$current_role = get_role($conn);
if (!$current_role || !in_array($current_role, ["Founder", "Admin", "Mod"])) {
    die("Error: Unauthorized action - Only staff members can delete bans");
}
$boards = new \pib\Ban($conn);
$boards->deleteBan($_POST['ban_id']);
header('Location: ../../admin.php');
