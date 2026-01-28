<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item_name   = trim($_POST['item_name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $date_lost   = $_POST['date_lost'] ?? null;
    $description = trim($_POST['description'] ?? '');

    if ($item_name === '' || $category === '' || $location === '' || !$date_lost) {
        $error = 'Please fill in all required fields.';
    } else {

        // Image upload (optional)
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        // INSERT INTO DATABASE  ✅ THIS WAS MISSING
        $stmt = $pdo->prepare("
            INSERT INTO items 
            (user_id, title, category, location, description, image, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'lost', 'open', NOW())
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $item_name,
            $category,
            $location,
            $description,
            $imagePath
        ]);

        $success = 'Lost item reported successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Lost Item | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
.layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: #F9FAFB;
}

.sidebar {
    background: linear-gradient(180deg, #4B2FDB, #6C4DFF);
    color: #fff;
    padding: 30px 20px;
}

.sidebar h2 {
    font-size: 22px;
    margin-bottom: 40px;
}

.sidebar a {
    display: block;
    color: #fff;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    font-size: 14px;
    text-decoration: none;
}

.sidebar a.active,
.sidebar a:hover {
    background: rgba(255,255,255,0.18);
}

.main {
    padding: 40px;
}

.form-card {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    max-width: 720px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 18px;
}

label {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 6px;
    display: block;
}

textarea {
    resize: vertical;
}

.alert-error {
    background: #ffe5e5;
    color: #b30000;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.alert-success {
    background: #e6fff1;
    color: #0f5132;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 20px;
}
</style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>Lost & Found</h2>

        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a class="active" href="<?= BASE_URL ?>/user/report-lost.php">Report Lost Item</a>
        <a href="<?= BASE_URL ?>/user/report-found.php">Report Found Item</a>
        <a href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>
        <a href="<?= BASE_URL ?>/search.php">Search Items</a>
        <a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/user/chat.php">Messages</a>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);margin:20px 0;">
        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <h1>Report <span style="color:#6C4DFF;">Lost Item</span></h1>

        <div class="form-card">

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select category</option>
                        <option>Electronics</option>
                        <option>Documents</option>
                        <option>Accessories</option>
                        <option>Books</option>
                        <option>Others</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Last Seen Location</label>
                    <input type="text" name="location" required>
                </div>

                <div class="form-group">
                    <label>Date Lost</label>
                    <input type="date" name="date_lost" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Image (optional)</label>
                    <input type="file" name="image">
                </div>

                <button class="btn-primary" style="margin-top:20px;">
                    Submit Lost Item
                </button>

            </form>
        </div>

    </main>

</div>

</body>
</html>
