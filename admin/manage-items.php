<?php
require_once __DIR__ . '/../includes/init.php';

// Admin protection
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

/**
 * HANDLE ACTIONS
 */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['approve', 'reject', 'delete', 'matched', 'claimed', 'closed'])) {
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            // Mapping approve to approved
            $status = ($action === 'approve') ? 'approved' : $action;
            $stmt = $pdo->prepare("UPDATE items SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
        header('Location: manage-items.php?msg=success');
        exit;
    }
}

/**
 * FETCH ITEMS WITH FILTERS
 */
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

$sql = "SELECT i.*, u.name as reporter_name 
        FROM items i 
        JOIN users u ON i.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND i.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (i.item_name LIKE ? OR i.location LIKE ? OR u.name LIKE ?)";
    $likeQuery = "%$searchQuery%";
    $params[] = $likeQuery;
    $params[] = $likeQuery;
    $params[] = $likeQuery;
}

$sql .= " ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for isActive
function isActiveLink($val, $current) { return ($val === $current) ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Command Intelligence | Item Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .admin-container { max-width: 1500px; margin: 0 auto; }
    
    /* Filtering & Search */
    .control-hub {
        background: #fff;
        padding: 30px;
        border-radius: var(--radius-2xl);
        box-shadow: var(--shadow-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 30px;
        margin-bottom: 40px;
    }
    .search-input-wrap { flex: 1; position: relative; }
    .search-input-wrap input {
        width: 100%;
        padding: 15px 25px 15px 50px;
        border: 2px solid var(--color-bg);
        border-radius: 15px;
        font-family: inherit;
        font-size: 14px;
        transition: 0.3s;
    }
    .search-input-wrap input:focus { outline: none; border-color: var(--color-primary); }
    .search-input-wrap::before { content: '🔍'; position: absolute; left: 20px; top: 50%; transform: translateY(-50%); opacity: 0.5; }

    .filter-tabs { display: flex; background: var(--color-bg); padding: 5px; border-radius: 15px; gap: 5px; }
    .filter-tab {
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text-muted);
        text-decoration: none;
        transition: 0.2s;
    }
    .filter-tab.active { background: #fff; color: var(--color-primary); box-shadow: var(--shadow-sm); }

    /* Elite Table */
    .intelligence-table-wrap {
        background: #fff;
        border-radius: var(--radius-3xl);
        overflow: hidden;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(0,0,0,0.03);
    }
    table { width: 100%; border-collapse: collapse; }
    th {
        background: #F9FAFB;
        padding: 25px 30px;
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid var(--color-bg);
    }
    td { padding: 30px; border-bottom: 1px solid var(--color-bg); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #FDFDFF; }

    .item-cell {显示: flex; align-items: center; gap: 20px; }
    .item-img-small { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; background: var(--color-bg); }
    .item-meta-name { display: block; font-weight: 800; color: var(--color-text-main); font-size: 16px; margin-bottom: 4px; }
    .item-meta-reporter { display: block; font-size: 12px; color: var(--color-primary); font-weight: 700; }

    .loc-badge { 
        display: flex; align-items: center; gap: 8px;
        background: var(--color-primary-light);
        color: var(--color-primary);
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .action-btns { display: flex; gap: 10px; }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found — <span style="color: var(--color-primary);">Admin Intelligence</span></div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-items.php" class="active">Items</a>
        <a href="manage-users.php">Users</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container admin-container">
    <div class="page-intro">
        <h1>Item Intelligence Control</h1>
        <p>Global system management for all campus recovery assets and user reports.</p>
    </div>

    <!-- STATS OVERLAY -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 40px;">
        <div class="stat-card-modern">
            <div style="font-size: 32px; font-weight: 800;"><?= count(array_filter($items, fn($i) => $i['status'] === 'pending')) ?></div>
            <div style="font-size: 12px; font-weight: 700; opacity: 0.6; text-transform: uppercase;">Pending Investigation</div>
        </div>
        <div class="stat-card-modern">
            <div style="font-size: 32px; font-weight: 800;"><?= count($items) ?></div>
            <div style="font-size: 12px; font-weight: 700; opacity: 0.6; text-transform: uppercase;">Total Active Reports</div>
        </div>
        <div class="stat-card-modern" style="background: var(--grad-primary); border: none; color: #fff;">
            <div style="font-size: 32px; font-weight: 800;"><?= count(array_filter($items, fn($i) => $i['status'] === 'matched')) ?></div>
            <div style="font-size: 12px; font-weight: 700; opacity: 0.8; text-transform: uppercase;">Recovery Matches</div>
        </div>
    </div>

    <!-- CONTROL HUB -->
    <div class="control-hub">
        <form class="search-input-wrap" method="GET">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <input type="text" name="search" placeholder="Search by item, location or user ID..." value="<?= htmlspecialchars($searchQuery) ?>">
        </form>

        <div class="filter-tabs">
            <a href="?status=all&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('all', $statusFilter) ?>">All</a>
            <a href="?status=pending&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('pending', $statusFilter) ?>">Pending</a>
            <a href="?status=approved&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('approved', $statusFilter) ?>">Approved</a>
            <a href="?status=matched&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('matched', $statusFilter) ?>">Matched</a>
            <a href="?status=claimed&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('claimed', $statusFilter) ?>">Claimed</a>
        </div>
    </div>

    <div class="intelligence-table-wrap">
        <?php if (empty($items)): ?>
            <div style="text-align: center; padding: 100px;">
                <span style="font-size: 60px;">📂</span>
                <h3 style="margin-top:20px; font-weight: 800;">No Intelligence Records</h3>
                <p style="opacity: 0.6;">The filtered intelligence feed is currently empty.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item & Reporter</th>
                        <th>Status Protocol</th>
                        <th>Recovery Point</th>
                        <th>Command Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-cell">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($item['image']) ?>" class="item-img-small">
                                    <?php else: ?>
                                        <div class="item-img-small" style="display:flex; align-items:center; justify-content:center; font-size: 24px;">
                                            <?= $item['type'] === 'lost' ? '🎒' : '🎁' ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="item-meta-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                        <span class="item-meta-reporter">By: <?= htmlspecialchars($item['reporter_name']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <span class="badge badge-<?= $item['type'] === 'lost' ? 'lost' : 'found' ?>" style="width: fit-content;"><?= strtoupper($item['type']) ?></span>
                                    <span class="badge status-<?= $item['status'] ?>" style="width: fit-content; font-size: 10px;"><?= strtoupper($item['status']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="loc-badge">📍 <?= htmlspecialchars($item['location']) ?></div>
                                <div style="font-size: 10px; font-weight: 700; opacity: 0.4; margin-top: 5px; padding-left: 5px;">
                                    <?= date('M j, Y • g:i A', strtotime($item['created_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <?php if ($item['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?= $item['id'] ?>" class="btn-sm btn-sm-success">Approve</a>
                                        <a href="?action=reject&id=<?= $item['id'] ?>" class="btn-sm btn-sm-danger">Reject</a>
                                    <?php endif; ?>

                                    <?php if (in_array($item['status'], ['approved', 'pending'])): ?>
                                        <a href="?action=matched&id=<?= $item['id'] ?>" class="btn-sm btn-sm-primary">Match</a>
                                    <?php endif; ?>

                                    <?php if ($item['status'] === 'matched'): ?>
                                        <a href="?action=claimed&id=<?= $item['id'] ?>" class="btn-sm btn-sm-success" style="background:#10B981;">Claimed</a>
                                    <?php endif; ?>

                                    <a href="?action=delete&id=<?= $item['id'] ?>" class="btn-sm btn-sm-secondary" onclick="return confirm('Purge record from system?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
