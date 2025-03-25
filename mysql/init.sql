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

-- 插入默认管理员账户 (密码: admin123)
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 社区用户基本信息表
CREATE TABLE IF NOT EXISTS demographics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    birth_date DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name_phone (name, phone)
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

-- 在现有表之后添加体格指标表
CREATE TABLE IF NOT EXISTS physical_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    bmi DECIMAL(4,2),
    waist DECIMAL(5,2),
    hip DECIMAL(5,2),
    blood_sugar DECIMAL(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 其他表保持不变，但需要修改 patient_id 为 user_id
ALTER TABLE physical_metrics RENAME COLUMN patient_id TO user_id;
ALTER TABLE lifestyle_habits RENAME COLUMN patient_id TO user_id;
ALTER TABLE diet_records RENAME COLUMN patient_id TO user_id;
ALTER TABLE bad_habits RENAME COLUMN patient_id TO user_id;
ALTER TABLE cognitive_assessment RENAME COLUMN patient_id TO user_id;

-- 创建索引
CREATE INDEX idx_user_date ON physiological_params(user_id, date);
CREATE INDEX idx_user_date_metrics ON physical_metrics(user_id, record_date);
CREATE INDEX idx_user_date_habits ON lifestyle_habits(user_id, record_date);
CREATE INDEX idx_user_date_diet ON diet_records(user_id, record_date);
CREATE INDEX idx_user_date_bad ON bad_habits(user_id, record_date);
CREATE INDEX idx_user_date_cog ON cognitive_assessment(user_id, assessment_date);

-- 生活习惯表
CREATE TABLE IF NOT EXISTS lifestyle_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    sleep_hours DECIMAL(3,1),
    exercise_minutes INT,
    exercise_type VARCHAR(100),
    stress_level INT,
    mood VARCHAR(50),
    smoking_count INT,
    drinking_amount VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 饮食记录表
CREATE TABLE IF NOT EXISTS diet_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    meal_time VARCHAR(20) NOT NULL,
    food_items TEXT NOT NULL,
    calories INT,
    protein DECIMAL(5,2),
    carbs DECIMAL(5,2),
    fat DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 用药记录表
CREATE TABLE IF NOT EXISTS medication_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    time_of_day VARCHAR(20) NOT NULL,
    medication_name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    purpose VARCHAR(200),
    side_effects TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- 运动习惯记录表
CREATE TABLE IF NOT EXISTS exercise_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    exercise_type VARCHAR(50) NOT NULL,
    duration_minutes INT,
    steps_count INT,
    distance_km DECIMAL(5,2),
    calories_burned INT,
    heart_rate_avg INT,
    intensity_level VARCHAR(20),
    location VARCHAR(100),
    weather VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

-- MMSE测试记录表
CREATE TABLE IF NOT EXISTS mmse_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_date DATE NOT NULL,
    orientation_time INT NOT NULL, -- 时间定向（5分）
    orientation_place INT NOT NULL, -- 地点定向（5分）
    registration INT NOT NULL, -- 记忆登记（3分）
    attention_calculation INT NOT NULL, -- 注意力和计算（5分）
    recall INT NOT NULL, -- 回忆（3分）
    naming INT NOT NULL, -- 命名（2分）
    repetition INT NOT NULL, -- 复述（1分）
    comprehension INT NOT NULL, -- 理解（3分）
    reading INT NOT NULL, -- 阅读（1分）
    writing INT NOT NULL, -- 书写（1分）
    drawing INT NOT NULL, -- 绘图（1分）
    total_score INT NOT NULL, -- 总分（30分）
    cognitive_status VARCHAR(50) NOT NULL, -- 认知状态评估
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES demographics(id)
);

