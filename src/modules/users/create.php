<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';

    if (!empty($name) && !empty($phone) && !empty($birth_date) && !empty($gender)) {
        $database = new Database();
        $db = $database->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO demographics (name, phone, birth_date, gender) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $birth_date, $gender]);
            $success = "用户创建成功！";
        } catch (PDOException $e) {
            $error = "创建失败：" . $e->getMessage();
        }
    } else {
        $error = "请填写所有必填字段";
    }
}
?>

<div class="container">
    <h2>创建新用户</h2>
    
    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="form-container">
                <div class="mb-3">
                    <label for="name" class="form-label">姓名 *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">电话 *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>
                
                <div class="mb-3">
                    <label for="birth_date" class="form-label">出生日期 *</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                </div>
                
                <div class="mb-3">
                    <label for="gender" class="form-label">性别 *</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">请选择</option>
                        <option value="男">男</option>
                        <option value="女">女</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="search.php" class="btn btn-secondary">返回</a>
                    <button type="submit" class="btn btn-primary">创建</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>