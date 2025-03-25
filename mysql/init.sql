-- 创建数据库和用户
CREATE DATABASE IF NOT EXISTS health_db;
CREATE USER IF NOT EXISTS 'healthuser'@'%' IDENTIFIED BY 'aa123456';
GRANT ALL PRIVILEGES ON health_db.* TO 'healthuser'@'%';
FLUSH PRIVILEGES;

USE health_db;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 人口统计表
CREATE TABLE IF NOT EXISTS demographics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    birth_date DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    location VARCHAR(255),
    emergency_contact VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 身体指标表
CREATE TABLE IF NOT EXISTS physical_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    measure_date DATE NOT NULL,
    height FLOAT,
    weight FLOAT,
    bmi FLOAT,
    waist FLOAT,
    bone_density FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 生理参数表
CREATE TABLE IF NOT EXISTS physiological_params (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    measure_date DATE NOT NULL,
    measure_time TIME NOT NULL,
    blood_pressure_sys INT,
    blood_pressure_dia INT,
    blood_sugar FLOAT,
    hba1c FLOAT,
    total_cholesterol FLOAT,
    ldl FLOAT,
    hdl FLOAT,
    triglycerides FLOAT,
    heart_rate INT,
    heart_rhythm_normal BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 生活习惯表
CREATE TABLE IF NOT EXISTS lifestyle_habits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    record_date DATE NOT NULL,
    steps_count INT,
    exercise_type VARCHAR(100),
    exercise_duration INT,
    sitting_duration INT,
    sleep_duration FLOAT,
    deep_sleep_duration FLOAT,
    wake_count INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 饮食记录表
CREATE TABLE IF NOT EXISTS diet_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    record_date DATE NOT NULL,
    calories FLOAT,
    salt_intake FLOAT,
    sugar_intake FLOAT,
    water_intake FLOAT,
    special_diet TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 不良习惯表
CREATE TABLE IF NOT EXISTS bad_habits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    record_date DATE NOT NULL,
    smoking_frequency INT,
    alcohol_frequency INT,
    quit_support_needed BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 认知情绪评估表
CREATE TABLE IF NOT EXISTS cognitive_assessment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    assessment_date DATE NOT NULL,
    mmse_score INT,
    gds_score INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);