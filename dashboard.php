<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// 1. FETCH OVERALL NETWORK METRICS (Approved/Matched items)
$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM items WHERE type='lost' AND status IN ('approved','matched')) as global_lost,
    (SELECT COUNT(*) FROM items WHERE type='found' AND status IN ('approved','matched')) as global_found,
    (SELECT COUNT(*) FROM items WHERE status='matched') as global_matched
")->fetch(PDO::FETCH_ASSOC);

// 2. FETCH GLOBAL INTELLIGENCE PULSE (All approved/active items)
$pulse_sql = "SELECT i.*, u.name as reporter_name 
             FROM items i 
             JOIN users u ON i.user_id = u.id 
             WHERE i.status IN ('approved', 'matched', 'claimed') 
             ORDER BY i.created_at DESC LIMIT 15";
$pulseItems = $pdo->query($pulse_sql)->fetchAll(PDO::FETCH_ASSOC);

// 3. FETCH PERSONAL ACTIVITY (My Reports)
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$myItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for relative time
function timeAgo($timestamp) {
    $time = time() - strtotime($timestamp);
    if ($time < 60) return "Just now";
    if ($time < 3600) return round($time/60) . "m ago";
    if ($time < 86400) return round($time/3600) . "h ago";
    return date('M j', strtotime($timestamp));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Campus Intelligence Hub | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .dashboard-layout { display: grid; grid-template-columns: 1fr 380px; gap: 40px; margin-top: 30px; }
    
    /* Global Feed Styles */
    .pulse-section-title { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; }
    .pulse-section-title span { background: var(--color-primary-light); color: var(--color-primary); padding: 5px 12px; border-radius: 10px; font-size: 12px; }

    .pulse-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
    .pulse-card {
        background: #fff;
        border-radius: var(--radius-2xl);
        padding: 25px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0,0,0,0.03);
        transition: 0.3s;
        display: flex;
        flex-direction: column;
    }
    .pulse-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-xl); border-color: var(--color-primary); }
    
    .pulse-img-wrap { width: 100%; height: 180px; border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 20px; background: var(--color-bg); position: relative; }
    .pulse-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .pulse-type-tag { position: absolute; top: 15px; left: 15px; font-size: 10px; font-weight: 800; letter-spacing: 1px; color: #fff; padding: 6px 12px; border-radius: 8px; text-transform: uppercase; }
    
    .pulse-meta h4 { font-size: 17px; font-weight: 800; margin-bottom: 5px; color: var(--color-text-main); }
    .pulse-loc { font-size: 12px; color: var(--color-text-muted); display: flex; align-items: center; gap: 5px; margin-bottom: 15px; font-weight: 600; }
    
    .pulse-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; }
    .reporter-info { display: flex; align-items: center; gap: 8px; }
    .reporter-avatar { width: 28px; height: 28px; background: var(--grad-primary); color: #fff; border-radius: 8px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    
    .btn-relay { background: var(--color-primary-light); color: var(--color-primary); text-decoration: none; padding: 10px 18px; border-radius: 12px; font-size: 12px; font-weight: 700; transition: 0.2s; }
    .btn-relay:hover { background: var(--color-primary); color: #fff; }

    /* Personal Sidebar */
    .sidebar-block { background: #fff; padding: 30px; border-radius: var(--radius-2xl); box-shadow: var(--shadow-sm); margin-bottom: 30px; border: 1px solid rgba(0,0,0,0.02); }
    .sidebar-block h3 { font-size: 17px; font-weight: 800; margin-bottom: 25px; }

    .my-item-sm { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(0,0,0,0.02); }
    .my-item-sm:last-child { border: none; margin: 0; padding: 0; }
    .my-item-img { width: 45px; height: 45px; border-radius: 10px; object-fit: cover; }
    .my-item-info strong { display: block; font-size: 14px; font-weight: 700; color: var(--color-text-main); }
    .my-item-info span { font-size: 11px; font-weight: 600; color: var(--color-primary); text-transform: uppercase; }

    @media (max-width: 1200px) { .dashboard-layout { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found</div>
    <div class="nav-links">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="report-lost.php">Report Lost</a>
        <a href="report-found.php">Report Found</a>
        <a href="notifications.php">Notifications</a>
        <a href="chat.php">Messages</a>
        <a href="profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    
    <!-- ELITE HERO -->
    <div class="hero-card">
        <h1>Global Network <span style="background: var(--grad-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Intelligence</span></h1>
        <p>Real-time visibility across the entire campus recovery ecosystem. Connect, coordinate, and recover with verified community members.</p>
        <div style="margin-top: 35px; display: flex; gap: 15px;">
            <a href="report-lost.php" class="btn-primary" style="padding: 15px 30px;">Broadcast Loss</a>
            <a href="report-found.php" class="btn-primary" style="padding: 15px 30px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); box-shadow: none;">Initialize Discovery</a>
        </div>
    </div>

    <!-- ELITE STATS -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 60px;">
        <div class="stat-card-modern">
            <span style="font-size: 40px; display: block; margin-bottom: 20px;">🛡️</span>
            <div style="font-size: 32px; font-weight: 800;"><?= number_format($stats['global_lost']) ?></div>
            <div style="font-size: 12px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase;">Active Searches</div>
        </div>
        <div class="stat-card-modern">
            <span style="font-size: 40px; display: block; margin-bottom: 20px;">📦</span>
            <div style="font-size: 32px; font-weight: 800;"><?= number_format($stats['global_found']) ?></div>
            <div style="font-size: 12px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase;">Awaiting Owners</div>
        </div>
        <div class="stat-card-modern" style="background: var(--grad-primary); border: none; color: #fff;">
            <span style="font-size: 40px; display: block; margin-bottom: 20px;">✨</span>
            <div style="font-size: 32px; font-weight: 800;"><?= number_format($stats['global_matched']) ?></div>
            <div style="font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.8); text-transform: uppercase;">Successful Matches</div>
        </div>
    </div>

    <div class="dashboard-layout">
        
        <!-- MAIN GLOBAL PULSE -->
        <main>
            <div class="pulse-section-title">
                Global Intelligence Pulse
                <span>Live Feed</span>
            </div>

            <div class="pulse-grid">
                <?php if (empty($pulseItems)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px; background: #fff; border-radius: var(--radius-3xl); border: 2px dashed #e2e8f0;">
                        <span style="font-size: 60px;">📡</span>
                        <h2 style="margin-top: 20px; font-weight: 800;">Silent Network</h2>
                        <p style="color: var(--color-text-muted);">No active intelligence signals found on the network.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pulseItems as $item): ?>
                        <div class="pulse-card">
                            <div class="pulse-img-wrap">
                                <?php if ($item['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($item['image']) ?>">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:40px;">
                                        <?= $item['type'] === 'lost' ? '🎒' : '🎁' ?>
                                    </div>
                                <?php endif; ?>
                                <span class="pulse-type-tag" style="background: <?= $item['type'] === 'lost' ? '#ef4444' : '#10b981' ?>;">
                                    <?= $item['type'] ?>
                                </span>
                            </div>
                            
                            <div class="pulse-meta">
                                <h4><?= htmlspecialchars($item['item_name']) ?></h4>
                                <div class="pulse-loc">📍 <?= htmlspecialchars($item['location']) ?></div>
                            </div>

                            <div class="pulse-footer">
                                <div class="reporter-info">
                                    <div class="reporter-avatar"><?= strtoupper(substr($item['reporter_name'],0,1)) ?></div>
                                    <div style="font-size: 11px; font-weight: 600; color: var(--color-text-muted);">
                                        <?= htmlspecialchars($item['reporter_name']) ?>
                                        <div style="opacity: 0.6; font-size: 9px; font-weight: 800;"><?= timeAgo($item['created_at']) ?></div>
                                    </div>
                                </div>
                                <?php if ($item['user_id'] != $userId): ?>
                                    <a href="chat.php?item_id=<?= $item['id'] ?>&peer_id=<?= $item['user_id'] ?>" class="btn-relay">Initialize Relay</a>
                                <?php else: ?>
                                    <span style="font-size: 10px; font-weight: 800; color: var(--color-primary); text-transform: uppercase; padding: 5px 10px; background: var(--color-primary-light); border-radius: 8px;">My Report</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <!-- SIDEBAR: PERSONAL INTELLIGENCE -->
        <aside>
            <div class="sidebar-block" style="background: var(--grad-dark); border: none; color: #fff;">
                <h3 style="color: #fff; margin-bottom: 20px;">Command Hub</h3>
                <div style="font-size: 13px; opacity: 0.7; line-height: 1.6; margin-bottom: 25px;">Logged in as: <strong style="color: #fff;"><?= htmlspecialchars($userName) ?></strong></div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="chat.php" style="background: rgba(255,255,255,0.08); padding: 15px 20px; border-radius: 12px; text-decoration: none; color: #fff; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-weight: 600; font-size: 13px;">Secure Communications</span>
                        <span>💬</span>
                    </a>
                    <a href="notifications.php" style="background: rgba(255,255,255,0.08); padding: 15px 20px; border-radius: 12px; text-decoration: none; color: #fff; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-weight: 600; font-size: 13px;">Intelligence Pulse</span>
                        <span>📡</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-block">
                <h3>My Personal Activity</h3>
                <?php if (empty($myItems)): ?>
                    <p style="font-size: 13px; color: var(--color-text-muted); text-align: center; padding: 20px 0;">No personal reports filed yet.</p>
                <?php else: ?>
                    <?php foreach ($myItems as $item): ?>
                        <div class="my-item-sm">
                            <?php if ($item['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($item['image']) ?>" class="my-item-img">
                            <?php else: ?>
                                <div class="my-item-img" style="background: var(--color-bg); display:flex; align-items:center; justify-content:center; font-size:20px;">
                                    <?= $item['type'] === 'lost' ? '🎒' : '🎁' ?>
                                </div>
                            <?php endif; ?>
                            <div class="my-item-info">
                                <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                                <span><?= $item['status'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <a href="my-reports.php" class="btn-primary" style="width: 100%; padding: 12px; font-size: 12px; margin-top: 10px;">Audit All My Reports</a>
                <?php endif; ?>
            </div>

            <div class="glass-card" style="padding: 30px; text-align: center;">
                <span style="font-size: 32px;">📱</span>
                <h5 style="margin-top: 15px; font-weight: 800;">Campus Patrol</h5>
                <p style="font-size: 12px; color: var(--color-text-muted); margin-top: 8px;">Direct link to Security Desk for high-priority recovery.</p>
                <a href="chat.php?admin_help=1" class="btn-primary" style="margin-top: 20px; padding: 10px 20px; font-size: 12px; width: 100%;">Connect to Desk</a>
            </div>
        </aside>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
