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

// Get questions
$qStmt = $pdo->prepare("SELECT * FROM questions WHERE level_id = ? ORDER BY id ASC");
$qStmt->execute([$level_id]);
$questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($questions)) {
    header('Location: learn.php?lang=' . $lang_id . '&level=' . $level_id);
    exit;
}

// Handle quiz submission
$showResults = false;
$score = 0;
$total = count($questions);
$answers = [];
$pointsEarned = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    foreach ($questions as $q) {
        $userAnswer = intval($_POST['q_' . $q['id']] ?? 0);
        $isCorrect = ($userAnswer === $q['correct_option']);
        if ($isCorrect) $score++;
        $answers[$q['id']] = ['selected' => $userAnswer, 'correct' => $isCorrect];
    }

    $pointsEarned = $score * 10;
    $showResults = true;

    // Update progress if passed (>=60%)
    $passed = ($score / $total) >= 0.6;
    if ($passed) {
        // Check current progress
        $progStmt = $pdo->prepare("SELECT level_completed FROM user_progress WHERE user_id = ? AND language_id = ?");
        $progStmt->execute([$user_id, $lang_id]);
        $progRow = $progStmt->fetch(PDO::FETCH_ASSOC);
        $currentCompleted = $progRow ? $progRow['level_completed'] : 0;

        // Only update if this level is the next one to complete
        if ($level['level_number'] > $currentCompleted) {
            if ($progRow) {
                $upd = $pdo->prepare("UPDATE user_progress SET level_completed = ?, score = score + ? WHERE user_id = ? AND language_id = ?");
                $upd->execute([$level['level_number'], $pointsEarned, $user_id, $lang_id]);
            } else {
                $ins = $pdo->prepare("INSERT INTO user_progress (user_id, language_id, level_completed, score) VALUES (?, ?, ?, ?)");
                $ins->execute([$user_id, $lang_id, $level['level_number'], $pointsEarned]);
            }

            // Update leaderboard
            $lbCheck = $pdo->prepare("SELECT id FROM leaderboard WHERE user_id = ?");
            $lbCheck->execute([$user_id]);
            if ($lbCheck->fetch()) {
                $pdo->prepare("UPDATE leaderboard SET points = points + ? WHERE user_id = ?")->execute([$pointsEarned, $user_id]);
            } else {
                $pdo->prepare("INSERT INTO leaderboard (user_id, points) VALUES (?, ?)")->execute([$user_id, $pointsEarned]);
            }

            // Update user total_points
            $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?")->execute([$pointsEarned, $user_id]);
        }
    }
}

// User points (refresh)
$ptStmt = $pdo->prepare("SELECT COALESCE(points, 0) FROM leaderboard WHERE user_id = ?");
$ptStmt->execute([$user_id]);
$points = $ptStmt->fetchColumn() ?: 0;

