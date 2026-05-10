<?php
session_start();

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'language_app';

try {
    $pdo = new PDO("mysql:host=$host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Create DB if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");

    // Auto-create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        total_points INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS languages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )");

    $pdo->exec("INSERT IGNORE INTO languages (name) VALUES 
    ('Tamil'), ('English'), ('Japanese'), ('Mandarin'), 
    ('German'), ('French'), ('Telugu'), ('Korean')");

    $pdo->exec("CREATE TABLE IF NOT EXISTS levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        language_id INT NOT NULL,
        level_number INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        content TEXT,
        FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level_id INT NOT NULL,
        question TEXT NOT NULL,
        option1 VARCHAR(255) NOT NULL,
        option2 VARCHAR(255) NOT NULL,
        option3 VARCHAR(255) NOT NULL,
        option4 VARCHAR(255) NOT NULL,
        correct_option INT NOT NULL,
        FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        language_id INT NOT NULL,
        level_completed INT DEFAULT 0,
        score INT DEFAULT 0,
        last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
        UNIQUE KEY (user_id, language_id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS leaderboard (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        badge_name VARCHAR(50) NOT NULL,
        criteria VARCHAR(255) NOT NULL
    )");

    $pdo->exec("INSERT IGNORE INTO badges (badge_name, criteria) VALUES
    ('Beginner', 'Completed 1 level'),
    ('Intermediate', 'Completed 5 levels'),
    ('Expert', 'Completed 15 levels')");

} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
