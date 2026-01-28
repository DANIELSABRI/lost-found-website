<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$target_id = $_GET['target_id'] ?? null;

if (!$target_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch Target Item
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$target_id]);
$targetInfo = $stmt->fetch();

if (!$targetInfo) {
    echo "Item not found.";
    exit;
}

if ($targetInfo['user_id'] == $_SESSION['user_id']) {
    echo "You cannot match your own item.";
    exit;
}

// Determine what we are looking for
// If target is LOST, we need my FOUND items
// If target is FOUND, we need my LOST items
$neededType = ($targetInfo['type'] === 'lost') ? 'found' : 'lost';

// Fetch My Candidates
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? AND type = ? AND status != 'closed'");
$stmt->execute([$_SESSION['user_id'], $neededType]);
$myCandidates = $stmt->fetchAll();

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['my_item_id'])) {
    $my_item_id = $_POST['my_item_id'];
    
    // Validate ownership
    $check = $pdo->prepare("SELECT id FROM items WHERE id = ? AND user_id = ?");
    $check->execute([$my_item_id, $_SESSION['user_id']]);
    if ($check->fetch()) {
        
        $lost_id = ($targetInfo['type'] === 'lost') ? $target_id : $my_item_id;
        $found_id = ($targetInfo['type'] === 'found') ? $target_id : $my_item_id;

        // Insert Match
        $stmt = $pdo->prepare("INSERT INTO matches (lost_item_id, found_item_id, proposed_by_user_id, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$lost_id, $found_id, $_SESSION['user_id']]);

        // Send Notification to the other user (Target Owner)
        // Check if notify function exists and is safe to use, otherwise skip or do manually
        if (function_exists('notify')) {
            notify($pdo, $targetInfo['user_id'], "New Match Proposal", "A user has proposed a match for your item: " . $targetInfo['item_name'], "match");
        }
        
        header("Location: ../item.php?id=$target_id&msg=match_proposed");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Propose Match | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .match-container { max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); }
    .candidate-list { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; }
    .candidate-item {
        display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #edeff5; border-radius: 12px; cursor: pointer; transition: 0.2s;
    }
    .candidate-item:hover { border-color: var(--color-primary); background: #f8f9ff; }
    .candidate-item input { width: 20px; height: 20px; accent-color: var(--color-primary); }
    .btn-submit { width: 100%; margin-top: 20px; padding: 15px; font-size: 16px; }

    /* Layout Shim */
    .dashboard-layout { display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-top: 40px; }
    @media (max-width: 900px) { .dashboard-layout { grid-template-columns: 1fr; } }
</style>
</head>
<body>
    <nav class="top-nav">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">Lost & Found</a>
    </nav>

    <div class="container">
        <div class="dashboard-layout">
            <main>
                <div class="match-container" style="max-width: 100%; margin: 0; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.02);">
                    <div style="border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px;">
                         <h2 style="margin-bottom: 10px; font-weight: 800;">Propose a Match</h2>
                         <p style="color: var(--color-text-muted);">Identify the counterpart to this item from your reports.</p>
                    </div>

                    <div style="background: #f8f9ff; border: 1px solid #e0e7ff; padding: 20px; border-radius: 15px; margin-bottom: 30px;">
                        <p style="font-size: 12px; font-weight: 700; color: var(--color-primary); text-transform: uppercase; margin-bottom: 5px;">Target Item</p>
                        <strong style="font-size: 18px;"><?= htmlspecialchars($targetInfo['item_name']) ?></strong> 
                        <span style="font-size: 12px; background: <?= $targetInfo['type'] == 'lost' ? '#fee2e2' : '#d1fae5' ?>; color: <?= $targetInfo['type'] == 'lost' ? '#dc2626' : '#059669' ?>; padding: 4px 8px; border-radius: 6px; margin-left: 10px; font-weight: 800;"><?= strtoupper($targetInfo['type']) ?></span>
                    </div>

                    <p style="color: var(--color-text-gray); font-size: 14px; margin-bottom: 15px;">Select one of your <strong><?= strtoupper($neededType) ?></strong> items to propose a match:</p>
                    
                    <?php if (empty($myCandidates)): ?>
                        <div style="padding: 40px; text-align: center; background: #fff; border: 2px dashed #e2e8f0; border-radius: 15px;">
                            <span style="font-size: 40px; display:block; margin-bottom:10px;">📂</span>
                            <p style="font-weight: 600;">No candiate items found.</p>
                            <p style="font-size: 13px; color: gray; margin-bottom: 20px;">You don't have any open "<?= $neededType ?>" items to match against this report.</p>
                            <a href="report-<?= $neededType ?>.php" class="btn-primary" style="display:inline-block;">Report <?= ucfirst($neededType) ?> Item</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="candidate-list">
                                <?php foreach ($myCandidates as $cand): ?>
                                    <label class="candidate-item">
                                        <input type="radio" name="my_item_id" value="<?= $cand['id'] ?>" required>
                                        <div>
                                            <strong><?= htmlspecialchars($cand['item_name']) ?></strong>
                                            <div style="font-size: 12px; color: gray;"><?= htmlspecialchars($cand['location']) ?> • <?= htmlspecialchars($cand['status']) ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn-primary btn-submit" style="margin-top: 30px;">Confirm Match Proposal</button>
                        </form>
                    <?php endif; ?>
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
                    <span style="font-size: 32px;">🔗</span>
                    <h5 style="margin-top: 15px; font-weight: 800;">Match Protocol</h5>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin-top: 8px;">Proposing a match notifies the other party instantly for confirmation.</p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
