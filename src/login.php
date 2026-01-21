<?php
require_once 'connection.php';
require_once 'pib/Board.php';
require_once 'pib/Comment.php';
require_once 'pib/Thread.php';
require_once 'pib/User.php';
require_once "pib/Ban.php";
require_once "pib/CSRF.php";
require_once 'pib/form_actions/utilities.php';
global $conn;
if(is_client_banned(new \pib\Ban($conn)))
    header("Location: ban_page.php");


$board = new pib\Board($conn);
$comments = new pib\Comment($conn);
$threads = new pib\Thread($conn);
$users = new pib\User($conn);

$username = "root";
$password = "";
$username = $email = $password = $confirm_password = "";
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!\pib\CSRF::validateRequest()) {
        $errors[] = "Invalid CSRF token";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username)) {
            $errors[] = "Username is required";
        }
        if (empty($password)) {
            $errors[] = "Password is required";
        }

        if (empty($errors)) {
            $result = $users->verifyLogin($username, $password);

            if ($result) {
                set_login_cookie($result['Id'], $result['Nickname']);
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Incorrect password or username";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - piBoard</title>
    <link rel="stylesheet" href="styles/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" href="styles/mainstyle.css?v=<?php echo time(); ?>"/>
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
    require_once "pib/components/header.php";
    renderHeader($conn, [
        'buttons' => ['register'],
        'showTagline' => true
    ]);
    ?>
<div class="centeringbody">
<div class="register-container card container-sm">
    <h2>Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="success">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>
    <form id="login-form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <?php echo \pib\CSRF::getTokenField(); ?>
        <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>"
               required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
    <p style="text-align: center; margin-top: 1rem;">
        <a href="register.php">Don't have an account? Register here</a>
    </p>
</div>
</div>
</body>
</html>