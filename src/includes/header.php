<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社區健康管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <i class="bi bi-heart-pulse-fill me-2"></i>社區健康管理系統
        </a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                <i class="bi bi-person-circle me-1"></i>歡迎，<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
            </span>
            <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>退出登錄
            </a>
        </div>
    </div>
</nav>
<div class="container mt-4">
<!-- 在导航菜单中找到用药记录的链接并注释掉 -->
<!-- 例如：
<li class="nav-item">
    <a class="nav-link" href="/modules/medication/list.php">用药记录</a>
</li>
-->
