<?php
require_once 'connection.php';
require_once 'pib/Board.php';
require_once 'pib/Comment.php';
require_once 'pib/Thread.php';
require_once 'pib/User.php';
require_once 'pib/Ban.php';
require_once 'pib/CSRF.php';
require_once "pib/form_actions/utilities.php";
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
        $confirm_password = $_POST['confirm_password'];

        if (empty($username)) {
            $errors[] = "Username is required";
        }
        if (empty($password)) {
            $errors[] = "Password is required";
        }
        if (!preg_match('/[A-Z]/', $password) or !preg_match('/[a-z]/', $password) or strlen($password) < 6) {
            $errors[] = "Insert a password longer than 6 character with at least one capital letter";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        if (empty($errors)) {
            $users->addUser($username, $password,null);
            header("Location: index.php");
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - piBoard</title>
    <link rel="stylesheet" href="styles/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" href="styles/mainstyle.css?v=<?php echo time(); ?>"/>
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
</head>
<body class="centeringbody">
    <?php
    require_once "pib/components/header.php";
    renderHeader($conn, [
        'buttons' => ['login'],
        'showTagline' => true
    ]);
    ?>
<div class="register-container card container-sm">
    <h2>Register</h2>
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
    <form id="register-form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <?php echo \pib\CSRF::getTokenField(); ?>
        <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
        <input type="password" id="password" name="password" placeholder="Password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="submit" value="Register">
    </form>
</div>
</body>
<script>
    const registerForm = document.getElementById("register-form");
    registerForm.addEventListener("submit", (e) => {
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm_password");
        if(password.value !== confirmPassword.value) {
            e.preventDefault();
            alert("Passwords do not match");
        }
    })
</script>
</html>