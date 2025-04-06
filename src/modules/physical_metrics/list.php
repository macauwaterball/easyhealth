<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$records = [];
$user = null;
$userId = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// 確保有效的用戶ID
if ($userId <= 0) {
    // 修正：將header移到任何可能的輸出之前
    header('Location: ../users/search.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // 獲取用戶基本信息
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ../users/search.php');
        exit;
    }
    
    // 獲取體格指標記錄
    $stmt = $db->prepare("
        SELECT * FROM physical_metrics 
        WHERE user_id = ? 
        ORDER BY date DESC
    ");
    $stmt->execute([$userId]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "數據獲取失敗：" . $e->getMessage();
}

// 確保在任何輸出前完成所有header操作
require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-rulers me-2"></i>體格指標記錄</h2>
        <div>
            <a href="../users/view.php?id=<?php echo $userId; ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>返回用戶資料
            </a>
            <a href="create.php?user_id=<?php echo $userId; ?>" class="btn btn-success ms-2">
                <i class="bi bi-plus-circle me-1"></i>添加新記錄
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($user): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($user['gender']); ?>, 
                            <?php 
                            if (!empty($user['birth_date'])) {
                                $birthDate = new DateTime($user['birth_date']);
                                $today = new DateTime();
                                echo $birthDate->diff($today)->y . '歲';
                            } else {
                                echo '年齡未知';
                            }
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (count($records) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>測量日期</th>
                        <th>身高 (cm)</th>
                        <th>體重 (kg)</th>
                        <th>BMI</th>
                        <th>腰圍 (cm)</th>
                        <th>血糖 (mmol/L)</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo isset($record['height']) ? htmlspecialchars(number_format($record['height'], 1)) : '-'; ?></td>
                            <td><?php echo isset($record['weight']) ? htmlspecialchars(number_format($record['weight'], 1)) : '-'; ?></td>
                            <td>
                                <?php 
                                if (isset($record['bmi'])) {
                                    $bmi = $record['bmi'];
                                    $bmi_class = 'success';
                                    $bmi_text = '正常';
                                    
                                    if ($bmi < 18.5) {
                                        $bmi_class = 'info';
                                        $bmi_text = '偏瘦';
                                    } elseif ($bmi >= 24 && $bmi < 28) {
                                        $bmi_class = 'warning';
                                        $bmi_text = '超重';
                                    } elseif ($bmi >= 28) {
                                        $bmi_class = 'danger';
                                        $bmi_text = '肥胖';
                                    }
                                    
                                    echo htmlspecialchars(number_format($bmi, 1));
                                    echo ' <span class="badge bg-'.$bmi_class.'">'.$bmi_text.'</span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo isset($record['waist']) ? htmlspecialchars(number_format($record['waist'], 1)) : '-'; ?></td>
                            <td>
                                <?php 
                                if (isset($record['blood_sugar']) && $record['blood_sugar']) {
                                    $sugar = $record['blood_sugar'];
                                    $sugar_class = 'success';
                                    $sugar_text = '正常';
                                    
                                    if ($sugar > 5.8) {
                                        $sugar_class = 'warning';
                                        $sugar_text = '需要注意';
                                    }
                                    
                                    echo htmlspecialchars(number_format($sugar, 1));
                                    echo ' <span class="badge bg-'.$sugar_class.'">'.$sugar_text.'</span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('確定要刪除這條記錄嗎？');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>暫無體格指標記錄
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>