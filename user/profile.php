<?php
require_once __DIR__ . '/../includes/init.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// ---------------------------------------------------------
// FETCH CURRENT USER DATA
// ---------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ---------------------------------------------------------
// HANDLE FORM SUBMISSION
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Update Profile Info
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $bio = trim($_POST['bio']);
        $department = trim($_POST['department']);
        $social_links = json_encode([
            'linkedin' => trim($_POST['linkedin']),
            'website' => trim($_POST['website'])
        ]);

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, department = ?, social_links = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $bio, $department, $social_links, $user_id]);
            $message = "Profile details updated successfully!";
            $_SESSION['user_name'] = $name;
        } catch (PDOException $e) {
            $error = "Could not update profile: " . $e->getMessage();
        }
    }

    // 2. Handle Profile Picture Upload
    // ... (logic remains same)
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = "profile_" . $user_id . "_" . time() . "." . $ext;
            $upload_path = __DIR__ . "/../uploads/profiles/" . $new_name;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                if ($user['profile_pic'] && file_exists(__DIR__ . "/../uploads/profiles/" . $user['profile_pic'])) {
                    unlink(__DIR__ . "/../uploads/profiles/" . $user['profile_pic']);
                }
                $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$new_name, $user_id]);
                $message = "Profile picture updated!";
            }
        }
    }

    // (Refetch user)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

