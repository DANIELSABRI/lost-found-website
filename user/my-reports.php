<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Reports | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
/* LAYOUT */
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

/* TABLE */
.table-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.08);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 16px 20px;
    text-align: left;
    font-size: 14px;
}

th {
    background: #F9FAFB;
    color: var(--text-muted);
    font-weight: 600;
}

tr:not(:last-child) {
    border-bottom: 1px solid var(--border-soft);
}

/* BADGES */
.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge.lost {
    background: #FEE2E2;
    color: #991B1B;
}

.badge.found {
    background: #DCFCE7;
    color: #065F46;
}

.badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.badge.matched {
    background: #E0E7FF;
    color: #3730A3;
}

/* ACTIONS */
.action-link {
    color: var(--purple-main);
    font-weight: 600;
    text-decoration: none;
    margin-right: 12px;
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
        <a class="active" href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>
        <a href="<?= BASE_URL ?>/search.php">Search Items</a>
        <a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/user/chat.php">Messages</a>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);margin:20px 0;">

        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="header">
            <h1>
                My <span>Reports</span>
            </h1>
            <p style="color:var(--text-muted);">
                View and manage all lost and found items you have reported.
            </p>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- SAMPLE ROW -->
                    <tr>
                        <td>Black Wallet</td>
                        <td><span class="badge lost">Lost</span></td>
                        <td>Library</td>
                        <td>12 Jan 2026</td>
                        <td><span class="badge matched">Matched</span></td>
                        <td>
                            <a class="action-link" href="<?= BASE_URL ?>/item.php">View</a>
                            <a class="action-link" href="<?= BASE_URL ?>/user/chat.php">Chat</a>
                        </td>
                    </tr>

                    <tr>
                        <td>Blue Backpack</td>
                        <td><span class="badge found">Found</span></td>
                        <td>Cafeteria</td>
                        <td>10 Jan 2026</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td>
                            <a class="action-link" href="<?= BASE_URL ?>/item.php">View</a>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
