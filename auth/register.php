<?php
require_once __DIR__ . '/../includes/init.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    // Basic validation
    if ($name === '') {
        $errors[] = "Full name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (!in_array($role, ['student', 'staff'])) {
        $role = 'student';
    }

    if (empty($errors)) {
        try {
            // Check duplicate email
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->fetch()) {
                $errors[] = "Email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare(
                    "INSERT INTO users (name, email, password, role, status)
                     VALUES (?, ?, ?, ?, 'active')"
                );

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
<title>Register | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #4B2FDB, #6C4DFF);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    background: #fff;
    border-radius: 22px;
    padding: 42px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 40px 90px rgba(0,0,0,0.25);
}

h1 span {
    color: #6C4DFF;
}

label {
    font-weight: 600;
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    margin-bottom: 16px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 14px;
}

button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 12px;
    background: #6C4DFF;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

.error {
    background: #FEE2E2;
    color: #991B1B;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 12px;
    font-size: 14px;
}

.success {
    background: #DCFCE7;
    color: #065F46;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 12px;
    font-size: 14px;
}
</style>
</head>

<body>

<div class="card">
    <h1>Create <span>Account</span></h1>
    <p style="color:#666;">Register to use the Lost & Found system.</p>

    <?php if ($success): ?>
        <div class="success">
            Registration successful.
            <br>
            <a href="<?= BASE_URL ?>/auth/login.php">Click here to login</a>
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" novalidate>
        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label>Password</label>
        <input type="password" name="password">

        <label>Role</label>
        <select name="role">
            <option value="student">Student</option>
            <option value="staff">Staff</option>
        </select>

        <button type="submit">Register</button>
    </form>

    <p style="margin-top:16px;font-size:14px;">
        Already have an account?
        <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
    </p>
</div>

</body>
</html>
