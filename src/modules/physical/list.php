<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$user = null;
$records = [];

if (isset($_GET['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // 获取用户信息
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取体格指标记录
        $stmt = $db->prepare("
            SELECT * FROM physical_metrics 
            WHERE user_id = ? 
            ORDER BY date DESC
        ");
        $stmt->execute([$_GET['user_id']]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "获取记录失败：" . $e->getMessage();
    }
}

if (!$user) {
    header('Location: ../users/search.php');
    exit;
}
?>

<div class="container">
    <h2>体格指标历史记录</h2>
    <p class="text-muted">用户：<?php echo htmlspecialchars($user['name']); ?></p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">历史记录列表</h5>
                <a href="create.php?user_id=<?php echo $user['id']; ?>" 
                   class="btn btn-primary btn-sm">添加记录</a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($records): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>测量日期</th>
                                <th>身高 (cm)</th>
                                <th>体重 (kg)</th>
                                <th>BMI</th>
                                <th>腰围 (cm)</th>
                                <th>臀围 (cm)</th>
                                <th>血糖 (mmol/L)</th>
                                <th>记录时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                                    <td><?php echo htmlspecialchars($record['height']); ?></td>
                                    <td><?php echo htmlspecialchars($record['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($record['bmi']); ?></td>
                                    <td><?php echo htmlspecialchars($record['waist']); ?></td>
                                    <td><?php echo htmlspecialchars($record['hip']); ?></td>
                                    <td><?php echo htmlspecialchars($record['blood_sugar']); ?></td>
                                    <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">暂无记录</p>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="../users/view.php?id=<?php echo $user['id']; ?>" 
               class="btn btn-secondary">返回用户信息</a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>