<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$user_id = $_GET['user_id'] ?? 0;
$error = '';
$success = '';

// 获取用户信息
$database = new Database();
$db = $database->getConnection();
$user = null;

if ($user_id) {
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    header("Location: ../users/search.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $date = trim($_POST['date'] ?? date('Y-m-d'));
    
    // IPAQ问卷数据
    $vigorous_days = intval($_POST['vigorous_days'] ?? 0);
    $vigorous_time = intval($_POST['vigorous_time'] ?? 0);
    $moderate_days = intval($_POST['moderate_days'] ?? 0);
    $moderate_time = intval($_POST['moderate_time'] ?? 0);
    $walking_days = intval($_POST['walking_days'] ?? 0);
    $walking_time = intval($_POST['walking_time'] ?? 0);
    $sitting_time = intval($_POST['sitting_time'] ?? 0);
    
    // 计算MET-min值
    // 剧烈活动：8.0 METs
    $vigorous_met = $vigorous_days * $vigorous_time * 8.0;
    // 中等强度活动：4.0 METs
    $moderate_met = $moderate_days * $moderate_time * 4.0;
    // 步行：3.3 METs
    $walking_met = $walking_days * $walking_time * 3.3;
    // 总MET-min/周
    $total_met = $vigorous_met + $moderate_met + $walking_met;
    
    // 确定活动水平
    $activity_level = '';
    if ($vigorous_days >= 3 && $vigorous_time >= 20) {
        $activity_level = '高强度';
    } elseif (($moderate_days + $walking_days) >= 5 && ($moderate_time + $walking_time) >= 30) {
        $activity_level = '中等强度';
    } elseif ($total_met > 0) {
        $activity_level = '低强度';
    } else {
        $activity_level = '不活跃';
    }
    
    // 备注
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $db->prepare("
            INSERT INTO exercise_records 
            (user_id, date, exercise_type, duration, intensity, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $user_id,
            $date,
            'IPAQ问卷',
            $vigorous_time + $moderate_time + $walking_time,
            $activity_level,
            "剧烈活动: {$vigorous_days}天/{$vigorous_time}分钟\n" .
            "中等强度活动: {$moderate_days}天/{$moderate_time}分钟\n" .
            "步行: {$walking_days}天/{$walking_time}分钟\n" .
            "久坐时间: {$sitting_time}分钟/天\n" .
            "总MET-min/周: {$total_met}\n" .
            "活动水平: {$activity_level}\n" .
            "备注: {$notes}"
        ]);
        
        if ($result) {
            $success = "运动记录添加成功";
        } else {
            $error = "添加失败，请重试";
        }
    } catch (PDOException $e) {
        $error = "添加失败：" . $e->getMessage();
    }
}

// 现在可以安全地包含header
require_once '../../includes/header.php';
?>

<div class="container">
    <h2>添加运动记录 (IPAQ短版问卷)</h2>
    <h4>用户：<?php echo htmlspecialchars($user['name']); ?></h4>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="date" class="form-label">日期</label>
                <input type="date" class="form-control" id="date" name="date" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">剧烈身体活动</h5>
                </div>
                <div class="card-body">
                    <p>指让您呼吸比平时急促得多、费力得多的活动，如提重物、挖地、做重体力劳动或快跑。</p>
                    
                    <div class="mb-3">
                        <label for="vigorous_days" class="form-label">在过去7天中，您进行剧烈身体活动的天数是多少？</label>
                        <select class="form-select" id="vigorous_days" name="vigorous_days">
                            <option value="0">0天</option>
                            <option value="1">1天</option>
                            <option value="2">2天</option>
                            <option value="3">3天</option>
                            <option value="4">4天</option>
                            <option value="5">5天</option>
                            <option value="6">6天</option>
                            <option value="7">7天</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vigorous_time" class="form-label">通常在这些天里，您每天进行剧烈身体活动的时间是多少分钟？</label>
                        <input type="number" class="form-control" id="vigorous_time" name="vigorous_time" min="0" value="0">
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">中等强度身体活动</h5>
                </div>
                <div class="card-body">
                    <p>指让您呼吸比平时稍微急促一些、稍微费力一些的活动，如提轻物、骑自行车或打太极拳。</p>
                    
                    <div class="mb-3">
                        <label for="moderate_days" class="form-label">在过去7天中，您进行中等强度身体活动的天数是多少？</label>
                        <select class="form-select" id="moderate_days" name="moderate_days">
                            <option value="0">0天</option>
                            <option value="1">1天</option>
                            <option value="2">2天</option>
                            <option value="3">3天</option>
                            <option value="4">4天</option>
                            <option value="5">5天</option>
                            <option value="6">6天</option>
                            <option value="7">7天</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="moderate_time" class="form-label">通常在这些天里，您每天进行中等强度身体活动的时间是多少分钟？</label>
                        <input type="number" class="form-control" id="moderate_time" name="moderate_time" min="0" value="0">
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">步行</h5>
                </div>
                <div class="card-body">
                    <p>包括在工作场所、在家中、从一个地方到另一个地方的步行，以及您纯粹为了娱乐、运动、锻炼或休闲而进行的步行。</p>
                    
                    <div class="mb-3">
                        <label for="walking_days" class="form-label">在过去7天中，您步行的天数是多少？</label>
                        <select class="form-select" id="walking_days" name="walking_days">
                            <option value="0">0天</option>
                            <option value="1">1天</option>
                            <option value="2">2天</option>
                            <option value="3">3天</option>
                            <option value="4">4天</option>
                            <option value="5">5天</option>
                            <option value="6">6天</option>
                            <option value="7">7天</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="walking_time" class="form-label">通常在这些天里，您每天步行的时间是多少分钟？</label>
                        <input type="number" class="form-control" id="walking_time" name="walking_time" min="0" value="0">
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">久坐时间</h5>
                </div>
                <div class="card-body">
                    <p>包括在工作场所、家中、做功课以及休闲时间的久坐时间，例如坐在桌旁、看望朋友、看书、坐着或躺着看电视。</p>
                    
                    <div class="mb-3">
                        <label for="sitting_time" class="form-label">在过去7天中，您在工作日每天坐着的时间通常是多少分钟？</label>
                        <input type="number" class="form-control" id="sitting_time" name="sitting_time" min="0" value="0">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">备注</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">保存记录</button>
                <a href="../users/view.php?id=<?php echo $user_id; ?>" class="btn btn-secondary">返回用户详情</a>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>