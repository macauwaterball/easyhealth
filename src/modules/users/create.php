<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    
    // 添加性別值驗證
    $allowed_genders = ['男', '女'];
    if (empty($name) || !in_array($gender, $allowed_genders)) {
        $error = "姓名為必填項且性別必須是'男'或'女'";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO demographics (name, phone, birth_date, gender) 
                VALUES (?, ?, ?, ?)
            ");
            
            if ($birth_date === '') {
                $birth_date = null;
            }
            
            $result = $stmt->execute([$name, $phone, $birth_date, $gender]);
            
            if ($result) {
                $success = "用戶創建成功";
                // 清空表单
                $name = $phone = $birth_date = $gender = '';
            } else {
                $error = "創建失敗，請重試";
            }
        } catch (PDOException $e) {
            $error = "創建失敗：" . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>創建新用户</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">姓名 *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">電話</label>
                <input type="tel" class="form-control" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="birth_date" class="form-label">出生日期</label>
                <input type="date" class="form-control" id="birth_date" name="birth_date" 
                       value="<?php echo htmlspecialchars($birth_date ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="gender" class="form-label">性别 *</label>
                <select class="form-select" id="gender" name="gender" required>
                    <option value="">請選擇</option>
                    <option value="男">男</option>
                    <option value="女">女</option>
                </select>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">創建用户</button>
                <a href="search.php" class="btn btn-secondary">返回列表</a>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
