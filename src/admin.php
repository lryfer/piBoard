<?php

use pib\Ban;
use pib\Board;

require_once "connection.php";
require_once "pib/User.php";
require_once "pib/Board.php";
require_once "pib/Ban.php";
require_once "pib/CSRF.php";
require_once "pib/Security.php";
require_once "pib/form_actions/utilities.php";

global $conn;
if (!get_role($conn)) header("Location: index.php");
if(is_client_banned(new \pib\Ban($conn)))
    header("Location: ban_page.php");

$users = new \pib\User($conn);
$all_users = $users->getUsers();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Control Panel - piBoard</title>
    <link rel="stylesheet" href="styles/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mainstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/controlpanel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
    require_once "pib/components/header.php";
    renderHeader($conn, [
        'buttons' => ['logout'],
        'showTagline' => false
    ]);
    ?>

<main class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">Control Panel</h1>
        <p class="admin-subtitle">Manage your piBoard instance</p>
    </div>
<div class="admin-content">
    <?php
    // Filter staff (with role)
    $staff = array_filter($all_users, function ($user) {
        return $user['Role'] != null;
    });

    // Filter normies (without role)
    $normies = array_filter($all_users, function ($user) {
        return $user['Role'] == null;
    });
    ?>
    <h1>Staff</h1>
    <div>
        <h2>Current Staff Members</h2>
        <div>
            <input type="text" id="staff_search" placeholder="Filter staff...">
        </div>
        <div class="list">
            <table id="staff_table">
                <thead>
                    <tr>
                        <th>Nickname</th>
                        <th>Role</th>
                        <th>Creation Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($staff as $user): ?>
                    <tr>
                        <td><?= \pib\Security::escapeHtml($user['Nickname']) ?></td>
                        <td><?= \pib\Security::escapeHtml($user['Role']) ?></td>
                        <td><?= \pib\Security::escapeHtml($user['CreationDate']) ?></td>
                        <td>
                        <?php if($user['Id'] != get_id() && get_role($conn) == "Founder" || (get_role($conn) == "Admin" &&  $user['Role'] == "Mod")) : ?>
                            <form method="post" action="pib/form_actions/demote_staff.php">
                                <?php echo \pib\CSRF::getTokenField(); ?>
                                <input type="hidden" name="user_id" value="<?= (int)$user["Id"] ?>">
                                <input type="submit" value="Demote">
                            </form>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <h1>All Users</h1>
    <div>
        <div>
            <input type="text" id="all_user_search" placeholder="Filter users by nickname or ID...">
        </div>
        <div class="list">
            <table id="all_users_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nickname</th>
                        <th>Role</th>
                        <th>Creation Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($all_users as $user): ?>
                    <tr>
                        <td><?= (int)$user['Id'] ?></td>
                        <td><?= \pib\Security::escapeHtml($user['Nickname']) ?></td>
                        <td><?= \pib\Security::escapeHtml($user['Role'] ?? 'User') ?></td>
                        <td><?= \pib\Security::escapeHtml($user['CreationDate']) ?></td>
                        <td>
                        <?php if ($user['Role'] == null && (get_role($conn) === "Admin" || get_role($conn) === "Founder")): ?>
                            <form method="post" action="pib/form_actions/make_member_staff.php" class="promote-form" data-user-id="<?= (int)$user['Id'] ?>" data-user-nickname="<?= \pib\Security::escapeHtml($user['Nickname']) ?>">
                                <?php echo \pib\CSRF::getTokenField(); ?>
                                <input type="hidden" name="user_id" value="<?= (int)$user['Id'] ?>">
                                <input type="hidden" name="role" value="" class="role-input">
                                <input type="submit" value="Promote" class="promote-btn">
                            </form>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $boards = new Board($conn);
    $boardsWithStats = $boards->getBoardsWithStats();
    ?>
    <h1>Boards</h1>
    <h2>Add new board: <span id="board_form_status" class="caret">▶</span></h2>
    <form action="pib/form_actions/insert_board.php" method="post" hidden="hidden" id="board_form">
        <?php echo \pib\CSRF::getTokenField(); ?>
        <div>
            <label for="board_id">Board ID:</label>
            <input type="text" id="board_id" name="board_id">
        </div>
        <div>
            <label for="board_name">Board name:</label>
            <input type="text" id="board_name" name="board_name">
        </div>
        <input type="submit" id="submit" name="submit">
    </form>
    <div>
        <div class="list">
        <table id="boards_table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Creator name</th>
                    <th>No. threads</th>
                    <th>No. comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($boardsWithStats as $board): ?>
                <tr>
                    <td><?= \pib\Security::escapeHtml($board['Id']) ?></td>
                    <td><?= \pib\Security::escapeHtml($board['FullName']) ?></td>
                    <td><?= \pib\Security::escapeHtml($board['CreatorNickname']) ?></td>
                    <td><?= (int)$board['ThreadCount'] ?></td>
                    <td><?= (int)$board['CommentCount'] ?></td>
                    <td>
                        <form method="post" action="pib/form_actions/delete_board.php">
                            <?php echo \pib\CSRF::getTokenField(); ?>
                            <input type="hidden" name="board_id" value="<?= \pib\Security::escapeHtml($board["Id"]) ?>">
                            <input type="submit" value="Delete">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
    $bans = new Ban($conn);
    $givenBans = $bans->getBans();
    ?>
    <h1>Bans</h1>
    <h2>Add new ban: <span id="ban_form_status" class="caret">▶</span></h2>
    <form action="pib/form_actions/insert_ban.php" method="post" hidden="hidden" id="ban_form">
        <?php echo \pib\CSRF::getTokenField(); ?>
        <div>
            <label for="account">Account (optional - leave empty for IP ban only)</label>
            <select name="account" id="account">
                <option value="">-- No account (IP ban only) --</option>
                <?php foreach ($all_users as $user): ?>
                    <option value="<?= (int)$user['Id'] ?>"><?= \pib\Security::escapeHtml($user['Nickname']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="ip_address">IP Address (optional - leave empty for account ban only)</label>
            <input type="text" name="ip_address" id="ip_address" placeholder="e.g., 192.168.1.1">
        </div>
        <div>
            <label for="startDate">Start Date:</label>
            <input type="datetime-local" id="startDate" name="startDate" required>
        </div>
        <div>
            <label for="endDate">End Date:</label>
            <input type="datetime-local" id="endDate" name="endDate" required>
        </div>
        <input type="submit" id="submit" name="submit" value="Create Ban">
    </form>
    <div>
        <div class="list">
            <table id="bans_table">
                <thead>
                    <tr>
                        <th>Account/IP</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($givenBans as $ban): ?>
                    <tr>
                        <td><?= \pib\Security::escapeHtml($ban['Nickname'] ?? $ban['IpAddress']) ?></td>
                        <td><?= \pib\Security::escapeHtml($ban['StartDate']) ?></td>
                        <td><?= \pib\Security::escapeHtml($ban['EndDate']) ?></td>
                        <td>
                            <form method="post" action="pib/form_actions/delete_ban.php">
                                <?php echo \pib\CSRF::getTokenField(); ?>
                                <input type="hidden" name="ban_id" value="<?= (int)$ban["Id"] ?>">
                                <input type="submit" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
</body>
<script>
    const arrowBoard = document.getElementById("board_form_status");
    arrowBoard.addEventListener("click", (e) => {
        arrowBoard.classList.toggle("down")
        const boardForm = document.getElementById("board_form");
        boardForm.hidden = !boardForm.hidden;
    })
    const arrowBan = document.getElementById("ban_form_status");
    const banForm = document.getElementById("ban_form");

    arrowBan.addEventListener("click", (e) => {
        arrowBan.classList.toggle("down")
        banForm.hidden = !banForm.hidden;
    })


    function filterTable(inputElement, tableElement, columnIndices) {
        const searchValue = inputElement.value.toLowerCase();
        const tbody = tableElement.querySelector('tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            let match = false;
            columnIndices.forEach(index => {
                const cell = row.cells[index];
                if (cell && cell.textContent.toLowerCase().includes(searchValue)) {
                    match = true;
                }
            });

            row.style.display = match ? '' : 'none';
        });
    }

    // Real-time filter function for select dropdowns
    function filterSelect(inputElement, selectElement) {
        const searchValue = inputElement.value.toLowerCase();
        const options = selectElement.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') return; // Skip empty option
            const text = option.textContent.toLowerCase();
            if (text.includes(searchValue)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    }

    const staffSearchInput = document.getElementById('staff_search');
    const staffTable = document.getElementById('staff_table');
    if (staffSearchInput && staffTable) {
        staffSearchInput.addEventListener('input', () => {
            filterTable(staffSearchInput, staffTable, [0, 1]);        });
    }

    const allUserSearchInput = document.getElementById('all_user_search');
    const allUsersTable = document.getElementById('all_users_table');
    if (allUserSearchInput && allUsersTable) {
        allUserSearchInput.addEventListener('input', () => {
            filterTable(allUserSearchInput, allUsersTable, [0, 1]); 
        });
    }

    document.querySelectorAll('.promote-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const userNickname = form.dataset.userNickname;

            <?php if (get_role($conn) === "Founder"): ?>
            const role = prompt(`Promote ${userNickname} to which role?\n\nEnter:\n- "Founder" for Founder\n- "Admin" for Administrator\n- "Mod" for Moderator`, 'Mod');
            <?php else: ?>
            const role = prompt(`Promote ${userNickname} to Moderator?\n\nEnter "Mod" to confirm:`, 'Mod');
            <?php endif; ?>

            if (!role) return;

            const validRoles = <?= get_role($conn) === "Founder" ? '["Founder", "Admin", "Mod"]' : '["Mod"]' ?>;
            if (!validRoles.includes(role)) {
                alert('Invalid role selected');
                return;
            }

            form.querySelector('.role-input').value = role;
            form.submit();
        });
    });

    banForm.addEventListener("submit", (e) => {
        function validateIpAddress(ipaddress) {
            if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) {
                return true
            }
            alert("You have entered an invalid IP address!")
            return false
        }

        const ipAddressField = document.getElementById("ip_address");
        const accountField = document.getElementById("account");

        if (!accountField.value && !ipAddressField.value) {
            e.preventDefault();
            alert("You must provide either an account or an IP address to ban");
            return;
        }

        if (ipAddressField.value && !validateIpAddress(ipAddressField.value)) {
            e.preventDefault();
        }
    })
</script>
</html>
