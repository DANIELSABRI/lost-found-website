<?php
require_once __DIR__ . '/../includes/init.php';

// Admin protection
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// ---------------------------------------------------------
// DATABASE INITIALIZATION (Self-healing settings table)
// ---------------------------------------------------------
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )");

    $defaults = [
        'site_name' => 'Lost & Found Portal',
        'site_email' => 'admin@university.edu',
        'maintenance_mode' => '0',
        'allow_registration' => '1',
        'auto_approve_items' => '0',
        'contact_phone' => '+1 (555) 000-0000',
        'reports_per_page' => '20'
    ];

    foreach ($defaults as $key => $val) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $val]);
    }
} catch (PDOException $e) {
    $error = "System Error: " . $e->getMessage();
}

// ---------------------------------------------------------
// HANDLE FORM SUBMISSION
// ---------------------------------------------------------
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        try {
            foreach ($_POST['settings'] as $key => $value) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            }
            $message = "Settings updated successfully!";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }

    if (isset($_POST['clear_logs'])) {
        $pdo->exec("TRUNCATE TABLE audit_logs");
        $message = "Audit logs cleared successfully!";
    }
}

$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>System Parameters | Command Intelligence</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .settings-wrapper {
        display: grid;
        grid-template-columns: 320px 1fr;
        background: #fff;
        border-radius: var(--radius-3xl);
        overflow: hidden;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(0,0,0,0.02);
    }

    .settings-sidebar {
        background: #F9FAFB;
        padding: 40px;
        border-right: 1px solid var(--color-bg);
    }
    .settings-sidebar h3 { font-size: 20px; font-weight: 800; margin-bottom: 30px; letter-spacing: -0.5px; }

    .setting-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 14px;
        color: var(--color-text-muted);
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        margin-bottom: 10px;
        font-size: 13px;
    }
    .setting-nav-item:hover { background: #fff; color: var(--color-primary); }
    .setting-nav-item.active { background: var(--color-primary); color: #fff; box-shadow: var(--shadow-md); }

    .settings-main { padding: 60px; }
    .setting-section { display: none; }
    .setting-section.active { display: block; animation: fadeIn 0.3s ease; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .form-group-elite { margin-bottom: 35px; }
    .form-group-elite label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; color: var(--color-text-light); }
    .form-control-elite {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid var(--color-bg);
        border-radius: 12px;
        font-family: inherit;
        font-size: 15px;
        font-weight: 600;
        transition: 0.3s;
        background: #FDFDFF;
    }
    .form-control-elite:focus { outline: none; border-color: var(--color-primary); background: #fff; }

    .switch-elite {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .switch-elite input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 4px; bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--color-primary); }
    input:checked + .slider:before { transform: translateX(24px); }

    .maintenance-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px;
        background: #F9FAFB;
        border-radius: 15px;
        margin-bottom: 20px;
    }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found — <span style="color: var(--color-primary);">Admin Intelligence</span></div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-items.php">Items</a>
        <a href="manage-users.php">Users</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php" class="active">Settings</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-intro">
        <h1>Global System Parameters</h1>
        <p>Command-level configuration for network intelligence, clearance rules, and platform health.</p>
    </div>

    <?php if ($message): ?>
        <div style="background: rgba(16, 185, 129, 0.1); color: var(--color-success); padding: 20px 30px; border-radius: var(--radius-lg); margin-bottom: 40px; border: 1px solid rgba(16, 185, 129, 0.2); font-weight: 700;">
            ✨ Protocol Updated: <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="settings-wrapper">
        <div class="settings-sidebar">
            <h3>Parameters</h3>
            <div class="setting-nav-item active" onclick="showTab('general')">🏠 Network Identity</div>
            <div class="setting-nav-item" onclick="showTab('clearance')">🛡️ Clearance Rules</div>
            <div class="setting-nav-item" onclick="showTab('maintenance')">🔧 Maintenance Loop</div>
        </div>

        <div class="settings-main">
            <form method="POST">
                <input type="hidden" name="update_settings" value="1">
                
                <!-- General Section -->
                <div id="section-general" class="setting-section active">
                    <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 35px;">Identity Configuration</h3>
                    
                    <div class="form-group-elite">
                        <label>Portal Designation</label>
                        <input type="text" name="settings[site_name]" class="form-control-elite" value="<?= htmlspecialchars($settings['site_name']) ?>">
                    </div>

                    <div class="form-group-elite">
                        <label>Primary Command Email</label>
                        <input type="email" name="settings[site_email]" class="form-control-elite" value="<?= htmlspecialchars($settings['site_email']) ?>">
                    </div>

                    <div class="form-group-elite">
                        <label>Emergency Support Frequency (Phone)</label>
                        <input type="text" name="settings[contact_phone]" class="form-control-elite" value="<?= htmlspecialchars($settings['contact_phone']) ?>">
                    </div>
                </div>

                <!-- Clearance Section -->
                <div id="section-clearance" class="setting-section">
                    <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 35px;">Security Protocols</h3>
                    
                    <div class="maintenance-toggle">
                        <div>
                            <strong style="display: block; font-size: 15px;">New Identity Clearance</strong>
                            <span style="font-size: 12px; color: var(--color-text-muted);">Allow new students/staff to join the network.</span>
                        </div>
                        <label class="switch-elite">
                            <input type="hidden" name="settings[allow_registration]" value="0">
                            <input type="checkbox" name="settings[allow_registration]" value="1" <?= $settings['allow_registration'] == '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="maintenance-toggle">
                        <div>
                            <strong style="display: block; font-size: 15px;">Autonomous Approval</strong>
                            <span style="font-size: 12px; color: var(--color-text-muted);">Bypass administrative review for new asset reports.</span>
                        </div>
                        <label class="switch-elite">
                            <input type="hidden" name="settings[auto_approve_items]" value="0">
                            <input type="checkbox" name="settings[auto_approve_items]" value="1" <?= $settings['auto_approve_items'] == '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="maintenance-toggle" style="background: rgba(239, 68, 68, 0.05);">
                        <div>
                            <strong style="display: block; font-size: 15px; color: #ef4444;">System Sentry Mode (Maintenance)</strong>
                            <span style="font-size: 12px; color: var(--color-text-muted);">Lock frontend access to administrators only.</span>
                        </div>
                        <label class="switch-elite">
                            <input type="hidden" name="settings[maintenance_mode]" value="0">
                            <input type="checkbox" name="settings[maintenance_mode]" value="1" <?= $settings['maintenance_mode'] == '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Maintenance Section -->
                <div id="section-maintenance" class="setting-section">
                    <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 35px;">Operations Buffer</h3>
                    
                    <div class="form-group-elite">
                        <label>Intelligence Feed Buffer (Pagination)</label>
                        <input type="number" name="settings[reports_per_page]" class="form-control-elite" value="<?= htmlspecialchars($settings['reports_per_page']) ?>">
                    </div>

                    <div style="background: #FFF1F2; padding: 30px; border-radius: 20px; border: 1px solid #FECDD3; margin-top: 50px;">
                        <h4 style="color: #E11D48; font-weight: 800; margin-bottom: 10px;">Deep Clearance Zone</h4>
                        <p style="font-size: 13px; color: #9F1239; margin-bottom: 25px;">Permanently purge system activity logs. This action cannot be reversed.</p>
                        <button type="submit" name="clear_logs" class="btn-sm btn-sm-danger" onclick="return confirm('Initiate total log purge?')">Total Log Purge</button>
                    </div>
                </div>

                <div style="margin-top: 50px; padding-top: 40px; border-top: 1px solid var(--color-bg);">
                    <button type="submit" class="btn-primary" style="width: auto; padding: 18px 50px;">Apply Global Parameters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
function showTab(id) {
    document.querySelectorAll('.setting-section').forEach(s => s.classList.remove('active'));
    document.getElementById('section-' + id).classList.add('active');
    
    document.querySelectorAll('.setting-nav-item').forEach(item => {
        item.classList.remove('active');
        if(item.innerText.toLowerCase().includes(id)) {
            item.classList.add('active');
        }
    });
}
</script>

</body>
</html>
