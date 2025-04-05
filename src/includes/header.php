<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社区健康管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/index.php">社区健康管理系统</a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                欢迎，<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
            </span>
            <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">退出登录</a>
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