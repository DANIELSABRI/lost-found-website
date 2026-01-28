<?php
session_start();
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// Mark as read
if (isset($_GET['read'])) {
    mark_notification_read($pdo, (int)$_GET['read'], $userId);
    header('Location: notifications.php');
    exit;
}

// Fetch notifications
$stmt = $pdo->prepare(
    "SELECT * FROM notifications 
     WHERE (user_id = ? OR user_id IS NULL) 
       AND role = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$userId, $role]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{margin:0;font-family:system-ui;background:#f4f6ff}
.layout{display:flex;min-height:100vh}
.sidebar{
    width:260px;
    background:linear-gradient(180deg,#5b3df5,#7b6cff);
    color:#fff;padding:24px
}
.menu a{display:block;padding:12px;border-radius:10px;color:#fff;text-decoration:none;margin-bottom:8px}
.menu a.active,.menu a:hover{background:rgba(255,255,255,.18)}
.main{flex:1;padding:36px}
.card{
    background:#fff;padding:24px;border-radius:16px;
    box-shadow:0 14px 35px rgba(0,0,0,.06);
    margin-bottom:16px
}
.unread{border-left:6px solid #6b5cff}
small{color:#777}
</style>
</head>

<body>
<div class="layout">
<aside class="sidebar">
    <h2>Lost & Found</h2>
    <nav class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="notifications.php">Notifications</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<main class="main">
<h2>Notifications</h2>

<?php if (empty($notifications)): ?>
<p>No notifications yet.</p>
<?php endif; ?>

<?php foreach ($notifications as $n): ?>
<div class="card <?= !$n['is_read'] ? 'unread' : '' ?>">
    <h4><?= htmlspecialchars($n['title']) ?></h4>
    <p><?= htmlspecialchars($n['message']) ?></p>
    <small><?= $n['created_at'] ?></small><br>
    <?php if (!$n['is_read']): ?>
        <a href="?read=<?= $n['id'] ?>">Mark as read</a>
    <?php endif; ?>
</div>
<?php endforeach; ?>

</main>
</div>
</body>
</html>
