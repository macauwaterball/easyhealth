<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$user = null;

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // 获取用户基本信息
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取最新的生理参数
        $stmt = $db->prepare("
            SELECT * FROM physiological_params 
            WHERE user_id = ? 
            ORDER BY date DESC 
            LIMIT 1
        ");
        $stmt->execute([$_GET['id']]);
        $physio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取最新的体格指标
        $stmt = $db->prepare("
            SELECT * FROM physical_metrics 
            WHERE user_id = ? 
            ORDER BY date DESC 
            LIMIT 1
        ");
        $stmt->execute([$_GET['id']]);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取最新的运动记录
        $stmt = $db->prepare("
            SELECT * FROM exercise_records 
            WHERE user_id = ? 
            ORDER BY date DESC 
            LIMIT 1
        ");
        $stmt->execute([$_GET['id']]);
        $exercise = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取最新的MMSE认知测试记录
        $stmt = $db->prepare("
            SELECT * FROM mmse_records 
            WHERE user_id = ? 
            ORDER BY test_date DESC 
            LIMIT 1
        ");
        $stmt->execute([$_GET['id']]);
        $mmse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 注释掉获取用药记录的代码
        /*
        // 获取最新的用药记录
        $stmt = $db->prepare("
            SELECT * FROM medication_records 
            WHERE user_id = ? 
            ORDER BY date DESC 
            LIMIT 1
        ");
        $stmt->execute([$_GET['id']]);
        $medication = $stmt->fetch(PDO::FETCH_ASSOC);
        */
        
    } catch (PDOException $e) {
        $error = "获取信息失败：" . $e->getMessage();
    }
}

if (!$user) {
    header('Location: search.php');
    exit;
}
?>

<div class="container">
    <h2>用户详细信息</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">基本信息</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">姓名：</th>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                        </tr>
                        <tr>
                            <th>电话：</th>
                            <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>出生日期：</th>
                            <td><?php echo htmlspecialchars($user['birth_date'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>性别：</th>
                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">编辑信息</a>
                    <a href="search.php" class="btn btn-secondary">返回列表</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">最新生理参数</h5>
                </div>
                <div class="card-body">
                    <?php if ($physio): ?>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">测量日期：</th>
                                <td><?php echo htmlspecialchars($physio['date']); ?></td>
                            </tr>
                            <tr>
                                <th>血压：</th>
                                <td><?php echo htmlspecialchars($physio['blood_pressure'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>心率：</th>
                                <td><?php echo htmlspecialchars($physio['heart_rate'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>体温：</th>
                                <td><?php echo htmlspecialchars($physio['temperature'] ?? ''); ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无生理参数记录</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="../physiological/create.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-success">添加生理参数</a>
                    <a href="../physiological/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-info">查看历史记录</a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">最新体格指标</h5>
                </div>
                <div class="card-body">
                    <?php if ($metrics): ?>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">测量日期：</th>
                                <td><?php echo htmlspecialchars($metrics['date']); ?></td>
                            </tr>
                            <tr>
                                <th>身高：</th>
                                <td><?php echo $metrics['height'] ? htmlspecialchars($metrics['height']).' cm' : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>体重：</th>
                                <td><?php echo $metrics['weight'] ? htmlspecialchars($metrics['weight']).' kg' : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>BMI：</th>
                                <td>
                                    <?php 
                                    if ($metrics['bmi']) {
                                        echo htmlspecialchars($metrics['bmi']);
                                        // 显示BMI评估
                                        if ($metrics['bmi'] < 18.5) {
                                            echo ' <span class="badge bg-info">偏瘦</span>';
                                        } elseif ($metrics['bmi'] < 24) {
                                            echo ' <span class="badge bg-success">正常</span>';
                                        } elseif ($metrics['bmi'] < 28) {
                                            echo ' <span class="badge bg-warning">超重</span>';
                                        } else {
                                            echo ' <span class="badge bg-danger">肥胖</span>';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>腰围：</th>
                                <td><?php echo isset($metrics['waist']) && $metrics['waist'] ? htmlspecialchars($metrics['waist']).' cm' : '-'; ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无体格指标记录</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="../physical_metrics/create.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-success">添加体格指标</a>
                    <a href="../physical_metrics/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-info">查看历史记录</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">最新运动记录</h5>
                </div>
                <div class="card-body">
                    <?php if ($exercise): ?>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">记录日期：</th>
                                <td><?php echo htmlspecialchars($exercise['date']); ?></td>
                            </tr>
                            <tr>
                                <th>运动类型：</th>
                                <td><?php echo htmlspecialchars($exercise['exercise_type'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>时长：</th>
                                <td><?php echo htmlspecialchars($exercise['duration'] ?? '').' 分钟'; ?></td>
                            </tr>
                            <tr>
                                <th>强度：</th>
                                <td>
                                    <?php 
                                    if (isset($exercise['intensity'])) {
                                        $intensity = htmlspecialchars($exercise['intensity']);
                                        $badge_class = 'bg-secondary';
                                        
                                        if ($intensity == '高强度') {
                                            $badge_class = 'bg-danger';
                                        } elseif ($intensity == '中等强度') {
                                            $badge_class = 'bg-warning';
                                        } elseif ($intensity == '低强度') {
                                            $badge_class = 'bg-info';
                                        }
                                        
                                        echo "<span class='badge {$badge_class}'>{$intensity}</span>";
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无运动记录</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="../exercise/create.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-success">添加运动记录</a>
                    <a href="../exercise/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-info">查看历史记录</a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">最新MMSE认知测试记录</h5>
                </div>
                <div class="card-body">
                    <?php if ($mmse): ?>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">测试日期：</th>
                                <td><?php echo htmlspecialchars($mmse['test_date']); ?></td>
                            </tr>
                            <tr>
                                <th>总分：</th>
                                <td>
                                    <?php 
                                    if (isset($mmse['total_score'])) {
                                        echo htmlspecialchars($mmse['total_score']);
                                        // 显示MMSE评估
                                        $score = intval($mmse['total_score']);
                                        if ($score >= 27) {
                                            echo ' <span class="badge bg-success">正常</span>';
                                        } elseif ($score >= 21) {
                                            echo ' <span class="badge bg-warning">轻度认知障碍</span>';
                                        } elseif ($score >= 10) {
                                            echo ' <span class="badge bg-danger">中度认知障碍</span>';
                                        } else {
                                            echo ' <span class="badge bg-dark">重度认知障碍</span>';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>定向力：</th>
                                <td><?php echo isset($mmse['orientation_score']) ? htmlspecialchars($mmse['orientation_score']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>记忆力：</th>
                                <td><?php echo isset($mmse['memory_score']) ? htmlspecialchars($mmse['memory_score']) : '-'; ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无MMSE认知测试记录</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="../mmse/create.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-success">添加MMSE记录</a>
                    <a href="../mmse/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-info">查看历史记录</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- 注释掉用药记录卡片
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">最新用药记录</h5>
                </div>
                <div class="card-body">
                    <?php if ($medication): ?>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">记录日期：</th>
                                <td><?php echo htmlspecialchars($medication['date']); ?></td>
                            </tr>
                            <tr>
                                <th>药物名称：</th>
                                <td><?php echo htmlspecialchars($medication['medication_name'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>剂量：</th>
                                <td><?php echo htmlspecialchars($medication['dosage'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>服用时间：</th>
                                <td><?php echo htmlspecialchars($medication['time_of_day'] ?? ''); ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无用药记录</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="../medication/create.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-success">添加用药记录</a>
                    <a href="../medication/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-info">查看历史记录</a>
                </div>
            </div>
            -->
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">健康记录管理</h5>
                </div>
                <div class="card-body">
                    <p>查看和管理该用户的所有健康记录</p>
                    <a href="../physical/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-primary btn-lg w-100 mb-2">健康记录中心</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php';