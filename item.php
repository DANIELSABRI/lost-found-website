<?php
require_once __DIR__ . '/includes/init.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: search.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    echo "Item not found.";
    exit;
}

// Admin Status Toggle Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $newStatus = $_POST['status'];
    $update = $pdo->prepare("UPDATE items SET status = ? WHERE id = ?");
    $update->execute([$newStatus, $id]);
    header("Location: item.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($item['item_name']) ?> | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
/* Detail Page Styles */
.detail-container {
    max-width: 1100px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 40px;
    padding: 0 40px;
}

/* Left Column: Image & Description */
.detail-main {
    background: var(--color-white);
    border-radius: var(--radius-xl);
    padding: 40px;
    box-shadow: var(--shadow-soft);
}

.detail-image-box {
    width: 100%;
    height: 400px;
    background: #F0F3F8;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 32px;
    position: relative;
}

.detail-image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-text {
    color: #B2B3BD;
    font-size: 16px;
    font-weight: 500;
}

.item-type-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.detail-title {
    font-size: 32px;
    margin-bottom: 16px;
    color: var(--color-text-dark);
}

.detail-desc {
    font-size: 16px;
    line-height: 1.8;
    color: var(--color-text-gray);
    margin-bottom: 40px;
    white-space: pre-line;
}

/* Right Column: Meta & Actions */
.detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.meta-card {
    background: var(--color-white);
    padding: 32px;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-card);
}

.meta-row {
    display: flex;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid #F0F3F8;
}

.meta-row:last-child {
    border-bottom: none;
}

.meta-label {
    color: var(--color-text-gray);
    font-size: 14px;
    font-weight: 500;
}

.meta-value {
    color: var(--color-text-dark);
    font-weight: 600;
    text-align: right;
}

.status-indicator {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.contact-card {
    background: linear-gradient(135deg, #6C5DD3, #8B80F8);
    padding: 32px;
    border-radius: var(--radius-xl);
    color: white;
    text-align: center;
}

.contact-card h3 {
    margin-bottom: 12px;
    font-size: 22px;
}

.contact-card p {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 24px;
}

.btn-contact {
    background: white;
    color: var(--color-primary);
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    display: inline-block;
    text-decoration: none;
    width: 100%;
}

.btn-contact:hover {
    background: #F0F3FF;
}

/* Admin Panel */
.admin-card {
    background: #FFF0ED;
    border: 2px solid #FF754C;
    padding: 24px;
    border-radius: var(--radius-lg);
}

.admin-card h4 {
    color: #FF754C;
    margin-bottom: 16px;
}

@media(max-width: 900px) {
    .detail-container { grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<!-- TOP NAVIGATION -->
<nav class="top-nav">
    <a href="index.php" class="nav-brand">Lost & Found</a>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost</a>
        <a href="<?= BASE_URL ?>/user/report-found.php">Report Found</a>
        <a href="<?= BASE_URL ?>/search.php">Search</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/auth/login.php" class="btn-primary" style="padding:10px 24px; color:white;">Login</a>
        <?php endif; ?>
    </div>
</nav>

<div class="detail-container">

    <!-- MAIN CONTENT -->
    <div class="detail-main">
        <div class="detail-image-box">
            <?php if (!empty($item['image'])): ?>
                <img src="<?= BASE_URL . '/uploads/' . htmlspecialchars($item['image']) ?>" alt="Item Image">
            <?php else: ?>
                <div class="placeholder-text">No image provided</div>
            <?php endif; ?>
            
            <span class="item-type-badge" 
                  style="background: <?= $item['type'] === 'lost' ? '#FFF0ED; color:#FF754C;' : '#E2FBD7; color:#34B53A;' ?>">
                <?= strtoupper($item['type']) ?> ITEM
            </span>
        </div>

        <h1 class="detail-title"><?= htmlspecialchars($item['item_name']) ?></h1>
        
        <div class="detail-desc">
            <strong>Description:</strong><br>
            <?= nl2br(htmlspecialchars($item['description'])) ?>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="detail-sidebar">
        
        <!-- Status Card -->
        <div class="meta-card">
            <div class="meta-row">
                <span class="meta-label">Status</span>
                <span class="status-indicator" 
                      style="background: <?= $item['status'] === 'matched' ? '#E2FBD7; color:#34B53A;' : '#E0E7FF; color:#4C6FFF;' ?>">
                    <?= strtoupper($item['status']) ?>
                </span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Category</span>
                <span class="meta-value"><?= htmlspecialchars($item['category']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Location</span>
                <span class="meta-value"><?= htmlspecialchars($item['location']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Date Reported</span>
                <span class="meta-value"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
            </div>
        </div>

        <!-- Contact / Claim Card -->
        <div class="contact-card">
            <?php if ($item['type'] === 'lost'): ?>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                    <h3>Your Lost Item</h3>
                    <p>Track the status of your reported item here.</p>
                    <a href="user/match.php?target_id=<?= $item['id'] ?>&view_matches=1" class="btn-contact" style="background: #fff; color: var(--color-primary);">Find Potential Matches</a>
                <?php else: ?>
                    <h3>Found this item?</h3>
                    <p>Please contact the owner to arrange a safe return.</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user/chat.php?item_id=<?= $item['id'] ?>" class="btn-contact">Message Owner</a>
                        <a href="user/match.php?target_id=<?= $item['id'] ?>" class="btn-contact" style="margin-top:10px; background: rgba(0,0,0,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);">Propose Match</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn-contact">Login to Contact</a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                    <h3>Your Found Item</h3>
                    <p>Thank you for reporting this. We'll notify you of matches.</p>
                    <a href="user/match.php?target_id=<?= $item['id'] ?>&view_matches=1" class="btn-contact" style="background: #fff; color: var(--color-primary);">Find Potential Matches</a>
                <?php else: ?>
                    <h3>Is this yours?</h3>
                    <p>If this item belongs to you, claim it now.</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user/chat.php?item_id=<?= $item['id'] ?>" class="btn-contact">Claim Item</a>
                        <a href="user/match.php?target_id=<?= $item['id'] ?>" class="btn-contact" style="margin-top:10px; background: rgba(0,0,0,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);">Propose Match</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn-contact">Login to Claim</a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Admin Controls -->
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <div class="admin-card">
                <h4>Admin Controls</h4>
                <form method="POST">
                    <label style="display:block; margin-bottom:8px; font-size:14px; font-weight:600;">Update Status</label>
                    <select name="status" style="width:100%; padding:10px; border-radius:8px; border:1px solid #FF754C; margin-bottom:12px;">
                        <option value="open" <?= $item['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="matched" <?= $item['status'] === 'matched' ? 'selected' : '' ?>>Matched</option>
                        <option value="closed" <?= $item['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                    <button class="btn-primary" style="background:#FF754C; padding:10px; width:100%;">Update Status</button>
                </form>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
