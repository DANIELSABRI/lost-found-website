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
<title>Search Items | Lost & Found</title>
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

/* SEARCH UI */
.search-box {
    background: #fff;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.08);
    display: grid;
    grid-template-columns: 1fr 160px;
    gap: 15px;
    margin-bottom: 30px;
}

.filters {
    background: #fff;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.08);
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.results {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
}

.result-card {
    background: #fff;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
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
        <a class="active" href="<?= BASE_URL ?>/search.php">Search Items</a>
        <a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/user/chat.php">Messages</a>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);margin:20px 0;">
        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="header">
            <h1>Search <span>Lost & Found Items</span></h1>
            <p style="color:var(--text-muted);">
                Quickly find items reported across the campus.
            </p>
        </div>

        <!-- SEARCH BAR -->
        <div class="search-box">
            <input type="text" placeholder="Search by item name, description, or location">
            <button class="btn-primary">Search</button>
        </div>

        <!-- FILTERS -->
        <div class="filters">
            <select>
                <option>All Categories</option>
                <option>Electronics</option>
                <option>Documents</option>
                <option>Accessories</option>
                <option>Books</option>
            </select>

            <select>
                <option>All Status</option>
                <option>Lost</option>
                <option>Found</option>
                <option>Claimed</option>
            </select>

            <input type="date">
            <input type="text" placeholder="Location">
        </div>

        <!-- RESULTS -->
        <div class="results">
            <div class="result-card">
                <h3>Black Wallet</h3>
                <p style="color:var(--text-muted);font-size:14px;">
                    Lost • Library • 12 Jan 2026
                </p>
                <a href="<?= BASE_URL ?>/item.php" class="action-link">View Details →</a>
            </div>

            <div class="result-card">
                <h3>Blue Backpack</h3>
                <p style="color:var(--text-muted);font-size:14px;">
                    Found • Cafeteria • 10 Jan 2026
                </p>
                <a href="<?= BASE_URL ?>/item.php" class="action-link">View Details →</a>
            </div>
        </div>

    </main>

</div>

</body>
</html>
