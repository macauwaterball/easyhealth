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
    $exercise_type = $_POST['exercise_type'] ?? '';
    $duration_minutes = $_POST['duration_minutes'] ?? null;
    $steps_count = $_POST['steps_count'] ?? null;
    $distance_km = $_POST['distance_km'] ?? null;
    $calories_burned = $_POST['calories_burned'] ?? null;
    $heart_rate_avg = $_POST['heart_rate_avg'] ?? null;
    $intensity_level = $_POST['intensity_level'] ?? null;
    $location = $_POST['location'] ?? null;
    $weather = $_POST['weather'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!empty($user_id) && !empty($date) && !empty($exercise_type)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO exercise_records 
                (user_id, date, exercise_type, duration_minutes, steps_count, 
                distance_km, calories_burned, heart_rate_avg, intensity_level, 
                location, weather, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $date, $exercise_type, $duration_minutes, $steps_count,
                $distance_km, $calories_burned, $heart_rate_avg, $intensity_level,
                $location, $weather, $notes
            ]);
            $success = "运动记录添加成功！";
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
    <h2>添加运动记录</h2>
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
                        <label for="date" class="form-label">运动日期 *</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="exercise_type" class="form-label">运动类型 *</label>
                        <select class="form-select" id="exercise_type" name="exercise_type" required>
                            <option value="">请选择</option>
                            <option value="步行">步行</option>
                            <option value="跑步">跑步</option>
                            <option value="游泳">游泳</option>
                            <option value="骑行">骑行</option>
                            <option value="健身">健身</option>
                            <option value="瑜伽">瑜伽</option>
                            <option value="球类运动">球类运动</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="duration_minutes" class="form-label">运动时长 (分钟)</label>
                        <input type="number" class="form-control" id="duration_minutes" 
                               name="duration_minutes" min="0" max="1440">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="steps_count" class="form-label">步数</label>
                        <input type="number" class="form-control" id="steps_count" 
                               name="steps_count" min="0">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="distance_km" class="form-label">运动距离 (公里)</label>
                        <input type="number" class="form-control" id="distance_km" 
                               name="distance_km" step="0.01" min="0">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="calories_burned" class="form-label">消耗热量 (kcal)</label>
                        <input type="number" class="form-control" id="calories_burned" 
                               name="calories_burned" min="0">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="heart_rate_avg" class="form-label">平均心率 (次/分)</label>
                        <input type="number" class="form-control" id="heart_rate_avg" 
                               name="heart_rate_avg" min="0" max="250">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="intensity_level" class="form-label">运动强度</label>
                        <select class="form-select" id="intensity_level" name="intensity_level">
                            <option value="">请选择</option>
                            <option value="低强度">低强度</option>
                            <option value="中等强度">中等强度</option>
                            <option value="高强度">高强度</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="location" class="form-label">运动地点</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="weather" class="form-label">天气情况</label>
                        <input type="text" class="form-control" id="weather" name="weather">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">备注</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
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