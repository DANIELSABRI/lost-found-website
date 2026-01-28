<?php
// auth/login.php

session_start();
require_once __DIR__ . '/../includes/init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {

        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role, status 
             FROM users 
             WHERE email = ? 
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['status'] !== 'active') {
            $error = 'Invalid email or password.';
        } else {

            if (!password_verify($password, $user['password'])) {
                $error = 'Invalid email or password.';
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Role-based redirection
                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . '/user/dashboard.php');
                }
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Secure Access | Lost & Found Intelligence</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
    body {
        margin: 0;
        min-height: 100vh;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Dynamic Background Slider */
    .bg-slider {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: -1;
    }
    .bg-slide {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 1.5s ease-in-out;
    }
    .bg-slide.active { opacity: 1; }
    .bg-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.9) 100%);
    }

    /* Back Logo */
    .back-logo {
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 15vw;
        font-weight: 900;
        color: rgba(255,255,255,0.03);
        z-index: 0;
        white-space: nowrap;
        pointer-events: none;
        text-transform: uppercase;
        letter-spacing: -10px;
    }

    .auth-card {
        width: 100%;
        max-width: 480px;
        position: relative;
        z-index: 10;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 60px;
        border-radius: var(--radius-3xl);
        box-shadow: 0 40px 100px rgba(0,0,0,0.5);
        color: #fff;
    }

    h1 { font-size: 36px; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px; }
    h1 span { background: var(--grad-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    p { opacity: 0.6; margin-bottom: 40px; font-size: 14px; font-weight: 500; }

    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; color: rgba(255,255,255,0.6); }
    .form-control {
        background: rgba(255,255,255,0.05);
        border: 2px solid rgba(255,255,255,0.1);
        color: #fff;
        padding: 16px 20px;
        border-radius: 12px;
        width: 100%;
        font-family: inherit;
        font-size: 15px;
        transition: 0.3s;
    }
    .form-control:focus { outline: none; border-color: var(--color-primary); background: rgba(255,255,255,0.08); }

    .error-box {
        background: rgba(239, 68, 68, 0.2);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #fca5a5;
        padding: 15px;
        border-radius: 12px;
        font-size: 13px;
        margin-bottom: 25px;
        font-weight: 600;
    }

    .btn-auth {
        width: 100%;
        padding: 18px;
        background: var(--grad-primary);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.3s;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }
    .btn-auth:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4); }

    .auth-footer { margin-top: 30px; text-align: center; font-size: 14px; opacity: 0.6; }
    .auth-footer a { color: #fff; font-weight: 700; text-decoration: none; }
    .auth-footer a:hover { text-decoration: underline; }
</style>

</head>
<body>

    <!-- DYNAMIC BACKGROUND -->
    <div class="bg-slider">
        <div class="bg-slide active" style="background-image: url('<?= BASE_URL ?>/assets/img/auth/bg1.png');"></div>
        <div class="bg-slide" style="background-image: url('<?= BASE_URL ?>/assets/img/auth/bg2.png');"></div>
        <div class="bg-slide" style="background-image: url('<?= BASE_URL ?>/assets/img/auth/bg3.png');"></div>
        <div class="bg-overlay"></div>
    </div>

    <!-- BACKGROUND LOGO -->
    <div class="back-logo">Lost & Found</div>

    <div class="auth-card">
        <h1>Login <span>Intelligence</span></h1>
        <p>Command center access for verified campus members.</p>

        <?php if ($error): ?>
            <div class="error-box">⚠️ Clearance Denied: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Security Email</label>
                <input type="email" name="email" class="form-control" placeholder="staff.student@campus.edu" required autofocus>
            </div>

            <div class="form-group">
                <label>Access Key</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
            </div>

            <button type="submit" class="btn-auth">Initialize Session</button>
        </form>

        <div class="auth-footer">
            No credentials? <a href="register.php">Register Identity</a>
        </div>
    </div>

    <script>
        const slides = document.querySelectorAll('.bg-slide');
        let currentSlide = 0;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, 5000);
    </script>

</body>
</html>
