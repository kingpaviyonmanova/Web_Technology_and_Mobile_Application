<?php
require_once 'db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        if ($name && $email && $password) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already registered!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                if ($stmt->execute([$name, $email, $hash])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // Create leaderboard entry
                    $stmt = $pdo->prepare("INSERT INTO leaderboard (user_id, points) VALUES (?, 0)");
                    $stmt->execute([$user_id]);

                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = "Registration failed!";
                }
            }
        } else {
            $error = "Please fill all fields!";
        }
    } elseif ($action === 'login') {
        if ($email && $password) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Invalid email or password!";
            }
        } else {
            $error = "Please fill all fields!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LanguageApp - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-wrapper">
    <div class="auth-container">
        <!-- Auth Header -->
        <div class="auth-header">
            <h1>LangLearn 🌍</h1>
            <p>Master languages like never before!</p>
        </div>

        <?php if($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Forms -->
        <div class="form-container">
            <!-- Login Form -->
            <form id="loginForm" method="POST" class="auth-form active">
                <input type="hidden" name="action" value="login">
                <h2>Login to Your Account</h2>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn primary-btn">Login</button>
                <p class="toggle-text">Don't have an account? <span onclick="toggleAuth('signup')">Sign up</span></p>
            </form>

            <!-- Signup Form -->
            <form id="signupForm" method="POST" class="auth-form">
                <input type="hidden" name="action" value="register">
                <h2>Create Account</h2>
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Enter your full name">
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Create a strong password">
                </div>
                <button type="submit" class="btn primary-btn">Sign Up</button>
                <p class="toggle-text">Already have an account? <span onclick="toggleAuth('login')">Login</span></p>
            </form>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
