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
    <title>社區健康管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .feature-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border-radius: 10px;
            overflow: hidden;
            border: none;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 140px;
            background-size: cover;
            background-position: center;
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .welcome-section {
            background-color: #f8f9fa;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
    </style>
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
