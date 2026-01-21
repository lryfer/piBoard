<?php

use pib\Board;
use pib\Comment;
use pib\Thread;
use pib\User;

require_once "pib/Board.php";
require_once "pib/Thread.php";
require_once "pib/User.php";
require_once "pib/Comment.php";
require_once "pib/CSRF.php";
require_once "pib/Security.php";
require_once "connection.php";
require_once "pib/form_actions/utilities.php";
global $conn;
$board_id = $_GET['id'] ?? null;
if (!isset($board_id) || empty($board_id)) {
    header("Location: index.php");
    exit();
}
if(is_client_banned(new \pib\Ban($conn)))
    header("Location: ban_page.php");
$boards = new Board($conn);
$current_board = $boards->getBoard($board_id);

if (!$current_board || empty($current_board)) {
    header("Location: index.php");
    exit();
}
$threads = new Thread($conn);
$users = new User($conn);
$comments = new Comment($conn);
$relevant_threads = $threads->getThreadsWithUser($board_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \pib\Security::escapeHtml($current_board['FullName']); ?> - piBoard</title>
    <link rel="stylesheet" href="styles/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mainstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/boardstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/fileupload.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
    require_once "pib/components/header.php";
    renderHeader($conn, [
        'buttons' => ['admin', 'login', 'register', 'logout'],
        'showTagline' => false
    ]);
    ?>
<main>
    <div class="board-header">
        <h1><?php echo \pib\Security::escapeHtml($current_board['FullName']); ?></h1>
        <button id="create_new_thread" class="btn-primary">Start a new thread</button>
    </div>
<?php
foreach ($relevant_threads as $thread) {
    $displayedName = \pib\Security::escapeHtml($thread['Nickname'] ?? 'Anonymous');
    $threadTitle = \pib\Security::escapeHtml($thread['Title']);
    $threadContent = \pib\Security::escapeHtml($thread['Content']);
    $threadId = (int)$thread['Id'];
    $hasMedia = isset($thread['MediaPath']) && !empty($thread['MediaPath']);
    $mediaPath = $hasMedia ? \pib\Security::escapeUrl($thread['MediaPath']) : '';
    $mediaType = $thread['MediaType'] ?? null;

    $replyCount = $comments->getThreadCommentsCount($threadId);

    echo "<div class='discussion'><a href='thread.php?id={$threadId}' class='thread-link'>
          <div id='thread{$threadId}' class='thread'>
            <div class='thread-content'>
                <span class='thread_title'>
                    <p>{$threadTitle}</p>
                    <p>{$displayedName}</p>
                    <p>Thread #{$threadId}</p>
                    <p class='reply-count'>{$replyCount} " . ($replyCount === 1 ? 'reply' : 'replies') . "</p>";
    if(get_role($conn) != null) {
        $ipAddress = \pib\Security::escapeHtml($thread['IpAddress']);
        echo "<p>{$ipAddress}</p>";
        echo "<form method='post' action='pib/form_actions/delete_thread.php'>
                " . \pib\CSRF::getTokenField() . "
                <input type='hidden' name='thread_id' value='{$threadId}'>
                <input type='submit' value='Delete'>
              </form>";
    }
    echo "      </span>
                <p>{$threadContent}</p>
            </div>";

    if ($hasMedia && !empty($mediaPath)) {
        echo "<div class='thread-thumbnail'>";
        if ($mediaType === 'video') {
            echo "<video controls src='{$mediaPath}'></video>";
        } else {
            echo "<img src='{$mediaPath}' alt='Thread Media'>";
        }
        echo "</div>";
    } else {
        echo "<div class='thread-thumbnail no-media'></div>";
    }
    echo "</div></a></div>";
}
?>
</main>
<dialog id="add_post_dialogue">
    <form id="post_form" action='pib/form_actions/post.php<?php echo "?board_id=$board_id" ?>' method="post" enctype="multipart/form-data">
        <?php echo \pib\CSRF::getTokenField(); ?>
        <div>
            <label for="content"></label>
            <textarea name="content" id="content" cols="48" rows="4"
                      placeholder="Comment" minlength="1"></textarea>
        </div>
        <input type="url" name="image_url" id="image_url" placeholder="Image URL">
        <input type="hidden" name="thread_id" id="thread_id">
        <input type="hidden" name="replying_to_thread" id="replying_to_thread" value="false">
        <div>
            <input type="submit" value="Submit">
        </div>
    </form>
</dialog>
</body>
<script src="js_frontend/fileupload.js"></script>
<script>
    function switchMediaTab(tab) {
        document.querySelectorAll('.media-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.media-option-content').forEach(c => c.classList.remove('active'));

        if (tab === 'upload') {
            document.querySelector('.media-tab:first-child').classList.add('active');
            document.getElementById('media-upload').classList.add('active');
        } else {
            document.querySelector('.media-tab:last-child').classList.add('active');
            document.getElementById('media-url').classList.add('active');
        }
    }

    const newThreadButton = document.getElementById("create_new_thread");
    const boardHeader = document.querySelector('.board-header');

    newThreadButton.addEventListener("click", (e) => {
        let wrapper = document.createElement('div');
        wrapper.className = 'thread-form-container';
        wrapper.innerHTML = `<h3 style="color: var(--mauve); margin-bottom: 1rem;">Create New Thread</h3>
        <form id="thread_form" action='pib/form_actions/thread.php<?php echo "?board_id=$board_id" ?>' method='post' enctype='multipart/form-data'>
        <?php echo \pib\CSRF::getTokenField(); ?>
        <div>
            <input name="title" id="title" type="text" minlength="1" placeholder="Thread Title" required>
        </div>
        <div>
            <textarea name="content" id="content" rows="6" placeholder="Thread content..." minlength="1" required></textarea>
        </div>
        <div class="media-option-tabs">
            <div class="media-tab active" onclick="switchMediaTab('upload')">Upload File</div>
            <div class="media-tab" onclick="switchMediaTab('url')">URL</div>
        </div>
        <div id="media-upload" class="media-option-content active">
            <div class="file-drop-zone" id="drop_zone_thread">
                <div class="icon">📁</div>
                <p>Drag & drop file here or click to select</p>
                <p style="font-size: 0.9rem; color: var(--subtext0);">Images (10MB max) or Videos (100MB max)</p>
            </div>
            <input type="file" name="media_file" id="file_input_thread" class="file-input-hidden" accept="image/*,video/*">
            <div id="preview_thread" class="preview-area"></div>
        </div>
        <div id="media-url" class="media-option-content">
            <input type="url" name="image_url" id="image_url" placeholder="Image/Video URL (optional)">
        </div>
        <input hidden="hidden" type="text" name="board_id" id="board_id" value="<?php echo $board_id?>">
        <div>
            <input type="submit" value="Create Thread">
        </div>
        </form>`;

        boardHeader.replaceWith(wrapper);

        window.threadUploader = new FileUploadHandler('drop_zone_thread', 'file_input_thread', 'preview_thread');
    })
    const selectedBoardId = document.getElementById("thread_id");
    const replyingToThread = document.getElementById("replying_to_thread");
    const dialogue = document.getElementById("add_post_dialogue");
    document.getElementById("post_form").addEventListener("submit", (e) => {
        const imageUrl = document.getElementById("image_url");
        const imageFile = document.getElementById("uploaded_image");
        if (imageUrl.value && imageFile.value) {
            e.preventDefault();
            alert("Enter either a URL OR a File for the image, not both");
        }
    })

    document.querySelectorAll("a.reply_to_comment").forEach(element => {
        element.addEventListener("click", (e) => {
            selectedBoardId.value = element.dataset.boardId;
            replyingToThread.value = false;
            dialogue.showModal();
        })
    })

    document.querySelectorAll("a.reply_to_thread").forEach(element => {
        element.addEventListener("click", (e) => {
            selectedBoardId.value = element.dataset.boardId;
            replyingToThread.value = true;
            dialogue.showModal();
        })
    })
</script>
</html>
