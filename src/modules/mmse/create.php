<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$success = '';
$user = null;
$userId = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// 確保有效的用戶ID
if ($userId <= 0) {
    header('Location: ../users/search.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // 獲取用戶基本信息
    $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ../users/search.php');
        exit;
    }
    
    // 處理表單提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 計算總分
        $orientation = (int)($_POST['time_orientation'] ?? 0) + (int)($_POST['place_orientation'] ?? 0);
        $registration = (int)($_POST['registration'] ?? 0);
        $attention = (int)($_POST['attention'] ?? 0);
        $recall = (int)($_POST['recall'] ?? 0);
        $naming = (int)($_POST['naming'] ?? 0);
        $repetition = (int)($_POST['repetition'] ?? 0);
        $comprehension = (int)($_POST['comprehension'] ?? 0);
        $reading = (int)($_POST['reading'] ?? 0);
        $writing = (int)($_POST['writing'] ?? 0);
        $drawing = (int)($_POST['drawing'] ?? 0);
        
        $language = $naming + $repetition + $comprehension + $reading + $writing + $drawing;
        $total_score = $orientation + $registration + $attention + $recall + $language;
        
        // 修改SQL語句，使用正確的列名
        $stmt = $db->prepare("
            INSERT INTO mmse_records (
                user_id, test_date, orientation_score, registration, attention, recall, 
                language, total_score, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $userId,
            $_POST['test_date'],
            $orientation,
            $registration,
            $attention,
            $recall,
            $language,
            $total_score,
            $_POST['notes'] ?? ''
        ]);
        
        if ($result) {
            $success = "MMSE測試記錄已成功保存！";
            // 可選：重定向到用戶詳情頁
            // header("Location: ../users/view.php?id=$userId");
            // exit;
        } else {
            $error = "保存記錄時發生錯誤";
        }
    }
    
} catch (PDOException $e) {
    $error = "數據操作失敗：" . $e->getMessage();
}

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-brain me-2"></i>MMSE認知測試</h2>
        <a href="../users/view.php?id=<?php echo $userId; ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>返回用戶資料
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
            <div class="mt-2">
                <a href="../users/view.php?id=<?php echo $userId; ?>" class="btn btn-sm btn-success">
                    返回用戶資料
                </a>
                <a href="list.php?user_id=<?php echo $userId; ?>" class="btn btn-sm btn-info ms-2">
                    查看測試歷史
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($user): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($user['gender']); ?>, 
                            <?php 
                            if (!empty($user['birth_date'])) {
                                $birthDate = new DateTime($user['birth_date']);
                                $today = new DateTime();
                                echo $birthDate->diff($today)->y . '歲';
                            } else {
                                echo '年齡未知';
                            }
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <i class="bi bi-clipboard-check me-2"></i>MMSE測試表單
        </div>
        <div class="card-body">
            <form id="mmseForm" method="post" action="">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="test_date" class="form-label">測試日期</label>
                        <input type="date" class="form-control" id="test_date" name="test_date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>測試說明：</strong> 本測試適用於室外環境，可通過口頭回答和手勢完成。請根據受試者的表現為每個項目評分。
                </div>
                
                <!-- 定向力 (10分) -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">一、定向力 (10分)</h5>
                    </div>
                    <div class="card-body">
                        <!-- 時間定向 (5分) -->
                        <div class="mb-4">
                            <h6 class="fw-bold">時間定向 (5分)</h6>
                            <p class="text-muted">詢問："請告訴我現在是什麼年份、季節、月份、日期和星期幾？"</p>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="year" name="time_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="year">年份</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="season" name="time_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="season">季節</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="month" name="time_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="month">月份</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="date" name="time_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="date">日期</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="day" name="time_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="day">星期</label>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="time_orientation" name="time_orientation" value="0">
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="time_orientation_score">0</span>/5</span>
                            </div>
                        </div>
                        
                        <!-- 地點定向 (5分) -->
                        <div>
                            <h6 class="fw-bold">地點定向 (5分)</h6>
                            <p class="text-muted">詢問："我們現在在哪裡？這是什麼地方/城市/區域/省份/國家？"</p>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="specific_place" name="place_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="specific_place">具體地點</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="floor" name="place_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="floor">樓層/街道</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="district" name="place_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="district">區域</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="city" name="place_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="city">城市</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="country" name="place_orientation_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="country">國家</label>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="place_orientation" name="place_orientation" value="0">
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="place_orientation_score">0</span>/5</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 記憶力-記憶登記 (3分) -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">二、記憶登記 (3分)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">說："我會告訴你三個詞，請你重複一遍，並記住它們，因為等一下我會再問你。"</p>
                        <p class="text-muted">例如：蘋果、鑰匙、皮包（每個詞1分）</p>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="word1" name="registration_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="word1">詞語1</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="word2" name="registration_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="word2">詞語2</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="word3" name="registration_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="word3">詞語3</label>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="registration" name="registration" value="0">
                        <div class="mt-2">
                            <span class="badge bg-primary">得分：<span id="registration_score">0</span>/3</span>
                        </div>
                    </div>
                </div>
                
                <!-- 注意力和計算力 (5分) -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">三、注意力和計算力 (5分)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">說："請從100開始，每次減去7，一直減五次。"</p>
                        <p class="text-muted">正確答案：93, 86, 79, 72, 65（每個答案1分）</p>
                        <p class="text-muted">替代方案：如果受試者無法計算，可以請其倒著拼"世界"這個詞。</p>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="calc1" name="attention_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="calc1">第一次計算正確</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="calc2" name="attention_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="calc2">第二次計算正確</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="calc3" name="attention_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="calc3">第三次計算正確</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="calc4" name="attention_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="calc4">第四次計算正確</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="calc5" name="attention_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="calc5">第五次計算正確</label>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="attention" name="attention" value="0">
                        <div class="mt-2">
                            <span class="badge bg-primary">得分：<span id="attention_score">0</span>/5</span>
                        </div>
                    </div>
                </div>
                
                <!-- 記憶力-回憶 (3分) -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">四、回憶 (3分)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">說："請告訴我剛才我讓你記住的三個詞。"</p>
                        <p class="text-muted">（每個詞1分）</p>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="recall1" name="recall_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="recall1">詞語1</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="recall2" name="recall_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="recall2">詞語2</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="recall3" name="recall_items[]" onchange="updateScore()">
                                    <label class="form-check-label" for="recall3">詞語3</label>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="recall" name="recall" value="0">
                        <div class="mt-2">
                            <span class="badge bg-primary">得分：<span id="recall_score">0</span>/3</span>
                        </div>
                    </div>
                </div>
                
                <!-- 語言能力 (9分) -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">五、語言能力 (9分)</h5>
                    </div>
                    <div class="card-body">
                        <!-- 命名 (2分) -->
                        <div class="mb-4">
                            <h6 class="fw-bold">命名 (2分)</h6>
                            <p class="text-muted">指著手錶和筆，問："這是什麼？"</p>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="naming1" name="naming_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="naming1">手錶</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="naming2" name="naming_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="naming2">筆</label>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="naming" name="naming" value="0">
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="naming_score">0</span>/2</span>
                            </div>
                        </div>
                        
                        <!-- 復述 (1分) -->
                        <div class="mb-4">
                            <h6 class="fw-bold">復述 (1分)</h6>
                            <p class="text-muted">說："請跟我重複一句話：'大家不要見外'"</p>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="repetition" name="repetition" onchange="updateScore()">
                                <label class="form-check-label" for="repetition">能夠完整復述</label>
                            </div>
                            
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="repetition_score">0</span>/1</span>
                            </div>
                        </div>
                        
                        <!-- 理解 (3分) -->
                        <div class="mb-4">
                            <h6 class="fw-bold">理解 (3分)</h6>
                            <p class="text-muted">說："請聽我的指令：用右手拿一張紙，對折，然後放在地上。"</p>
                            <p class="text-muted">（可用手勢代替實際動作，每個動作1分）</p>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="comprehension1" name="comprehension_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="comprehension1">用右手拿紙</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="comprehension2" name="comprehension_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="comprehension2">對折</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="comprehension3" name="comprehension_items[]" onchange="updateScore()">
                                        <label class="form-check-label" for="comprehension3">放在地上</label>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="comprehension" name="comprehension" value="0">
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="comprehension_score">0</span>/3</span>
                            </div>
                        </div>
                        
                        <!-- 閱讀 (1分) -->
                        <div class="mb-4">
                            <h6 class="fw-bold">閱讀 (1分)</h6>
                            <p class="text-muted">展示"請閉上眼睛"的紙條，說："請閱讀並執行上面的指令。"</p>
                            <p class="text-muted">（如果老人無法閱讀，可以口頭告知指令，觀察是否能理解並執行）</p>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="reading" name="reading" onchange="updateScore()">
                                <label class="form-check-label" for="reading">能夠理解並執行</label>
                            </div>
                            
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="reading_score">0</span>/1</span>
                            </div>
                        </div>
                        
                        <!-- 書寫 (1分) -->
                        <div class="mb-4">
                            <h6 class="fw-bold">書寫 (1分)</h6>
                            <p class="text-muted">說："請說一個完整的句子。"</p>
                            <p class="text-muted">（由於在室外無法書寫，可以請老人口頭說出一個完整的句子）</p>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="writing" name="writing" onchange="updateScore()">
                                <label class="form-check-label" for="writing">能夠說出完整句子</label>
                            </div>
                            
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="writing_score">0</span>/1</span>
                            </div>
                        </div>
                        
                        <!-- 視覺空間 (1分) -->
                        <div>
                            <h6 class="fw-bold">視覺空間 (1分)</h6>
                            <p class="text-muted">說："請在空中用手指畫出兩個相交的五邊形。"</p>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="drawing" name="drawing" onchange="updateScore()">
                                <label class="form-check-label" for="drawing">能夠正確畫出</label>
                            </div>
                            
                            <div class="mt-2">
                                <span class="badge bg-primary">得分：<span id="drawing_score">0</span>/1</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 總分和評估 -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">總分和評估</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h3 class="mb-0">總分：<span id="total_score">0</span>/30</h3>
                            </div>
                            <div class="col-md-8">
                                <div id="score_evaluation" class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <span id="evaluation_text">請完成測試以獲得評估結果</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 備註 -->
                <div class="mb-4">
                    <label for="notes" class="form-label">備註</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="請輸入測試過程中的觀察或其他備註"></textarea>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-1"></i>保存測試結果
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 更新分數的函數
function updateScore() {
    // 時間定向
    const timeOrientationItems = document.querySelectorAll('input[name="time_orientation_items[]"]:checked');
    const timeOrientationScore = timeOrientationItems.length;
    document.getElementById('time_orientation_score').textContent = timeOrientationScore;
    document.getElementById('time_orientation').value = timeOrientationScore;
    
    // 地點定向
    const placeOrientationItems = document.querySelectorAll('input[name="place_orientation_items[]"]:checked');
    const placeOrientationScore = placeOrientationItems.length;
    document.getElementById('place_orientation_score').textContent = placeOrientationScore;
    document.getElementById('place_orientation').value = placeOrientationScore;
    
    // 記憶登記
    const registrationItems = document.querySelectorAll('input[name="registration_items[]"]:checked');
    const registrationScore = registrationItems.length;
    document.getElementById('registration_score').textContent = registrationScore;
    document.getElementById('registration').value = registrationScore;
    
    // 注意力和計算力
    const attentionItems = document.querySelectorAll('input[name="attention_items[]"]:checked');
    const attentionScore = attentionItems.length;
    document.getElementById('attention_score').textContent = attentionScore;
    document.getElementById('attention').value = attentionScore;
    
    // 回憶
    const recallItems = document.querySelectorAll('input[name="recall_items[]"]:checked');
    const recallScore = recallItems.length;
    document.getElementById('recall_score').textContent = recallScore;
    document.getElementById('recall').value = recallScore;
    
    // 命名
    const namingItems = document.querySelectorAll('input[name="naming_items[]"]:checked');
    const namingScore = namingItems.length;
    document.getElementById('naming_score').textContent = namingScore;
    document.getElementById('naming').value = namingScore;
    
    // 復述
    const repetitionChecked = document.getElementById('repetition').checked;
    const repetitionScore = repetitionChecked ? 1 : 0;
    document.getElementById('repetition_score').textContent = repetitionScore;
    
    // 理解
    const comprehensionItems = document.querySelectorAll('input[name="comprehension_items[]"]:checked');
    const comprehensionScore = comprehensionItems.length;
    document.getElementById('comprehension_score').textContent = comprehensionScore;
    document.getElementById('comprehension').value = comprehensionScore;
    
    // 閱讀
    const readingChecked = document.getElementById('reading').checked;
    const readingScore = readingChecked ? 1 : 0;
    document.getElementById('reading_score').textContent = readingScore;
    
    // 書寫
    const writingChecked = document.getElementById('writing').checked;
    const writingScore = writingChecked ? 1 : 0;
    document.getElementById('writing_score').textContent = writingScore;
    
    // 視覺空間
    const drawingChecked = document.getElementById('drawing').checked;
    const drawingScore = drawingChecked ? 1 : 0;
    document.getElementById('drawing_score').textContent = drawingScore;
    
    // 計算總分
    const orientationScore = timeOrientationScore + placeOrientationScore;
    const languageScore = namingScore + repetitionScore + comprehensionScore + readingScore + writingScore + drawingScore;
    const totalScore = orientationScore + registrationScore + attentionScore + recallScore + languageScore;
    
    // 更新總分顯示
    document.getElementById('total_score').textContent = totalScore;
    
    // 評估認知水平
    updateEvaluation(totalScore);
}

// 根據總分更新評估結果
function updateEvaluation(score) {
    let evaluationText = '';
    let alertClass = '';
    
    if (score >= 27) {
        evaluationText = '認知功能正常';
        alertClass = 'alert-success';
    } else if (score >= 21 && score <= 26) {
        evaluationText = '輕度認知障礙';
        alertClass = 'alert-info';
    } else if (score >= 10 && score <= 20) {
        evaluationText = '中度認知障礙';
        alertClass = 'alert-warning';
    } else {
        evaluationText = '重度認知障礙';
        alertClass = 'alert-danger';
    }
    
    // 更新評估文字
    document.getElementById('evaluation_text').textContent = evaluationText;
    
    // 更新評估框的樣式
    const evaluationDiv = document.getElementById('score_evaluation');
    evaluationDiv.className = 'alert ' + alertClass;
}

// 頁面加載時初始化
document.addEventListener('DOMContentLoaded', function() {
    // 為所有複選框添加事件監聽器
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateScore);
    });
    
    // 初始化分數顯示
    updateScore();
});
</script>

<?php require_once '../../includes/footer.php'; ?>
