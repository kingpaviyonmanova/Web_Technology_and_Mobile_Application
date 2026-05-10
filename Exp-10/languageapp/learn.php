<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$lang_id = intval($_GET['lang'] ?? 0);
$level_id = intval($_GET['level'] ?? 0);

if ($lang_id <= 0 || $level_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Get language
$langStmt = $pdo->prepare("SELECT * FROM languages WHERE id = ?");
$langStmt->execute([$lang_id]);
$language = $langStmt->fetch(PDO::FETCH_ASSOC);
if (!$language) { header('Location: dashboard.php'); exit; }

// Get level
$levelStmt = $pdo->prepare("SELECT * FROM levels WHERE id = ? AND language_id = ?");
$levelStmt->execute([$level_id, $lang_id]);
$level = $levelStmt->fetch(PDO::FETCH_ASSOC);
if (!$level) { header('Location: levels.php?lang=' . $lang_id); exit; }

// Check if user can access this level
$progStmt = $pdo->prepare("SELECT level_completed FROM user_progress WHERE user_id = ? AND language_id = ?");
$progStmt->execute([$user_id, $lang_id]);
$progRow = $progStmt->fetch(PDO::FETCH_ASSOC);
$levelsCompleted = $progRow ? $progRow['level_completed'] : 0;

if ($level['level_number'] > ($levelsCompleted + 1)) {
    header('Location: levels.php?lang=' . $lang_id);
    exit;
}

// Parse lesson content
$words = json_decode($level['content'], true);
if (!$words) $words = [];

// Check if there are questions for this level
$qCountStmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE level_id = ?");
$qCountStmt->execute([$level_id]);
$hasQuiz = $qCountStmt->fetchColumn() > 0;

// User points
$ptStmt = $pdo->prepare("SELECT COALESCE(points, 0) FROM leaderboard WHERE user_id = ?");
$ptStmt->execute([$user_id]);
$points = $ptStmt->fetchColumn() ?: 0;

$langEmojis = [
    'Tamil' => '🇮🇳', 'English' => '🇬🇧', 'Japanese' => '🇯🇵', 'Mandarin' => '🇨🇳',
    'German' => '🇩🇪', 'French' => '🇫🇷', 'Telugu' => '🇮🇳', 'Korean' => '🇰🇷'
];
$emoji = $langEmojis[$language['name']] ?? '🌍';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($level['title']) ?> - LangLearn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-animation">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay"></div>

    <div class="app-layout">
        <nav class="sidebar">
            <div class="sidebar-logo">LangLearn 🌍</div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
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

        <main class="main-content">
            <a href="levels.php?lang=<?= $lang_id ?>" class="back-btn">← Back to Levels</a>

            <div class="page-header">
                <h1><?= $emoji ?> <?= htmlspecialchars($level['title']) ?></h1>
                <p class="subtitle"><?= htmlspecialchars($language['name']) ?> · Level <?= $level['level_number'] ?></p>
            </div>

            <div class="lesson-container">
                <h2 class="section-title">📝 Vocabulary</h2>

                <?php if (empty($words)): ?>
                    <div class="question-card" style="text-align:center; padding: 40px;">
                        <p>No lesson content available for this level yet.</p>
                    </div>
                <?php else: ?>
                    <div class="word-cards">
                        <?php foreach ($words as $i => $word): ?>
                        <div class="word-card" style="animation-delay: <?= $i * 0.1 ?>s;">
                            <div class="word-title"><?= htmlspecialchars($word['word'] ?? '') ?></div>
                            <div class="word-meaning">📖 <?= htmlspecialchars($word['meaning'] ?? '') ?></div>
                            <?php if (!empty($word['example'])): ?>
                                <div class="word-example">"<?= htmlspecialchars($word['example']) ?>"</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Action buttons -->
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px;">
                    <?php if ($hasQuiz): ?>
                        <a href="quiz.php?lang=<?= $lang_id ?>&level=<?= $level_id ?>" class="btn primary-btn" style="width: auto; flex: 1; min-width: 200px;">
                            🧠 Take the Quiz
                        </a>
                    <?php endif; ?>
                    <a href="levels.php?lang=<?= $lang_id ?>" class="btn secondary-btn" style="flex: 1; min-width: 200px;">
                        📚 Back to Levels
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
