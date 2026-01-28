<?php
// ======================================
// ADMIN ABUSE REPORTS MANAGEMENT
// ======================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/functions.php';

// -------------------------------
// ADMIN ONLY
// -------------------------------
if (!is_admin()) {
    die("Access denied.");
}

// -------------------------------
// HANDLE STATUS UPDATE
// -------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {

    $report_id  = (int) $_POST['report_id'];
    $admin_note = trim($_POST['admin_note']);
    $status     = $_POST['status'];

    if (in_array($status, ['open', 'under_review', 'resolved'])) {

        $stmt = $conn->prepare("
            UPDATE abuse_reports
            SET status = ?, admin_note = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $admin_note, $report_id]);

        // Log admin action
        $conn->prepare("
            INSERT INTO admin_logs (admin_id, action)
            VALUES (?, ?)
        ")->execute([
            $_SESSION['user_id'],
            "Updated abuse report ID: $report_id to status: $status"
        ]);
    }

    redirect('/admin/abuse.php');
}

// -------------------------------
// FETCH ABUSE REPORTS
// -------------------------------
$reports = $conn->query("
    SELECT ar.*, u.name AS reporter_name, i.item_name
    FROM abuse_reports ar
    JOIN users u ON ar.reported_by = u.id
    LEFT JOIN items i ON ar.item_id = i.id
    ORDER BY ar.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Abuse Reports | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">

    <style>
        .report-card {
            background: #111;
            border: 1px solid #222;
        }
        .status-open { color: #dc3545; }
        .status-under_review { color: #ffc107; }
        .status-resolved { color: #28a745; }
    </style>
</head>

<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-dark bg-black py-3">
    <div class="container">
        <a href="/admin/dashboard.php" class="navbar-brand gold-text fw-bold">
            Admin Panel
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            Logout
        </a>
    </div>
</nav>

<!-- ================= ABUSE REPORTS ================= -->
<section class="py-5 bg-black">
    <div class="container">

        <h3 class="gold-text mb-4">Abuse & Claim Reports</h3>

        <?php if (count($reports) === 0): ?>
            <p class="text-secondary">No reports available.</p>
        <?php endif; ?>

        <?php foreach ($reports as $r): ?>
            <div class="report-card p-4 mb-4">
                <div class="d-flex justify-content-between">
                    <h6 class="fw-bold">
                        Report #<?= $r['id'] ?>
                        <?php if ($r['item_name']): ?>
                            – <?= htmlspecialchars($r['item_name']) ?>
                        <?php endif; ?>
                    </h6>
                    <span class="fw-bold status-<?= $r['status'] ?>">
                        <?= strtoupper(str_replace('_', ' ', $r['status'])) ?>
                    </span>
                </div>

                <p class="text-secondary small mb-2">
                    Reported by: <?= htmlspecialchars($r['reporter_name']) ?> |
                    <?= format_date($r['created_at']) ?>
                </p>

                <p class="text-secondary">
                    <?= nl2br(htmlspecialchars($r['message'])) ?>
                </p>

                <form method="POST" class="mt-3">
                    <input type="hidden" name="report_id" value="<?= $r['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_note"
                                  class="form-control"
                                  rows="2"><?= htmlspecialchars($r['admin_note']) ?></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="open" <?= $r['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="under_review" <?= $r['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                                <option value="resolved" <?= $r['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-gold w-100">
                                Update Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>

    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="py-4 text-center bg-black border-top border-dark">
    <p class="mb-0 text-secondary small">
        © <?= date('Y'); ?> Admin Moderation | Lost & Found Portal
    </p>
</footer>

</body>
</html>
