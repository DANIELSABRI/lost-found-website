<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Actions
if (isset($_GET['read_all'])) {
    mark_all_notifications_read($pdo, $userId);
    header('Location: notifications.php');
    exit;
}

if (isset($_GET['read'])) {
    mark_notification_read($pdo, (int)$_GET['read'], $userId);
    header('Location: notifications.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM notifications 
     WHERE (user_id = ? OR user_id IS NULL) 
     ORDER BY created_at DESC"
);
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Intelligence Pulse | Notifications</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .pulse-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .pulse-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
    }
    .pulse-feed {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .pulse-item {
        background: #fff;
        padding: 30px;
        border-radius: var(--radius-2xl);
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: var(--shadow-md);
        display: flex;
        gap: 25px;
        transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    .pulse-item:hover {
        transform: scale(1.02);
        box-shadow: var(--shadow-xl);
        border-color: var(--color-primary);
    }
    .pulse-item.unread {
        background: #FDFDFF;
        border-left: 5px solid var(--color-primary);
    }
    .pulse-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .icon-match { background: #ecfdf5; color: #10b981; }
    .icon-security { background: #fff1f2; color: #e11d48; }
    .icon-system { background: #eef2ff; color: #6366f1; }
    .icon-message { background: #fffbeb; color: #d97706; }

    .pulse-content h4 {
        font-size: 17px;
        font-weight: 800;
        color: var(--color-text-main);
        margin-bottom: 6px;
    }
    .pulse-content p {
        font-size: 14px;
        color: var(--color-text-muted);
        line-height: 1.6;
        margin-bottom: 12px;
    }
    .pulse-time {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--color-text-light);
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 12px;
        transition: 0.2s;
    }
    .btn-mark-read {
        background: var(--color-primary-light);
        color: var(--color-primary);
        text-decoration: none;
    }
    .btn-mark-read:hover {
        background: var(--color-primary);
        color: #fff;
    }

    .empty-pulse {
        text-align: center;
        padding: 100px 40px;
        background: #fff;
        border-radius: var(--radius-3xl);
        border: 2px dashed #e2e8f0;
    }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="report-lost.php">Report Lost</a>
        <a href="report-found.php">Report Found</a>
        <a href="notifications.php" class="active">Notifications</a>
        <a href="chat.php">Messages</a>
        <a href="profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="pulse-container">
        
        <div class="pulse-header">
            <div class="page-intro" style="text-align: left; margin: 0;">
                <h1>Intelligence <span style="color: var(--color-primary);">Pulse</span></h1>
                <p>Stay updated on item matches, security alerts, and system movements.</p>
            </div>
            <?php if (!empty($notifications)): ?>
                <a href="?read_all=1" class="btn-primary" style="padding: 12px 25px; font-size: 13px;">Clear All Sentry</a>
            <?php endif; ?>
        </div>

        <div class="pulse-feed">
            <?php if (empty($notifications)): ?>
                <div class="empty-pulse">
                    <span style="font-size: 60px;">📡</span>
                    <h2 style="margin-top: 20px; font-weight: 800;">No Signal Detected</h2>
                    <p style="color: var(--color-text-muted); margin-top: 10px;">Your intelligence feed is clear. We'll alert you if the network detects a match.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <?php 
                        $iconClass = 'icon-system'; $icon = '📢';
                        if ($n['type'] === 'match') { $iconClass = 'icon-match'; $icon = '✨'; }
                        if ($n['type'] === 'security') { $iconClass = 'icon-security'; $icon = '🛡️'; }
                        if ($n['type'] === 'message') { $iconClass = 'icon-message'; $icon = '💬'; }
                    ?>
                    <div class="pulse-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                        <div class="pulse-icon-wrap <?= $iconClass ?>">
                            <?= $icon ?>
                        </div>
                        <div class="pulse-content" style="flex: 1;">
                            <h4><?= htmlspecialchars($n['title']) ?></h4>
                            <p><?= htmlspecialchars($n['body']) ?></p>
                            <div class="pulse-time"><?= date('F j, Y • g:i a', strtotime($n['created_at'])) ?></div>
                        </div>
                        <?php if (!$n['is_read']): ?>
                            <div style="display: flex; align-items: center;">
                                <a href="?read=<?= $n['id'] ?>" class="btn-action btn-mark-read">Acknowledge</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