$socials = json_decode($user['social_links'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .profile-card {
        background: #fff;
        border-radius: 30px;
        box-shadow: var(--shadow-soft);
        overflow: hidden;
        display: grid;
        grid-template-columns: 320px 1fr;
        min-height: 600px;
    }
    
    .profile-sidebar {
        background: #F9FAFB;
        padding: 40px;
        text-align: center;
        border-right: 1px solid var(--color-bg);
    }

    .avatar-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto 20px;
    }

    .avatar-preview {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        background: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        color: #fff;
    }

    .upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: var(--color-primary);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        transition: 0.2s;
    }
    .upload-btn:hover { transform: scale(1.1); }

    .profile-nav { margin-top: 40px; text-align: left; }
    .profile-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        border-radius: 12px;
        color: var(--color-text-gray);
        font-weight: 500;
        cursor: pointer;
        margin-bottom: 8px;
        transition: 0.2s;
    }
    .profile-nav-item.active { background: var(--color-primary); color: #fff; }
    .profile-nav-item:hover:not(.active) { background: #fff; color: var(--color-primary); }

    .profile-main { padding: 50px; }

    .form-section { margin-bottom: 40px; }
    .form-section h3 { margin-bottom: 24px; font-size: 20px; color: var(--color-text-dark); border-bottom: 2px solid var(--color-bg); padding-bottom: 15px; }
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 13px; color: var(--color-text-gray); text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 15px;
        transition: 0.2s;
        background: #fff;
    }
    .form-control:focus { outline: none; border-color: var(--color-primary); background: #fff; }

    textarea.form-control { resize: none; min-height: 120px; }

    .status-alert {
        padding: 16px 24px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-weight: 500;
    }
    .alert-success { background: #DCFCE7; color: #15803D; }
    .alert-error { background: #FEE2E2; color: #B91C1C; }

    @media (max-width: 900px) {
        .profile-card { grid-template-columns: 1fr; }
        .profile-sidebar { border-right: none; border-bottom: 1px solid var(--color-bg); }
    }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        <span style="color: var(--color-primary);">Lost & Found</span>
    </div>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/<?= $_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php' ?>">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/profile.php" class="active">Profile</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-intro">
        <h1>Identity & Intelligence <span style="color: var(--color-primary);">Profile</span></h1>
        <p>Manage your campus credentials and professional presence within the network.</p>
    </div>

    <?php if ($message): ?>
        <div style="background: rgba(16, 185, 129, 0.1); color: var(--color-success); padding: 20px 30px; border-radius: var(--radius-lg); margin-bottom: 40px; border: 1px solid rgba(16, 185, 129, 0.2); font-weight: 700;">
            ✨ Protocol Update: <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="profile-card" style="box-shadow: var(--shadow-xl); border-radius: var(--radius-3xl); overflow: hidden; border: 1px solid rgba(255,255,255,0.7);">
        <!-- Sidebar -->
        <div class="profile-sidebar" style="background: rgba(248, 250, 252, 0.5); backdrop-filter: blur(5px);">
            <div class="avatar-wrapper">
                <?php if ($user['profile_pic']): ?>
                    <img id="avatar-img" src="<?= BASE_URL ?>/uploads/profiles/<?= $user['profile_pic'] ?>" class="avatar-preview" style="border: 6px solid #fff; box-shadow: var(--shadow-lg);">
                <?php else: ?>
                    <div class="avatar-preview" style="background: var(--grad-primary); border: 6px solid #fff; box-shadow: var(--shadow-lg);"><?= strtoupper(substr($user['name'],0,1)) ?></div>
                <?php endif; ?>
                
                <form id="pic-form" method="POST" enctype="multipart/form-data">
                    <label class="upload-btn" style="background: var(--color-primary); box-shadow: var(--shadow-md);">
                        📸
                        <input type="file" name="profile_pic" hidden onchange="document.getElementById('pic-form').submit()">
                    </label>
                </form>
            </div>
            
            <h2 style="font-size: 26px; font-weight: 800; letter-spacing: -1px; color: var(--color-text-main);"><?= htmlspecialchars($user['name']) ?></h2>
            <div class="badge status-matched" style="margin-top: 10px; font-weight: 800;"><?= strtoupper($user['role']) ?></div>
            
            <div style="margin-top: 25px; background: #fff; padding: 20px; border-radius: var(--radius-xl); border: 1px solid rgba(0,0,0,0.03); text-align: left;">
                <p style="color: var(--color-text-muted); font-size: 13px; line-height: 1.6; font-weight: 500;">
                    <?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'No bio added yet. Tell the campus about your role and expertise.' ?>
                </p>
            </div>

            <div class="profile-nav">
                <div class="profile-nav-item active" onclick="showSection('personal')" style="font-size: 13px;">🛡️ Profile Metadata</div>
                <div class="profile-nav-item" onclick="showSection('professional')" style="font-size: 13px;">🎓 Professional Data</div>
                <div class="profile-nav-item" onclick="showSection('security')" style="font-size: 13px;">🔒 Security Settings</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="profile-main" style="background: #fff;">
            
            <!-- Personal Info Section -->
            <form method="POST" id="section-personal" class="profile-section">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-section">
                    <h3>Core Credentials</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Full Legal Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Intelligence *</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+1 234 567 890">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Primary Access Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Brief Intelligence Bio</label>
                        <textarea name="bio" class="form-control" placeholder="Write a short bio..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; padding: 20px;">Synchronize Profile</button>
            </form>

            <!-- Professional Section -->
            <form method="POST" id="section-professional" class="profile-section" style="display:none;">
                <input type="hidden" name="update_profile" value="1">
                <input type="hidden" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                <input type="hidden" name="bio" value="<?= htmlspecialchars($user['bio'] ?? '') ?>">

                <div class="form-section">
                    <h3>Domain Expertise</h3>
                    <div class="form-group">
                        <label>Departmental Affiliation</label>
                        <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>" placeholder="e.g., Cyber Security Department">
                    </div>
                    <div class="grid-2" style="margin-top: 30px;">
                        <div class="form-group">
                            <label>Social Intelligence (LinkedIn)</label>
                            <input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($socials['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
                        </div>
                        <div class="form-group">
                            <label>Professional Portfolio</label>
                            <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($socials['website'] ?? '') ?>" placeholder="https://yourwork.com">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; padding: 20px;">Update Professional Record</button>
            </form>

            <!-- Security Section -->
            <form method="POST" id="section-security" class="profile-section" style="display:none;">
                <input type="hidden" name="update_password" value="1">
                <div class="form-section">
                    <h3>Access Protocols</h3>
                    <div class="form-group">
                        <label>Current Security Key</label>
                        <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="grid-2" style="margin-top: 30px;">
                        <div class="form-group">
                            <label>New Security Key</label>
                            <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label>Verify New Key</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; padding: 20px; background: var(--grad-dark); box-shadow: var(--shadow-lg);">Rotate Security Keys</button>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
function showSection(id) {
    document.querySelectorAll('.profile-section').forEach(s => s.style.display = 'none');
    document.getElementById('section-' + id).style.display = 'block';
    
    document.querySelectorAll('.profile-nav-item').forEach(item => {
        item.classList.remove('active');
        if(item.innerText.toLowerCase().includes(id)) {
            item.classList.add('active');
        }
    });
}
</script>

</body>
</html>
