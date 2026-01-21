<?php
/**
 * require_once "pib/components/header.php";
 * renderHeader($conn, ['buttons' => ['login', 'register', 'admin', 'logout']]);
 *
 * @param mysqli $conn Database connection
 */
function renderHeader($conn, $options = []) {
    require_once __DIR__ . "/../form_actions/utilities.php";
    require_once __DIR__ . "/../quotes.php";

    $defaults = [
        'buttons' => ['login', 'register', 'logout', 'admin'],
        'showTagline' => true
    ];

    $config = array_merge($defaults, $options);
    $userId = get_id();
    $userRole = get_role($conn);
    ?>
    <nav class="pi-header">
        <div class="pi-header-left">
            <a href="index.php" class="pi-brand">piBoard</a>
            <?php if ($config['showTagline']): ?>
                <span class="pi-tagline"><?php echo \pib\Quotes::getRandom(); ?></span>
            <?php endif; ?>
        </div>

        <div class="pi-header-right">
            <?php
            // Admin button - only show if user is logged in and has a role
            if (in_array('admin', $config['buttons']) && $userId && $userRole != null):
            ?>
                <a href="admin.php" class="pi-nav-btn" title="Admin">
                    <span class="pi-btn-text">⚙️</span>
                </a>
            <?php endif; ?>

            <?php
            // Login/Register buttons - only show if user is NOT logged in
            if (!$userId):
                if (in_array('login', $config['buttons'])):
            ?>
                    <a href="login.php" class="pi-nav-btn" title="Login">
                        <span class="pi-btn-text">Login</span>
                    </a>
            <?php
                endif;
                if (in_array('register', $config['buttons'])):
            ?>
                    <a href="register.php" class="pi-nav-btn" title="Register">
                        <span class="pi-btn-text">Register</span>
                    </a>
            <?php
                endif;
            else:
                // Logout button - only show if user IS logged in
                if (in_array('logout', $config['buttons'])):
            ?>
                    <a href="logout.php" class="pi-nav-btn" title="Logout">
                        <span class="pi-btn-text">Logout</span>
                    </a>
            <?php
                endif;
            endif;
            ?>
        </div>
    </nav>
    <?php
}
