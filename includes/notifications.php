<?php
/**
 * Create a notification
 */
function notify(PDO $pdo, $userId, $role, $title, $message) {
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, role, title, message)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $role, $title, $message]);
}

/**
 * Mark notification as read
 */
function mark_notification_read(PDO $pdo, $id, $userId) {
    $stmt = $pdo->prepare(
        "UPDATE notifications 
         SET is_read = 1 
         WHERE id = ? AND (user_id = ? OR user_id IS NULL)"
    );
    $stmt->execute([$id, $userId]);
}
