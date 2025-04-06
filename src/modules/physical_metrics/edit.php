<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// 获取记录信息
$database = new Database();
$db = $database->getConnection();
$record = null;
$user = null;

if ($id) {
    try {
        $stmt = $db->prepare("SELECT * FROM physical_metrics WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
            $stmt->execute([$record['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error = "获取记录失败：" . $e->getMessage();
    }
}

if (!$record || !$user) {
    header("Location: ../users/search.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = trim($_POST['date'] ?? date('Y-m-d'));
    $height = trim($_POST['height'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $waist = trim($_POST['waist'] ?? '');
    $blood_sugar = trim($_POST['blood_sugar'] ?? '');
    
    // 计算BMI
    $bmi = null;
    if (!empty($height) && !empty($weight) && $height > 0) {
        // BMI = 体重(kg) / (身高(m) * 身高(m))
        $height_m = $height / 100; // 转换为米
        $bmi = round($weight / ($height_m * $height_m), 2);
    }
    
    try {
        $stmt = $db->prepare("
            UPDATE physical_metrics 
            SET date = ?, height = ?, weight = ?, bmi = ?, waist = ?, blood_sugar = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $date, 
            $height ?: null, 
            $weight ?: null, 
            $bmi, 
            $waist ?: null,
            $blood_sugar ?: null,
            $id
        ]);
        
        if ($result) {
            $success = "体格指标记录更新成功";
        } else {
            $error = "更新失败，请重试";
        }
    } catch (PDOException $e) {
        $error = "更新失败：" . $e->getMessage();
    }
}
?>

<div class="container">
    <h2>编辑体格指标记录</h2>
    <h4>用户：<?php echo htmlspecialchars($user['name']); ?></h4>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="date" class="form-label">日期</label>
                <input type="date" class="form-control" id="date" name="date" 
                       value="<?php echo htmlspecialchars($record['date']); ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="height" class="form-label">身高 (cm)</label>
                    <input type="number" step="0.1" class="form-control" id="height" name="height" 
                           placeholder="例如：170.5" value="<?php echo htmlspecialchars($record['height'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="weight" class="form-label">体重 (kg)</label>
                    <input type="number" step="0.1" class="form-control" id="weight" name="weight" 
                           placeholder="例如：65.5" value="<?php echo htmlspecialchars($record['weight'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="waist" class="form-label">腰围 (cm)</label>
                    <input type="number" step="0.1" class="form-control" id="waist" name="waist" 
                           placeholder="例如：80.5" value="<?php echo htmlspecialchars($record['waist'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="blood_sugar" class="form-label">血糖 (mmol/L)</label>
                    <input type="number" step="0.1" class="form-control" id="blood_sugar" name="blood_sugar" 
                           placeholder="例如：5.6" value="<?php echo htmlspecialchars($record['blood_sugar'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="list.php?user_id=<?php echo $record['user_id']; ?>" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-primary">保存更改</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>