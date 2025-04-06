<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$users = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
    
    if (!empty($keyword)) {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $stmt = $db->prepare("
                SELECT * FROM demographics 
                WHERE name LIKE ? OR phone LIKE ?
                ORDER BY created_at DESC
            ");
            $searchTerm = "%{$keyword}%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "查詢失敗：" . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>用户查詢</h2>
    <div class="mb-4">
        <a href="create.php" class="btn btn-primary">新建用户</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="keyword" 
                           placeholder="輸入姓名或電話號碼搜索" 
                           value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">搜索</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>姓名</th>
                        <th>電話</th>
                        <th>出生日期</th>
                        <th>性别</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['birth_date']); ?></td>
                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                            <td>
                                <a href="view.php?id=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-info">查看</a>
                                <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-warning">编辑</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (isset($_GET['keyword'])): ?>
        <div class="alert alert-info">未找到匹配的用户 </div>
    <?php endif; ?>
</div>