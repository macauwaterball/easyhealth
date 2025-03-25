<?php
$host = getenv('MYSQL_HOST');
$db   = getenv('MYSQL_DATABASE');
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "<p style='color: green;'>数据库连接成功！</p>";
} catch (\PDOException $e) {
    die("连接失败: " . $e->getMessage());
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $birth_date = $_POST['birth_date'];
        $gender = $_POST['gender'];

        // 检查是否已存在
        $stmt = $pdo->prepare("SELECT * FROM demographics WHERE name = ? AND phone = ?");
        $stmt->execute([$name, $phone]);
        $existing = $stmt->fetch();

        if ($existing) {
            echo "<p style='color: red;'>患者已存在！</p>";
        } else {
            // 插入新记录
            $sql = "INSERT INTO demographics (name, phone, birth_date, gender) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$name, $phone, $birth_date, $gender]);

            if ($result) {
                echo "<p style='color: green;'>患者创建成功！</p>";
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>患者管理测试</title>
    <meta charset="utf-8">
    <style>
        body { max-width: 800px; margin: 20px auto; padding: 0 20px; font-family: Arial, sans-serif; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>患者管理测试页面</h1>
    
    <form method="post">
        <div class="form-group">
            <label>姓名:</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>电话:</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>出生日期:</label>
            <input type="date" name="birth_date" required>
        </div>
        <div class="form-group">
            <label>性别:</label>
            <select name="gender">
                <option value="男">男</option>
                <option value="女">女</option>
            </select>
        </div>
        <button type="submit">创建患者</button>
    </form>

    <h2>现有患者列表</h2>
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM demographics ORDER BY id DESC LIMIT 10");
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>姓名</th><th>电话</th><th>出生日期</th><th>性别</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['phone']}</td>";
            echo "<td>{$row['birth_date']}</td>";
            echo "<td>{$row['gender']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>获取患者列表失败: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>