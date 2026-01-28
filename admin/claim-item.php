<?php
// ======================================
// CLAIM ITEM (USER)
// ======================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

// -------------------------------
// VALIDATE ITEM
// -------------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid item.");
}

$item_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// -------------------------------
// FETCH ITEM
// -------------------------------
$stmt = $conn->prepare("
    SELECT * FROM items
    WHERE id = ?
      AND status = 'pending'
");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not available for claim.");
}

// Prevent owner from claiming own item
if ($item['user_id'] == $user_id) {
    die("You cannot claim your own item.");
}

// -------------------------------
// SUBMIT CLAIM
// -------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $reason = clean_input($_POST['reason']);

    if (empty($reason)) {
        die("Claim reason required.");
    }

    // Create abuse_reports entry as claim record
    $stmt = $conn->prepare("
        INSERT INTO abuse_reports (reported_by, item_id, message, status)
        VALUES (?, ?, ?, 'under_review')
    ");
    $stmt->execute([
        $user_id,
        $item_id,
        "CLAIM REQUEST: " . $reason
    ]);

    redirect("/item.php?id=$item_id&claimed=1");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Claim Item | Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-black">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card bg-dark border-dark p-4">
                <h3 class="gold-text text-center mb-3">Claim Item</h3>

                <p class="text-secondary text-center">
                    Please describe proof or details to verify ownership.
                </p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

                    <div class="mb-3">
                        <textarea name="reason"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Describe identifying marks, contents, or proof"
                                  required></textarea>
                    </div>

                    <button class="btn btn-gold w-100">
                        Submit Claim Request
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>
