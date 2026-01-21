<?php
require_once "../../connection.php";
require_once "../Thread.php";
require_once "../CSRF.php";
require_once "../ErrorHandler.php";
require_once "utilities.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    \pib\ErrorHandler::handleBadRequest("Invalid request method");
}

if (!\pib\CSRF::validateRequest()) {
    \pib\ErrorHandler::handleUnauthorized("Invalid CSRF token");
}

if (!isset($_POST['thread_id'])) {
    \pib\ErrorHandler::handleBadRequest("Required data not provided");
}

global $conn;

$current_role = get_role($conn);
if ($current_role === null) {
    \pib\ErrorHandler::handleUnauthorized("Only staff members can delete threads");
}

$threads = new \pib\Thread($conn);
$threads->deleteThread($_POST['thread_id']);
header('Location: ../../index.php');
