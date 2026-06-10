<?php
require_once __DIR__ . '/auth.php';

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check() {
    $t = $_POST['csrf'] ?? '';
    if (!hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        die('Invalid CSRF token.');
    }
}
