<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$user_id = $_GET['user_id'] ?? 0;
$error = '';
$success = '';

// 获取用户信息
$database = new Database();
$db = $database->getConnection();
$user = null;

if ($user_id) {
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    header("Location: ../users/search.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = trim($_POST['date'] ?? date('Y-m-d'));
    $height = trim($_POST['height'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $waist = trim($_POST['waist'] ?? '');
    // 删除 hip 变量
    $blood_sugar = trim($_POST['blood_sugar'] ?? '');
    
    // 计算BMI
    $bmi = null;
    if (!empty($height) && !empty($weight) && $height > 0) {
        // BMI = 体重(kg) / (身高(m) * 身高(m))
        $height_m = $height / 100; // 转换为米
        $bmi = round($weight / ($height_m * $height_m), 2);
    }
    
    try {
        // 检查表是否存在blood_sugar字段，如果不存在则添加
        try {
            $stmt = $db->prepare("SELECT blood_sugar FROM physical_metrics LIMIT 1");
            $stmt->execute();
        } catch (PDOException $e) {
            // 字段不存在，添加这些字段
            if (strpos($e->getMessage(), "Unknown column") !== false) {
                // 删除添加 hip 字段的代码
                $db->exec("ALTER TABLE physical_metrics ADD COLUMN blood_sugar DECIMAL(5,1) NULL AFTER waist");
            }
        }
        
        $stmt = $db->prepare("
            INSERT INTO physical_metrics (user_id, date, height, weight, bmi, waist, blood_sugar) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $user_id, 
            $date, 
            $height ?: null, 
            $weight ?: null, 
            $bmi, 
            $waist ?: null,
            $blood_sugar ?: null
        ]);
        
        if ($result) {
            $success = "体格指标记录添加成功";
        } else {
            $error = "添加失败，请重试";
        }
    } catch (PDOException $e) {
        $error = "添加失败：" . $e->getMessage();
    }
}
?>

<div class="container">
    <h2>添加体格指标记录</h2>
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
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="height" class="form-label">身高 (cm)</label>
                    <input type="number" step="0.1" class="form-control" id="height" name="height" 
                           placeholder="例如：170.5">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="weight" class="form-label">体重 (kg)</label>
                    <input type="number" step="0.1" class="form-control" id="weight" name="weight" 
                           placeholder="例如：65.5">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="waist" class="form-label">腰围 (cm)</label>
                    <input type="number" step="0.1" class="form-control" id="waist" name="waist" 
                           placeholder="例如：80.5">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="blood_sugar" class="form-label">血糖 (mmol/L)</label>
                    <input type="number" step="0.1" class="form-control" id="blood_sugar" name="blood_sugar" 
                           placeholder="例如：5.5">
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">保存记录</button>
                <a href="../users/view.php?id=<?php echo $user_id; ?>" class="btn btn-secondary">返回用户详情</a>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>