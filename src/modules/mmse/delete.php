<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$recordId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = 0;

// 確保有效的記錄ID
if ($recordId <= 0) {
    header('Location: ../users/search.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // 先獲取用戶ID，以便刪除後重定向
    $stmt = $db->prepare("SELECT user_id FROM mmse_records WHERE id = ?");
    $stmt->execute([$recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($record) {
        $userId = $record['user_id'];
        
        // 刪除記錄
        $stmt = $db->prepare("DELETE FROM mmse_records WHERE id = ?");
        $result = $stmt->execute([$recordId]);
        
        if ($result) {
            header('Location: list.php?user_id=' . $userId . '&deleted=1');
            exit;
        } else {
            $error = "刪除失敗，請稍後再試";
        }
    } else {
        $error = "找不到指定的記錄";
    }
    
} catch (PDOException $e) {
    $error = "數據操作失敗：" . $e->getMessage();
}

// 如果執行到這裡，說明出現了錯誤
if ($userId > 0) {
    header('Location: list.php?user_id=' . $userId . '&error=' . urlencode($error));
} else {
    header('Location: ../users/search.php?error=' . urlencode($error));
}
exit;
?>