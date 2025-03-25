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
        
        // 获取运动记录
        $stmt = $db->prepare("
            SELECT * FROM exercise_records 
            WHERE user_id = ? 
            ORDER BY date DESC
        ");
        $stmt->execute([$_GET['user_id']]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 计算每日运动总量
        foreach ($records as $record) {
            $date = $record['date'];
            if (!isset($dailyTotals[$date])) {
                $dailyTotals[$date] = [
                    'duration' => 0,
                    'steps' => 0,
                    'distance' => 0,
                    'calories' => 0
                ];
            }
            $dailyTotals[$date]['duration'] += $record['duration_minutes'] ?? 0;
            $dailyTotals[$date]['steps'] += $record['steps_count'] ?? 0;
            $dailyTotals[$date]['distance'] += $record['distance_km'] ?? 0;
            $dailyTotals[$date]['calories'] += $record['calories_burned'] ?? 0;
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
    <h2>运动记录历史</h2>
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
                                    <td colspan="3"><strong>当日运动总量：</strong></td>
                                    <td><?php echo $dailyTotals[$currentDate]['duration']; ?> 分钟</td>
                                    <td><?php echo $dailyTotals[$currentDate]['steps']; ?> 步</td>
                                    <td><?php echo number_format($dailyTotals[$currentDate]['distance'], 2); ?> 公里</td>
                                    <td><?php echo $dailyTotals[$currentDate]['calories']; ?> kcal</td>
                                    <td colspan="3"></td>
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
                                    <th>运动类型</th>
                                    <th>强度</th>
                                    <th>地点</th>
                                    <th>时长</th>
                                    <th>步数</th>
                                    <th>距离</th>
                                    <th>消耗热量</th>
                                    <th>平均心率</th>
                                    <th>天气</th>
                                    <th>备注</th>
                                </tr>
                            </thead>
                            <tbody>
                    <?php 
                    $currentDate = $record['date'];
                    endif; 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['exercise_type']); ?></td>
                        <td><?php echo htmlspecialchars($record['intensity_level'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['location'] ?? '-'); ?></td>
                        <td><?php echo $record['duration_minutes'] ? htmlspecialchars($record['duration_minutes']) . ' 分钟' : '-'; ?></td>
                        <td><?php echo $record['steps_count'] ? htmlspecialchars($record['steps_count']) . ' 步' : '-'; ?></td>
                        <td><?php echo $record['distance_km'] ? htmlspecialchars($record['distance_km']) . ' 公里' : '-'; ?></td>
                        <td><?php echo $record['calories_burned'] ? htmlspecialchars($record['calories_burned']) . ' kcal' : '-'; ?></td>
                        <td><?php echo $record['heart_rate_avg'] ? htmlspecialchars($record['heart_rate_avg']) . ' 次/分' : '-'; ?></td>
                        <td><?php echo htmlspecialchars($record['weather'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['notes'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($currentDate !== null): ?>
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <td colspan="3"><strong>当日运动总量：</strong></td>
                            <td><?php echo $dailyTotals[$currentDate]['duration']; ?> 分钟</td>
                            <td><?php echo $dailyTotals[$currentDate]['steps']; ?> 步</td>
                            <td><?php echo number_format($dailyTotals[$currentDate]['distance'], 2); ?> 公里</td>
                            <td><?php echo $dailyTotals[$currentDate]['calories']; ?> kcal</td>
                            <td colspan="3"></td>
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