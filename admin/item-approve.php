<?php
// ======================================
// APPROVE ITEM (ADMIN ONLY)
// ======================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/functions.php';

// -------------------------------
// ADMIN CHECK
// -------------------------------
if (!is_admin()) {
    die("Access denied.");
}

// -------------------------------
// VALIDATE ITEM ID
// -------------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$item_id = (int) $_GET['id'];

// -------------------------------
// UPDATE ITEM STATUS
// -------------------------------
$stmt = $conn->prepare("
    UPDATE items
    SET status = 'pending',
        approved_by = ?,
        approved_at = NOW()
    WHERE id = ?
");

$stmt->execute([
    $_SESSION['user_id'],
    $item_id
]);

// -------------------------------
// ADMIN LOG
// -------------------------------
$log = $conn->prepare("
    INSERT INTO admin_logs (admin_id, action)
    VALUES (?, ?)
");

$log->execute([
    $_SESSION['user_id'],
    "Approved item ID: $item_id"
]);

// -------------------------------
// REDIRECT BACK
// -------------------------------
redirect("/admin/dashboard.php");
