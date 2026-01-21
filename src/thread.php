<?php
require_once "pib/Board.php";
require_once "pib/Thread.php";
require_once "pib/Comment.php";
require_once "pib/User.php";
require_once "pib/Ban.php";
require_once "pib/CSRF.php";
require_once "pib/Security.php";
require_once "connection.php";
require_once "pib/form_actions/utilities.php";

global $conn;

$thread_id = $_GET['id'] ?? null;
if (!isset($thread_id) || empty($thread_id)) {
    header("Location: index.php");
    exit();
}

if(is_client_banned(new \pib\Ban($conn)))
    header("Location: ban_page.php");

$threads = new \pib\Thread($conn);
$thread_data = $threads->getThread((int)$thread_id);

if (!$thread_data || empty($thread_data)) {
    header("Location: index.php");
    exit();
}

$boards = new \pib\Board($conn);
$current_board = $boards->getBoard($thread_data['BoardId']);
$comments = new \pib\Comment($conn);
$thread_comments = $comments->getThreadComments((int)$thread_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \pib\Security::escapeHtml($thread_data['Title']); ?> - piBoard</title>
    <link rel="stylesheet" href="styles/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mainstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/boardstyle.css?v=<?php echo time(); ?>">
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
        <div>
            <a href="board.php?id=<?php echo \pib\Security::escapeHtml($thread_data['BoardId']); ?>" style="color: var(--mauve); text-decoration: none;">
                ← Back to /<?php echo \pib\Security::escapeHtml($current_board['FullName'] ?? $thread_data['BoardId']); ?>/
            </a>
            <h1><?php echo \pib\Security::escapeHtml($thread_data['Title']); ?></h1>
        </div>
    </div>

    <?php
    // Display the main thread
    $displayedName = \pib\Security::escapeHtml($thread_data['Nickname'] ?? 'Anonymous');
    $threadContent = \pib\Security::escapeHtml($thread_data['Content']);
    $hasMedia = isset($thread_data['MediaPath']) && !empty($thread_data['MediaPath']);
    $mediaPath = $hasMedia ? \pib\Security::escapeUrl($thread_data['MediaPath']) : '';
    $mediaType = $thread_data['MediaType'] ?? null;
    ?>

    <div class="discussion">
        <div id="thread<?php echo (int)$thread_id; ?>" class="thread thread-full">
            <div class="thread-header">
                <h2 class="thread-main-title"><?php echo \pib\Security::escapeHtml($thread_data['Title']); ?></h2>
                <span class="thread_title">
                    <p class="content_display_name"><?php echo $displayedName; ?></p>
                    <p>Thread #<?php echo (int)$thread_id; ?></p>
                    <?php if(get_role($conn) != null): ?>
                        <p><?php echo \pib\Security::escapeHtml($thread_data['IpAddress']); ?></p>
                        <form method="post" action="pib/form_actions/delete_thread.php">
                            <?php echo \pib\CSRF::getTokenField(); ?>
                            <input type="hidden" name="thread_id" value="<?php echo (int)$thread_id; ?>">
                            <input class="thread_delete_btn" type="submit" value="Delete">
                        </form>
                    <?php endif; ?>
                </span>
            </div>
            <div class="thread-body">
                <?php if ($hasMedia && !empty($mediaPath)): ?>
                    <div class="thread-media">
                        <?php if ($mediaType === 'video'): ?>
                            <video controls src="<?php echo $mediaPath; ?>" class="thread-full-image"></video>
                        <?php else: ?>
                            <img src="<?php echo $mediaPath; ?>" alt="Thread Media" class="thread-full-image">
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="thread-text-box">
                    <p class="thread-full-content"><?php echo $threadContent; ?></p>
                </div>
            </div>
        </div>

        <div class="comments-section">
            <h2 style="color: var(--pink); margin-top: 2rem;">Comments (<?php echo count($thread_comments); ?>)</h2>

            <!-- Inline comment form -->
            <div class="comment-form-container">
                <h3 style="color: var(--mauve); margin-bottom: 1rem;">Add a Comment</h3>
                <form id="comment_form" action="pib/form_actions/post.php?board_id=<?php echo \pib\Security::escapeHtml($thread_data['BoardId']); ?>" method="post">
                    <?php echo \pib\CSRF::getTokenField(); ?>
                    <div>
                        <textarea name="content" id="content" cols="48" rows="4" placeholder="What are your thoughts?" minlength="1" required></textarea>
                    </div>
                    <div>
                        <input type="url" name="image_url" id="image_url" placeholder="Image URL (optional)">
                    </div>
                    <input type="hidden" name="thread_id" value="<?php echo (int)$thread_id; ?>">
                    <input type="hidden" name="replying_to_thread" value="true">
                    <div>
                        <input type="submit" value="Comment">
                    </div>
                </form>
            </div>

            <?php foreach ($thread_comments as $comment): ?>
                <?php
                $commentDisplayedName = \pib\Security::escapeHtml($comment['Nickname'] ?? 'Anonymous');
                $commentContent = \pib\Security::escapeHtml($comment['Content']);
                $commentId = (int)$comment['Id'];
                $commentHasMedia = isset($comment['MediaPath']) && !empty($comment['MediaPath']);
                $commentMediaPath = $commentHasMedia ? \pib\Security::escapeUrl($comment['MediaPath']) : '';
                $commentMediaType = $comment['MediaType'] ?? null;
                ?>
                <div id="post<?php echo $commentId; ?>" class="comment">
                    <span class="thread_title">
                        <p><?php echo $commentDisplayedName; ?></p>
                        <p>Post #<?php echo $commentId; ?></p>
                        <?php if(get_role($conn) != null): ?>
                            <p><?php echo \pib\Security::escapeHtml($comment['IpAddress']); ?></p>
                            <form method="post" action="pib/form_actions/delete_comment.php" style="display: inline;">
                                <?php echo \pib\CSRF::getTokenField(); ?>
                                <input type="hidden" name="comment_id" value="<?php echo $commentId; ?>">
                                <input type="hidden" name="thread_id" value="<?php echo (int)$thread_id; ?>">
                                <input type="submit" value="Delete">
                            </form>
                        <?php endif; ?>
                    </span>
                    <p><?php echo $commentContent; ?></p>
                    <?php if ($commentHasMedia && !empty($commentMediaPath)): ?>
                        <?php if ($commentMediaType === 'video'): ?>
                            <video controls src="<?php echo $commentMediaPath; ?>"></video>
                        <?php else: ?>
                            <img src="<?php echo $commentMediaPath; ?>" alt="Comment Media">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

</body>
</html>
