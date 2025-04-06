<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$records = [];
$user = null;
$userId = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// 確保有效的用戶ID
if ($userId <= 0) {
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
    
    // 獲取MMSE測試記錄
    $stmt = $db->prepare("
        SELECT * FROM mmse_records 
        WHERE user_id = ? 
        ORDER BY test_date DESC
    ");
    $stmt->execute([$userId]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "數據獲取失敗：" . $e->getMessage();
}

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-brain me-2"></i>MMSE認知測試記錄</h2>
        <div>
            <a href="../users/view.php?id=<?php echo $userId; ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>返回用戶資料
            </a>
            <a href="create.php?user_id=<?php echo $userId; ?>" class="btn btn-success ms-2">
                <i class="bi bi-plus-circle me-1"></i>添加新測試
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
                        <th>測試日期</th>
                        <th>定向力</th>
                        <th>記憶登記</th>
                        <th>注意力</th>
                        <th>回憶</th>
                        <th>語言能力</th>
                        <th>總分</th>
                        <th>認知評估</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['test_date']); ?></td>
                            <td><?php echo htmlspecialchars($record['orientation_score'] ?? 0); ?>/10</td>
                            <td><?php echo htmlspecialchars($record['registration'] ?? 0); ?>/3</td>
                            <td><?php echo htmlspecialchars($record['attention'] ?? 0); ?>/5</td>
                            <td><?php echo htmlspecialchars($record['recall'] ?? 0); ?>/3</td>
                            <td><?php echo htmlspecialchars($record['language'] ?? 0); ?>/9</td>
                            <td><strong><?php echo htmlspecialchars($record['total_score']); ?>/30</strong></td>
                            <td>
                                <?php 
                                $score = $record['total_score'];
                                $evaluation_class = 'success';
                                $evaluation_text = '正常';
                                
                                if ($score >= 27) {
                                    $evaluation_class = 'success';
                                    $evaluation_text = '正常';
                                } elseif ($score >= 21 && $score <= 26) {
                                    $evaluation_class = 'info';
                                    $evaluation_text = '輕度障礙';
                                } elseif ($score >= 10 && $score <= 20) {
                                    $evaluation_class = 'warning';
                                    $evaluation_text = '中度障礙';
                                } else {
                                    $evaluation_class = 'danger';
                                    $evaluation_text = '重度障礙';
                                }
                                
                                echo '<span class="badge bg-'.$evaluation_class.'">'.$evaluation_text.'</span>';
                                ?>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i> 詳情
                                </a>
                                <a href="delete.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('確定要刪除這條記錄嗎？');">
                                    <i class="bi bi-trash"></i> 刪除
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>暫無MMSE測試記錄
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>