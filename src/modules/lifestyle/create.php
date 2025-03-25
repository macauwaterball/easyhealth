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
    $sleep_hours = $_POST['sleep_hours'] ?? null;
    $exercise_minutes = $_POST['exercise_minutes'] ?? null;
    $exercise_type = $_POST['exercise_type'] ?? null;
    $stress_level = $_POST['stress_level'] ?? null;
    $mood = $_POST['mood'] ?? null;
    $smoking_count = $_POST['smoking_count'] ?? null;
    $drinking_amount = $_POST['drinking_amount'] ?? null;

    if (!empty($user_id) && !empty($date)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO lifestyle_records 
                (user_id, date, sleep_hours, exercise_minutes, exercise_type, 
                stress_level, mood, smoking_count, drinking_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $date, $sleep_hours, $exercise_minutes, $exercise_type,
                $stress_level, $mood, $smoking_count, $drinking_amount
            ]);
            $success = "生活习惯记录添加成功！";
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
    <h2>添加生活习惯记录</h2>
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
                    <label for="date" class="form-label">记录日期 *</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="sleep_hours" class="form-label">睡眠时长 (小时)</label>
                        <input type="number" class="form-control" id="sleep_hours" name="sleep_hours" 
                               step="0.5" min="0" max="24">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="exercise_minutes" class="form-label">运动时长 (分钟)</label>
                        <input type="number" class="form-control" id="exercise_minutes" name="exercise_minutes" 
                               min="0" max="1440">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="exercise_type" class="form-label">运动类型</label>
                    <input type="text" class="form-control" id="exercise_type" name="exercise_type" 
                           placeholder="例如：步行、跑步、游泳等">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="stress_level" class="form-label">压力等级 (1-5)</label>
                        <select class="form-select" id="stress_level" name="stress_level">
                            <option value="">请选择</option>
                            <option value="1">1 - 很轻松</option>
                            <option value="2">2 - 较轻松</option>
                            <option value="3">3 - 一般</option>
                            <option value="4">4 - 较压力</option>
                            <option value="5">5 - 很压力</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mood" class="form-label">心情</label>
                        <select class="form-select" id="mood" name="mood">
                            <option value="">请选择</option>
                            <option value="很好">很好</option>
                            <option value="好">好</option>
                            <option value="一般">一般</option>
                            <option value="不好">不好</option>
                            <option value="很不好">很不好</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="smoking_count" class="form-label">吸烟数量 (支)</label>
                        <input type="number" class="form-control" id="smoking_count" name="smoking_count" 
                               min="0" max="100">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="drinking_amount" class="form-label">饮酒情况</label>
                        <input type="text" class="form-control" id="drinking_amount" name="drinking_amount" 
                               placeholder="例如：2瓶啤酒">
                    </div>
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