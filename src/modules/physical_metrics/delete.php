<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;
$error = '';
$user_id = 0;

// 获取记录信息
$database = new Database();
$db = $database->getConnection();

if ($id) {
    try {
        // 先获取用户ID，以便删除后重定向
        $stmt = $db->prepare("SELECT user_id FROM physical_metrics WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            $user_id = $record['user_id'];
            
            // 删除记录
            $stmt = $db->prepare("DELETE FROM physical_metrics WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                header("Location: list.php?user_id=" . $user_id . "&deleted=1");
                exit;
            } else {
                $error = "删除失败，请重试";
            }
        } else {
            $error = "找不到指定的记录";
        }
    } catch (PDOException $e) {
        $error = "删除失败：" . $e->getMessage();
    }
}

// 如果执行到这里，说明出现了错误
if ($user_id > 0) {
    header("Location: list.php?user_id=" . $user_id . "&error=" . urlencode($error));
} else {
    header("Location: ../users/search.php?error=" . urlencode($error));
}
exit;
?>