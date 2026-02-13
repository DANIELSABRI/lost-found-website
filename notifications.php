<?php
require_once __DIR__ . '/includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
.layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: #F9FAFB;
}

/* SIDEBAR */
.sidebar {
    background: linear-gradient(180deg, #4B2FDB, #6C4DFF);
    color: #fff;
    padding: 30px 20px;
}

.sidebar h2 {
    font-size: 22px;
    margin-bottom: 40px;
}

.sidebar a {
    display: block;
    color: #fff;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    font-size: 14px;
    text-decoration: none;
}

.sidebar a.active,
.sidebar a:hover {
    background: rgba(255,255,255,0.18);
}

/* MAIN */
.main {
    padding: 40px;
}

.header {
    margin-bottom: 30px;
}

.header h1 span {
    background: var(--gradient-main);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* NOTIFICATIONS */
.notification {
    background: #fff;
    border-radius: 18px;
    padding: 22px 26px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    margin-bottom: 18px;
}

.notification.unread {
    border-left: 5px solid var(--purple-main);
}
</style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>Lost & Found</h2>

        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost Item</a>
        <a href="<?= BASE_URL ?>/user/report-found.php">Report Found Item</a>
        <a href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>
        <a href="<?= BASE_URL ?>/search.php">Search Items</a>
        <a class="active" href="<?= BASE_URL ?>/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/user/chat.php">Messages</a>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);margin:20px 0;">
        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="header">
            <h1>Your <span>Notifications</span></h1>
            <p style="color:var(--text-muted);">
                Updates related to your lost and found activity.
            </p>
        </div>

        <div class="notification unread">
            <div>
                <strong>Match Found</strong>
                <p style="color:var(--text-muted);font-size:14px;">
                    A found item matches your lost item “Black Wallet”.
                </p>
                <a href="<?= BASE_URL ?>/item.php">View Item</a>
            </div>
            <small>2 mins ago</small>
        </div>

        <div class="notification">
            <div>
                <strong>New Message</strong>
                <p style="color:var(--text-muted);font-size:14px;">
                    You received a message regarding “Blue Backpack”.
                </p>
                <a href="<?= BASE_URL ?>/user/chat.php">Open Chat</a>
            </div>
            <small>1 hour ago</small>
        </div>

    </main>

</div>

</body>
</html>
