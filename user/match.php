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
</style>
</head>
<body>
    <nav class="top-nav">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">Lost & Found</a>
    </nav>

    <div class="match-container">
        <h2 style="margin-bottom: 20px;">Propose a Match</h2>
        <p>You are viewing: <strong><?= htmlspecialchars($targetInfo['item_name']) ?></strong> (<?= strtoupper($targetInfo['type']) ?>)</p>
        <p style="color: var(--color-text-gray); font-size: 14px;">Select one of your <strong><?= strtoupper($neededType) ?></strong> items to propose a match:</p>
        
        <?php if (empty($myCandidates)): ?>
            <div style="padding: 30px; text-align: center; background: #f8f9fa; border-radius: 10px; margin-top: 20px;">
                <p>You don't have any open <?= $neededType ?> items to match.</p>
                <a href="report-<?= $neededType ?>.php" class="btn-primary" style="display:inline-block; margin-top:10px;">Report <?= ucfirst($neededType) ?> Item</a>
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
                <button type="submit" class="btn-primary btn-submit">Propose Match</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
