<?php
/**
 * Create a professional notification
 * Types: 'match', 'security', 'system', 'message'
 */
function notify(PDO $pdo, $userId, $title, $body, $type = 'system') {
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, title, body, type)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $title, $body, $type]);
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

/**
 * Mark all notifications as read for a user
 */
function mark_all_notifications_read(PDO $pdo, $userId) {
    $stmt = $pdo->prepare(
        "UPDATE notifications 
         SET is_read = 1 
         WHERE user_id = ? OR user_id IS NULL"
    );
    $stmt->execute([$userId]);
}
