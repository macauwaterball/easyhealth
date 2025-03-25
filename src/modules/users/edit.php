<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$success = '';
$user = null;

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "获取用户信息失败：" . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $id = $_POST['id'] ?? '';

    if (!empty($name) && !empty($phone) && !empty($birth_date) && !empty($gender) && !empty($id)) {
        try {
            $stmt = $db->prepare("UPDATE demographics SET name = ?, phone = ?, birth_date = ?, gender = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $birth_date, $gender, $id]);
            $success = "用户信息更新成功！";
            
            // 重新获取用户信息
            $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "更新失败：" . $e->getMessage();
        }
    } else {
        $error = "请填写所有必填字段";
    }
}

if (!$user) {
    header('Location: search.php');
    exit;
}
?>

<div class="container">
    <h2>编辑用户信息</h2>
    
    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="form-container">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label">姓名 *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">电话 *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="birth_date" class="form-label">出生日期 *</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" 
                           value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="gender" class="form-label">性别 *</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">请选择</option>
                        <option value="男" <?php echo $user['gender'] == '男' ? 'selected' : ''; ?>>男</option>
                        <option value="女" <?php echo $user['gender'] == '女' ? 'selected' : ''; ?>>女</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">返回</a>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>