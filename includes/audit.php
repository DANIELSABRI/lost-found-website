<?php
/**
 * Audit logging helper
 */
function log_action(PDO $pdo, $userId, $role, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $pdo->prepare(
        "INSERT INTO audit_logs (user_id, user_role, action, ip_address)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $userId,
        $role,
        $action,
        $ip
    ]);
}
