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
    $meal_time = $_POST['meal_time'] ?? '';
    $food_items = $_POST['food_items'] ?? '';
    $calories = $_POST['calories'] ?? null;
    $protein = $_POST['protein'] ?? null;
    $carbs = $_POST['carbs'] ?? null;
    $fat = $_POST['fat'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!empty($user_id) && !empty($date) && !empty($meal_time) && !empty($food_items)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO diet_records 
                (user_id, date, meal_time, food_items, calories, protein, carbs, fat, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $date, $meal_time, $food_items, $calories, $protein, $carbs, $fat, $notes
            ]);
            $success = "饮食记录添加成功！";
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
    <h2>添加饮食记录</h2>
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
                        <label for="date" class="form-label">记录日期 *</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="meal_time" class="form-label">用餐时间 *</label>
                        <select class="form-select" id="meal_time" name="meal_time" required>
                            <option value="">请选择</option>
                            <option value="早餐">早餐</option>
                            <option value="上午加餐">上午加餐</option>
                            <option value="午餐">午餐</option>
                            <option value="下午加餐">下午加餐</option>
                            <option value="晚餐">晚餐</option>
                            <option value="夜宵">夜宵</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="food_items" class="form-label">食物清单 *</label>
                    <textarea class="form-control" id="food_items" name="food_items" 
                              rows="3" required placeholder="请列出食用的食物，每项食物用逗号分隔"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="calories" class="form-label">热量 (kcal)</label>
                        <input type="number" class="form-control" id="calories" name="calories" 
                               min="0" max="5000">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="protein" class="form-label">蛋白质 (g)</label>
                        <input type="number" class="form-control" id="protein" name="protein" 
                               step="0.1" min="0" max="500">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="carbs" class="form-label">碳水化合物 (g)</label>
                        <input type="number" class="form-control" id="carbs" name="carbs" 
                               step="0.1" min="0" max="500">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="fat" class="form-label">脂肪 (g)</label>
                        <input type="number" class="form-control" id="fat" name="fat" 
                               step="0.1" min="0" max="500">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">备注</label>
                    <textarea class="form-control" id="notes" name="notes" 
                              rows="2" placeholder="添加任何相关备注"></textarea>
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