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

            // ✅ ADMIN → PLAIN PASSWORD (DEV MODE)
            if ($user['role'] === 'admin') {

                if ($password !== $user['password']) {
                    $error = 'Invalid email or password.';
                } else {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = 'admin';

                    header('Location: ../admin/dashboard.php');
                    exit;
                }

            } 
            // ✅ USERS → HASHED PASSWORD
            else {

                if (!password_verify($password, $user['password'])) {
                    $error = 'Invalid email or password.';
                } else {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = 'user';

                    header('Location: ../user/dashboard.php');
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#5b3df5,#7c6cff);
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:system-ui,-apple-system,BlinkMacSystemFont,sans-serif;
}
.card{
    width:100%;
    max-width:420px;
    background:#fff;
    padding:32px;
    border-radius:16px;
    box-shadow:0 20px 40px rgba(0,0,0,.15);
}
h1{margin:0 0 6px;font-size:28px;}
h1 span{color:#6b5cff;}
p{margin:0 0 20px;color:#555;}
.error{
    background:#fde2e2;
    color:#b42318;
    padding:12px;
    border-radius:8px;
    margin-bottom:16px;
    font-size:14px;
}
label{display:block;margin-bottom:6px;font-weight:500;}
input{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid #ddd;
    margin-bottom:16px;
    font-size:15px;
}
input:focus{outline:none;border-color:#6b5cff;}
button{
    width:100%;
    padding:12px;
    background:#6b5cff;
    color:#fff;
    border:none;
    border-radius:10px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
button:hover{background:#5848e5;}
.switch{
    margin-top:16px;
    text-align:center;
    font-size:14px;
}
.switch a{
    color:#6b5cff;
    font-weight:600;
    text-decoration:none;
}
.switch a:hover{text-decoration:underline;}
</style>
</head>

<body>

<div class="card">
<h1>Login <span>Account</span></h1>
<p>Access the Lost & Found system.</p>

<?php if ($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
<label>Email</label>
<input type="email" name="email" required>

<label>Password</label>
<input type="password" name="password" required>

<button type="submit">Login</button>
</form>

<div class="switch">
Don’t have an account?
<a href="register.php">Register here</a>
</div>
</div>

</body>
</html>
