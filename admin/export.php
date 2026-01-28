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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Export Reports | Admin</title>
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
.sub{color:#666;margin-bottom:28px}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:24px;
}

.card{
    background:#fff;
    padding:26px;
    border-radius:20px;
    box-shadow:0 14px 35px rgba(0,0,0,.06);
}

.card h3{
    margin-top:0;
}

.card p{
    color:#666;
}

.card a{
    display:inline-block;
    margin-top:14px;
    padding:10px 18px;
    background:#6b5cff;
    color:#fff;
    border-radius:999px;
    text-decoration:none;
    font-weight:600;
}
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
        <a href="reports.php">Reports & Analytics</a>
        <a class="active" href="export.php">Export Reports</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">

<h1>Export Reports</h1>
<div class="sub">Download system data in CSV format</div>

<div class="grid">

    <div class="card">
        <h3>Export Users</h3>
        <p>Download all registered users with roles and status.</p>
        <a href="export_users.php">Download CSV</a>
    </div>

    <div class="card">
        <h3>Export Items</h3>
        <p>Download all lost & found items with status.</p>
        <a href="export_items.php">Download CSV</a>
    </div>

</div>

</main>
</div>

</body>
</html>
