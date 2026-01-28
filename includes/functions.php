<?php
// ======================================
// COMMON FUNCTIONS
// ======================================

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function format_date($date) {
    return date("d M Y", strtotime($date));
}
