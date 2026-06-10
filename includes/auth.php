<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT id, name, email, is_verified FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function login_user($id) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}

function logout_user() {
    $_SESSION = [];
    session_destroy();
}

function sanitize($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
