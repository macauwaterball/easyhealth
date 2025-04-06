<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$user = null;
$userId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    header('Location: search.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // 获取用户基本信息
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: search.php');
        exit;
    }

    // 封装获取最新记录的方法
    function getLatestRecord($db, $table, $userId, $dateColumn = 'date') {
        $stmt = $db->prepare("
            SELECT * FROM $table 
            WHERE user_id = ? 
            ORDER BY $dateColumn DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $physio = getLatestRecord($db, 'physiological_params', $userId);
    $metrics = getLatestRecord($db, 'physical_metrics', $userId);
    $exercise = getLatestRecord($db, 'exercise_records', $userId);
    $mmse = getLatestRecord($db, 'mmse_records', $userId, 'test_date');

} catch (PDOException $e) {
    $error = "數據獲取失敗：" . $e->getMessage();
    error_log($error); // 记录错误日志
}

// 计算年龄
$age = '';
if (!empty($user['birth_date'])) {
    date_default_timezone_set('Asia/Shanghai');
    $birthDate = new DateTime($user['birth_date']);
    $today = new DateTime();
    $age = $birthDate->diff($today)->y;
}

require_once '../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #fff;
        color: #4e73df;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-right: 1.5rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .profile-name {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .profile-info {
        font-size: 1rem;
        opacity: 0.9;
    }
    .profile-info-item {
        margin-right: 1.5rem;
        display: inline-flex;
        align-items: center;
    }
    .profile-info-item i {
        margin-right: 0.5rem;
    }
    .health-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        height: 100%;
    }
    .card-header {
        border-bottom: none;
        padding: 1rem 1.5rem;
        font-weight: 600;
        border-radius: 10px 10px 0 0 !important;
    }
    .card-icon {
        margin-right: 0.5rem;
    }
    .health-value {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0.5rem 0;
    }
    .health-unit {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .badge-custom {
        font-size: 0.8rem;
        padding: 0.5rem 0.8rem;
        border-radius: 50px;
    }
    .btn-action {
        border-radius: 50px;
        padding: 0.375rem 1.2rem;
    }
</style>

<div class="profile-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <?php 
                $genderIcon = 'person';
                if ($user['gender'] == '男') {
                    $genderIcon = 'gender-male';
                } elseif ($user['gender'] == '女') {
                    $genderIcon = 'gender-female';
                }
                ?>
                <div class="profile-avatar">
                    <i class="bi bi-<?php echo $genderIcon; ?>"></i>
                </div>
                <div>
                    <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="profile-info">
                        <div class="profile-info-item">
                            <i class="bi bi-telephone"></i>
                            <?php echo htmlspecialchars($user['phone'] ?? '未設置'); ?>
                        </div>
                        <?php if ($age): ?>
                            <div class="profile-info-item">
                                <i class="bi bi-calendar3"></i>
                                <?php echo $age; ?>歲
                            </div>
                        <?php endif; ?>
                        <div class="profile-info-item">
                            <i class="bi bi-<?php echo $genderIcon; ?>"></i>
                            <?php echo htmlspecialchars($user['gender']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-light btn-action">
                    <i class="bi bi-pencil-square me-1"></i>編輯資料
                </a>
                <a href="search.php" class="btn btn-outline-light btn-action ms-2">
                    <i class="bi bi-arrow-left me-1"></i>返回列表
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <!-- 生理参数卡片 -->
        <div class="col-md-6">
            <div class="card health-card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-heart-pulse card-icon"></i>最新生理參數
                </div>
                <div class="card-body">
                    <?php if ($physio): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="bi bi-calendar-check me-1"></i>測量日期</span>
                            <span class="badge bg-light text-dark badge-custom">
                                <?php echo htmlspecialchars($physio['date'] ?? '-'); ?>
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-activity text-danger"></i></div>
                                    <div class="health-value">
                                        <?php 
                                        // 修改血压显示逻辑
                                        if (isset($physio['blood_pressure']) && !empty($physio['blood_pressure'])) {
                                            // 如果是单一字段存储血压值
                                            echo htmlspecialchars($physio['blood_pressure']);
                                        } elseif (!empty($physio['blood_pressure_systolic']) && !empty($physio['blood_pressure_diastolic'])) {
                                            // 如果是分开存储收缩压和舒张压
                                            echo htmlspecialchars("{$physio['blood_pressure_systolic']}/{$physio['blood_pressure_diastolic']}");
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </div>
                                    <div class="health-unit">血壓 (mmHg)</div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-heart text-danger"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($physio['heart_rate']) ? htmlspecialchars($physio['heart_rate']) : '-'; ?>
                                    </div>
                                    <div class="health-unit">心率 (bpm)</div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-thermometer-half text-warning"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($physio['temperature']) ? htmlspecialchars($physio['temperature']) : '-'; ?>
                                    </div>
                                    <div class="health-unit">體溫 (°C)</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2">暫無生理參數記錄</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="../physiological/create.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-primary btn-action flex-grow-1">
                            <i class="bi bi-plus-circle me-1"></i>添加記錄
                        </a>
                        <a href="../physiological/list.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-outline-primary btn-action flex-grow-1">
                            <i class="bi bi-list-ul me-1"></i>查看歷史
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 体格指标卡片 -->
        <div class="col-md-6">
            <div class="card health-card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-rulers card-icon"></i>最新體格指標
                </div>
                <div class="card-body">
                    <?php if ($metrics): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="bi bi-calendar-check me-1"></i>測量日期</span>
                            <span class="badge bg-light text-dark badge-custom">
                                <?php echo htmlspecialchars($metrics['date'] ?? '-'); ?>
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-arrow-up-square text-success"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($metrics['height']) ? htmlspecialchars(number_format($metrics['height'], 1)) : '-'; ?>
                                    </div>
                                    <div class="health-unit">身高 (cm)</div>
                                </div>
                            </div>
                            <div class="col text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-speedometer text-success"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($metrics['weight']) ? htmlspecialchars(number_format($metrics['weight'], 1)) : '-'; ?>
                                    </div>
                                    <div class="health-unit">體重 (kg)</div>
                                </div>
                            </div>
                            <div class="col text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-calculator text-success"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($metrics['bmi']) ? htmlspecialchars(number_format($metrics['bmi'], 1)) : '-'; ?>
                                    </div>
                                    <div class="health-unit">BMI</div>
                                </div>
                            </div>
                            <div class="col text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-circle-square text-success"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($metrics['waist']) ? htmlspecialchars(number_format($metrics['waist'], 1)) : '-'; ?>
                                    </div>
                                    <div class="health-unit">腰圍 (cm)</div>
                                </div>
                            </div>
                            <div class="col text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-droplet-half text-danger"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($metrics['blood_sugar']) && $metrics['blood_sugar'] ? htmlspecialchars(number_format($metrics['blood_sugar'], 1)) : '-'; ?>
                                    </div>
                                    <div class="health-unit">血糖 (mmol/L)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 移除原來的血糖顯示區塊 -->
                        
                        <div class="mt-3 text-center">
                            <?php 
                            // BMI評估
                            if (isset($metrics['bmi'])) {
                                $bmi = $metrics['bmi'];
                                $bmi_class = 'bg-success';
                                $bmi_text = '正常';
                                
                                if ($bmi < 18.5) {
                                    $bmi_class = 'bg-info';
                                    $bmi_text = '偏瘦';
                                } elseif ($bmi >= 24 && $bmi < 28) {
                                    $bmi_class = 'bg-warning';
                                    $bmi_text = '超重';
                                } elseif ($bmi >= 28) {
                                    $bmi_class = 'bg-danger';
                                    $bmi_text = '肥胖';
                                }
                            ?>
                                <span class="badge <?php echo $bmi_class; ?> badge-custom me-2">
                                    <i class="bi bi-info-circle me-1"></i>BMI評估: <?php echo $bmi_text; ?>
                                </span>
                            <?php } ?>
                            
                            <!-- 血糖評估 -->
                            <?php if (isset($metrics['blood_sugar']) && $metrics['blood_sugar']):
                                $sugar_class = 'bg-success';
                                $sugar_text = '正常';
                                
                                if ($metrics['blood_sugar'] > 5.8) {
                                    $sugar_class = 'bg-warning';
                                    $sugar_text = '需要注意';
                                } elseif ($metrics['blood_sugar'] <= 5.7) {
                                    $sugar_class = 'bg-success';
                                    $sugar_text = '正常';
                                }
                            ?>
                                <span class="badge <?php echo $sugar_class; ?> badge-custom">
                                    <i class="bi bi-droplet me-1"></i>血糖評估: <?php echo $sugar_text; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2">暫無體格指標記錄</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="../physical_metrics/create.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-success btn-action flex-grow-1">
                            <i class="bi bi-plus-circle me-1"></i>添加記錄
                        </a>
                        <a href="../physical_metrics/list.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-outline-success btn-action flex-grow-1">
                            <i class="bi bi-list-ul me-1"></i>查看歷史
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 运动记录卡片 -->
        <div class="col-md-6">
            <div class="card health-card">
                <div class="card-header bg-warning text-white">
                    <i class="bi bi-activity card-icon"></i>最新運動記錄
                </div>
                <div class="card-body">
                    <?php if ($exercise): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="bi bi-calendar-check me-1"></i>記錄日期</span>
                            <span class="badge bg-light text-dark badge-custom">
                                <?php echo htmlspecialchars($exercise['date'] ?? '-'); ?>
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-bicycle text-warning"></i></div>
                                    <div class="health-value">
                                        <?php echo htmlspecialchars($exercise['exercise_type'] ?? '-'); ?>
                                    </div>
                                    <div class="health-unit">運動類型</div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-clock text-warning"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($exercise['duration']) ? htmlspecialchars($exercise['duration']) : '-'; ?>
                                    </div>
                                    <div class="health-unit">時長 (分鐘)</div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-lightning text-warning"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($exercise['intensity']) ? htmlspecialchars($exercise['intensity']) : '-'; ?>
                                    </div>
                                    <div class="health-unit">強度</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2">暫無運動記錄</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="../exercise/create.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-warning btn-action flex-grow-1">
                            <i class="bi bi-plus-circle me-1"></i>添加記錄
                        </a>
                        <a href="../exercise/list.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-outline-warning btn-action flex-grow-1">
                            <i class="bi bi-list-ul me-1"></i>查看歷史
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MMSE认知测试记录卡片 -->
        <div class="col-md-6">
            <div class="card health-card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-brain card-icon"></i>最新MMSE認知測試記錄
                </div>
                <div class="card-body">
                    <?php if ($mmse): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="bi bi-calendar-check me-1"></i>測試日期</span>
                            <span class="badge bg-light text-dark badge-custom">
                                <?php echo htmlspecialchars($mmse['test_date'] ?? '-'); ?>
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <div><i class="bi bi-award text-info"></i></div>
                                    <div class="health-value">
                                        <?php echo isset($mmse['total_score']) ? htmlspecialchars($mmse['total_score']) : '-'; ?>
                                    </div>
                                    <div class="health-unit">總分</div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="p-3 bg-light rounded">
                                    <div class="mb-2"><i class="bi bi-card-checklist text-info me-1"></i>測試項目</div>
                                    <div class="small">
                                        <div class="row">
                                            <div class="col-6">定向力: <?php echo isset($mmse['orientation']) ? htmlspecialchars($mmse['orientation']) : '-'; ?>/10</div>
                                            <div class="col-6">注意力: <?php echo isset($mmse['attention']) ? htmlspecialchars($mmse['attention']) : '-'; ?>/5</div>
                                            <div class="col-6">記憶力: <?php echo isset($mmse['memory']) ? htmlspecialchars($mmse['memory']) : '-'; ?>/3</div>
                                            <div class="col-6">語言能力: <?php echo isset($mmse['language']) ? htmlspecialchars($mmse['language']) : '-'; ?>/8</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- MMSE评估 -->
                            <?php if (isset($mmse['total_score'])): ?>
                                <div class="col-12 mt-3 text-center">
                                    <?php 
                                    $score = (int)$mmse['total_score'];
                                    $score_class = 'bg-success';
                                    $score_text = '正常';
                                    if ($score >= 24 && $score <= 30) {
                                        $score_class = 'bg-success';
                                        $score_text = '正常';
                                    } elseif ($score >= 18 && $score <= 23) {
                                        $score_class = 'bg-warning';
                                        $score_text = '輕度認知障礙';
                                    } else {
                                        $score_class = 'bg-danger';
                                        $score_text = '中重度認知障礙';
                                    }
                                    ?>
                                    <span class="badge <?php echo $score_class; ?> badge-custom">
                                        <i class="bi bi-info-circle me-1"></i>評估: <?php echo $score_text; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2">暫無MMSE認知測試記錄</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="../mmse/create.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-info btn-action flex-grow-1">
                            <i class="bi bi-plus-circle me-1"></i>添加記錄
                        </a>
                        <a href="../mmse/list.php?user_id=<?php echo $user['id']; ?>" 
                           class="btn btn-outline-info btn-action flex-grow-1">
                            <i class="bi bi-list-ul me-1"></i>查看歷史
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php';