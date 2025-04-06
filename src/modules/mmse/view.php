<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$record = null;
$user = null;
$recordId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// 確保有效的記錄ID
if ($recordId <= 0) {
    header('Location: ../users/search.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // 獲取MMSE測試記錄
    $stmt = $db->prepare("SELECT * FROM mmse_records WHERE id = ?");
    $stmt->execute([$recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        header('Location: ../users/search.php');
        exit;
    }
    
    // 獲取用戶基本信息
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$record['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "數據獲取失敗：" . $e->getMessage();
}

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-brain me-2"></i>MMSE測試詳情</h2>
        <div>
            <a href="list.php?user_id=<?php echo $record['user_id']; ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>返回測試列表
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
    
    <?php if ($record): ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">測試基本信息</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>測試日期：</strong> <?php echo htmlspecialchars($record['test_date']); ?></p>
                        <p><strong>總分：</strong> <?php echo htmlspecialchars($record['total_score']); ?>/30</p>
                        
                        <?php 
                        $score = $record['total_score'];
                        $evaluation_class = 'success';
                        $evaluation_text = '認知功能正常';
                        
                        if ($score >= 27) {
                            $evaluation_class = 'success';
                            $evaluation_text = '認知功能正常';
                        } elseif ($score >= 21 && $score <= 26) {
                            $evaluation_class = 'info';
                            $evaluation_text = '輕度認知障礙';
                        } elseif ($score >= 10 && $score <= 20) {
                            $evaluation_class = 'warning';
                            $evaluation_text = '中度認知障礙';
                        } else {
                            $evaluation_class = 'danger';
                            $evaluation_text = '重度認知障礙';
                        }
                        ?>
                        
                        <div class="alert alert-<?php echo $evaluation_class; ?>">
                            <strong>認知評估：</strong> <?php echo $evaluation_text; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($record['notes'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">測試備註</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">測試詳細分數</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>測試項目</th>
                                    <th>得分</th>
                                    <th>滿分</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>定向力</td>
                                    <td><?php echo htmlspecialchars($record['orientation_score'] ?? 0); ?></td>
                                    <td>10</td>
                                </tr>
                                <tr>
                                    <td>記憶登記</td>
                                    <td><?php echo htmlspecialchars($record['registration'] ?? 0); ?></td>
                                    <td>3</td>
                                </tr>
                                <tr>
                                    <td>注意力和計算力</td>
                                    <td><?php echo htmlspecialchars($record['attention'] ?? 0); ?></td>
                                    <td>5</td>
                                </tr>
                                <tr>
                                    <td>回憶</td>
                                    <td><?php echo htmlspecialchars($record['recall'] ?? 0); ?></td>
                                    <td>3</td>
                                </tr>
                                <tr>
                                    <td>語言能力</td>
                                    <td><?php echo htmlspecialchars($record['language'] ?? 0); ?></td>
                                    <td>9</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>總分</strong></td>
                                    <td><strong><?php echo htmlspecialchars($record['total_score']); ?></strong></td>
                                    <td><strong>30</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="mt-4">
                            <h6>MMSE分數解釋：</h6>
                            <ul class="list-group">
                                <li class="list-group-item list-group-item-success">27-30分：認知功能正常</li>
                                <li class="list-group-item list-group-item-info">21-26分：輕度認知障礙</li>
                                <li class="list-group-item list-group-item-warning">10-20分：中度認知障礙</li>
                                <li class="list-group-item list-group-item-danger">0-9分：重度認知障礙</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>