<?php
require_once __DIR__ . '/../includes/init.php';

// Admin protection
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Fetch Real Statistics
try {
    $statusStats = $pdo->query("SELECT status, COUNT(*) as count FROM items GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    $typeStats = $pdo->query("SELECT type, COUNT(*) as count FROM items GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);

    $trendData = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %d') as day, COUNT(*) as count 
        FROM items 
        WHERE created_at >= NOW() - INTERVAL 7 DAY 
        GROUP BY DATE(created_at) 
        ORDER BY created_at ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $lostCount = $typeStats['lost'] ?? 0;
    $foundCount = $typeStats['found'] ?? 0;
    $totalReported = array_sum($typeStats);
    $matchedCount = $statusStats['matched'] ?? 0;
    $claimedCount = $statusStats['claimed'] ?? 0;
    $pendingCount = $statusStats['pending'] ?? 0;
    $approvedCount = $statusStats['approved'] ?? 0;
    
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $successRate = ($totalReported > 0) ? round((($matchedCount + $claimedCount) / $totalReported) * 100, 1) : 0;

} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Intelligence | Command Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .admin-hero {
        background: var(--grad-dark);
        color: #fff;
        padding: 80px 60px;
        border-radius: var(--radius-3xl);
        margin-bottom: 50px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-xl);
    }
    .admin-hero h1 { font-size: 48px; font-weight: 800; line-height: 1.1; margin-bottom: 15px; letter-spacing: -1px; }
    .admin-hero p { font-size: 18px; opacity: 0.7; max-width: 600px; }
    .hero-icon { position: absolute; right: -40px; top: -40px; font-size: 300px; opacity: 0.05; transform: rotate(-15deg); }

    .stat-grid-top { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 50px; }
    
    .dashboard-split { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: start; }
    .glass-chart-card {
        background: #fff;
        padding: 40px;
        border-radius: var(--radius-2xl);
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(0,0,0,0.02);
    }
    .glass-chart-card h3 { font-size: 20px; font-weight: 800; margin-bottom: 30px; letter-spacing: -0.5px; }

    .mini-stat-box {
        background: #fff;
        padding: 25px;
        border-radius: var(--radius-xl);
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 5px;
        transition: 0.3s;
    }
    .mini-stat-box:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
    .mini-stat-box .val { font-size: 32px; font-weight: 800; color: var(--color-text-main); }
    .mini-stat-box .lbl { font-size: 12px; font-weight: 700; color: var(--color-text-light); text-transform: uppercase; letter-spacing: 1px; }

    .action-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 60px;
    }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found — <span style="color: var(--color-primary);">Admin Intelligence</span></div>
    <div class="nav-links">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="manage-items.php">Items</a>
        <a href="manage-users.php">Users</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="admin-hero">
        <div class="hero-icon">📡</div>
        <h1>Command Intelligence Hub</h1>
        <p>Operational overview of campus recovery protocols and system health metrics.</p>
    </div>

    <!-- ELITE TOP STATS -->
    <div class="stat-grid-top">
        <div class="mini-stat-box">
            <span class="val"><?= number_format($totalReported) ?></span>
            <span class="lbl">Intelligence Files</span>
        </div>
        <div class="mini-stat-box">
            <span class="val" style="color: var(--color-primary);"><?= number_format($pendingCount) ?></span>
            <span class="lbl">Pending Review</span>
        </div>
        <div class="mini-stat-box">
            <span class="val" style="color: #10b981;"><?= number_format($claimedCount) ?></span>
            <span class="lbl">Verified Recoveries</span>
        </div>
        <div class="mini-stat-box" style="background: var(--grad-primary); border: none;">
            <span class="val" style="color: #fff;"><?= $successRate ?>%</span>
            <span class="lbl" style="color: rgba(255,255,255,0.7);">System Match Rate</span>
        </div>
    </div>

    <div class="dashboard-split">
        <div class="glass-chart-card">
            <h3>Recovery Activity Timeline</h3>
            <canvas id="trendChart" height="280"></canvas>
        </div>

        <div style="display: flex; flex-direction: column; gap: 30px;">
            <div class="glass-chart-card" style="padding: 30px;">
                <h3 style="font-size: 16px; margin-bottom: 20px;">Protocol Distribution</h3>
                <canvas id="statusChart" height="200"></canvas>
            </div>
            
            <div class="mini-stat-box" style="padding: 30px; text-align: center;">
                <span class="val" style="font-size: 40px;">👥</span>
                <div class="val"><?= number_format($userCount) ?></div>
                <div class="lbl">Verified Field Agents (Users)</div>
                <a href="manage-users.php" class="btn-primary" style="margin-top: 20px; padding: 12px; font-size: 13px; width: 100%;">Audit User Accounts</a>
            </div>
        </div>
    </div>

    <div class="action-strip">
        <a href="manage-items.php" class="action-card">
            <div class="action-icon-wrap" style="background: #eef2ff; color: #6366f1;">📦</div>
            <div>
                <strong style="display: block; font-size: 14px; color: var(--color-text-main);">Manage Items</strong>
                <span style="font-size: 11px; color: var(--color-text-muted);">Audits & Approvals</span>
            </div>
        </a>
        <a href="reports.php" class="action-card">
            <div class="action-icon-wrap" style="background: #ecfdf5; color: #10b981;">📊</div>
            <div>
                <strong style="display: block; font-size: 14px; color: var(--color-text-main);">System Reports</strong>
                <span style="font-size: 11px; color: var(--color-text-muted);">Data Export Hub</span>
            </div>
        </a>
        <a href="settings.php" class="action-card">
            <div class="action-icon-wrap" style="background: #fffbeb; color: #f59e0b;">⚙️</div>
            <div>
                <strong style="display: block; font-size: 14px; color: var(--color-text-main);">Global Settings</strong>
                <span style="font-size: 11px; color: var(--color-text-muted);">System Parameters</span>
            </div>
        </a>
        <a href="logs.php" class="action-card">
            <div class="action-icon-wrap" style="background: #fff1f2; color: #e11d48;">📜</div>
            <div>
                <strong style="display: block; font-size: 14px; color: var(--color-text-main);">Audit Logs</strong>
                <span style="font-size: 11px; color: var(--color-text-muted);">Operation Tracking</span>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
const ctxTrend = document.getElementById('trendChart').getContext('2d');
new Chart(ctxTrend, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($trendData)) ?>,
        datasets: [{
            label: 'Incident Reports',
            data: <?= json_encode(array_values($trendData)) ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.05)',
            fill: true,
            tension: 0.4,
            borderWidth: 4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#6366f1',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' } },
            x: { grid: { display: false } }
        }
    }
});

const ctxStatus = document.getElementById('statusChart').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($statusStats)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusStats)) ?>,
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#1e293b', '#64748b'],
            borderWidth: 0,
            hoverOffset: 15
        }]
    },
    options: {
        cutout: '75%',
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Poppins' }, padding: 20 } }
        }
    }
});
</script>
</body>
</html>
