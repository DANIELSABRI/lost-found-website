<?php
// auth/register.php
require_once __DIR__ . '/../includes/init.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    if ($name === '') $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    if (empty($errors)) {
        try {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = "Email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
                $insert->execute([$name, $email, $hash, $role]);
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register Identity | Lost & Found Intelligence</title>
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
        max-width: 520px;
        position: relative;
        z-index: 10;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 50px 60px;
        border-radius: var(--radius-3xl);
        box-shadow: 0 40px 100px rgba(0,0,0,0.5);
        color: #fff;
    }

    h1 { font-size: 36px; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px; }
    h1 span { background: var(--grad-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    p { opacity: 0.6; margin-bottom: 30px; font-size: 14px; font-weight: 500; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; color: rgba(255,255,255,0.6); }
    .form-control {
        background: rgba(255,255,255,0.05);
        border: 2px solid rgba(255,255,255,0.1);
        color: #fff;
        padding: 14px 20px;
        border-radius: 12px;
        width: 100%;
        font-family: inherit;
        font-size: 14px;
        transition: 0.3s;
    }
    .form-control:focus { outline: none; border-color: var(--color-primary); background: rgba(255,255,255,0.08); }

    .error-box { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; padding: 12px; border-radius: 12px; font-size: 13px; margin-bottom: 20px; font-weight: 600; }
    .success-box { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7; padding: 15px; border-radius: 12px; font-size: 13px; margin-bottom: 20px; text-align: center; }

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

    <div class="bg-slider">
        <div class="bg-slide active" style="background-image: url('<?= BASE_URL ?>/assets/img/auth/bg1.png');"></div>
        <div class="bg-slide" style="background-image: url('<?= BASE_URL ?>/assets/img/auth/bg2.png');"></div>
        <div class="bg-slide" style="background-image: url('<?= BASE_URL ?>/assets/img/auth/bg3.png');"></div>
        <div class="bg-overlay"></div>
    </div>

    <div class="back-logo">Lost & Found</div>

    <div class="auth-card">
        <h1>Create <span>Identity</span></h1>
        <p>Register to join the campus intelligence network.</p>

        <?php if ($success): ?>
            <div class="success-box">
                ✨ Identity Synchronized. <br>
                <a href="login.php" style="color:#fff; font-weight:800;">Initialize Session Here</a>
            </div>
        <?php else: ?>
            <?php foreach ($errors as $e): ?>
                <div class="error-box">⚠️ Clearance Denied: <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Legal Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Daniel Sabri" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Security Email</label>
                        <input type="email" name="email" class="form-control" placeholder="name@campus.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Campus Role</label>
                        <select name="role" class="form-control" style="background: rgba(255,255,255,0.05); color: #fff; appearance: none; cursor: pointer;">
                            <option value="student" style="background:#1e293b;">Student</option>
                            <option value="staff" style="background:#1e293b;">Staff</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Establish Access Key</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>

                <button type="submit" class="btn-auth">Register Identity</button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            Already verified? <a href="login.php">Initialize Session</a>
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
