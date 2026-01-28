<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Item Details | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
.page {
    padding: 50px;
    background: #F9FAFB;
    min-height: 100vh;
}

.item-wrapper {
    max-width: 1000px;
    margin: auto;
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 40px;
}

/* IMAGE */
.item-image {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.image-box {
    background: linear-gradient(135deg, #EEF2FF, #F5F3FF);
    border-radius: 16px;
    height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 14px;
}

/* DETAILS */
.item-details {
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.item-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-title h1 {
    font-size: 30px;
}

.status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status.lost {
    background: #FEE2E2;
    color: #991B1B;
}

.status.found {
    background: #DCFCE7;
    color: #065F46;
}

/* META */
.meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin: 25px 0;
}

.meta div {
    font-size: 14px;
}

.meta span {
    display: block;
    color: var(--text-muted);
    font-size: 12px;
}

/* DESCRIPTION */
.description {
    margin-bottom: 30px;
}

.description h3 {
    margin-bottom: 10px;
}

/* ACTIONS */
.actions {
    display: flex;
    gap: 15px;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="brand">Lost & Found</div>
    <div>
        <a href="<?= BASE_URL ?>/search.php">Search</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
            <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
        <?php endif; ?>
    </div>
</div>

<div class="page">

    <div class="item-wrapper">

        <!-- IMAGE -->
        <div class="item-image">
            <div class="image-box">
                Item Image Preview
            </div>
        </div>

        <!-- DETAILS -->
        <div class="item-details">

            <div class="item-title">
                <h1>Black Wallet</h1>
                <span class="status lost">Lost</span>
            </div>

            <div class="meta">
                <div>
                    <span>Category</span>
                    Accessories
                </div>
                <div>
                    <span>Date</span>
                    12 Jan 2026
                </div>
                <div>
                    <span>Location</span>
                    Library
                </div>
                <div>
                    <span>Reported By</span>
                    Student
                </div>
            </div>

            <div class="description">
                <h3>Description</h3>
                <p style="font-size:14px; color:var(--text-muted);">
                    Leather wallet containing ID card, debit card, and some cash.
                    Lost near the library seating area.
                </p>
            </div>

            <div class="actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#" class="btn-primary">Contact / Claim Item</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn-primary">Login to Contact</a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/search.php" class="btn-outline">Back to Search</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>
