<?php
// 确保这里没有空行或其他输出
session_start();
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
        <?php if (isset($_SESSION['username'])): ?>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">登出</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<div class="container mt-4">