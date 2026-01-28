<?php
require_once __DIR__ . '/../includes/init.php';

// Admin protection
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
.admin-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
    background: linear-gradient(180deg, #4B2FDB, #6C4DFF);
    color: #fff;
    padding: 30px 20px;
}

.sidebar h2 {
    margin-bottom: 40px;
    font-size: 22px;
}

.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 8px;
    font-size: 14px;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.15);
}

/* MAIN */
.admin-main {
    padding: 40px;
    background: #F9FAFB;
}

.admin-header {
    margin-bottom: 40px;
}

.admin-header h1 {
    font-size: 30px;
}

.admin-header span {
    background: var(--gradient-main);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* METRICS */
.metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.metric-card {
    background: #fff;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.metric-card h3 {
    font-size: 28px;
    margin-bottom: 6px;
}

.metric-card p {
    color: var(--text-muted);
    font-size: 14px;
}

/* ACTIONS */
.admin-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
}

.action-box {
    background: #fff;
    border-radius: 18px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.action-box h3 {
    margin-bottom: 10px;
}

.action-box p {
    color: var(--text-muted);
    font-size: 14px;
    margin-bottom: 20px;
}
</style>
</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
        <a href="#">Manage Items</a>
        <a href="#">Manage Users</a>
        <a href="#">Reports</a>
        <a href="#">Settings</a>
        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <div class="admin-header">
            <h1>
                Welcome,
                <span><?= htmlspecialchars($_SESSION['name']) ?></span>
            </h1>
            <p style="color:var(--text-muted);">
                Overview and control of the Lost & Found system.
            </p>
        </div>

        <!-- METRICS -->
        <div class="metrics">
            <div class="metric-card">
                <h3>0</h3>
                <p>Total Items</p>
            </div>
            <div class="metric-card">
                <h3>0</h3>
                <p>Pending Approvals</p>
            </div>
            <div class="metric-card">
                <h3>0</h3>
                <p>Active Users</p>
            </div>
            <div class="metric-card">
                <h3>0%</h3>
                <p>Success Rate</p>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="admin-actions">
            <div class="action-box">
                <h3>Approve Items</h3>
                <p>
                    Review newly reported lost and found items before publishing.
                </p>
                <a class="btn-primary" href="#">Go to Approvals</a>
            </div>

            <div class="action-box">
                <h3>User Management</h3>
                <p>
                    View users, suspend accounts, and manage permissions.
                </p>
                <a class="btn-primary" href="#">Manage Users</a>
            </div>

            <div class="action-box">
                <h3>Reports & Analytics</h3>
                <p>
                    View monthly statistics and system performance reports.
                </p>
                <a class="btn-primary" href="#">View Reports</a>
            </div>
        </div>

    </main>

</div>

</body>
</html>
