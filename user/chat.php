<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages | Lost & Found</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

<style>
.layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: #F9FAFB;
}

/* SIDEBAR */
.sidebar {
    background: linear-gradient(180deg, #4B2FDB, #6C4DFF);
    color: #fff;
    padding: 30px 20px;
}

.sidebar h2 {
    font-size: 22px;
    margin-bottom: 40px;
}

.sidebar a {
    display: block;
    color: #fff;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    font-size: 14px;
    text-decoration: none;
}

.sidebar a.active,
.sidebar a:hover {
    background: rgba(255,255,255,0.18);
}

/* MAIN */
.main {
    padding: 40px;
}

/* CHAT */
.chat-box {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.08);
    max-width: 900px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border-soft);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header span {
    background: #EEF2FF;
    color: var(--purple-main);
    padding: 6px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 600;
}

.chat-messages {
    padding: 25px;
    background: #F9FAFB;
    flex: 1;
    overflow-y: auto;
}

.message {
    max-width: 65%;
    margin-bottom: 16px;
    padding: 14px 18px;
    border-radius: 16px;
    font-size: 14px;
}

.message.sent {
    background: var(--gradient-main);
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.message.received {
    background: #fff;
    border: 1px solid var(--border-soft);
    border-bottom-left-radius: 4px;
}

.message-time {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 6px;
    text-align: right;
}

.chat-input {
    display: flex;
    gap: 12px;
    padding: 18px;
    border-top: 1px solid var(--border-soft);
}
</style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>Lost & Found</h2>

        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost Item</a>
        <a href="<?= BASE_URL ?>/user/report-found.php">Report Found Item</a>
        <a href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>
        <a href="<?= BASE_URL ?>/search.php">Search Items</a>
        <a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
        <a class="active" href="<?= BASE_URL ?>/user/chat.php">Messages</a>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.2);margin:20px 0;">
        <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="chat-box">

            <div class="chat-header">
                <h3>Black Wallet</h3>
                <span>Lost Item</span>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="message received">
                    Hi, I think I might have found your wallet near the library.
                    <div class="message-time">11 Jan 2026, 4:15 PM</div>
                </div>

                <div class="message sent">
                    That’s great! Can you tell me what color it is?
                    <div class="message-time">11 Jan 2026, 4:17 PM</div>
                </div>
            </div>

            <form class="chat-input" id="chatForm">
                <input type="text" id="messageInput" placeholder="Type your message..." required>
                <button class="btn-primary">Send</button>
            </form>

        </div>

    </main>

</div>

<script>
const form = document.getElementById('chatForm');
const input = document.getElementById('messageInput');
const chat = document.getElementById('chatMessages');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    const msg = document.createElement('div');
    msg.className = 'message sent';
    msg.innerHTML = `${text}<div class="message-time">${new Date().toLocaleString()}</div>`;

    chat.appendChild(msg);
    chat.scrollTop = chat.scrollHeight;
    input.value = '';
});
</script>

</body>
</html>
