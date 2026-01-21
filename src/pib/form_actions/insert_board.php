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

if (!isset($_POST['board_id']) || !isset($_POST['board_name'])) {
    die("Error, required data not provided");
}

global $conn;

$current_role = get_role($conn);
if ($current_role === null) {
    die("Error: Unauthorized action. Only staff can create boards.");
}
$board_id = trim($_POST['board_id']);
$board_name = trim($_POST['board_name']);
if (empty($board_id) || empty($board_name)) {
    die("Error: Board ID and name are required");
}
if (strlen($board_id) > 10) {
    die("Error: Board ID too long (max 10 characters)");
}
if (strlen($board_name) > 100) {
    die("Error: Board name too long (max 100 characters)");
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $board_id)) {
    die("Error: Board ID must contain only letters, numbers and underscores");
}

$boards = new \pib\Board($conn);
$boards->addBoard($board_id, get_id(), $board_name);

header('Location: ../../admin.php');
