<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Ensure tables exist – run schema idempotently
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

// Seed levels and questions if empty
$levelCount = $pdo->query("SELECT COUNT(*) FROM levels")->fetchColumn();
if ($levelCount == 0) {
    // We seed several languages with levels and questions
    $seedData = [
        'English' => [
            ['Basic Greetings', [
                ['word'=>'Hello','meaning'=>'A greeting','example'=>'Hello, how are you?'],
                ['word'=>'Thank you','meaning'=>'Expressing gratitude','example'=>'Thank you for the help.'],
                ['word'=>'Good morning','meaning'=>'Morning greeting','example'=>'Good morning, teacher!'],
            ], [
                ['What does "Hello" mean?', 'A greeting', 'A farewell', 'A question', 'An insult', 1],
                ['How do you express gratitude?', 'Sorry', 'Thank you', 'Please', 'Excuse me', 2],
                ['Which greeting is for morning?', 'Good night', 'Good evening', 'Good morning', 'Goodbye', 3],
                ['What is the opposite of "Hello"?', 'Goodbye', 'Hi', 'Hey', 'Greetings', 1],
                ['"Thank you" in French?', 'Merci', 'Bonjour', 'Au revoir', 'S\'il vous plaît', 1],
            ]],
            ['Common Phrases', [
                ['word'=>'Please','meaning'=>'Polite request','example'=>'Please pass the salt.'],
                ['word'=>'Excuse me','meaning'=>'Polite interruption','example'=>'Excuse me, where is the station?'],
                ['word'=>'Sorry','meaning'=>'Apology','example'=>'I am sorry for the delay.'],
            ], [
                ['What word is used for polite requests?', 'Please', 'Now', 'Quick', 'Stop', 1],
                ['"Excuse me" is used when?', 'Sleeping', 'Politely interrupting', 'Eating', 'Dancing', 2],
                ['Which word expresses an apology?', 'Hello', 'Thanks', 'Sorry', 'Yes', 3],
                ['Fill in: "___ pass the salt."', 'Please', 'Never', 'Always', 'Quick', 1],
                ['"Sorry" conveys...', 'Joy', 'Anger', 'Apology', 'Hunger', 3],
            ]],
            ['Numbers & Counting', [
                ['word'=>'One','meaning'=>'The number 1','example'=>'I have one cat.'],
                ['word'=>'Ten','meaning'=>'The number 10','example'=>'There are ten apples.'],
                ['word'=>'Hundred','meaning'=>'The number 100','example'=>'A hundred students attended.'],
            ], [
                ['What number is "One"?', '1', '2', '3', '4', 1],
                ['How many is "Ten"?', '5', '10', '15', '20', 2],
                ['"Hundred" equals?', '10', '50', '100', '1000', 3],
                ['Which is the smallest?', 'Hundred', 'Ten', 'Thousand', 'One', 4],
                ['Spell the number 10:', 'Tin', 'Ten', 'Tan', 'Ton', 2],
            ]],
        ],
        'French' => [
            ['Salutations', [
                ['word'=>'Bonjour','meaning'=>'Hello / Good day','example'=>'Bonjour, comment allez-vous?'],
                ['word'=>'Merci','meaning'=>'Thank you','example'=>'Merci beaucoup!'],
                ['word'=>'Au revoir','meaning'=>'Goodbye','example'=>'Au revoir, à demain!'],
            ], [
                ['What does "Bonjour" mean?', 'Hello', 'Goodbye', 'Sorry', 'Please', 1],
                ['"Merci" translates to?', 'Sorry', 'Thank you', 'Hello', 'Goodbye', 2],
                ['How do you say "Goodbye" in French?', 'Bonjour', 'Merci', 'Au revoir', 'S\'il vous plaît', 3],
                ['"Bonjour" is used for?', 'Night greeting', 'Morning/Day greeting', 'Farewell', 'Apology', 2],
                ['"Merci beaucoup" means?', 'Sorry a lot', 'Thank you very much', 'Hello again', 'See you', 2],
            ]],
            ['Les Nombres', [
                ['word'=>'Un','meaning'=>'One','example'=>'J\'ai un chat.'],
                ['word'=>'Deux','meaning'=>'Two','example'=>'Deux cafés, s\'il vous plaît.'],
                ['word'=>'Trois','meaning'=>'Three','example'=>'Il y a trois livres.'],
            ], [
                ['"Un" means?', 'One', 'Two', 'Three', 'Four', 1],
                ['"Deux" equals?', '1', '2', '3', '4', 2],
                ['What is "Trois"?', '1', '2', '3', '4', 3],
                ['How do you say "2" in French?', 'Un', 'Deux', 'Trois', 'Quatre', 2],
                ['"Un, deux, ___"', 'Quatre', 'Cinq', 'Trois', 'Six', 3],
            ]],
        ],
        'Japanese' => [
            ['Basic Japanese', [
                ['word'=>'こんにちは (Konnichiwa)','meaning'=>'Hello','example'=>'こんにちは、お元気ですか？'],
                ['word'=>'ありがとう (Arigatou)','meaning'=>'Thank you','example'=>'ありがとうございます。'],
                ['word'=>'さようなら (Sayounara)','meaning'=>'Goodbye','example'=>'さようなら、また明日。'],
            ], [
                ['What does "Konnichiwa" mean?', 'Hello', 'Goodbye', 'Sorry', 'Please', 1],
                ['"Arigatou" means?', 'Sorry', 'Thank you', 'Hello', 'Goodbye', 2],
                ['Japanese for "Goodbye"?', 'Konnichiwa', 'Arigatou', 'Sayounara', 'Sumimasen', 3],
                ['"Konnichiwa" is used when?', 'Night', 'Daytime greeting', 'Farewell', 'Apology', 2],
                ['"Arigatou gozaimasu" is?', 'Very sorry', 'Thank you (formal)', 'Hello sir', 'See you', 2],
            ]],
        ],
        'German' => [
            ['Grundlagen', [
                ['word'=>'Hallo','meaning'=>'Hello','example'=>'Hallo, wie geht es Ihnen?'],
                ['word'=>'Danke','meaning'=>'Thank you','example'=>'Danke schön!'],
                ['word'=>'Tschüss','meaning'=>'Goodbye (informal)','example'=>'Tschüss, bis morgen!'],
            ], [
                ['"Hallo" means?', 'Hello', 'Help', 'Sorry', 'Stop', 1],
                ['"Danke" translates to?', 'Sorry', 'Thank you', 'Hello', 'Please', 2],
                ['Informal "Goodbye" in German?', 'Hallo', 'Danke', 'Tschüss', 'Bitte', 3],
                ['"Danke schön" means?', 'Sorry much', 'Thank you very much', 'Hello again', 'Goodbye dear', 2],
                ['"Hallo" is a...?', 'Farewell', 'Question', 'Greeting', 'Command', 3],
            ]],
        ],
        'Korean' => [
            ['기초 한국어', [
                ['word'=>'안녕하세요 (Annyeonghaseyo)','meaning'=>'Hello','example'=>'안녕하세요, 잘 지내세요?'],
                ['word'=>'감사합니다 (Gamsahamnida)','meaning'=>'Thank you','example'=>'감사합니다!'],
                ['word'=>'안녕히 가세요 (Annyeonghi gaseyo)','meaning'=>'Goodbye','example'=>'안녕히 가세요!'],
            ], [
                ['"Annyeonghaseyo" means?', 'Hello', 'Goodbye', 'Sorry', 'Please', 1],
                ['"Gamsahamnida" translates to?', 'Sorry', 'Thank you', 'Hello', 'Help', 2],
                ['Korean for "Goodbye"?', 'Annyeonghaseyo', 'Gamsahamnida', 'Annyeonghi gaseyo', 'Mianhamnida', 3],
                ['Which is a greeting?', 'Gamsahamnida', 'Annyeonghaseyo', 'Mianhamnida', 'Juseyo', 2],
                ['"Gamsahamnida" expresses?', 'Anger', 'Sorrow', 'Gratitude', 'Fear', 3],
            ]],
        ],
        'Tamil' => [
            ['அடிப்படை தமிழ்', [
                ['word'=>'வணக்கம் (Vanakkam)','meaning'=>'Hello','example'=>'வணக்கம், எப்படி இருக்கிறீர்கள்?'],
                ['word'=>'நன்றி (Nandri)','meaning'=>'Thank you','example'=>'மிக்க நன்றி!'],
                ['word'=>'போய் வருகிறேன் (Poi Varugiren)','meaning'=>'Goodbye','example'=>'போய் வருகிறேன், நாளை சந்திப்போம்.'],
            ], [
                ['"Vanakkam" means?', 'Hello', 'Goodbye', 'Sorry', 'Please', 1],
                ['"Nandri" translates to?', 'Sorry', 'Thank you', 'Hello', 'Help', 2],
                ['Tamil for "Goodbye"?', 'Vanakkam', 'Nandri', 'Poi Varugiren', 'Mannikkavum', 3],
                ['Which Tamil word is a greeting?', 'Nandri', 'Vanakkam', 'Mannikkavum', 'Tharunga', 2],
                ['"Nandri" expresses?', 'Anger', 'Sorrow', 'Gratitude', 'Fear', 3],
            ]],
        ],
        'Telugu' => [
            ['ప్రాథమిక తెలుగు', [
                ['word'=>'నమస్కారం (Namaskaram)','meaning'=>'Hello','example'=>'నమస్కారం, మీరు ఎలా ఉన్నారు?'],
                ['word'=>'ధన్యవాదాలు (Dhanyavaadaalu)','meaning'=>'Thank you','example'=>'చాలా ధన్యవాదాలు!'],
            ], [
                ['"Namaskaram" means?', 'Hello', 'Goodbye', 'Sorry', 'Please', 1],
                ['"Dhanyavaadaalu" means?', 'Sorry', 'Thank you', 'Hello', 'Help', 2],
                ['Which is a Telugu greeting?', 'Dhanyavaadaalu', 'Namaskaram', 'Kshaminchhandi', 'Ivvandi', 2],
                ['"Namaskaram" is used for?', 'Farewell', 'Greeting', 'Apology', 'Request', 2],
                ['"Dhanyavaadaalu" expresses?', 'Anger', 'Gratitude', 'Sorrow', 'Fear', 2],
            ]],
        ],
        'Mandarin' => [
            ['基础中文', [
                ['word'=>'你好 (Nǐ hǎo)','meaning'=>'Hello','example'=>'你好，你好吗？'],
                ['word'=>'谢谢 (Xièxiè)','meaning'=>'Thank you','example'=>'非常谢谢！'],
                ['word'=>'再见 (Zàijiàn)','meaning'=>'Goodbye','example'=>'再见，明天见！'],
            ], [
                ['"Nǐ hǎo" means?', 'Hello', 'Goodbye', 'Sorry', 'Please', 1],
                ['"Xièxiè" translates to?', 'Sorry', 'Thank you', 'Hello', 'Help', 2],
                ['Mandarin for "Goodbye"?', 'Nǐ hǎo', 'Xièxiè', 'Zàijiàn', 'Duìbùqǐ', 3],
                ['Which is a Mandarin greeting?', 'Xièxiè', 'Nǐ hǎo', 'Duìbùqǐ', 'Qǐng', 2],
                ['"Xièxiè" expresses?', 'Anger', 'Sorrow', 'Gratitude', 'Fear', 3],
            ]],
        ],
    ];

    foreach ($seedData as $langName => $levels) {
        $langStmt = $pdo->prepare("SELECT id FROM languages WHERE name = ?");
        $langStmt->execute([$langName]);
        $langRow = $langStmt->fetch(PDO::FETCH_ASSOC);
        if (!$langRow) continue;
        $langId = $langRow['id'];

        foreach ($levels as $idx => $levelData) {
            $levelNum = $idx + 1;
            $title = $levelData[0];
            $words = $levelData[1];
            $questions = $levelData[2];
            $contentJson = json_encode($words);

            $ins = $pdo->prepare("INSERT INTO levels (language_id, level_number, title, content) VALUES (?, ?, ?, ?)");
            $ins->execute([$langId, $levelNum, $title, $contentJson]);
            $levelId = $pdo->lastInsertId();

            foreach ($questions as $q) {
                $qIns = $pdo->prepare("INSERT INTO questions (level_id, question, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $qIns->execute([$levelId, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5]]);
            }
        }
    }
}

// Fetch languages
$languages = $pdo->query("SELECT * FROM languages ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch user progress
$progressStmt = $pdo->prepare("SELECT language_id, level_completed, score FROM user_progress WHERE user_id = ?");
$progressStmt->execute([$user_id]);
$progressData = [];
while ($row = $progressStmt->fetch(PDO::FETCH_ASSOC)) {
    $progressData[$row['language_id']] = $row;
}

// Count total levels per language
$totalLevels = [];
foreach ($languages as $lang) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM levels WHERE language_id = ?");
    $stmt->execute([$lang['id']]);
    $totalLevels[$lang['id']] = $stmt->fetchColumn();
}

// User stats
$totalPoints = $pdo->prepare("SELECT COALESCE(points, 0) FROM leaderboard WHERE user_id = ?");
$totalPoints->execute([$user_id]);
$points = $totalPoints->fetchColumn() ?: 0;

$langStarted = count($progressData);

$totalCompleted = 0;
foreach ($progressData as $p) {
    $totalCompleted += $p['level_completed'];
}

// Language emojis map
$langEmojis = [
    'Tamil' => '🇮🇳', 'English' => '🇬🇧', 'Japanese' => '🇯🇵', 'Mandarin' => '🇨🇳',
    'German' => '🇩🇪', 'French' => '🇫🇷', 'Telugu' => '🇮🇳', 'Korean' => '🇰🇷'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LangLearn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay"></div>

    <div class="app-layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-logo">LangLearn 🌍</div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php" class="active"><span class="nav-icon">🏠</span> Dashboard</a></li>
                <li><a href="leaderboard.php"><span class="nav-icon">🏆</span> Leaderboard</a></li>
            </ul>
            <div class="sidebar-bottom">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="user-email"><?= $points ?> points</div>
                    </div>
                </div>
                <a href="logout.php" class="btn secondary-btn" style="width:100%;margin-top:8px;">🚪 Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Welcome back, <?= htmlspecialchars($user_name) ?>! 👋</h1>
                <p class="subtitle">Continue your language learning journey</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-value"><?= $points ?></div>
                    <div class="stat-label">Total Points</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🌐</div>
                    <div class="stat-value"><?= $langStarted ?></div>
                    <div class="stat-label">Languages Started</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?= $totalCompleted ?></div>
                    <div class="stat-label">Levels Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-value"><?= count($languages) ?></div>
                    <div class="stat-label">Languages Available</div>
                </div>
            </div>

            <!-- Languages -->
            <h2 class="section-title">📖 Choose a Language</h2>
            <div class="language-grid">
                <?php foreach ($languages as $lang): 
                    $prog = $progressData[$lang['id']] ?? null;
                    $completed = $prog ? $prog['level_completed'] : 0;
                    $total = $totalLevels[$lang['id']] ?? 1;
                    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                    $emoji = $langEmojis[$lang['name']] ?? '🌍';
                ?>
                <a href="levels.php?lang=<?= $lang['id'] ?>" class="lang-card" style="text-decoration:none;color:inherit;">
                    <span class="lang-emoji"><?= $emoji ?></span>
                    <div class="lang-name"><?= htmlspecialchars($lang['name']) ?></div>
                    <div class="lang-progress-text"><?= $completed ?>/<?= $total ?> levels · <?= $percent ?>%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
