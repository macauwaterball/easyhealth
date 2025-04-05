<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社区健康管理系统</title>
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
                <i class="bi bi-heart-pulse-fill me-2"></i>社区健康管理系统
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle me-1"></i>欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>退出登录
                </a>
            </div>
        </div>
    </nav>

    <div class="welcome-section">
        <div class="container">
            <h1 class="display-5 fw-bold">欢迎使用社区健康管理系统</h1>
            <p class="lead">这里提供全面的健康管理功能，帮助您更好地跟踪和管理社区居民的健康状况。</p>
        </div>
    </div>

    <div class="container">
        <h2 class="mb-4"><i class="bi bi-grid-3x3-gap-fill me-2"></i>功能菜单</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm">
                    <div class="card-img-top bg-primary text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-people-fill" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">用户管理</h5>
                        <p class="card-text">管理系统用户信息和基础档案</p>
                        <a href="/modules/users/search.php" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i>进入
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm">
                    <div class="card-img-top bg-success text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-clipboard2-pulse" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">健康记录</h5>
                        <p class="card-text">查看和管理综合健康记录</p>
                        <a href="/modules/physical/list.php" class="btn btn-success w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i>进入
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm">
                    <div class="card-img-top bg-warning text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-activity" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">运动习惯</h5>
                        <p class="card-text">记录和分析运动情况</p>
                        <a href="/modules/exercise/list.php" class="btn btn-warning w-100 text-white">
                            <i class="bi bi-arrow-right-circle me-1"></i>进入
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm">
                    <div class="card-img-top bg-info text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-brain" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">认知测试记录</h5>
                        <p class="card-text">MMSE认知测试记录与分析</p>
                        <a href="/modules/mmse/list.php" class="btn btn-info w-100 text-white">
                            <i class="bi bi-arrow-right-circle me-1"></i>进入
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light mt-5 py-3 text-center text-muted">
        <div class="container">
            <p class="mb-0">© 2023 社区健康管理系统 | 为社区居民健康保驾护航</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>