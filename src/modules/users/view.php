<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$user = null;

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // 获取用户基本信息
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取最新的生理参数
        $stmt = $db->prepare("
            SELECT * FROM physiological_params 
            WHERE user_id = ? 
            ORDER BY date DESC 
            LIMIT 1
        ");
        $stmt->execute([$_GET['id']]);
        $physio = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "获取信息失败：" . $e->getMessage();
    }
}

if (!$user) {
    header('Location: search.php');
    exit;
}
?>

<div class="container">
    <h2>用户详细信息</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">基本信息</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">姓名：</th>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                        </tr>
                        <tr>
                            <th>电话：</th>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>出生日期：</th>
                            <td><?php echo htmlspecialchars($user['birth_date']); ?></td>
                        </tr>
                        <tr>
                            <th>性别：</th>
                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">编辑信息</a>
                    <a href="search.php" class="btn btn-secondary">返回列表</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">最新生理参数</h5>
                </div>
                <div class="card-body">
                    <?php if ($physio): ?>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">测量日期：</th>
                                <td><?php echo htmlspecialchars($physio['date']); ?></td>
                            </tr>
                            <tr>
                                <th>血压：</th>
                                <td><?php echo htmlspecialchars($physio['blood_pressure']); ?></td>
                            </tr>
                            <tr>
                                <th>心率：</th>
                                <td><?php echo htmlspecialchars($physio['heart_rate']); ?></td>
                            </tr>
                            <tr>
                                <th>体温：</th>
                                <td><?php echo htmlspecialchars($physio['temperature']); ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无生理参数记录</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="../physiological/create.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-success">添加生理参数</a>
                    <a href="../physiological/list.php?user_id=<?php echo $user['id']; ?>" 
                       class="btn btn-info">查看历史记录</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>