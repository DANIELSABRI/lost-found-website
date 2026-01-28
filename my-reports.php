<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$type_filter = $_GET['type'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';

$sql = "SELECT * FROM items WHERE user_id = ?";
$params = [$user_id];

if ($type_filter !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $type_filter;
}

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Tracking Hub | Lost & Found Intelligence</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .tracking-container { max-width: 1200px; margin: 0 auto; }
    
    .filter-hub {
        background: #fff;
        padding: 40px;
        border-radius: var(--radius-3xl);
        box-shadow: var(--shadow-lg);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 30px;
        margin-bottom: 50px;
        border: 1px solid rgba(0,0,0,0.02);
    }
    .filter-group-elite { display: flex; flex-direction: column; gap: 12px; flex: 1; }
    .filter-group-elite label { font-size: 11px; font-weight: 800; color: var(--color-text-light); text-transform: uppercase; letter-spacing: 1.5px; }
    .filter-control-elite {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid var(--color-bg);
        border-radius: 12px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        color: var(--color-text-main);
        transition: 0.3s;
        appearance: none;
        background: #FDFDFF url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%236C5DD3' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 15px center;
        background-size: 16px;
    }
    .filter-control-elite:focus { outline: none; border-color: var(--color-primary); background-color: #fff; }

    .elite-table-card {
        background: #fff;
        border-radius: var(--radius-3xl);
        overflow: hidden;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(0,0,0,0.02);
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

    .item-cell-elite { display: flex; align-items: center; gap: 20px; }
    .item-img-elite { width: 60px; height: 60px; border-radius: 15px; object-fit: cover; background: var(--color-bg); box-shadow: var(--shadow-sm); }
    .item-title-elite { display: block; font-weight: 800; color: var(--color-text-main); font-size: 16px; margin-bottom: 4px; }
    .item-meta-elite { display: block; font-size: 12px; color: var(--color-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    .badge-protocol { font-weight: 800; font-size: 10px; padding: 8px 14px; border-radius: 10px; }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="report-lost.php">Report Lost</a>
        <a href="report-found.php">Report Found</a>
        <a href="my-reports.php" class="active">My Reports</a>
        <a href="chat.php">Messages</a>
        <a href="profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container tracking-container">
    <div class="page-intro" style="text-align: left; margin: 0 0 40px 0;">
        <h1>My Personal <span style="color: var(--color-primary);">Tracking Hub</span></h1>
        <p>Strategic monitoring of your reported incident files and active recovery protocols.</p>
    </div>

    <!-- FILTER HUB -->
    <form class="filter-hub" method="GET">
        <div class="filter-group-elite">
            <label>Intelligence Type</label>
            <select name="type" class="filter-control-elite">
                <option value="all">All Incident Types</option>
                <option value="lost" <?= $type_filter === 'lost' ? 'selected' : '' ?>>Lost Intel</option>
                <option value="found" <?= $type_filter === 'found' ? 'selected' : '' ?>>Found Intel</option>
            </select>
        </div>
        <div class="filter-group-elite">
            <label>Current Status</label>
            <select name="status" class="filter-control-elite">
                <option value="all">Every Signal</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved Visibility</option>
                <option value="matched" <?= $status_filter === 'matched' ? 'selected' : '' ?>>Network Match</option>
                <option value="claimed" <?= $status_filter === 'claimed' ? 'selected' : '' ?>>Successful Recovery</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="width: auto; padding: 18px 40px; border-radius: 12px; height: 52px; display: flex; align-items: center; justify-content: center;">Apply Parameters</button>
    </form>

    <div class="elite-table-card">
        <?php if (empty($items)): ?>
            <div style="text-align: center; padding: 100px 40px;">
                <span style="font-size: 60px;">📂</span>
                <h2 style="margin-top: 25px; font-weight: 800;">No Incident Files</h2>
                <p style="color: var(--color-text-muted); max-width: 350px; margin: 15px auto;">Your tracking hub is currently clear. Report a loss or discovery to begin intelligence tracking.</p>
                <a href="report-lost.php" class="btn-primary" style="margin-top: 30px; width: auto; display: inline-flex; padding: 15px 30px;">Report New Incident</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th width="45%">Asset Identity</th>
                        <th>File Protocol</th>
                        <th>Current Status</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-cell-elite">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($item['image']) ?>" class="item-img-elite">
                                    <?php else: ?>
                                        <div class="item-img-elite" style="display: flex; align-items:center; justify-content: center; font-size: 24px;">
                                            <?= $item['type'] === 'lost' ? '🎒' : '🎁' ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="item-title-elite"><?= htmlspecialchars($item['item_name']) ?></span>
                                        <span class="item-meta-elite">📍 <?= htmlspecialchars($item['location']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $item['type'] === 'lost' ? 'lost' : 'found' ?> badge-protocol">
                                    <?= strtoupper($item['type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = 'status-pending'; 
                                    if ($item['status'] === 'matched') $statusClass = 'status-matched';
                                    if ($item['status'] === 'claimed') $statusClass = 'status-returned';
                                    if ($item['status'] === 'approved') $statusClass = 'status-matched'; // reuse style for clarity
                                ?>
                                <span class="badge <?= $statusClass ?> badge-protocol">
                                    <?= strtoupper($item['status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
                                <?= date('M j, Y', strtotime($item['created_at'])) ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/item.php?id=<?= $item['id'] ?>" class="btn-sm btn-sm-primary" style="padding: 10px 18px; border-radius: 10px;">Audit Intel</a>
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
