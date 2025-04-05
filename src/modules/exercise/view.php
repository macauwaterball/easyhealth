<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;
$error = '';

// 获取记录信息
$database = new Database();
$db = $database->getConnection();
$record = null;
$user = null;

if ($id) {
    try {
        $stmt = $db->prepare("SELECT * FROM exercise_records WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
            $stmt->execute([$record['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error = "获取记录失败：" . $e->getMessage();
    }
}

if (!$record || !$user) {
    // 使用JavaScript重定向而不是header()
    echo "<script>window.location.href = '../physical/list.php';</script>";
    exit;
}

// 解析备注中的IPAQ数据
$notes = $record['notes'];
$ipaq_data = [];
$lines = explode("\n", $notes);
foreach ($lines as $line) {
    if (strpos($line, ':') !== false) {
        list($key, $value) = explode(':', $line, 2);
        $ipaq_data[trim($key)] = trim($value);
    }
}

// 现在可以安全地包含header
require_once '../../includes/header.php';
?>

<div class="container">
    <h2>运动记录详情</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">基本信息</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="30%">用户：</th>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                </tr>
                <tr>
                    <th>日期：</th>
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                </tr>
                <tr>
                    <th>运动类型：</th>
                    <td><?php echo htmlspecialchars($record['exercise_type']); ?></td>
                </tr>
                <tr>
                    <th>总时长：</th>
                    <td><?php echo htmlspecialchars($record['duration']); ?> 分钟</td>
                </tr>
                <tr>
                    <th>活动强度：</th>
                    <td>
                        <?php 
                        $intensity = htmlspecialchars($record['intensity']);
                        $badge_class = 'bg-secondary';
                        
                        if ($intensity == '高强度') {
                            $badge_class = 'bg-danger';
                        } elseif ($intensity == '中等强度') {
                            $badge_class = 'bg-warning';
                        } elseif ($intensity == '低强度') {
                            $badge_class = 'bg-info';
                        } elseif ($intensity == '不活跃') {
                            $badge_class = 'bg-secondary';
                        }
                        
                        echo "<span class='badge {$badge_class}'>{$intensity}</span>";
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">IPAQ问卷详情</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">剧烈身体活动</h6>
                        </div>
                        <div class="card-body">
                            <p><?php echo isset($ipaq_data['剧烈活动']) ? htmlspecialchars($ipaq_data['剧烈活动']) : '无数据'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">中等强度身体活动</h6>
                        </div>
                        <div class="card-body">
                            <p><?php echo isset($ipaq_data['中等强度活动']) ? htmlspecialchars($ipaq_data['中等强度活动']) : '无数据'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">步行</h6>
                        </div>
                        <div class="card-body">
                            <p><?php echo isset($ipaq_data['步行']) ? htmlspecialchars($ipaq_data['步行']) : '无数据'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0">久坐时间</h6>
                        </div>
                        <div class="card-body">
                            <p><?php echo isset($ipaq_data['久坐时间']) ? htmlspecialchars($ipaq_data['久坐时间']) : '无数据'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <strong>总MET-min/周：</strong> <?php echo isset($ipaq_data['总MET-min/周']) ? htmlspecialchars($ipaq_data['总MET-min/周']) : '无数据'; ?>
            </div>
            
            <?php if (isset($ipaq_data['备注']) && !empty(trim($ipaq_data['备注']))): ?>
                <div class="mt-3">
                    <h6>备注：</h6>
                    <p><?php echo nl2br(htmlspecialchars($ipaq_data['备注'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4">
        <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-primary">编辑记录</a>
        <a href="list.php?user_id=<?php echo $record['user_id']; ?>" class="btn btn-secondary">返回列表</a>
        <a href="../users/view.php?id=<?php echo $record['user_id']; ?>" class="btn btn-info">返回用户详情</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>