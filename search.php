<?php
require_once __DIR__ . '/includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$query = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

// 1. STANDARD SEARCH QUERY
$sql = "SELECT * FROM items WHERE status != 'closed'";
$params = [];

if ($query) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $term = "%$query%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($category && $category !== 'All Categories') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY created_at DESC LIMIT 20";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}

// 2. INTELLIGENT MATCHING (Bonus Feature)
// Look for items that match the user's "Lost" reports with others' "Found" reports (and vice versa) based on Category
$matches = [];
if (empty($query) && empty($category)) {
    // Only run this if not actively searching, to avoid clutter
    try {
        // Find my lost items
        $myLost = $pdo->prepare("SELECT category, title FROM items WHERE user_id = ? AND type = 'lost' AND status = 'open' LIMIT 3");
        $myLost->execute([$_SESSION['user_id']]);
        $lostItems = $myLost->fetchAll();
        
        foreach($lostItems as $lost) {
            // Find found items in same category, NOT by me
            $matchStmt = $pdo->prepare("SELECT * FROM items WHERE type = 'found' AND category = ? AND status = 'open' AND user_id != ? LIMIT 2");
            $matchStmt->execute([$lost['category'], $_SESSION['user_id']]);
            $found = $matchStmt->fetchAll();
            $matches = array_merge($matches, $found);
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Items | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
/* Search Local Styles */
.search-container {
    background: var(--color-white);
    padding: 32px;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-soft);
    margin-bottom: 40px;
}

.search-form {
    display: grid;
    grid-template-columns: 1fr 200px 140px;
    gap: 16px;
    align-items: center;
}

.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

.item-card {
    background: var(--color-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    transition: transform 0.2s;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.03);
    display: flex;
    flex-direction: column;
    position: relative;
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-soft);
}

.card-img-placeholder {
    height: 160px;
    background: #F0F3F8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #B2B3BD;
    font-size: 13px;
}

.card-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.card-type {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 10;
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--color-text-dark);
}

.card-meta {
    font-size: 13px;
    color: var(--color-text-gray);
    margin-bottom: 12px;
    display:flex; 
    align-items:center; 
    gap:6px;
}

.btn-details {
    margin-top: auto;
    text-align: center;
    width: 100%;
    padding: 10px;
    background: #F8F9FD;
    color: var(--color-primary);
    font-weight: 600;
    font-size: 13px;
    border-radius: 8px;
}

.btn-details:hover {
    background: #EEF2FF;
}

/* Match Section */
.match-section {
    background: linear-gradient(135deg, #FFF0ED 0%, #FFF 100%);
    border: 2px solid #FF754C;
    border-radius: var(--radius-xl);
    padding: 32px;
    margin-bottom: 40px;
}

.match-header {
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #11142D;
}

@media(max-width: 768px) {
    .search-form { grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<!-- TOP NAVIGATION -->
<nav class="top-nav">
    <a href="#" class="nav-brand">Lost & Found</a>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost</a>
        <a href="<?= BASE_URL ?>/user/report-found.php">Report Found</a>
        <a href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>
        <a href="<?= BASE_URL ?>/search.php" class="active">Search</a> <!-- Note: Assuming search.php is in root, but linking directly -->
        <a href="<?= BASE_URL ?>/user/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/user/chat.php">Messages</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="section-header">
        <h3>Search Items</h3>
    </div>

    <!-- POSSIBLE MATCHES (Only shown if we found correlations) -->
    <?php if (!empty($matches)): ?>
        <div class="match-section">
            <div class="match-header">
                <div style="width:40px;height:40px;background:#FF754C;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;">!</div>
                <div>
                    <h3 style="margin:0;">Possible Matches Found</h3>
                    <p style="margin:0; font-size:14px; opacity:0.8;">Based on your recent lost reports, these found items might be yours.</p>
                </div>
            </div>
            <div class="results-grid">
                <?php foreach ($matches as $item): ?>
                    <!-- Reuse Item Card (Simplified) -->
                    <div class="item-card">
                         <div style="position:relative;">
                            <?php if(!empty($item['image'])): ?>
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($item['image']) ?>" style="width:100%; height:160px; object-fit:cover;">
                            <?php else: ?>
                                <div class="card-img-placeholder">No Image</div>
                            <?php endif; ?>
                            <div class="card-type"><span class="badge badge-found">MATCH?</span></div>
                        </div>
                        <div class="card-content">
                            <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
                            <div class="card-meta"><span>📍 <?= htmlspecialchars($item['location']) ?></span></div>
                            <a href="item.php?id=<?= $item['id'] ?>" class="btn-details">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="search-container">
        <form method="GET" class="search-form">
            <input type="text" name="q" placeholder="Type keyword (e.g. Wallet, Keys)..." value="<?= htmlspecialchars($query) ?>">
            
            <select name="category">
                <option>All Categories</option>
                <option <?= $category === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                <option <?= $category === 'Documents' ? 'selected' : '' ?>>Documents</option>
                <option <?= $category === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
                <option <?= $category === 'Books' ? 'selected' : '' ?>>Books</option>
                <option <?= $category === 'Others' ? 'selected' : '' ?>>Others</option>
            </select>
            
            <button class="btn-primary">Search</button>
        </form>
    </div>

    <div class="results-grid">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <div style="position:relative;">
                        <?php if(!empty($item['image'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($item['image']) ?>" style="width:100%; height:160px; object-fit:cover;">
                        <?php else: ?>
                            <div class="card-img-placeholder">No Image</div>
                        <?php endif; ?>
                        
                        <div class="card-type">
                            <span class="badge badge-<?= $item['type'] === 'lost' ? 'lost' : 'found' ?>">
                                <?= strtoupper($item['type']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
                        <div class="card-meta">
                            <span>📍 <?= htmlspecialchars($item['location']) ?></span>
                        </div>
                        <div class="card-meta">
                            <span>📅 <?= date('M j', strtotime($item['created_at'])) ?></span>
                        </div>
                        
                        <a href="item.php?id=<?= $item['id'] ?>" class="btn-details">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1/-1;">
                <p>No items found matching your search.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
