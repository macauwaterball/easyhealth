<?php
// 确保在任何输出之前进行重定向
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
require_once 'includes/header.php';
?>

<div class="container">
    <h2>欢迎使用社区健康管理系统</h2>
    <!-- 其他内容 -->
</div>

<?php require_once 'includes/footer.php'; ?>