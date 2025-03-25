<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$error = '';
$success = '';
$user = null;
$test_result = null;

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

function getCognitiveStatus($score) {
    if ($score >= 24) {
        return "正常范围";
    } elseif ($score >= 18) {
        return "轻度认知障碍";
    } elseif ($score >= 10) {
        return "中度认知障碍";
    } else {
        return "重度认知障碍";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scores = [
        'orientation_time' => min(5, max(0, intval($_POST['orientation_time'] ?? 0))),
        'orientation_place' => min(5, max(0, intval($_POST['orientation_place'] ?? 0))),
        'registration' => min(3, max(0, intval($_POST['registration'] ?? 0))),
        'attention_calculation' => min(5, max(0, intval($_POST['attention_calculation'] ?? 0))),
        'recall' => min(3, max(0, intval($_POST['recall'] ?? 0))),
        'naming' => min(2, max(0, intval($_POST['naming'] ?? 0))),
        'repetition' => min(1, max(0, intval($_POST['repetition'] ?? 0))),
        'comprehension' => min(3, max(0, intval($_POST['comprehension'] ?? 0))),
        'reading' => min(1, max(0, intval($_POST['reading'] ?? 0))),
        'writing' => min(1, max(0, intval($_POST['writing'] ?? 0))),
        'drawing' => min(1, max(0, intval($_POST['drawing'] ?? 0)))
    ];
    
    $total_score = array_sum($scores);
    $cognitive_status = getCognitiveStatus($total_score);
    
    try {
        $stmt = $db->prepare("
            INSERT INTO mmse_records 
            (user_id, test_date, orientation_time, orientation_place, registration, 
            attention_calculation, recall, naming, repetition, comprehension, 
            reading, writing, drawing, total_score, cognitive_status, notes)
            VALUES (?, CURRENT_DATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['user_id'], 
            $scores['orientation_time'],
            $scores['orientation_place'],
            $scores['registration'],
            $scores['attention_calculation'],
            $scores['recall'],
            $scores['naming'],
            $scores['repetition'],
            $scores['comprehension'],
            $scores['reading'],
            $scores['writing'],
            $scores['drawing'],
            $total_score,
            $cognitive_status,
            $_POST['notes'] ?? null
        ]);
        
        $test_result = [
            'scores' => $scores,
            'total_score' => $total_score,
            'cognitive_status' => $cognitive_status
        ];
        
        $success = "MMSE测试记录已保存！";
    } catch (PDOException $e) {
        $error = "保存失败：" . $e->getMessage();
    }
}

if (!$user) {
    header('Location: ../users/search.php');
    exit;
}
?>

<div class="container">
    <h2>简易心智量表（MMSE）测试</h2>
    <p class="text-muted">用户：<?php echo htmlspecialchars($user['name']); ?></p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($test_result): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">测试结果</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>分项得分：</h6>
                    <ul class="list-unstyled">
                        <li>时间定向：<?php echo $test_result['scores']['orientation_time']; ?>/5分</li>
                        <li>地点定向：<?php echo $test_result['scores']['orientation_place']; ?>/5分</li>
                        <li>记忆登记：<?php echo $test_result['scores']['registration']; ?>/3分</li>
                        <li>注意力和计算：<?php echo $test_result['scores']['attention_calculation']; ?>/5分</li>
                        <li>回忆：<?php echo $test_result['scores']['recall']; ?>/3分</li>
                        <li>命名：<?php echo $test_result['scores']['naming']; ?>/2分</li>
                        <li>复述：<?php echo $test_result['scores']['repetition']; ?>/1分</li>
                        <li>理解：<?php echo $test_result['scores']['comprehension']; ?>/3分</li>
                        <li>阅读：<?php echo $test_result['scores']['reading']; ?>/1分</li>
                        <li>书写：<?php echo $test_result['scores']['writing']; ?>/1分</li>
                        <li>绘图：<?php echo $test_result['scores']['drawing']; ?>/1分</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>总评：</h6>
                    <p>总分：<?php echo $test_result['total_score']; ?>/30分</p>
                    <p>认知状态：<?php echo $test_result['cognitive_status']; ?></p>
                    <div class="alert alert-info">
                        <?php
                        switch($test_result['cognitive_status']) {
                            case '正常范围':
                                echo '认知功能大致正常，无明显障碍。可能存在轻微的记忆力下降，但不影响日常生活。';
                                break;
                            case '轻度认知障碍':
                                echo '可能出现记忆力、注意力或语言能力的轻微问题。日常生活可能受到轻微影响，但整体功能仍能维持。';
                                break;
                            case '中度认知障碍':
                                echo '严重影响日常生活，例如难以完成复杂任务（如购物、管理财务）。需要他人协助处理部分事务。';
                                break;
                            case '重度认知障碍':
                                echo '几乎完全丧失独立生活能力，可能需要全天候照护。语言和行为能力大幅退化。';
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="form-container">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                
                <!-- 时间定向（5分） -->
                <div class="mb-4">
                    <h5>一、时间定向（5分）</h5>
                    <p>请询问以下问题，每答对一题得1分：</p>
                    <ol>
                        <li>现在是哪一年？</li>
                        <li>现在是什么季节？</li>
                        <li>现在是几月？</li>
                        <li>今天是几号？</li>
                        <li>今天是星期几？</li>
                    </ol>
                    <div class="mb-3">
                        <label class="form-label">得分（0-5分）</label>
                        <input type="number" class="form-control" name="orientation_time" 
                               min="0" max="5" required>
                    </div>
                </div>
                
                <!-- 地点定向（5分） -->
                <div class="mb-4">
                    <h5>二、地点定向（5分）</h5>
                    <p>请询问以下问题，每答对一题得1分：</p>
                    <ol>
                        <li>这是什么地方？</li>
                        <li>这是几楼？</li>
                        <li>这是哪个城市？</li>
                        <li>这是哪个省？</li>
                        <li>这是哪个国家？</li>
                    </ol>
                    <div class="mb-3">
                        <label class="form-label">得分（0-5分）</label>
                        <input type="number" class="form-control" name="orientation_place" 
                               min="0" max="5" required>
                    </div>
                </div>
                
                <!-- 记忆登记（3分） -->
                <div class="mb-4">
                    <h5>三、记忆登记（3分）</h5>
                    <p>说出"皮球"、"国旗"、"树木"三个词，要求被检查者重复，每答对一个得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-3分）</label>
                        <input type="number" class="form-control" name="registration" 
                               min="0" max="3" required>
                    </div>
                </div>
                
                <!-- 注意力和计算（5分） -->
                <div class="mb-4">
                    <h5>四、注意力和计算（5分）</h5>
                    <p>从100连续减7，做五次，每答对一次得1分。或要求被检查者将"世界"二字倒写。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-5分）</label>
                        <input type="number" class="form-control" name="attention_calculation" 
                               min="0" max="5" required>
                    </div>
                </div>
                
                <!-- 回忆（3分） -->
                <div class="mb-4">
                    <h5>五、回忆（3分）</h5>
                    <p>回忆前面提到的三个词，每答对一个得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-3分）</label>
                        <input type="number" class="form-control" name="recall" 
                               min="0" max="3" required>
                    </div>
                </div>
                
                <!-- 命名（2分） -->
                <div class="mb-4">
                    <h5>六、命名（2分）</h5>
                    <p>指出手表和铅笔，要求被检查者说出它们的名称，每答对一个得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-2分）</label>
                        <input type="number" class="form-control" name="naming" 
                               min="0" max="2" required>
                    </div>
                </div>
                
                <!-- 复述（1分） -->
                <div class="mb-4">
                    <h5>七、复述（1分）</h5>
                    <p>要求被检查者复述"大家齐心协力"，完全正确得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-1分）</label>
                        <input type="number" class="form-control" name="repetition" 
                               min="0" max="1" required>
                    </div>
                </div>
                
                <!-- 理解（3分） -->
                <div class="mb-4">
                    <h5>八、理解（3分）</h5>
                    <p>要求被检查者执行"用右手拿纸"、"将纸对折"、"放在桌子上"三个动作，每答对一个得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-3分）</label>
                        <input type="number" class="form-control" name="comprehension" 
                               min="0" max="3" required>
                    </div>
                </div>
                
                <!-- 阅读（1分） -->
                <div class="mb-4">
                    <h5>九、阅读（1分）</h5>
                    <p>出示"请闭上眼睛"的卡片，要求被检查者阅读并执行，正确得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-1分）</label>
                        <input type="number" class="form-control" name="reading" 
                               min="0" max="1" required>
                    </div>
                </div>
                
                <!-- 书写（1分） -->
                <div class="mb-4">
                    <h5>十、书写（1分）</h5>
                    <p>要求被检查者写一句完整的句子，有主谓词即可得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-1分）</label>
                        <input type="number" class="form-control" name="writing" 
                               min="0" max="1" required>
                    </div>
                </div>
                
                <!-- 绘图（1分） -->
                <div class="mb-4">
                    <h5>十一、绘图（1分）</h5>
                    <p>要求被检查者模仿画两个相交的五边形，五边形必须有5个角，两个图形必须相交，正确得1分。</p>
                    <div class="mb-3">
                        <label class="form-label">得分（0-1分）</label>
                        <input type="number" class="form-control" name="drawing" 
                               min="0" max="1" required>
                    </div>
                </div>
                
                <!-- 备注 -->
                <div class="mb-4">
                    <label for="notes" class="form-label">备注</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="../users/view.php?id=<?php echo $user['id']; ?>" 
                       class="btn btn-secondary">返回</a>
                    <button type="submit" class="btn btn-primary">提交测试结果</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>