$langEmojis = [
    'Tamil' => '🇮🇳', 'English' => '🇬🇧', 'Japanese' => '🇯🇵', 'Mandarin' => '🇨🇳',
    'German' => '🇩🇪', 'French' => '🇫🇷', 'Telugu' => '🇮🇳', 'Korean' => '🇰🇷'
];
$emoji = $langEmojis[$language['name']] ?? '🌍';
$timerSeconds = $total * 30; // 30 seconds per question
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz: <?= htmlspecialchars($level['title']) ?> - LangLearn</title>
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
            <a href="learn.php?lang=<?= $lang_id ?>&level=<?= $level_id ?>" class="back-btn">← Back to Lesson</a>

            <?php if ($showResults): ?>
                <!-- RESULTS -->
                <div class="quiz-container">
                    <div class="results-card">
                        <?php
                        $pct = round(($score / $total) * 100);
                        if ($pct >= 80) { $resultEmoji = '🎉'; $resultTitle = 'Excellent!'; $resultSub = 'You nailed it!'; }
                        elseif ($pct >= 60) { $resultEmoji = '👏'; $resultTitle = 'Well Done!'; $resultSub = 'You passed the quiz!'; }
                        else { $resultEmoji = '💪'; $resultTitle = 'Keep Practicing!'; $resultSub = 'You need 60% to pass. Try again!'; }
                        ?>
                        <div class="result-emoji"><?= $resultEmoji ?></div>
                        <h2><?= $resultTitle ?></h2>
                        <p class="result-subtitle"><?= $resultSub ?></p>
                        <div class="score-display">
                            <span class="score-num"><?= $score ?></span>
                            <span class="score-total">/ <?= $total ?></span>
                        </div>
                        <?php if ($pct >= 60): ?>
                            <p style="color: var(--success); margin-bottom: 20px; font-weight: 600;">+<?= $pointsEarned ?> points earned! 🏆</p>
                        <?php endif; ?>
                        <div class="result-actions">
                            <a href="quiz.php?lang=<?= $lang_id ?>&level=<?= $level_id ?>" class="btn primary-btn" style="width:auto;">🔄 Retry Quiz</a>
                            <a href="levels.php?lang=<?= $lang_id ?>" class="btn secondary-btn">📚 All Levels</a>
                            <a href="dashboard.php" class="btn secondary-btn">🏠 Dashboard</a>
                        </div>
                    </div>

                    <!-- Show answer review -->
                    <h2 class="section-title" style="margin-top: 36px;">📋 Answer Review</h2>
                    <?php foreach ($questions as $qi => $q):
                        $ans = $answers[$q['id']] ?? ['selected' => 0, 'correct' => false];
                        $options = [$q['option1'], $q['option2'], $q['option3'], $q['option4']];
                        $letters = ['A', 'B', 'C', 'D'];
                    ?>
                    <div class="question-card" style="margin-bottom: 16px;">
                        <div class="question-text"><?= ($qi + 1) ?>. <?= htmlspecialchars($q['question']) ?></div>
                        <div class="options-list">
                            <?php foreach ($options as $oi => $opt):
                                $optNum = $oi + 1;
                                $cls = 'disabled';
                                if ($optNum === $q['correct_option']) $cls .= ' correct';
                                if ($optNum === $ans['selected'] && !$ans['correct']) $cls .= ' wrong';
                            ?>
                            <div class="option-btn <?= $cls ?>">
                                <span class="option-letter"><?= $letters[$oi] ?></span>
                                <span><?= htmlspecialchars($opt) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- QUIZ FORM -->
                <div class="page-header">
                    <h1>🧠 Quiz: <?= htmlspecialchars($level['title']) ?></h1>
                    <p class="subtitle"><?= htmlspecialchars($language['name']) ?> · Level <?= $level['level_number'] ?> · <?= $total ?> questions</p>
                </div>

                <div class="quiz-container">
                    <form method="POST" id="quizForm">
                        <div class="quiz-header">
                            <div class="quiz-progress">📝 <?= $total ?> Questions</div>
                            <div class="quiz-timer">
                                ⏱️ <span id="timerDisplay">--:--</span>
                            </div>
                        </div>

                        <?php foreach ($questions as $qi => $q):
                            $options = [$q['option1'], $q['option2'], $q['option3'], $q['option4']];
                            $letters = ['A', 'B', 'C', 'D'];
                        ?>
                        <div class="question-card">
                            <div class="question-text"><?= ($qi + 1) ?>. <?= htmlspecialchars($q['question']) ?></div>
                            <div class="options-list">
                                <?php foreach ($options as $oi => $opt): ?>
                                <label class="option-btn" id="opt_<?= $q['id'] ?>_<?= $oi + 1 ?>" onclick="selectOption(this, '<?= $q['id'] ?>', <?= $oi + 1 ?>)">
                                    <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $oi + 1 ?>" required style="display:none;">
                                    <span class="option-letter"><?= $letters[$oi] ?></span>
                                    <span><?= htmlspecialchars($opt) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <input type="hidden" name="submit_quiz" value="1">
                        <button type="submit" class="btn primary-btn" style="margin-top: 12px;">
                            ✅ Submit Quiz
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
    <?php if (!$showResults): ?>
    <script>
        // Highlight selected option
        function selectOption(el, qid, optNum) {
            // Remove selected state from siblings
            const parent = el.closest('.options-list');
            parent.querySelectorAll('.option-btn').forEach(btn => {
                btn.style.background = '';
                btn.style.borderColor = '';
            });
            // Highlight chosen
            el.style.background = 'rgba(124, 58, 237, 0.15)';
            el.style.borderColor = '#7c3aed';
        }

        // Start timer
        startQuizTimer(<?= $timerSeconds ?>, function() {
            document.getElementById('quizForm').submit();
        });
    </script>
    <?php endif; ?>
</body>
</html>
