<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // 先获取记录信息，以便删除后重定向到用户的记录列表
        $stmt = $db->prepare("SELECT user_id FROM exercise_records WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            $user_id = $record['user_id'];
            
            // 删除记录
            $stmt = $db->prepare("DELETE FROM exercise_records WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                // 重定向到用户的运动记录列表
                header("Location: list.php?user_id={$user_id}&deleted=1");
                exit;
            }
        }
    } catch (PDOException $e) {
        // 出错时重定向到列表页面
        header("Location: ../physical/list.php?error=" . urlencode("删除失败：" . $e->getMessage()));
        exit;
    }
}

// 如果没有ID或删除失败，重定向到健康记录主页
header("Location: ../physical/list.php");
exit;
?>