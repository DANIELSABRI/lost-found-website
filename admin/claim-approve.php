<?php
// ======================================
// ADMIN CLAIM APPROVAL
// ======================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/functions.php';

// -------------------------------
// ADMIN ONLY
// -------------------------------
if (!is_admin()) {
    die("Access denied.");
}

// -------------------------------
// VALIDATE CLAIM
// -------------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$claim_id = (int) $_GET['id'];

// -------------------------------
// FETCH CLAIM
// -------------------------------
$stmt = $conn->prepare("
    SELECT * FROM abuse_reports
    WHERE id = ? AND status = 'under_review'
");
$stmt->execute([$claim_id]);
$claim = $stmt->fetch();

if (!$claim) {
    die("Claim not found.");
}

// -------------------------------
// APPROVE CLAIM
// -------------------------------
$conn->beginTransaction();

// Mark item as claimed
$conn->prepare("
    UPDATE items
    SET status = 'claimed'
    WHERE id = ?
")->execute([$claim['item_id']]);

// Close claim
$conn->prepare("
    UPDATE abuse_reports
    SET status = 'resolved', admin_note = 'Claim approved'
    WHERE id = ?
")->execute([$claim_id]);

// Log admin action
$conn->prepare("
    INSERT INTO admin_logs (admin_id, action)
    VALUES (?, ?)
")->execute([
    $_SESSION['user_id'],
    "Approved claim ID: $claim_id"
]);

$conn->commit();

redirect('/admin/dashboard.php');
