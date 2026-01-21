<?php
require_once "../../connection.php";
require_once "../User.php";
require_once "../Thread.php";
require_once "../Comment.php";
require_once "../CSRF.php";
require_once "../Security.php";
require_once "../ErrorHandler.php";
require_once "../FileUpload.php";
require_once "utilities.php";

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    \pib\ErrorHandler::handleBadRequest("Invalid request method");
}

if (!\pib\CSRF::validateRequest()) {
    \pib\ErrorHandler::handleUnauthorized("Invalid CSRF token");
}

if(!isset($_POST['content'])) {
    \pib\ErrorHandler::handleBadRequest("Content not provided");
}

$mediaPath = null;
$mediaType = null;

if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadedPath = \pib\FileUpload::uploadFile($_FILES['media_file']);
    if ($uploadedPath === false) {
        \pib\ErrorHandler::handleBadRequest("File upload failed. Check file type and size.");
    }
    $fileInfo = \pib\FileUpload::getFileInfo($uploadedPath);
    $mediaPath = $uploadedPath;
    $mediaType = $fileInfo['is_video'] ? 'video' : 'image';
} elseif (!empty($_POST['image_url'])) {
    if (!\pib\Security::validateImageUrl($_POST['image_url'])) {
        \pib\ErrorHandler::handleBadRequest("Invalid image URL");
    }
    $mediaPath = $_POST['image_url'];
    $mediaType = 'url';
}

global $conn;
$comments = new \pib\Comment($conn);

$id_inserted = $comments->addComment(get_id(), get_client_ip(), $_POST['thread_id'], $_POST['content'], $mediaPath, $mediaType);
header("Location: ../../board.php?id={$_GET['board_id']}#post$id_inserted");
