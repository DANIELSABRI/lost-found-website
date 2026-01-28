<?php
require_once __DIR__ . '/../includes/init.php';

// Admin protection
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

/* ===============================
   REPORT METRICS
   =============================== */

// USERS
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$suspendedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='suspended'")->fetchColumn();

// ITEMS
$totalItems = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$lostItems = $pdo->query("SELECT COUNT(*) FROM items WHERE type='lost'")->fetchColumn();
$foundItems = $pdo->query("SELECT COUNT(*) FROM items WHERE type='found'")->fetchColumn();

// ITEM STATUSES
$matchedItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='matched'")->fetchColumn();
$claimedItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='claimed'")->fetchColumn();
$approvedItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='approved'")->fetchColumn();
$rejectedItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='rejected'")->fetchColumn();
$pendingItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status='pending'")->fetchColumn();

// RECENT (last 30 days)
$recentItems = $pdo->query("SELECT COUNT(*) FROM items WHERE created_at >= NOW() - INTERVAL 30 DAY")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Intelligence Reports | Command Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .report-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 60px; }
    .report-card-elite {
        background: #fff;
        padding: 40px;
        border-radius: var(--radius-3xl);
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: 0.3s;
    }
    .report-card-elite:hover { transform: translateY(-10px); box-shadow: var(--shadow-xl); }
    .report-card-elite h2 { font-size: 14px; font-weight: 800; color: var(--color-text-light); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px; }
    .report-card-elite .val { font-size: 48px; font-weight: 800; color: var(--color-text-main); margin-bottom: 5px; }
    .report-card-elite .lbl { font-size: 13px; font-weight: 600; color: var(--color-text-muted); }

    .hero-metric {
        background: var(--grad-dark);
        color: #fff;
        padding: 60px;
        border-radius: var(--radius-3xl);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 60px;
        position: relative;
        overflow: hidden;
    }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found — <span style="color: var(--color-primary);">Admin Intelligence</span></div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-items.php">Items</a>
        <a href="manage-users.php">Users</a>
        <a href="reports.php" class="active">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-intro">
        <h1>Intelligence Reports & Analytics</h1>
        <p>Strategic data insights and operational performance metrics for the campus network.</p>
    </div>

    <!-- HERO METRIC -->
    <div class="hero-metric">
        <div>
            <h1 style="font-size: 64px; font-weight: 800; margin-bottom: 10px;"><?= number_format($recentItems) ?></h1>
            <p style="font-size: 20px; font-weight: 700; opacity: 0.8;">New Intelligence Files in the last 30 days</p>
            <p style="font-size: 14px; opacity: 0.6; margin-top: 10px; max-width: 500px;">Our network is firing on all cylinders. Every new report increases our predictive recovery accuracy.</p>
        </div>
        <div style="font-size: 120px; opacity: 0.1;">📈</div>
    </div>

    <!-- USERS -->
    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 30px;">Agent Identities</h3>
    <div class="report-grid">
        <div class="report-card-elite">
            <h2>Total Verified</h2>
            <div class="val"><?= number_format($totalUsers) ?></div>
            <div class="lbl">Campus identities tracked</div>
        </div>
        <div class="report-card-elite">
            <h2>Active Ready</h2>
            <div class="val" style="color: #10b981;"><?= number_format($activeUsers) ?></div>
            <div class="lbl">Active session clearance</div>
        </div>
        <div class="report-card-elite" style="background: rgba(239, 68, 68, 0.05);">
            <h2 style="color: #ef4444;">Suspended</h2>
            <div class="val" style="color: #ef4444;"><?= number_format($suspendedUsers) ?></div>
            <div class="lbl">Sentry block active</div>
        </div>
    </div>

    <!-- ITEMS -->
    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 30px;">Intelligence Assets</h3>
    <div class="report-grid">
        <div class="report-card-elite">
            <h2>Total Files</h2>
            <div class="val"><?= number_format($totalItems) ?></div>
            <div class="lbl">Cumulative system reports</div>
        </div>
        <div class="report-card-elite">
            <h2>Lost Assets</h2>
            <div class="val" style="color: #6366f1;"><?= number_format($lostItems) ?></div>
            <div class="lbl">Recovery missions active</div>
        </div>
        <div class="report-card-elite">
            <h2>Found Assets</h2>
            <div class="val" style="color: #10b981;"><?= number_format($foundItems) ?></div>
            <div class="lbl">Awaiting identification</div>
        </div>
    </div>

    <!-- STATUSES -->
    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 30px;">Fulfillment Protocols</h3>
    <div class="report-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="report-card-elite">
            <h2>Matched</h2>
            <div class="val" style="font-size: 32px; color: #6366f1;"><?= number_format($matchedItems) ?></div>
            <div class="lbl">Pre-recovery verification</div>
        </div>
        <div class="report-card-elite">
            <h2>Claimed</h2>
            <div class="val" style="font-size: 32px; color: #10b981;"><?= number_format($claimedItems) ?></div>
            <div class="lbl">Mission accomplished</div>
        </div>
        <div class="report-card-elite">
            <h2>Approved</h2>
            <div class="val" style="font-size: 32px; color: #f59e0b;"><?= number_format($approvedItems) ?></div>
            <div class="lbl">Visible to network</div>
        </div>
        <div class="report-card-elite">
            <h2>Pending</h2>
            <div class="val" style="font-size: 32px; opacity: 0.5;"><?= number_format($pendingItems) ?></div>
            <div class="lbl">Investigation required</div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
