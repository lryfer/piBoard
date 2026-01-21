<?php
// Random functions that will be changed once backend is ready
global $conn;
require_once "pib/Board.php";
require_once "pib/Ban.php";
require_once "pib/Security.php";
require_once "connection.php";
require_once "pib/form_actions/utilities.php";
if(is_client_banned(new \pib\Ban($conn)))
    header("Location: ban_page.php");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>piBoard - PHP ImageBoard</title>
    <link rel="stylesheet" href="styles/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mainstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
    require_once "pib/components/header.php";
    renderHeader($conn, [
        'buttons' => ['admin', 'login', 'register', 'logout'],
        'showTagline' => true
    ]);
    ?>

    <main>
        <div class="welcome-section">
            <div class="welcome-greeting">
                Welcome, <span class="user-info"><?php echo get_nickname() ?? "Anon"; ?></span>
            </div>
        </div>

        <div class="boards-box">
            <div class="boards-title">
                <h2>📋 Boards</h2>
            </div>
            <div class="boards-columns" style="display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 1.5rem !important;">
                <?php
                $boards = new \pib\Board($conn);
                foreach ($boards->getBoards() as $board) {
                    $boardName = \pib\Security::escapeHtml($board['FullName']);
                    $boardId = \pib\Security::escapeHtml($board['Id']);
                    echo "<a href='board.php?id={$boardId}' class='board-card'><p>{$boardName}</p></a>";
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>
