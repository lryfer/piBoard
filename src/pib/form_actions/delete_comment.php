<?php
require_once "../ErrorHandler.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Error, method should be POST");
}

require_once "../../connection.php";
require_once "../Comment.php";
require_once "../Thread.php";
require_once "../CSRF.php";
require_once "utilities.php";

if (!\pib\CSRF::validateRequest()) {
    die("Error: Invalid CSRF token");
}

if (!isset($_POST['comment_id']) || !isset($_POST['thread_id'])) {
    die("Error, required data not provided");
}

global $conn;

$current_role = get_role($conn);
if ($current_role === null) {
    die("Error: Unauthorized action - Only staff members can delete comments");
}

$comments = new \pib\Comment($conn);
$comments->deleteComment($_POST['comment_id']);

$threads = new \pib\Thread($conn);
$thread = $threads->getThread((int)$_POST['thread_id']);

if ($thread && isset($thread['BoardId'])) {
    header("Location: ../../thread.php?id=" . (int)$_POST['thread_id']);
} else {
    header("Location: ../../index.php");
}
