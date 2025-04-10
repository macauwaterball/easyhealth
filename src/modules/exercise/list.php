<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$user_id = $_GET['user_id'] ?? 0;

// 获取用户信息
$database = new Database();
$db = $database->getConnection();
$user = null;

if ($user_id) {
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    // 使用JavaScript重定向而不是header()
    echo "<script>window.location.href = '../physical/list.php';</script>";
    exit;
}

// 获取运动记录
$stmt = $db->prepare("
    SELECT * FROM exercise_records 
    WHERE user_id = ? 
    ORDER BY date DESC
");
$stmt->execute([$user_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 现在可以安全地包含header
require_once '../../includes/header.php';
?>

<div class="container">
    <h2>运动记录</h2>
    <h4>用户：<?php echo htmlspecialchars($user['name']); ?></h4>
    
    <div class="mb-3">
        <a href="create.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">添加新记录</a>
        <a href="../users/view.php?id=<?php echo $user_id; ?>" class="btn btn-secondary">返回用户详情</a>
    </div>
    
    <?php if (empty($records)): ?>
        <div class="alert alert-info">暂无运动记录</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th>运动类型</th>
                        <th>时长(分钟)</th>
                        <th>强度</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo htmlspecialchars($record['exercise_type']); ?></td>
                            <td><?php echo htmlspecialchars($record['duration']); ?></td>
                            <td>
                                <?php 
                                $intensity = htmlspecialchars($record['intensity']);
                                $badge_class = 'bg-secondary';
                                
                                if ($intensity == '高强度') {
                                    $badge_class = 'bg-danger';
                                } elseif ($intensity == '中等强度') {
                                    $badge_class = 'bg-warning';
                                } elseif ($intensity == '低强度') {
                                    $badge_class = 'bg-info';
                                } elseif ($intensity == '不活跃') {
                                    $badge_class = 'bg-secondary';
                                }
                                
                                echo "<span class='badge {$badge_class}'>{$intensity}</span>";
                                ?>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-info">详情</a>
                                <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">编辑</a>
                                <a href="delete.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('确定要删除这条记录吗？')">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>