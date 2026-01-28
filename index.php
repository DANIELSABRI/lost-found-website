<?php
require_once __DIR__ . '/includes/init.php';

// Fetch Live Stats
$totalItems = 0;
$itemsReturned = 0;

try {
    $totalItems = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    // Assuming 'matched' or 'closed' means returned/resolved
    $itemsReturned = $pdo->query("SELECT COUNT(*) FROM items WHERE status IN ('matched', 'closed')")->fetchColumn();
} catch (Exception $e) {
    // Silent fail for public page
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>University Lost & Found | Secure Campus Recovery</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
/* LANDING PAGE SPECIFIC STYLES */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 40px;
    max-width: 1280px;
    margin: 0 auto;
}

.hero-section {
    padding: 80px 40px;
    max-width: 1280px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 60px;
}

.hero-text h1 {
    font-size: 56px;
    line-height: 1.1;
    margin-bottom: 24px;
    background: linear-gradient(135deg, #11142D 0%, #6C5DD3 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-text p {
    font-size: 18px;
    color: var(--color-text-gray);
    margin-bottom: 40px;
    line-height: 1.6;
    max-width: 540px;
}

.hero-btns {
    display: flex;
    gap: 16px;
}

.btn-lg {
    padding: 16px 32px;
    font-size: 16px;
    border-radius: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s;
}

.btn-lg:hover {
    transform: translateY(-2px);
}

.btn-primary-lg {
    background: var(--color-primary);
    color: #fff;
    box-shadow: 0 10px 30px rgba(108, 93, 211, 0.4);
}

.btn-outline-lg {
    background: #fff;
    color: var(--color-text-dark);
    border: 2px solid #E4E4E4;
}

.hero-stats {
    display: flex;
    gap: 40px;
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px solid #E4E4E4;
}

.stat-item strong {
    font-size: 32px;
    color: var(--color-text-dark);
    display: block;
}

.stat-item span {
    font-size: 14px;
    color: var(--color-text-gray);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.illustration-card {
    background: var(--color-white);
    padding: 40px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}

.illustration-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(108, 93, 211, 0.1) 0%, rgba(255, 117, 76, 0.1) 100%);
    border-radius: 50%;
    z-index: 0;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    padding: 80px 40px;
    max-width: 1280px;
    margin: 0 auto;
}

.feature-card {
    background: #fff;
    padding: 40px;
    border-radius: 24px;
    box-shadow: var(--shadow-soft);
    text-align: center;
}

.feature-icon {
    width: 64px;
    height: 64px;
    background: #F0F3F8;
    color: var(--color-primary);
    border-radius: 20px;
    display: inline-flex; /* Use inline-flex to center content horizontally */
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 24px;
    margin-left: auto;
    margin-right: auto;
}

.footer {
    background: #fff;
    border-top: 1px solid #E4E4E4;
    padding: 60px 40px;
    margin-top: 80px;
}

.footer-content {
    max-width: 1280px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

@media(max-width: 900px) {
    .hero-section { grid-template-columns: 1fr; text-align: center; }
    .hero-text p { margin: 0 auto 40px auto; }
    .hero-btns { justify-content: center; }
    .hero-stats { justify-content: center; }
    .feature-grid { grid-template-columns: 1fr; }
    .footer-content { flex-direction: column; gap: 20px; }
}
</style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand" style="text-decoration:none;">
            Lost & Found
        </a>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn-primary" style="padding:10px 24px; border-radius:10px; color:white;">Go to Admin Dashboard</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn-primary" style="padding:10px 24px; border-radius:10px; color:white;">Go to Dashboard</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--color-text-gray); font-weight:600; margin-right:24px;">Login</a>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn-primary" style="padding:10px 24px; border-radius:10px; color:white;">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="hero-text">
            <h1>Lost Something<br>on Campus?</h1>
            <p>Don't panic. Our centralized recovery system helps you report, track, and recover your lost belongings quickly and securely.</p>
            
            <div class="hero-btns">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="user/report-lost.php" class="btn-lg btn-primary-lg">Report Lost Item</a>
                    <a href="user/report-found.php" class="btn-lg btn-outline-lg">I Found Something</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn-lg btn-primary-lg">Get Started</a>
                    <a href="auth/login.php" class="btn-lg btn-outline-lg">Login</a>
                <?php endif; ?>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <strong><?= number_format($totalItems) ?></strong>
                    <span>Items Reported</span>
                </div>
                <!-- Vertical Line -->
                <div style="width:1px; background:#E4E4E4;"></div>
                <div class="stat-item">
                    <strong><?= number_format($itemsReturned / max($totalItems, 1) * 100, 0) ?>%</strong>
                    <span>Recovery Rate</span>
                </div>
                 <!-- Vertical Line -->
                 <div style="width:1px; background:#E4E4E4;"></div>
                 <div class="stat-item">
                    <strong>24/7</strong>
                    <span>Active Support</span>
                </div>
            </div>
        </div>

        <div class="illustration-card">
            <h3 style="font-size:24px; margin-bottom:16px;">How It Works</h3>
            <div style="display:flex; flex-direction:column; gap:20px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:40px; height:40px; background:#F0F3F8; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--color-primary); font-weight:bold;">1</div>
                    <p style="margin:0; font-size:15px;">Log in and report your lost or found item.</p>
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:40px; height:40px; background:#F0F3F8; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--color-primary); font-weight:bold;">2</div>
                    <p style="margin:0; font-size:15px;">Our system checks for potential matches.</p>
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:40px; height:40px; background:#F0F3F8; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--color-primary); font-weight:bold;">3</div>
                    <p style="margin:0; font-size:15px;">Connect securely and claim your item.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">🔍</div>
            <h3>Smart Search</h3>
            <p style="color:var(--color-text-gray); margin-top:12px; line-height:1.6;">Easily filter items by category, date, and location to find exactly what you're looking for.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h3>Secure Claims</h3>
            <p style="color:var(--color-text-gray); margin-top:12px; line-height:1.6;">Verification process ensures items are returned only to their rightful owners.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>Direct Chat</h3>
            <p style="color:var(--color-text-gray); margin-top:12px; line-height:1.6;">Communicate anonymously with finders helps coordinate item recovery.</p>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-content">
            <div style="text-align:left;">
                <div class="nav-brand" style="margin-bottom:12px;">Lost & Found</div>
                <p style="font-size:14px; color:var(--color-text-gray);">© <?= date('Y') ?> University Management System. All rights reserved.</p>
            </div>
            <div style="display:flex; gap:32px;">
                <a href="#" style="color:var(--color-text-gray); font-size:14px;">Privacy Policy</a>
                <a href="#" style="color:var(--color-text-gray); font-size:14px;">Terms of Service</a>
                <a href="#" style="color:var(--color-text-gray); font-size:14px;">Contact Support</a>
            </div>
        </div>
    </footer>

</body>
</html>
