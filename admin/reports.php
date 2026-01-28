<?php
session_start();
require_once __DIR__ . '/../includes/init.php';

/* ===============================
   ADMIN ACCESS CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

/* ===============================
   REPORT METRICS
================================ */

// USERS
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$suspendedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='suspended'")->fetchColumn();

// ITEMS
$totalItems = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$lostItems = $pdo->query("SELECT COUNT(*) FROM items WHERE type='lost'")->fetchColumn();
$foundItems = $pdo->query("SELECT COUNT(*) FROM items WHERE type='found'")->fetchColumn();

$approvedItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='approved'")->fetchColumn();
$pendingItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='pending'")->fetchColumn();

// RECENT (last 7 days)
$recentItems = $pdo->query("
    SELECT COUNT(*) 
    FROM items 
    WHERE created_at >= NOW() - INTERVAL 7 DAY
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports & Analytics | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:system-ui,-apple-system,sans-serif;
    background:#f4f6ff;
}
.layout{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{
    width:280px;
    background:linear-gradient(180deg,#4f2cf5,#7c6cff);
    color:#fff;
    padding:26px;
}
.brand{
    font-size:22px;
    font-weight:800;
    margin-bottom:36px;
}
.menu a{
    display:block;
    padding:13px 18px;
    border-radius:12px;
    color:#fff;
    text-decoration:none;
    margin-bottom:10px;
}
.menu a.active,
.menu a:hover{
    background:rgba(255,255,255,.18);
}

/* MAIN */
.main{flex:1;padding:36px}
h1{margin-top:0}
.sub{color:#666;margin-bottom:24px}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:22px;
}

/* CARD */
.card{
    background:#fff;
    padding:24px;
    border-radius:18px;
    box-shadow:0 14px 35px rgba(0,0,0,.06);
}
.card h2{
    margin:0;
    font-size:32px;
}
.card p{
    margin:6px 0 0;
    color:#555;
}
.small{
    margin-top:12px;
    font-size:14px;
    color:#777;
}

/* SECTION */
.section{
    margin-top:40px;
}
.section h3{
    margin-bottom:16px;
}
.highlight{
    background:linear-gradient(135deg,#6b5cff,#8f7bff);
    color:#fff;
}
.highlight p{color:#eee}
</style>
</head>

<body>

<div class="layout">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="brand">Lost & Found</div>
    <nav class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="items.php">Manage Items</a>
        <a href="users.php">Manage Users</a>
        <a class="active" href="reports.php">Reports & Analytics</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">

<h1>Reports & Analytics</h1>
<div class="sub">System overview and performance insights</div>

<!-- USERS -->
<section class="section">
<h3>User Statistics</h3>
<div class="grid">
    <div class="card">
        <h2><?= $totalUsers ?></h2>
        <p>Total Users</p>
    </div>
    <div class="card">
        <h2><?= $activeUsers ?></h2>
        <p>Active Users</p>
    </div>
    <div class="card">
        <h2><?= $suspendedUsers ?></h2>
        <p>Suspended Users</p>
    </div>
</div>
</section>

<!-- ITEMS -->
<section class="section">
<h3>Item Statistics</h3>
<div class="grid">
    <div class="card">
        <h2><?= $totalItems ?></h2>
        <p>Total Items</p>
    </div>
    <div class="card">
        <h2><?= $lostItems ?></h2>
        <p>Lost Items</p>
    </div>
    <div class="card">
        <h2><?= $foundItems ?></h2>
        <p>Found Items</p>
    </div>
    <div class="card">
        <h2><?= $approvedItems ?></h2>
        <p>Approved Items</p>
    </div>
    <div class="card">
        <h2><?= $pendingItems ?></h2>
        <p>Pending Approval</p>
    </div>
</div>
</section>

<!-- HIGHLIGHT -->
<section class="section">
<div class="grid">
    <div class="card highlight">
        <h2><?= $recentItems ?></h2>
        <p>Items Reported in Last 7 Days</p>
        <div class="small">Shows system activity & engagement</div>
    </div>
</div>
</section>

</main>
</div>

</body>
</html>
