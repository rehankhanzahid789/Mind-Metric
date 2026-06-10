<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$page = $page ?? 'MindMetric';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= sanitize($page) ?> · MindMetric</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a href="index.php" class="brand">
        <span class="brand-mark">M</span>
        <span class="brand-name">MindMetric</span>
    </a>
    <nav class="nav">
        <?php if ($user): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="test.php">Take Test</a>
            <a href="history.php">History</a>
            <a href="logout.php" class="nav-btn">Sign out</a>
        <?php else: ?>
            <a href="index.php">Home</a>
            <a href="login.php">Sign in</a>
            <a href="register.php" class="nav-btn">Create account</a>
        <?php endif; ?>
    </nav>
</header>
<main class="main">
