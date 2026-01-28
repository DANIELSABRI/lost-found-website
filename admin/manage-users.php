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

    if ($id !== $_SESSION['user_id']) {
        if ($action === 'suspend') {
            $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?")->execute([$id]);
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        }
    }
    header('Location: manage-users.php');
    exit;
}

/**
 * FETCH USERS WITH FILTERS
 */
$roleFilter = $_GET['role'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($roleFilter !== 'all') {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $likeQuery = "%$searchQuery%";
    $params[] = $likeQuery;
    $params[] = $likeQuery;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getInitials($name) {
    if (empty($name)) return "??";
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) $initials .= strtoupper($w[0]);
    return substr($initials, 0, 2);
}
function isActiveLink($val, $current) { return ($val === $current) ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Authority | Command Intelligence</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .admin-container { max-width: 1500px; margin: 0 auto; }
    
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

    .elite-table-wrap {
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
    td { padding: 25px 30px; border-bottom: 1px solid var(--color-bg); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #FDFDFF; }

    .user-cell { display: flex; align-items: center; gap: 20px; }
    .avatar-elite {
        width: 52px; height: 52px;
        background: var(--grad-primary);
        color: #fff;
        border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 18px;
        box-shadow: var(--shadow-md);
    }
    .user-meta-name { display: block; font-weight: 800; color: var(--color-text-main); font-size: 15px; margin-bottom: 2px; }
    .user-meta-email { display: block; font-size: 13px; color: var(--color-text-light); font-weight: 500; }

    .action-btns { display: flex; gap: 10px; }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found — <span style="color: var(--color-primary);">Admin Intelligence</span></div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-items.php">Items</a>
        <a href="manage-users.php" class="active">Users</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container admin-container">
    <div class="page-intro">
        <h1>User Authority Management</h1>
        <p>Expert-level audit and clearance control over campus network identities.</p>
    </div>

    <!-- CONTROL HUB -->
    <div class="control-hub">
        <form class="search-input-wrap" method="GET">
            <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
            <input type="text" name="search" placeholder="Search by name or intelligence email..." value="<?= htmlspecialchars($searchQuery) ?>">
        </form>

        <div class="filter-tabs">
            <a href="?role=all&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('all', $roleFilter) ?>">All Agents</a>
            <a href="?role=student&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('student', $roleFilter) ?>">Students</a>
            <a href="?role=staff&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('staff', $roleFilter) ?>">Staff</a>
            <a href="?role=admin&search=<?= urlencode($searchQuery) ?>" class="filter-tab <?= isActiveLink('admin', $roleFilter) ?>">Admins</a>
        </div>
    </div>

    <div class="elite-table-wrap">
        <?php if (empty($users)): ?>
            <div style="text-align: center; padding: 100px;">
                <span style="font-size: 60px;">🕵️</span>
                <h3 style="margin-top:20px; font-weight: 800;">No Identity Matches</h3>
                <p style="opacity: 0.6;">Your search criteria did not match any verified campus agents.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Agent Identity</th>
                        <th>Clearance Role</th>
                        <th>Security Status</th>
                        <th>Administrative Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar-elite" style="<?= $u['role'] === 'admin' ? 'background: var(--grad-dark);' : '' ?>">
                                        <?= getInitials($u['name']) ?>
                                    </div>
                                    <div>
                                        <span class="user-meta-name"><?= htmlspecialchars($u['name']) ?></span>
                                        <span class="user-meta-email"><?= htmlspecialchars($u['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'status-matched' : 'badge-found' ?>">
                                    <?= strtoupper($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $u['status'] === 'active' ? 'status-matched' : 'status-pending' ?>" style="font-size: 10px;">
                                    <?= strtoupper($u['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <a href="?action=<?= $u['status'] === 'active' ? 'suspend' : 'activate' ?>&id=<?= $u['id'] ?>" 
                                           class="btn-sm <?= $u['status'] === 'active' ? 'btn-sm-danger' : 'btn-sm-success' ?>">
                                            <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                        </a>
                                        <a href="?action=delete&id=<?= $u['id'] ?>" 
                                           class="btn-sm btn-sm-secondary" 
                                           onclick="return confirm('Purge agent identity from system?')">Purge</a>
                                    <?php else: ?>
                                        <span class="badge status-matched" style="font-size: 10px; opacity: 0.7;">OWNER ACCOUNT</span>
                                    <?php endif; ?>
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
