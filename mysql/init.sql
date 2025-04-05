-- 创建数据库
CREATE DATABASE IF NOT EXISTS health_db;
USE health_db;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建默认管理员账户 (admin/admin123)
INSERT INTO users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 人口统计信息表
CREATE TABLE IF NOT EXISTS demographics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    birth_date DATE,
    gender ENUM('男', '女') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 生理参数表
CREATE TABLE IF NOT EXISTS physiological_params (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    blood_pressure VARCHAR(20),
    heart_rate INT,
    temperature DECIMAL(3,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 体格指标表
CREATE TABLE IF NOT EXISTS physical_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    bmi DECIMAL(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 用药记录表
CREATE TABLE IF NOT EXISTS medication_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    medication_name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50),
    time_of_day ENUM('早餐前', '早餐后', '午餐前', '午餐后', '晚餐前', '晚餐后', '睡前'),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- MMSE认知测试记录表
CREATE TABLE IF NOT EXISTS mmse_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_date DATE NOT NULL,
    orientation_score INT,
    memory_score INT,
    attention_score INT,
    language_score INT,
    total_score INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- Create user with proper privileges with error handling
DROP USER IF EXISTS 'easyhealth'@'%';
CREATE USER 'easyhealth'@'%' IDENTIFIED WITH mysql_native_password BY 'easyhealth123';
GRANT ALL PRIVILEGES ON health_db.* TO 'easyhealth'@'%';
FLUSH PRIVILEGES;

-- Verify user creation
SELECT user, host FROM mysql.user WHERE user = 'easyhealth';

-- Verify database creation
SHOW DATABASES;

-- Verify table creation
USE health_db;
SHOW TABLES;

