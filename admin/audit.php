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
   FETCH LOGS (LATEST FIRST)
================================ */
$stmt = $pdo->query(
    "SELECT a.*, u.name 
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.created_at DESC
     LIMIT 500"
);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Logs | Admin</title>
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

table{
    width:100%;
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 14px 35px rgba(0,0,0,.06);
}
th,td{
    padding:14px;
    text-align:left;
    font-size:14px;
}
th{
    background:#f0edff;
}
tr:not(:last-child) td{
    border-bottom:1px solid #eee;
}

.role{
    padding:4px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}
.admin{background:#e0d7ff;color:#4f2cf5}
.user{background:#e6f7ff;color:#0056b3}

.empty{
    background:#fff;
    padding:40px;
    border-radius:16px;
    text-align:center;
    color:#777;
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
        <a href="reports.php">Reports</a>
        <a href="charts.php">Charts</a>
        <a class="active" href="audit.php">Audit Logs</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">

<h1>Audit Logs</h1>
<p>System activity tracking (latest 500 actions)</p>

<?php if (count($logs) === 0): ?>
    <div class="empty">No activity recorded yet.</div>
<?php else: ?>
<table>
    <tr>
        <th>User</th>
        <th>Role</th>
        <th>Action</th>
        <th>IP Address</th>
        <th>Date & Time</th>
    </tr>

<?php foreach ($logs as $log): ?>
<tr>
    <td><?= htmlspecialchars($log['name'] ?? 'Unknown') ?></td>
    <td>
        <span class="role <?= $log['user_role'] ?>">
            <?= ucfirst($log['user_role']) ?>
        </span>
    </td>
    <td><?= htmlspecialchars($log['action']) ?></td>
    <td><?= htmlspecialchars($log['ip_address']) ?></td>
    <td><?= htmlspecialchars($log['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</main>
</div>

</body>
</html>
