<?php
require_once "../ErrorHandler.php";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Error, method should be POST");
}

require_once "../../connection.php";
require_once "../Board.php";
require_once "../CSRF.php";
require_once "utilities.php";

if (!\pib\CSRF::validateRequest()) {
    die("Error: Invalid CSRF token");
}

if (!isset($_POST['board_id'])) {
    die("Error, required data not provided");
}

global $conn;

$current_role = get_role($conn);
if ($current_role === null) {
    die("Error: Unauthorized action. Only staff can delete boards.");
}

$boards = new \pib\Board($conn);
$boards->deleteBoard($_POST['board_id']);
header('Location: ../../admin.php');
