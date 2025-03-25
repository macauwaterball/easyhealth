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
    $time_of_day = $_POST['time_of_day'] ?? '';
    $medication_name = $_POST['medication_name'] ?? '';
    $dosage = $_POST['dosage'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $purpose = $_POST['purpose'] ?? null;
    $side_effects = $_POST['side_effects'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!empty($user_id) && !empty($date) && !empty($medication_name) && !empty($dosage)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO medication_records 
                (user_id, date, time_of_day, medication_name, dosage, unit, purpose, side_effects, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $date, $time_of_day, $medication_name, $dosage, $unit, $purpose, $side_effects, $notes
            ]);
            $success = "用药记录添加成功！";
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
    <h2>添加用药记录</h2>
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
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">用药日期 *</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="time_of_day" class="form-label">服用时间 *</label>
                        <select class="form-select" id="time_of_day" name="time_of_day" required>
                            <option value="">请选择</option>
                            <option value="早餐前">早餐前</option>
                            <option value="早餐后">早餐后</option>
                            <option value="午餐前">午餐前</option>
                            <option value="午餐后">午餐后</option>
                            <option value="晚餐前">晚餐前</option>
                            <option value="晚餐后">晚餐后</option>
                            <option value="睡前">睡前</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="medication_name" class="form-label">药品名称 *</label>
                    <input type="text" class="form-control" id="medication_name" name="medication_name" 
                           required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dosage" class="form-label">剂量 *</label>
                        <input type="text" class="form-control" id="dosage" name="dosage" 
                               required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">单位 *</label>
                        <select class="form-select" id="unit" name="unit" required>
                            <option value="">请选择</option>
                            <option value="片">片</option>
                            <option value="粒">粒</option>
                            <option value="毫克">毫克</option>
                            <option value="克">克</option>
                            <option value="毫升">毫升</option>
                            <option value="包">包</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="purpose" class="form-label">用药目的</label>
                    <input type="text" class="form-control" id="purpose" name="purpose">
                </div>
                
                <div class="mb-3">
                    <label for="side_effects" class="form-label">不良反应</label>
                    <textarea class="form-control" id="side_effects" name="side_effects" 
                              rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">备注</label>
                    <textarea class="form-control" id="notes" name="notes" 
                              rows="2"></textarea>
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