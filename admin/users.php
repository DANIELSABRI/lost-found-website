<?php
session_start();
require_once __DIR__ . '/../includes/init.php';

/* ===============================
   ADMIN ACCESS CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

/* ===============================
   HANDLE ACTIONS
================================ */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    // Prevent admin deleting himself
    if ($id === $_SESSION['user_id']) {
        header('Location: users.php');
        exit;
    }

    if ($action === 'suspend') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
        $stmt->execute([$id]);
    }

    if ($action === 'activate') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$id]);
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    header('Location: users.php');
    exit;
}

/* ===============================
   FETCH USERS
================================ */
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users | Admin</title>
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
.active{background:#d4edda;color:#155724}
.suspended{background:#f8d7da;color:#721c24}
.admin{background:#e0d7ff;color:#4f2cf5}

.actions a{
    margin-right:10px;
    text-decoration:none;
    font-weight:600;
}
.suspend{color:#dc3545}
.activate{color:#28a745}
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
        <a href="items.php">Manage Items</a>
        <a class="active" href="users.php">Manage Users</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">
    <h1>Manage Users</h1>
    <p>Control user access and roles.</p>

<?php if (count($users) === 0): ?>
    <div class="empty">No users found.</div>
<?php else: ?>
<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

<?php foreach ($users as $user): ?>
<tr>
    <td><?= htmlspecialchars($user['name']) ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td>
        <span class="badge <?= $user['role'] === 'admin' ? 'admin' : '' ?>">
            <?= ucfirst($user['role']) ?>
        </span>
    </td>
    <td>
        <span class="badge <?= $user['status'] ?>">
            <?= ucfirst($user['status']) ?>
        </span>
    </td>
    <td class="actions">
        <?php if ($user['status'] === 'active'): ?>
            <a class="suspend" href="?action=suspend&id=<?= $user['id'] ?>">Suspend</a>
        <?php else: ?>
            <a class="activate" href="?action=activate&id=<?= $user['id'] ?>">Activate</a>
        <?php endif; ?>

        <?php if ($user['role'] !== 'admin'): ?>
            <a class="delete"
               href="?action=delete&id=<?= $user['id'] ?>"
               onclick="return confirm('Delete this user permanently?')">
               Delete
            </a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</main>
</div>

</body>
</html>
