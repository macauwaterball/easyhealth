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
        
        // 获取生活习惯记录
        $stmt = $db->prepare("
            SELECT * FROM lifestyle_records 
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

// 压力等级映射
$stressLevels = [
    1 => '很轻松',
    2 => '较轻松',
    3 => '一般',
    4 => '较压力',
    5 => '很压力'
];
?>

<div class="container">
    <h2>生活习惯历史记录</h2>
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
                                <th>记录日期</th>
                                <th>睡眠时长</th>
                                <th>运动时长</th>
                                <th>运动类型</th>
                                <th>压力等级</th>
                                <th>心情</th>
                                <th>吸烟数量</th>
                                <th>饮酒情况</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                                    <td><?php echo $record['sleep_hours'] ? htmlspecialchars($record['sleep_hours']) . '小时' : '-'; ?></td>
                                    <td><?php echo $record['exercise_minutes'] ? htmlspecialchars($record['exercise_minutes']) . '分钟' : '-'; ?></td>
                                    <td><?php echo $record['exercise_type'] ? htmlspecialchars($record['exercise_type']) : '-'; ?></td>
                                    <td><?php echo $record['stress_level'] ? htmlspecialchars($stressLevels[$record['stress_level']]) : '-'; ?></td>
                                    <td><?php echo $record['mood'] ? htmlspecialchars($record['mood']) : '-'; ?></td>
                                    <td><?php echo $record['smoking_count'] !== null ? htmlspecialchars($record['smoking_count']) . '支' : '-'; ?></td>
                                    <td><?php echo $record['drinking_amount'] ? htmlspecialchars($record['drinking_amount']) : '-'; ?></td>
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