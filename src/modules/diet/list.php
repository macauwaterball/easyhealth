<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$user = null;
$records = [];
$dailyTotals = [];

if (isset($_GET['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // 获取用户信息
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取饮食记录
        $stmt = $db->prepare("
            SELECT * FROM diet_records 
            WHERE user_id = ? 
            ORDER BY date DESC, 
            CASE meal_time 
                WHEN '早餐' THEN 1 
                WHEN '上午加餐' THEN 2 
                WHEN '午餐' THEN 3 
                WHEN '下午加餐' THEN 4 
                WHEN '晚餐' THEN 5 
                WHEN '夜宵' THEN 6 
            END
        ");
        $stmt->execute([$_GET['user_id']]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 计算每日营养总量
        foreach ($records as $record) {
            $date = $record['date'];
            if (!isset($dailyTotals[$date])) {
                $dailyTotals[$date] = [
                    'calories' => 0,
                    'protein' => 0,
                    'carbs' => 0,
                    'fat' => 0
                ];
            }
            $dailyTotals[$date]['calories'] += $record['calories'] ?? 0;
            $dailyTotals[$date]['protein'] += $record['protein'] ?? 0;
            $dailyTotals[$date]['carbs'] += $record['carbs'] ?? 0;
            $dailyTotals[$date]['fat'] += $record['fat'] ?? 0;
        }
        
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
    <h2>饮食记录历史</h2>
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
                            <tfoot class="table-info">
                                <tr>
                                    <td colspan="3"><strong>当日营养总量：</strong></td>
                                    <td><?php echo $dailyTotals[$currentDate]['calories']; ?> kcal</td>
                                    <td><?php echo $dailyTotals[$currentDate]['protein']; ?> g</td>
                                    <td><?php echo $dailyTotals[$currentDate]['carbs']; ?> g</td>
                                    <td><?php echo $dailyTotals[$currentDate]['fat']; ?> g</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                        <?php endif; ?>
                        <h5 class="mt-4"><?php echo $record['date']; ?></h5>
                        <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>用餐时间</th>
                                    <th>食物清单</th>
                                    <th>备注</th>
                                    <th>热量 (kcal)</th>
                                    <th>蛋白质 (g)</th>
                                    <th>碳水 (g)</th>
                                    <th>脂肪 (g)</th>
                                    <th>记录时间</th>
                                </tr>
                            </thead>
                            <tbody>
                    <?php 
                    $currentDate = $record['date'];
                    endif; 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['meal_time']); ?></td>
                        <td><?php echo htmlspecialchars($record['food_items']); ?></td>
                        <td><?php echo htmlspecialchars($record['notes'] ?? ''); ?></td>
                        <td><?php echo $record['calories'] ?? '-'; ?></td>
                        <td><?php echo $record['protein'] ?? '-'; ?></td>
                        <td><?php echo $record['carbs'] ?? '-'; ?></td>
                        <td><?php echo $record['fat'] ?? '-'; ?></td>
                        <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($currentDate !== null): ?>
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <td colspan="3"><strong>当日营养总量：</strong></td>
                            <td><?php echo $dailyTotals[$currentDate]['calories']; ?> kcal</td>
                            <td><?php echo $dailyTotals[$currentDate]['protein']; ?> g</td>
                            <td><?php echo $dailyTotals[$currentDate]['carbs']; ?> g</td>
                            <td><?php echo $dailyTotals[$currentDate]['fat']; ?> g</td>
                            <td></td>
                        </tr>
                    </tfoot>
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