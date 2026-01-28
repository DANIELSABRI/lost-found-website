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
   MONTHLY LOST vs FOUND (LAST 6 MONTHS)
================================ */
$months = [];
$lostData = [];
$foundData = [];

for ($i = 5; $i >= 0; $i--) {
    $label = date('M Y', strtotime("-$i months"));
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd   = date('Y-m-t', strtotime("-$i months"));

    $months[] = $label;

    $lostStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM items 
         WHERE type='lost' AND created_at BETWEEN ? AND ?"
    );
    $lostStmt->execute([$monthStart, $monthEnd]);
    $lostData[] = (int)$lostStmt->fetchColumn();

    $foundStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM items 
         WHERE type='found' AND created_at BETWEEN ? AND ?"
    );
    $foundStmt->execute([$monthStart, $monthEnd]);
    $foundData[] = (int)$foundStmt->fetchColumn();
}

/* ===============================
   ITEM STATUS DISTRIBUTION
================================ */
$approved = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='approved'")->fetchColumn();
$pending  = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='pending'")->fetchColumn();
$rejected = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='rejected'")->fetchColumn();

/* ===============================
   USER ROLES
================================ */
$adminCount   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$studentCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role!='admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Charts & Trends | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:28px;
}

.card{
    background:#fff;
    padding:26px;
    border-radius:20px;
    box-shadow:0 14px 35px rgba(0,0,0,.06);
}
.card h3{
    margin-top:0;
    margin-bottom:18px;
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
        <a class="active" href="charts.php">Charts & Trends</a>
        <a href="export.php">Export Reports</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">

<h1>Charts & Trends</h1>
<div class="sub">Visual insights for system performance</div>

<div class="grid">

    <!-- LINE CHART -->
    <div class="card">
        <h3>Lost vs Found Items (Last 6 Months)</h3>
        <canvas id="lostFoundChart"></canvas>
    </div>

    <!-- PIE CHART -->
    <div class="card">
        <h3>Item Status Distribution</h3>
        <canvas id="statusChart"></canvas>
    </div>

    <!-- BAR CHART -->
    <div class="card">
        <h3>User Roles</h3>
        <canvas id="userChart"></canvas>
    </div>

</div>

</main>
</div>

<script>
/* LOST vs FOUND */
new Chart(document.getElementById('lostFoundChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [
            {
                label: 'Lost',
                data: <?= json_encode($lostData) ?>,
                borderWidth: 3,
                tension: 0.3
            },
            {
                label: 'Found',
                data: <?= json_encode($foundData) ?>,
                borderWidth: 3,
                tension: 0.3
            }
        ]
    }
});

/* STATUS PIE */
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: <?= json_encode([$approved, $pending, $rejected]) ?>
        }]
    }
});

/* USER BAR */
new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: ['Admins', 'Users'],
        datasets: [{
            data: <?= json_encode([$adminCount, $studentCount]) ?>
        }]
    }
});
</script>

</body>
</html>
