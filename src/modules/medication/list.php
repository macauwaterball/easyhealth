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
        
        // 获取用药记录
        $stmt = $db->prepare("
            SELECT * FROM medication_records 
            WHERE user_id = ? 
            ORDER BY date DESC, 
            CASE time_of_day 
                WHEN '早餐前' THEN 1 
                WHEN '早餐后' THEN 2 
                WHEN '午餐前' THEN 3 
                WHEN '午餐后' THEN 4 
                WHEN '晚餐前' THEN 5 
                WHEN '晚餐后' THEN 6 
                WHEN '睡前' THEN 7 
                ELSE 8 
            END
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
    <h2>用药记录历史</h2>
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
                <?php 
                $currentDate = null;
                foreach ($records as $record):
                    if ($currentDate !== $record['date']):
                        if ($currentDate !== null): ?>
                            </tbody>
                        </table>
                        </div>
                        <?php endif; ?>
                        <h5 class="mt-4"><?php echo $record['date']; ?></h5>
                        <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>服用时间</th>
                                    <th>药品名称</th>
                                    <th>剂量</th>
                                    <th>用药目的</th>
                                    <th>不良反应</th>
                                    <th>备注</th>
                                    <th>记录时间</th>
                                </tr>
                            </thead>
                            <tbody>
                    <?php 
                    $currentDate = $record['date'];
                    endif; 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['time_of_day']); ?></td>
                        <td><?php echo htmlspecialchars($record['medication_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['dosage'] . ' ' . $record['unit']); ?></td>
                        <td><?php echo htmlspecialchars($record['purpose'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['side_effects'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['notes'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($currentDate !== null): ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
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