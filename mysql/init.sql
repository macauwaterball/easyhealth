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
    gender VARCHAR(10) NOT NULL,  -- 修改这里，从VARCHAR(2)改为VARCHAR(10)
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
    waist DECIMAL(5,2),  -- 添加腰围字段
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

-- 运动记录表
-- First verify if table exists
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = 'health_db' 
AND table_name = 'exercise_records';

-- Create exercise_records table if not exists
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

