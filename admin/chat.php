<?php
// ======================================
// ITEM CHAT (USER ↔ USER)
// ======================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

// -------------------------------
// VALIDATE ITEM
// -------------------------------
if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) {
    die("Invalid item.");
}

$item_id = (int) $_GET['item_id'];
$current_user = $_SESSION['user_id'];

// -------------------------------
// FETCH ITEM & OWNER
// -------------------------------
$stmt = $conn->prepare("
    SELECT items.*, users.name AS owner_name, users.id AS owner_id
    FROM items
    JOIN users ON items.user_id = users.id
    WHERE items.id = ?
      AND items.status != 'rejected'
");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found.");
}

// Prevent chatting with yourself
if ($item['owner_id'] == $current_user) {
    die("You cannot chat on your own item.");
}

$receiver_id = $item['owner_id'];

// -------------------------------
// SEND MESSAGE
// -------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $message = clean_input($_POST['message']);

    if (!empty($message)) {
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, item_id, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $current_user,
            $receiver_id,
            $item_id,
            $message
        ]);
    }

    redirect("/user/chat.php?item_id=$item_id");
}

// -------------------------------
// FETCH CHAT HISTORY
// -------------------------------
$messages = $conn->prepare("
    SELECT m.*, u.name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.item_id = ?
      AND (m.sender_id = ? OR m.receiver_id = ?)
    ORDER BY m.created_at ASC
");
$messages->execute([$item_id, $current_user, $current_user]);
$chat = $messages->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat | Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">

    <style>
        .chat-box {
            background: #111;
            border: 1px solid #222;
            max-height: 400px;
            overflow-y: auto;
        }
        .msg-you {
            background: #d4af37;
            color: #000;
        }
        .msg-other {
            background: #222;
        }
    </style>
</head>

<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-dark bg-black py-3">
    <div class="container">
        <a href="/item.php?id=<?= $item_id ?>" class="navbar-brand gold-text fw-bold">
            Chat – <?= htmlspecialchars($item['item_name']) ?>
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<!-- ================= CHAT ================= -->
<section class="py-4 bg-black">
    <div class="container">
        <div class="chat-box p-3 mb-3">

            <?php if (count($chat) === 0): ?>
                <p class="text-secondary text-center">
                    No messages yet. Start the conversation.
                </p>
            <?php endif; ?>

            <?php foreach ($chat as $msg): ?>
                <div class="mb-2">
                    <div class="p-2 rounded 
                        <?= $msg['sender_id'] == $current_user ? 'msg-you text-end' : 'msg-other' ?>">
                        <small class="fw-bold">
                            <?= htmlspecialchars($msg['name']) ?>
                        </small><br>
                        <?= htmlspecialchars($msg['message']) ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

            <div class="input-group">
                <input type="text"
                       name="message"
                       class="form-control"
                       placeholder="Type your message..."
                       required>
                <button class="btn btn-gold">Send</button>
            </div>
        </form>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="py-3 text-center bg-black border-top border-dark">
    <p class="mb-0 text-secondary small">
        Secure internal chat • Admin monitored
    </p>
</footer>

</body>
</html>
