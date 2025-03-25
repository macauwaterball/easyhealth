<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$success = '';
$user = null;

if (isset($_GET['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "获取用户信息失败：" . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $height = $_POST['height'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $waist = $_POST['waist'] ?? null;
    $hip = $_POST['hip'] ?? null;
    $blood_sugar = $_POST['blood_sugar'] ?? null;
    
    // 计算 BMI
    $bmi = null;
    if ($height && $weight) {
        $height_m = $height / 100; // 转换为米
        $bmi = round($weight / ($height_m * $height_m), 2);
    }

    if (!empty($user_id) && !empty($date)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO physical_metrics 
                (user_id, date, height, weight, bmi, waist, hip, blood_sugar)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $date, $height, $weight, $bmi, $waist, $hip, $blood_sugar]);
            $success = "体格指标记录添加成功！";
        } catch (PDOException $e) {
            $error = "添加失败：" . $e->getMessage();
        }
    } else {
        $error = "请填写必填字段";
    }
}

if (!$user) {
    header('Location: ../users/search.php');
    exit;
}
?>

<div class="container">
    <h2>添加体格指标记录</h2>
    <p class="text-muted">用户：<?php echo htmlspecialchars($user['name']); ?></p>
    
    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="form-container">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                
                <div class="mb-3">
                    <label for="date" class="form-label">测量日期 *</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="height" class="form-label">身高 (cm)</label>
                        <input type="number" class="form-control" id="height" name="height" 
                               step="0.1" min="0" max="300">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">体重 (kg)</label>
                        <input type="number" class="form-control" id="weight" name="weight" 
                               step="0.1" min="0" max="500">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="waist" class="form-label">腰围 (cm)</label>
                        <input type="number" class="form-control" id="waist" name="waist" 
                               step="0.1" min="0" max="200">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="hip" class="form-label">臀围 (cm)</label>
                        <input type="number" class="form-control" id="hip" name="hip" 
                               step="0.1" min="0" max="200">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="blood_sugar" class="form-label">血糖 (mmol/L)</label>
                    <input type="number" class="form-control" id="blood_sugar" name="blood_sugar" 
                           step="0.1" min="0" max="30">
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="../users/view.php?id=<?php echo $user['id']; ?>" 
                       class="btn btn-secondary">返回</a>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>