<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$lang_id = intval($_GET['lang'] ?? 0);
if ($lang_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Get language info
$langStmt = $pdo->prepare("SELECT * FROM languages WHERE id = ?");
$langStmt->execute([$lang_id]);
$language = $langStmt->fetch(PDO::FETCH_ASSOC);
if (!$language) {
    header('Location: dashboard.php');
    exit;
}

// Get all levels for this language
$levelsStmt = $pdo->prepare("SELECT * FROM levels WHERE language_id = ? ORDER BY level_number ASC");
$levelsStmt->execute([$lang_id]);
$levels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user progress for this language
$progStmt = $pdo->prepare("SELECT level_completed FROM user_progress WHERE user_id = ? AND language_id = ?");
$progStmt->execute([$user_id, $lang_id]);
$progRow = $progStmt->fetch(PDO::FETCH_ASSOC);
$levelsCompleted = $progRow ? $progRow['level_completed'] : 0;

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
    <title><?= htmlspecialchars($language['name']) ?> Levels - LangLearn</title>
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
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

            <div class="page-header">
                <h1><?= $emoji ?> <?= htmlspecialchars($language['name']) ?></h1>
                <p class="subtitle"><?= count($levels) ?> levels available · <?= $levelsCompleted ?> completed</p>
            </div>

            <!-- Progress bar -->
            <div style="margin-bottom: 32px;">
                <div class="progress-bar" style="height: 10px;">
                    <div class="progress-fill" style="width: <?= count($levels) > 0 ? round(($levelsCompleted / count($levels)) * 100) : 0 ?>%"></div>
                </div>
            </div>

            <div class="levels-container">
                <div class="level-list">
                    <?php if (empty($levels)): ?>
                        <div class="question-card" style="text-align:center; padding: 48px;">
                            <div style="font-size: 3rem; margin-bottom: 16px;">📭</div>
                            <h2>No Levels Yet</h2>
                            <p class="subtitle" style="margin-top: 8px;">Content for this language is coming soon!</p>
                            <a href="dashboard.php" class="btn primary-btn" style="margin-top: 20px; width: auto; display: inline-flex;">← Choose Another Language</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($levels as $level):
                            $levelNum = $level['level_number'];
                            $isCompleted = $levelNum <= $levelsCompleted;
                            $isUnlocked = $levelNum <= ($levelsCompleted + 1);
                            $isLocked = !$isUnlocked;

                            if ($isCompleted) {
                                $numClass = 'completed';
                                $statusClass = 'completed-badge';
                                $statusText = '✅ Completed';
                            } elseif ($isUnlocked) {
                                $numClass = 'unlocked';
                                $statusClass = 'start-badge';
                                $statusText = '▶ Start';
                            } else {
                                $numClass = 'locked-num';
                                $statusClass = 'locked-badge';
                                $statusText = '🔒 Locked';
                            }
                        ?>
                        <?php if ($isLocked): ?>
                            <div class="level-item locked">
                        <?php else: ?>
                            <a href="learn.php?lang=<?= $lang_id ?>&level=<?= $level['id'] ?>" class="level-item">
                        <?php endif; ?>
                                <div class="level-number <?= $numClass ?>"><?= $levelNum ?></div>
                                <div class="level-info">
                                    <h3><?= htmlspecialchars($level['title']) ?></h3>
                                    <p>Level <?= $levelNum ?></p>
                                </div>
                                <span class="level-status <?= $statusClass ?>"><?= $statusText ?></span>
                        <?php if ($isLocked): ?>
                            </div>
                        <?php else: ?>
                            </a>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
