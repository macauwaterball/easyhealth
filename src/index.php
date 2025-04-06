<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-heart-pulse-fill me-2"></i>社區健康管理系統
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle me-1"></i>歡迎，<?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>退出登錄
                </a>
            </div>
        </div>
    </nav>

    <div class="welcome-section">
        <div class="container">
            <h1 class="display-5 fw-bold">歡迎使用社區健康管理系統</h1>
            <p class="lead">這裡提供全面的健康管理功能，幫助您更好地跟蹤和管理社區居民的健康狀況。</p>
        </div>
    </div>

    <div class="container">
        <h2 class="mb-4"><i class="bi bi-grid-3x3-gap-fill me-2"></i>功能菜單</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-img-top bg-primary text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-people-fill" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">用戶管理</h5>
                        <p class="card-text">管理系統用戶信息和基礎檔案</p>
                        <a href="/modules/users/search.php" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i>進入
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-img-top bg-success text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-clipboard2-pulse-fill" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">健康記錄</h5>
                        <p class="card-text">管理用戶的體格指標和健康檢查記錄</p>
                        <a href="/modules/physical_metrics/list.php" class="btn btn-success w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i>進入
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 其他功能菜单已隐藏，以后再开发 -->
        </div>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0 text-muted">© 2023 社區健康管理系統 | 版權所有</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>