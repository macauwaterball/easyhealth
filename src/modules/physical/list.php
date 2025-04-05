<?php
// 确保在输出任何内容前进行所有重定向
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// 获取所有用户
$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("SELECT id, name FROM demographics ORDER BY name");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取用户列表失败：" . $e->getMessage();
}

// 在输出任何HTML内容前完成所有可能的重定向
$selectedUser = null;
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // 获取选中的用户信息
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$user_id]);
    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$selectedUser) {
        // 使用JavaScript重定向而不是header()
        echo "<script>window.location.href = 'list.php';</script>";
        exit;
    }
}

// 现在可以安全地包含header，因为不再需要使用header()函数进行重定向
require_once '../../includes/header.php';
?>

<div class="container">
    <h2>健康记录</h2>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">选择用户</label>
                        <select class="form-select" id="user_id" name="user_id" onchange="this.form.submit()">
                            <option value="">请选择用户</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selectedUser): ?>
        <div class="row">
            <div class="col-md-12">
                <h3>用户：<?php echo htmlspecialchars($selectedUser['name']); ?></h3>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">生理参数记录</h5>
                            </div>
                            <div class="card-body">
                                <a href="../physiological/list.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-primary">查看生理参数记录</a>
                                <a href="../physiological/create.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-success">添加生理参数记录</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">体格指标记录</h5>
                            </div>
                            <div class="card-body">
                                <a href="../physical_metrics/list.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-primary">查看体格指标记录</a>
                                <a href="../physical_metrics/create.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-success">添加体格指标记录</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 注释掉用药记录卡片
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">用药记录</h5>
                            </div>
                            <div class="card-body">
                                <a href="../medication/list.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-primary">查看用药记录</a>
                                <a href="../medication/create.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-success">添加用药记录</a>
                            </div>
                        </div>
                    </div>
                    -->
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">MMSE认知测试记录</h5>
                            </div>
                            <div class="card-body">
                                <a href="../mmse/list.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-primary">查看MMSE记录</a>
                                <a href="../mmse/create.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-success">添加MMSE记录</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">运动记录</h5>
                            </div>
                            <div class="card-body">
                                <a href="../exercise/list.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-primary">查看运动记录</a>
                                <a href="../exercise/create.php?user_id=<?php echo $selectedUser['id']; ?>" class="btn btn-success">添加运动记录</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">请选择一个用户查看健康记录</div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>