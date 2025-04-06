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

-- 修改管理员账户插入语句
INSERT INTO users (username, password) 
SELECT 'admin', 'admin123'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'admin'
);

-- 人口统计信息表
CREATE TABLE IF NOT EXISTS demographics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    birth_date DATE,
    gender VARCHAR(10) NOT NULL,
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
    waist DECIMAL(5,2),
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
    time_of_day VARCHAR(10),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 运动记录表
CREATE TABLE IF NOT EXISTS exercise_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    exercise_type VARCHAR(50) NOT NULL,
    duration INT,
    intensity VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- MMSE認知測試記錄表
CREATE TABLE IF NOT EXISTS mmse_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_date DATE NOT NULL,
    orientation_score INT NOT NULL DEFAULT 0,
    registration INT NOT NULL DEFAULT 0,
    attention INT NOT NULL DEFAULT 0,
    recall INT NOT NULL DEFAULT 0,
    language INT NOT NULL DEFAULT 0,
    total_score INT NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id) ON DELETE CASCADE
);

-- 创建数据库用户
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