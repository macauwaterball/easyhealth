<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
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
    header("Location: ../users/search.php");
    exit;
}

// 获取体格指标记录
$stmt = $db->prepare("
    SELECT * FROM physical_metrics 
    WHERE user_id = ? 
    ORDER BY date DESC
");
$stmt->execute([$user_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>体格指标记录</h2>
    <h4>用户：<?php echo htmlspecialchars($user['name']); ?></h4>
    
    <div class="mb-3">
        <a href="create.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">添加新记录</a>
        <a href="../users/view.php?id=<?php echo $user_id; ?>" class="btn btn-secondary">返回用户详情</a>
    </div>
    
    <?php if (empty($records)): ?>
        <div class="alert alert-info">暂无体格指标记录</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th>身高 (cm)</th>
                        <th>体重 (kg)</th>
                        <th>BMI</th>
                        <th>腰围 (cm)</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo $record['height'] ? htmlspecialchars($record['height']) : '-'; ?></td>
                            <td><?php echo $record['weight'] ? htmlspecialchars($record['weight']) : '-'; ?></td>
                            <td>
                                <?php 
                                if ($record['bmi']) {
                                    echo htmlspecialchars($record['bmi']);
                                    // 显示BMI评估
                                    if ($record['bmi'] < 18.5) {
                                        echo ' <span class="badge bg-info">偏瘦</span>';
                                    } elseif ($record['bmi'] < 24) {
                                        echo ' <span class="badge bg-success">正常</span>';
                                    } elseif ($record['bmi'] < 28) {
                                        echo ' <span class="badge bg-warning">超重</span>';
                                    } else {
                                        echo ' <span class="badge bg-danger">肥胖</span>';
                                    }
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo $record['waist'] ? htmlspecialchars($record['waist']) : '-'; ?></td>
                            <td>
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