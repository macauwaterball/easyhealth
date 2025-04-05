<?php
session_start();
define('SESSION_TIMEOUT', 1800); // 30分钟超时

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: /auth/login.php?msg=timeout');
    exit;
}
$_SESSION['last_activity'] = time();
?>