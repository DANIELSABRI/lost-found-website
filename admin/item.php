<?php
session_start();
require_once __DIR__ . '/../includes/init.php';

/**
 * ADMIN CHECK
 */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

/**
 * HANDLE ACTIONS
 */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['approve','reject','delete'])) {

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare("UPDATE items SET status = ? WHERE id = ?");
            $stmt->execute([$action === 'approve' ? 'approved' : 'rejected', $id]);
        }

        header('Location: items.php');
        exit;
    }
}

/**
 * FETCH ITEMS
 */
$stmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Items | Admin</title>
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
}
th{
    background:#f0edff;
}
tr:not(:last-child) td{
    border-bottom:1px solid #eee;
}
.badge{
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}
.pending{background:#fff3cd;color:#856404}
.approved{background:#d4edda;color:#155724}
.rejected{background:#f8d7da;color:#721c24}

.actions a{
    margin-right:8px;
    text-decoration:none;
    font-weight:600;
}
.approve{color:#28a745}
.reject{color:#dc3545}
.delete{color:#6c757d}
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
        <a class="active" href="items.php">Manage Items</a>
        <a href="users.php">Manage Users</a>
        <a href="reports.php">Reports</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">
    <h1>Manage Items</h1>
    <p>Approve, reject or remove reported lost & found items.</p>

    <?php if (count($items) === 0): ?>
        <div class="empty">No items have been reported yet.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Type</th>
                <th>Category</th>
                <th>Location</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= ucfirst($item['type']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['location']) ?></td>
                    <td>
                        <span class="badge <?= $item['status'] ?>">
                            <?= ucfirst($item['status']) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <?php if ($item['status'] === 'pending'): ?>
                            <a class="approve" href="?action=approve&id=<?= $item['id'] ?>">Approve</a>
                            <a class="reject" href="?action=reject&id=<?= $item['id'] ?>">Reject</a>
                        <?php endif; ?>
                        <a class="delete" href="?action=delete&id=<?= $item['id'] ?>"
                           onclick="return confirm('Delete this item permanently?')">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

</main>
</div>

</body>
</html>
