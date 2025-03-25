<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /modules/auth/login.php');
    exit;
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>欢迎使用社区健康管理系统</h2>
        <?php if(isset($_SESSION['current_user'])): ?>
        <div class="alert alert-info">
            当前用户：<?php echo htmlspecialchars($_SESSION['current_user']['name']); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>