CREATE DATABASE IF NOT EXISTS language_app;
USE language_app;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    total_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Languages Table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert starting languages
INSERT IGNORE INTO languages (name) VALUES 
('Tamil'), ('English'), ('Japanese'), ('Mandarin'), 
('German'), ('French'), ('Telugu'), ('Korean');

-- 3. Levels Table
CREATE TABLE IF NOT EXISTS levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language_id INT NOT NULL,
    level_number INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
);

-- 4. Questions Table
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level_id INT NOT NULL,
    question TEXT NOT NULL,
    option1 VARCHAR(255) NOT NULL,
    option2 VARCHAR(255) NOT NULL,
    option3 VARCHAR(255) NOT NULL,
    option4 VARCHAR(255) NOT NULL,
    correct_option INT NOT NULL CHECK (correct_option BETWEEN 1 AND 4),
    FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
);

-- 5. User Progress Table
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    level_completed INT DEFAULT 0,
    score INT DEFAULT 0,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, language_id)
);

-- 6. Leaderboard Table
-- We could compute this view, but keeping the table per user prompt
CREATE TABLE IF NOT EXISTS leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. Badges Table
CREATE TABLE IF NOT EXISTS badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    badge_name VARCHAR(50) NOT NULL,
    criteria VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO badges (badge_name, criteria) VALUES
('Beginner', 'Completed 1 level'),
('Intermediate', 'Completed 5 levels'),
('Expert', 'Completed 15 levels');

-- Insert Dummy Data for English Level 1
INSERT INTO levels (language_id, level_number, title, content) VALUES
(2, 1, 'Basic Greetings', '[{"word": "Hello", "meaning": "A greeting", "example": "Hello, how are you?", "audio": ""}, {"word": "Thank you", "meaning": "Expressing gratitude", "example": "Thank you for the help.", "audio": ""}]');

-- Insert Dummy Questions for Level 1
INSERT INTO questions (level_id, question, option1, option2, option3, option4, correct_option) VALUES
(1, 'What does "Hello" mean?', 'A greeting', 'A farewell', 'A question', 'An insult', 1),
(1, 'How do you express gratitude?', 'Sorry', 'Thank you', 'Please', 'Excuse me', 2),
(1, 'Which is a polite way to greet someone in the morning?', 'Good night', 'Good evening', 'Good morning', 'Goodbye', 3),
(1, 'What is the opposite of "Hello"?', 'Goodbye', 'Hi', 'Hey', 'Greetings', 1),
(1, 'Translate "Thank you" to French?', 'Merci', 'Bonjour', 'Au revoir', 'S''il vous plaît', 1);

