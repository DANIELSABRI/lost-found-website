<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name   = trim($_POST['item_name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $item_date   = $_POST['item_date'] ?? null;
    $description = trim($_POST['description'] ?? '');

    if ($item_name === '' || $category === '' || $location === '' || !$item_date) {
        $error = 'Please fill in all required fields.';
    } else {
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $imageName = time() . '_' . uniqid() . '.' . $ext;
                $targetPath = __DIR__ . '/../uploads/' . $imageName;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image type. Only JPG, PNG and WebP are allowed.";
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO items 
                    (user_id, item_name, category, location, item_date, description, image, type, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'found', 'pending', NOW())
                ");

                $stmt->execute([
                    $_SESSION['user_id'],
                    $item_name,
                    $category,
                    $location,
                    $item_date,
                    $description,
                    $imageName
                ]);

                header('Location: dashboard.php?msg=reported');
                exit;

            } catch (PDOException $e) {
                $error = 'System Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Found Item | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .report-card {
        background: #fff;
        border-radius: 30px;
        box-shadow: var(--shadow-soft);
        padding: 50px;
        max-width: 900px;
        margin: 0 auto;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 10px; font-size: 14px; color: var(--color-text-gray); text-transform: uppercase; letter-spacing: 1px; }
    
    .form-control {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid var(--color-bg);
        border-radius: 12px;
        font-family: inherit;
        font-size: 15px;
        transition: 0.2s;
        background: #FDFDFF;
    }
    .form-control:focus { outline: none; border-color: var(--color-primary); background: #fff; }
    
    textarea.form-control { resize: none; min-height: 120px; }

    .image-upload-box {
        border: 2px dashed var(--color-bg);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: 0.2s;
        position: relative;
    }
    .image-upload-box:hover { border-color: var(--color-primary); background: var(--color-primary-light); }
    .image-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 12px;
        display: none;
        margin-top: 15px;
    }

    .btn-submit {
        background: var(--color-primary);
        color: #fff;
        border: none;
        padding: 16px 40px;
        border-radius: 15px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
        margin-top: 20px;
    }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(108, 93, 211, 0.2); }

    .status-alert { padding: 20px; border-radius: 15px; margin-bottom: 30px; }
    .alert-error { background: #FEF2F2; color: #B91C1C; }

    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

    /* Layout Shim */
    .dashboard-layout { display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-top: 40px; }
    @media (max-width: 900px) { .dashboard-layout { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        <span style="color: var(--color-primary);">Lost & Found</span>
    </div>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost</a>
        <a href="<?= BASE_URL ?>/user/report-found.php" class="active">Report Found</a>
        <a href="<?= BASE_URL ?>/user/profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="dashboard-layout">
        <!-- Main Form Content -->
        <main>
            <div class="page-intro" style="text-align: left; margin: 0 0 30px 0;">
                <h1>Discovery Intelligence: <span style="color: var(--color-success);">Found Item</span></h1>
                <p>Your discovery is the first step toward recovery. Provide precise details to match this item with its owner.</p>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: var(--color-danger); padding: 20px 30px; border-radius: var(--radius-lg); margin-bottom: 40px; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 700;">
                    ⚠️ Protocol Error: <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="glass-card" style="margin: 0;">
                <form method="POST" enctype="multipart/form-data">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
                        <section>
                            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 25px; color: var(--color-text-main); display: flex; align-items: center; gap: 10px;">
                                <span style="width: 32px; height: 32px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px;">1</span>
                                Identification Details
                            </h3>
                            
                            <div class="form-group">
                                <label>Item Name *</label>
                                <input type="text" name="item_name" class="form-control" placeholder="e.g. Red Nike Backpack" required>
                            </div>

                            <div class="form-group">
                                <label>System Category *</label>
                                <select name="category" class="form-control" required style="cursor: pointer;">
                                    <option value="">Select Category</option>
                                    <option>Electronics</option>
                                    <option>Wallets & Bags</option>
                                    <option>Documents (ID, Keys)</option>
                                    <option>Jewelry & Watches</option>
                                    <option>Others</option>
                                </select>
                            </div>
                        </section>

                        <section>
                            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 25px; color: var(--color-text-main); display: flex; align-items: center; gap: 10px;">
                                <span style="width: 32px; height: 32px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px;">2</span>
                                Discovery Parameters
                            </h3>

                            <div class="form-group">
                                <label>Recovery Location *</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Student Union Hall" required>
                            </div>

                            <div class="form-group">
                                <label>Date of Discovery *</label>
                                <input type="date" name="item_date" class="form-control" required>
                            </div>
                        </section>
                    </div>

                    <div style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 40px; margin-bottom: 40px;">
                        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 25px; color: var(--color-text-main); display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px;">3</span>
                            Visual Assets
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                            <div class="form-group">
                                <label>Item Health & Description *</label>
                                <textarea name="description" class="form-control" style="min-height: 180px;" placeholder="Describe the item's condition and any distinguishing features..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Visual Asset (Recommended)</label>
                                <div class="image-upload-box" style="height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center;" onclick="document.getElementById('item-img').click()">
                                    <div id="upload-placeholder">
                                        <span style="font-size: 40px;">🎁</span>
                                        <p style="margin-top: 10px; font-weight: 800; font-size: 14px;">Upload Item Photo</p>
                                        <p style="font-size: 11px; color: var(--color-text-light);">HEIC, JPG, PNG allowed</p>
                                    </div>
                                    <img id="preview" class="image-preview" style="max-height: 140px;">
                                    <input type="file" id="item-img" name="image" hidden accept="image/*" onchange="previewImage(this)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; border-radius: var(--radius-xl); padding: 22px; background: var(--color-success); box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);">Initialize Found Intelligence</button>
                </form>
            </div>
        </main>

         <!-- Sidebar -->
        <aside>
            <div class="sidebar-block" style="background: var(--grad-dark); border: none; color: #fff;">
                <h3 style="color: #fff; margin-bottom: 20px;">Command Hub</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="dashboard.php" style="background: rgba(255,255,255,0.08); padding: 15px 20px; border-radius: 12px; text-decoration: none; color: #fff; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-weight: 600; font-size: 13px;">◄ Return to Dashboard</span>
                    </a>
                </div>
            </div>

            <div class="glass-card" style="padding: 30px; text-align: center;">
                <span style="font-size: 32px;">🤝</span>
                <h5 style="margin-top: 15px; font-weight: 800;">Integrity Protocol</h5>
                <p style="font-size: 12px; color: var(--color-text-muted); margin-top: 8px;">Turning in lost items increases trust ratings by 85%. Thank you.</p>
            </div>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('preview').style.display = 'inline-block';
            document.getElementById('upload-placeholder').style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>
