<?php
require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['email'])) {
    die("Invalid verification link.");
}

$email = $_GET['email'];

$stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
$stmt->execute([$email]);

echo "Email verified successfully. You can now login.";
