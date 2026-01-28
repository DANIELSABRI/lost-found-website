<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Found Item | Lost & Found</title>
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

/* SIDEBAR */
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

/* MAIN */
.main {
    padding: 40px;
}

.header {
    margin-bottom: 30px;
}

.header h1 span {
    background: var(--gradient-main);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
</style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>Lost & Found</h2>

        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost Item</a>
        <a class="active" href="<?= BASE_URL ?>/user/report-found.php">Report Found Item</a>
        <a href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>
        <a href="<?= BASE_URL ?>/search.php">Search Items</a>
        <a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/user/chat.php">Messages</a>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);margin:20px 0;">
        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="header">
            <h1>
                Report <span>Found Item</span>
            </h1>
            <p style="color:var(--text-muted);">
                Found an item on campus? Report it so the owner can recover it.
            </p>
        </div>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" placeholder="e.g. Blue Backpack" required>
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
                    <label>Found Location</label>
                    <input type="text" name="location" placeholder="e.g. Cafeteria" required>
                </div>

                <div class="form-group">
                    <label>Date Found</label>
                    <input type="date" name="date_found" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe the item and condition..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Image (optional)</label>
                    <input type="file" name="image">
                </div>

                <button class="btn-primary" style="margin-top:20px;">
                    Submit Found Item
                </button>

            </form>
        </div>

    </main>

</div>

</body>
</html>
