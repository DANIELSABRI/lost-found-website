<?php
session_start();
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

$userName = $_SESSION['user_name'];

// Stats (safe defaults)
$lostCount = 0;
$foundCount = 0;
$matchCount = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:system-ui,-apple-system,sans-serif;
    background:#f5f6ff;
}
.layout{
    display:flex;
    min-height:100vh;
}
.sidebar{
    width:260px;
    background:linear-gradient(180deg,#5b3df5,#7b6cff);
    color:#fff;
    padding:24px;
}
.brand{
    font-size:22px;
    font-weight:700;
    margin-bottom:32px;
}
.menu a{
    display:block;
    padding:12px 16px;
    border-radius:10px;
    color:#fff;
    text-decoration:none;
    margin-bottom:8px;
    opacity:.9;
}
.menu a.active,
.menu a:hover{
    background:rgba(255,255,255,.18);
}
.menu .logout{
    margin-top:32px;
    opacity:.8;
}
.main{
    flex:1;
    padding:32px;
}
.header h1{
    margin:0;
    font-size:32px;
}
.header span{
    color:#6b5cff;
}
.subtitle{
    color:#666;
    margin-top:6px;
}
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-top:32px;
}
.stat{
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
}
.stat h2{
    margin:0;
    font-size:34px;
}
.stat p{
    margin:6px 0 0;
    color:#555;
}
.actions{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:24px;
    margin-top:40px;
}
.action{
    background:linear-gradient(135deg,#6b5cff,#8c7bff);
    color:#fff;
    padding:28px;
    border-radius:20px;
}
.action h3{
    margin-top:0;
}
.action p{
    opacity:.9;
}
.action a{
    display:inline-block;
    margin-top:16px;
    padding:10px 20px;
    background:#fff;
    color:#6b5cff;
    border-radius:999px;
    font-weight:600;
    text-decoration:none;
}
.recent{
    margin-top:48px;
}
.recent h3{
    margin-bottom:16px;
}
.activity{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
    color:#666;
}
</style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">Lost & Found</div>

        <nav class="menu">
            <a class="active" href="dashboard.php">Dashboard</a>
            <a href="report-lost.php">Report Lost Item</a>
            <a href="report-found.php">Report Found Item</a>
            <a href="search.php">Search Items</a>
            <a href="notifications.php">Notifications</a>
            <a href="chat.php">Messages</a>

            <a class="logout" href="../auth/logout.php">Logout</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">

        <div class="header">
            <h1>Welcome back, <span><?= htmlspecialchars($userName) ?></span></h1>
            <div class="subtitle">University Lost & Found Management Dashboard</div>
        </div>

        <!-- STATS -->
        <section class="stats">
            <div class="stat">
                <h2><?= $lostCount ?></h2>
                <p>Lost Items Reported</p>
            </div>
            <div class="stat">
                <h2><?= $foundCount ?></h2>
                <p>Found Items Reported</p>
            </div>
            <div class="stat">
                <h2><?= $matchCount ?></h2>
                <p>Items Matched</p>
            </div>
        </section>

        <!-- QUICK ACTIONS -->
        <section class="actions">
            <div class="action">
                <h3>Report Lost Item</h3>
                <p>Lost something on campus? Submit details and let the system find matches.</p>
                <a href="report-lost.php">Report Lost</a>
            </div>
            <div class="action">
                <h3>Report Found Item</h3>
                <p>Found an item? Help return it to the rightful owner.</p>
                <a href="report-found.php">Report Found</a>
            </div>
        </section>

        <!-- RECENT ACTIVITY -->
        <section class="recent">
            <h3>Recent Activity</h3>
            <div class="activity">
                No recent activity yet. Your reported items and messages will appear here.
            </div>
        </section>

    </main>
</div>

</body>
</html>
