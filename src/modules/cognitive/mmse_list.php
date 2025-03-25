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
        
        // 获取MMSE测试记录
        $stmt = $db->prepare("
            SELECT * FROM mmse_records 
            WHERE user_id = ? 
            ORDER BY test_date DESC, created_at DESC
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
    <h2>MMSE测试历史记录</h2>
    <p class="text-muted">用户：<?php echo htmlspecialchars($user['name']); ?></p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">测试记录列表</h5>
                <a href="mmse_test.php?user_id=<?php echo $user['id']; ?>" 
                   class="btn btn-primary btn-sm">新增测试</a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($records): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>测试日期</th>
                                <th>总分</th>
                                <th>认知状态</th>
                                <th>时间定向</th>
                                <th>地点定向</th>
                                <th>记忆登记</th>
                                <th>注意力计算</th>
                                <th>回忆</th>
                                <th>命名</th>
                                <th>复述</th>
                                <th>理解</th>
                                <th>阅读</th>
                                <th>书写</th>
                                <th>绘图</th>
                                <th>备注</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['test_date']); ?></td>
                                    <td class="<?php echo $record['total_score'] >= 24 ? 'text-success' : 
                                        ($record['total_score'] >= 18 ? 'text-warning' : 'text-danger'); ?>">
                                        <strong><?php echo htmlspecialchars($record['total_score']); ?>/30</strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['cognitive_status']); ?></td>
                                    <td><?php echo htmlspecialchars($record['orientation_time']); ?>/5</td>
                                    <td><?php echo htmlspecialchars($record['orientation_place']); ?>/5</td>
                                    <td><?php echo htmlspecialchars($record['registration']); ?>/3</td>
                                    <td><?php echo htmlspecialchars($record['attention_calculation']); ?>/5</td>
                                    <td><?php echo htmlspecialchars($record['recall']); ?>/3</td>
                                    <td><?php echo htmlspecialchars($record['naming']); ?>/2</td>
                                    <td><?php echo htmlspecialchars($record['repetition']); ?>/1</td>
                                    <td><?php echo htmlspecialchars($record['comprehension']); ?>/3</td>
                                    <td><?php echo htmlspecialchars($record['reading']); ?>/1</td>
                                    <td><?php echo htmlspecialchars($record['writing']); ?>/1</td>
                                    <td><?php echo htmlspecialchars($record['drawing']); ?>/1</td>
                                    <td><?php echo htmlspecialchars($record['notes'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">暂无测试记录</p>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="../users/view.php?id=<?php echo $user['id']; ?>" 
               class="btn btn-secondary">返回用户信息</a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>