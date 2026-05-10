<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// User points
$ptStmt = $pdo->prepare("SELECT COALESCE(points, 0) FROM leaderboard WHERE user_id = ?");
$ptStmt->execute([$user_id]);
$points = $ptStmt->fetchColumn() ?: 0;

// Fetch leaderboard - top 50
$lbStmt = $pdo->query("
    SELECT u.id, u.name, COALESCE(l.points, 0) as points 
    FROM users u 
    LEFT JOIN leaderboard l ON u.id = l.user_id 
    ORDER BY COALESCE(l.points, 0) DESC 
    LIMIT 50
");
$leaderboard = $lbStmt->fetchAll(PDO::FETCH_ASSOC);

// Find current user rank
$rankStmt = $pdo->prepare("
    SELECT COUNT(*) + 1 as user_rank 
    FROM leaderboard 
    WHERE points > (SELECT COALESCE(points, 0) FROM leaderboard WHERE user_id = ?)
");
$rankStmt->execute([$user_id]);
$userRank = $rankStmt->fetchColumn() ?: '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - LangLearn</title>
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
                <li><a href="leaderboard.php" class="active"><span class="nav-icon">🏆</span> Leaderboard</a></li>
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
            <div class="page-header">
                <h1>🏆 Leaderboard</h1>
                <p class="subtitle">Your rank: #<?= $userRank ?> · <?= $points ?> points</p>
            </div>

            <!-- User rank card -->
            <div class="stats-grid" style="margin-bottom: 32px;">
                <div class="stat-card">
                    <div class="stat-icon">🏅</div>
                    <div class="stat-value">#<?= $userRank ?></div>
                    <div class="stat-label">Your Rank</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-value"><?= $points ?></div>
                    <div class="stat-label">Your Points</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?= count($leaderboard) ?></div>
                    <div class="stat-label">Total Players</div>
                </div>
            </div>

            <div class="leaderboard-container">
                <?php if (empty($leaderboard)): ?>
                    <div class="question-card" style="text-align: center; padding: 48px;">
                        <div style="font-size: 3rem; margin-bottom: 16px;">🏆</div>
                        <h2>No Rankings Yet</h2>
                        <p class="subtitle" style="margin-top: 8px;">Complete quizzes to appear on the leaderboard!</p>
                    </div>
                <?php else: ?>
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Rank</th>
                                <th>Player</th>
                                <th style="text-align: right;">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $idx => $player):
                                $rank = $idx + 1;
                                $isCurrentUser = ($player['id'] == $user_id);
                                $initial = strtoupper(substr($player['name'], 0, 1));

                                if ($rank === 1) $rankClass = 'rank-1';
                                elseif ($rank === 2) $rankClass = 'rank-2';
                                elseif ($rank === 3) $rankClass = 'rank-3';
                                else $rankClass = 'rank-default';
                            ?>
                            <tr class="<?= $isCurrentUser ? 'current-user' : '' ?>">
                                <td>
                                    <div class="rank-badge <?= $rankClass ?>">
                                        <?php if ($rank <= 3): ?>
                                            <?= ['🥇','🥈','🥉'][$rank - 1] ?>
                                        <?php else: ?>
                                            <?= $rank ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="lb-user">
                                        <div class="lb-avatar"><?= $initial ?></div>
                                        <span class="lb-name">
                                            <?= htmlspecialchars($player['name']) ?>
                                            <?php if ($isCurrentUser): ?>
                                                <span style="color: var(--accent-secondary); font-size: 0.8rem;"> (You)</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <span class="lb-points"><?= number_format($player['points']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
