<?php
session_start();
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/notifications.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);

    if ($title && $message) {
        // Broadcast to all users
        notify($pdo, null, 'user', $title, $message);
        $success = 'Notification sent to all users.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Send Notification | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{margin:0;font-family:system-ui;background:#f4f6ff}
.layout{display:flex;min-height:100vh}
.sidebar{
    width:280px;
    background:linear-gradient(180deg,#4f2cf5,#7c6cff);
    color:#fff;padding:26px
}
.menu a{display:block;padding:12px;border-radius:10px;color:#fff;text-decoration:none;margin-bottom:8px}
.menu a.active,.menu a:hover{background:rgba(255,255,255,.18)}
.main{flex:1;padding:36px}
.card{
    background:#fff;padding:28px;border-radius:18px;
    max-width:520px;
    box-shadow:0 14px 35px rgba(0,0,0,.06)
}
input,textarea{
    width:100%;padding:12px;margin-bottom:16px;
    border-radius:10px;border:1px solid #ddd
}
button{
    padding:12px 22px;border:none;border-radius:999px;
    background:#6b5cff;color:#fff;font-weight:600
}
.success{color:green;margin-bottom:16px}
</style>
</head>

<body>
<div class="layout">
<aside class="sidebar">
    <h2>Lost & Found</h2>
    <nav class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="audit.php">Audit Logs</a>
        <a class="active" href="notify.php">Send Notification</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<main class="main">
<div class="card">
<h2>Broadcast Notification</h2>

<?php if ($success): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="title" placeholder="Title" required>
    <textarea name="message" rows="5" placeholder="Message" required></textarea>
    <button type="submit">Send</button>
</form>
</div>
</main>
</div>
</body>
</html>
