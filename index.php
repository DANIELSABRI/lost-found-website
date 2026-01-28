<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Lost & Found Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="brand">Lost & Found</div>
    <div>
        <a href="#how">How It Works</a>
        <a href="#features">Features</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
            <a href="<?= BASE_URL ?>/auth/register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- HERO -->
<section style="padding:80px 40px; display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:60px;">
    <div>
        <h1 style="font-size:46px; line-height:1.2;">
            Find Lost Items <br>
            <span style="background:var(--gradient-main); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                Faster & Smarter
            </span>
        </h1>

        <p style="color:var(--text-muted); font-size:17px; max-width:520px;">
            A modern online platform to report, search, and recover lost items securely.
            Designed for students, staff, and administrators.
        </p>

        <div style="margin-top:30px;">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn-primary">Get Started</a>
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn-outline" style="margin-left:15px;">Login</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/user/report-lost.php" class="btn-primary">Report Lost Item</a>
                <a href="<?= BASE_URL ?>/user/report-found.php" class="btn-outline" style="margin-left:15px;">Report Found Item</a>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <!-- Placeholder illustration block -->
        <div class="card" style="text-align:center;">
            <h3 style="margin-bottom:10px;">Smart Matching</h3>
            <p style="color:var(--text-muted); font-size:14px;">
                Automatically matches lost and found items using category, date, and location.
            </p>
        </div>
    </div>
</section>

</body>
</html>
