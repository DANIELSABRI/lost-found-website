<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$active_item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$active_peer_id = isset($_GET['peer_id']) ? (int)$_GET['peer_id'] : 0;

// HANDLE SENDING MESSAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    $msg = trim($_POST['message'] ?? '');
    $item_id = (int)$_POST['item_id'];
    $receiver_id = (int)$_POST['receiver_id'];

    if (!empty($msg)) {
        $stmt = $pdo->prepare("INSERT INTO messages (item_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$item_id, $user_id, $receiver_id, $msg]);
        
        // Notify receiver
        require_once __DIR__ . '/../includes/notifications.php';
        notify($pdo, $receiver_id, "New Message", "You have a new message regarding your item.", "message");
        
        header("Location: chat.php?item_id=$item_id&peer_id=$receiver_id");
        exit;
    }
}

// FETCH UNIQUE CONVERSATIONS
$conv_sql = "
    SELECT DISTINCT 
        m.item_id, 
        i.item_name,
        CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as peer_id,
        u.name as peer_name,
        (SELECT message FROM messages WHERE item_id = m.item_id AND ((sender_id = m.sender_id AND receiver_id = m.receiver_id) OR (sender_id = m.receiver_id AND receiver_id = m.sender_id)) ORDER BY created_at DESC LIMIT 1) as last_msg,
        (SELECT created_at FROM messages WHERE item_id = m.item_id AND ((sender_id = m.sender_id AND receiver_id = m.receiver_id) OR (sender_id = m.receiver_id AND receiver_id = m.sender_id)) ORDER BY created_at DESC LIMIT 1) as last_time
    FROM messages m
    JOIN items i ON m.item_id = i.id
    JOIN users u ON (u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY last_time DESC
";
$stmt = $pdo->prepare($conv_sql);
$stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If we have an active relay that DOESN'T have messages yet, push it to the top of the list
if ($active_item_id && $active_peer_id) {
    $existsInSidebar = false;
    foreach ($conversations as $c) {
        if ($c['item_id'] == $active_item_id && $c['peer_id'] == $active_peer_id) {
            $existsInSidebar = true;
            break;
        }
    }
    if (!$existsInSidebar) {
        // Fetch item and peer info to mock a conversation item
        $i_stmt = $pdo->prepare("SELECT item_name FROM items WHERE id = ?");
        $i_stmt->execute([$active_item_id]);
        $i_name = $i_stmt->fetchColumn();

        $u_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $u_stmt->execute([$active_peer_id]);
        $u_name = $u_stmt->fetchColumn();

        if ($i_name && $u_name) {
            array_unshift($conversations, [
                'item_id' => $active_item_id,
                'item_name' => $i_name,
                'peer_id' => $active_peer_id,
                'peer_name' => $u_name,
                'last_msg' => 'Pending Investigation...',
                'last_time' => date('Y-m-d H:i:s')
            ]);
        }
    }
}

// FETCH ACTIVE CHAT MESSAGES
$messages = [];
$active_peer_name = "";
$active_item_title = "";
if ($active_item_id && $active_peer_id) {
    // Mark as read
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE item_id = ? AND sender_id = ? AND receiver_id = ?")->execute([$active_item_id, $active_peer_id, $user_id]);

    $stmt = $pdo->prepare("
        SELECT m.*, u.name as sender_name 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.item_id = ? 
        AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$active_item_id, $user_id, $active_peer_id, $active_peer_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $peer_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $peer_stmt->execute([$active_peer_id]);
    $active_peer_name = $peer_stmt->fetchColumn();

    $item_stmt = $pdo->prepare("SELECT item_name FROM items WHERE id = ?");
    $item_stmt->execute([$active_item_id]);
    $active_item_title = $item_stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Secure Communications | University Network</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<style>
    .chat-wrapper {
        display: grid;
        grid-template-columns: 350px 1fr;
        height: 800px;
        background: #fff;
        border-radius: var(--radius-3xl);
        overflow: hidden;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(0,0,0,0.05);
    }

    /* Sidebar */
    .chat-sidebar { background: #F9FAFB; border-right: 1px solid var(--color-bg); display: flex; flex-direction: column; }
    .sidebar-header { padding: 30px; border-bottom: 1px solid var(--color-bg); }
    .sidebar-header h3 { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }

    .conv-list { flex: 1; overflow-y: auto; }
    .conv-item {
        padding: 20px 30px;
        display: flex;
        gap: 15px;
        align-items: center;
        text-decoration: none;
        transition: 0.2s;
        border-bottom: 1px solid rgba(0,0,0,0.02);
    }
    .conv-item:hover { background: #fff; }
    .conv-item.active { background: #fff; border-right: 5px solid var(--color-primary); }

    .peer-avatar {
        width: 52px; height: 52px;
        background: var(--grad-primary);
        color: #fff;
        font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        border-radius: 16px;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }
    .conv-info { flex: 1; overflow: hidden; }
    .conv-info .peer-name { display: block; font-weight: 800; font-size: 15px; color: var(--color-text-main); margin-bottom: 2px; }
    .conv-info .item-name { display: block; font-size: 11px; color: var(--color-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .conv-info .preview { display: block; font-size: 13px; color: var(--color-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500; }

    /* Main Chat */
    .chat-main { display: flex; flex-direction: column; background: #fff; position: relative; }
    .chat-main-header { padding: 30px 40px; border-bottom: 1px solid var(--color-bg); display: flex; justify-content: space-between; align-items: center; }
    .active-info h4 { font-size: 19px; font-weight: 800; color: var(--color-text-main); margin-bottom: 2px; }
    .active-meta { font-size: 12px; font-weight: 700; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.5px; }

    .msg-list {
        flex: 1;
        padding: 40px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
        background: #FDFDFF;
    }
    .msg-bubble {
        max-width: 65%;
        padding: 18px 24px;
        border-radius: var(--radius-2xl);
        font-size: 14px;
        font-weight: 500;
        line-height: 1.6;
    }
    .msg-sent { background: var(--color-primary); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2); }
    .msg-received { background: #fff; color: var(--color-text-main); align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); border: 1px solid var(--color-bg); }
    .msg-time { font-size: 10px; margin-top: 8px; opacity: 0.6; display: block; text-align: right; }

    .chat-footer { padding: 30px 40px; border-top: 1px solid var(--color-bg); background: #fff; }
    .input-wrap { display: flex; gap: 15px; background: #F3F4F6; padding: 10px; border-radius: 20px; border: 2px solid transparent; transition: 0.3s; }
    .input-wrap:focus-within { border-color: var(--color-primary); background: #fff; box-shadow: var(--shadow-sm); }
    .input-wrap input { flex: 1; border: none; background: transparent; padding: 10px 20px; font-weight: 600; color: var(--color-text-main); font-family: inherit; }
    .input-wrap input:focus { outline: none; }
    
    .btn-send {
        background: var(--grad-primary); color: #fff; border: none; width: 50px; height: 50px; border-radius: 16px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s;
    }

    /* Layout Shim */
    .dashboard-layout { display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-top: 40px; }
    @media (max-width: 1200px) { .dashboard-layout { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">Lost & Found</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="report-lost.php">Report Lost</a>
        <a href="report-found.php">Report Found</a>
        <a href="notifications.php">Notifications</a>
        <a href="chat.php" class="active">Messages</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-intro" style="text-align: left; margin: 0 0 30px 0;">
        <h1>Secure Communication Hub</h1>
        <p>Coordinate recovery missions through verified campus intelligence channels.</p>
    </div>

    <div class="dashboard-layout">
        <!-- Main Chat Interface -->
        <main>
            <div class="chat-wrapper" style="height: 700px; border: 1px solid rgba(0,0,0,0.08); box-shadow: var(--shadow-md);">
                <!-- Sidebar -->
                <div class="chat-sidebar">
                    <div class="sidebar-header">
                        <h3>Intelligence Feed</h3>
                    </div>
                    <div class="conv-list">
                        <?php if (empty($conversations)): ?>
                            <div style="text-align: center; padding: 60px 40px;">
                                <span style="font-size: 50px;">🧊</span>
                                <p style="font-size: 13px; color: var(--color-text-muted); margin-top: 15px; font-weight: 500;">No active channels. Select an item from the dashboard to start a relay.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $c): ?>
                                <a href="?item_id=<?= $c['item_id'] ?>&peer_id=<?= $c['peer_id'] ?>" 
                                   class="conv-item <?= ($active_item_id == $c['item_id'] && $active_peer_id == $c['peer_id']) ? 'active' : '' ?>">
                                    <div class="peer-avatar" style="<?= $c['last_msg'] === 'Pending Investigation...' ? 'opacity: 0.6;' : '' ?>">
                                        <?= strtoupper(substr($c['peer_name'],0,1)) ?>
                                    </div>
                                    <div class="conv-info">
                                        <span class="item-name"><?= htmlspecialchars($c['item_name']) ?></span>
                                        <span class="peer-name"><?= htmlspecialchars($c['peer_name']) ?></span>
                                        <span class="preview"><?= htmlspecialchars($c['last_msg']) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="chat-main">
                    <?php if ($active_item_id && $active_peer_id): ?>
                        <div class="chat-main-header">
                            <div class="active-info">
                                <h4><?= htmlspecialchars($active_peer_name) ?></h4>
                                <span class="active-meta">Mission Protocol: <?= htmlspecialchars($active_item_title) ?></span>
                            </div>
                            <div class="badge status-matched" style="font-weight: 800; font-size: 10px;">SECURE RELAY</div>
                        </div>

                        <div class="msg-list" id="msgScroll">
                            <?php if (empty($messages)): ?>
                                <div style="margin: auto; text-align: center; max-width: 300px;">
                                    <span style="font-size: 40px;">📡</span>
                                    <h3 style="font-weight: 800; margin-top: 20px;">Initialize Communications</h3>
                                    <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.6; margin-top: 10px;">
                                        You are opening a secure channel with <strong><?= htmlspecialchars($active_peer_name) ?></strong> regarding the <strong><?= htmlspecialchars($active_item_title) ?></strong>.
                                    </p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $m): ?>
                                    <div class="msg-bubble <?= $m['sender_id'] == $user_id ? 'msg-sent' : 'msg-received' ?>">
                                        <?= htmlspecialchars($m['message']) ?>
                                        <span class="msg-time"><?= date('g:i A', strtotime($m['created_at'])) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="chat-footer">
                            <form method="POST">
                                <input type="hidden" name="send_msg" value="1">
                                <input type="hidden" name="item_id" value="<?= $active_item_id ?>">
                                <input type="hidden" name="receiver_id" value="<?= $active_peer_id ?>">
                                <div class="input-wrap">
                                    <input type="text" name="message" placeholder="Initialize message relay..." autocomplete="off" required autofocus>
                                    <button type="submit" class="btn-send">🚀</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px;">
                            <div>
                                <span style="font-size: 80px;">📂</span>
                                <h2 style="font-weight: 900; margin-top: 25px; letter-spacing: -1px;">Select Intelligence Stream</h2>
                                <p style="color: var(--color-text-muted); max-width: 400px; margin: 15px auto; font-weight: 500;">
                                    Your correspondence is grouped by recovery asset. Select a mission from the intelligence feed to view secure logs.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
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
                <span style="font-size: 32px;">🔐</span>
                <h5 style="margin-top: 15px; font-weight: 800;">Encrypted Channel</h5>
                <p style="font-size: 12px; color: var(--color-text-muted); margin-top: 8px;">All communications are logged for campus security and audit purposes.</p>
            </div>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    const msgList = document.getElementById('msgScroll');
    if (msgList) { msgList.scrollTop = msgList.scrollHeight; }
</script>

</body>
</html>
