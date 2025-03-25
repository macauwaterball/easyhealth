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
    $blood_pressure = $_POST['blood_pressure'] ?? '';
    $heart_rate = $_POST['heart_rate'] ?? '';
    $temperature = $_POST['temperature'] ?? '';

    if (!empty($user_id) && !empty($date)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO physiological_params 
                (user_id, date, blood_pressure, heart_rate, temperature)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $date, $blood_pressure, $heart_rate, $temperature]);
            $success = "生理参数记录添加成功！";
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
    <h2>添加生理参数记录</h2>
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
                
                <div class="mb-3">
                    <label for="blood_pressure" class="form-label">血压 (mmHg)</label>
                    <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" 
                           placeholder="例如：120/80">
                </div>
                
                <div class="mb-3">
                    <label for="heart_rate" class="form-label">心率 (次/分)</label>
                    <input type="number" class="form-control" id="heart_rate" name="heart_rate" 
                           min="0" max="200">
                </div>
                
                <div class="mb-3">
                    <label for="temperature" class="form-label">体温 (°C)</label>
                    <input type="number" class="form-control" id="temperature" name="temperature" 
                           step="0.1" min="35" max="42">
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