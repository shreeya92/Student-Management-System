-- =========================================================
-- SEMS — Student Exam Management System
-- Complete Database Setup
-- Run this file in phpMyAdmin or MySQL CLI
-- =========================================================

CREATE DATABASE IF NOT EXISTS sems;
USE sems;

-- 1. USERS
CREATE TABLE IF NOT EXISTS users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','teacher','student') NOT NULL,
    status     ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. STUDENTS
CREATE TABLE IF NOT EXISTS students (
    student_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    roll_number    VARCHAR(50) UNIQUE,
    program        VARCHAR(100),
    semester       INT,
    admission_year YEAR,
    document_path  VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. TEACHERS
CREATE TABLE IF NOT EXISTS teachers (
    teacher_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    department  VARCHAR(100),
    designation VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 4. EXAMS
CREATE TABLE IF NOT EXISTS exams (
    exam_id   INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    semester  INT,
    year      YEAR,
    start_date DATE,
    end_date   DATE,
    status     ENUM('scheduled','completed','published') DEFAULT 'scheduled'
);

-- 5. SUBJECTS
CREATE TABLE IF NOT EXISTS subjects (
    subject_id   INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(50) UNIQUE,
    semester     INT
);

-- 6. EXAM ROUTINE
CREATE TABLE IF NOT EXISTS exam_routine (
    routine_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id    INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date  DATE,
    start_time TIME,
    end_time   TIME,
    FOREIGN KEY (exam_id)    REFERENCES exams(exam_id)    ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

-- 7. MARKS
CREATE TABLE IF NOT EXISTS marks (
    mark_id        INT AUTO_INCREMENT PRIMARY KEY,
    exam_id        INT NOT NULL,
    student_id     INT NOT NULL,
    subject_id     INT NOT NULL,
    teacher_id     INT NOT NULL,
    marks_obtained FLOAT,
    grade          VARCHAR(5),
    status         ENUM('draft','submitted') DEFAULT 'draft',
    UNIQUE KEY uq_mark (exam_id, student_id, subject_id),
    FOREIGN KEY (exam_id)    REFERENCES exams(exam_id)       ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE
);

-- 8. RESULTS
CREATE TABLE IF NOT EXISTS results (
    result_id      INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT NOT NULL,
    exam_id        INT NOT NULL,
    total_marks    FLOAT,
    gpa            FLOAT,
    result_status  ENUM('pass','fail'),
    published_date DATE,
    UNIQUE KEY uq_result (student_id, exam_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id)    REFERENCES exams(exam_id)       ON DELETE CASCADE
);

-- 9. ADMIT CARDS
CREATE TABLE IF NOT EXISTS admit_cards (
    admit_id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id         INT NOT NULL,
    exam_id            INT NOT NULL,
    issue_date         DATE,
    eligibility_status BOOLEAN DEFAULT 1,
    UNIQUE KEY uq_admit (student_id, exam_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id)    REFERENCES exams(exam_id)       ON DELETE CASCADE
);

-- 10. STUDENT REGISTRATION REQUESTS
CREATE TABLE IF NOT EXISTS student_registration_requests (
    request_id    INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL,
    document_path VARCHAR(255),
    status        ENUM('pending','approved','rejected') DEFAULT 'pending',
    submitted_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 11. PASSWORD RESETS (for forgot-password.php)
CREATE TABLE IF NOT EXISTS password_resets (
    user_id    INT          NOT NULL,
    token      VARCHAR(255) NOT NULL,
    expires_at DATETIME     NOT NULL,
    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =========================================================
-- SEED DATA — Default Admin Account
-- Email: admin@sems.com | Password: admin@1234
-- =========================================================
INSERT INTO users (full_name, email, password, role, status) VALUES
(
    'System Admin',
    'admin@sems.com',
    '$2y$12$8K1p/a0dR1lXxoXDSO.nVuYVOv0OJjxU9DGmKlEFJoFqkn1W0uZmS',
    'admin',
    'approved'
)
ON DUPLICATE KEY UPDATE user_id=user_id;

-- Note: The hash above is for 'admin@1234'
-- To regenerate: echo password_hash('admin@1234', PASSWORD_BCRYPT, ['cost'=>12]);
-- =========================================================
