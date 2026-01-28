<?php
session_start();
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    exit('Unauthorized');
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="items_report.csv"');

$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, ['ID', 'Title', 'Type', 'Category', 'Location', 'Status', 'Created At']);

$stmt = $pdo->query("
    SELECT id, title, type, category, location, status, created_at 
    FROM items 
    ORDER BY created_at DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
