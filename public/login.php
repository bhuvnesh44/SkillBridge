<?php
/**
 * Skillbridg — Student Login
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireGuest();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT user_id, name, email, password FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                loginUser($user);
                setFlashMessage('success', 'Welcome back, ' . sanitize($user['name']) . '!');
                redirect('dashboard.php');
            } else {
                // Generic error for security
                $error = "Invalid email address or password.";
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = "An unexpected server error occurred. Please try again.";
        }
    }
}

$page_title = "Student Login";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h2 class="form-title">Student Login</h2>
    <p class="form-subtitle">Enter your credentials to access Skillbridg</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span><?php echo sanitize($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" id="loginForm">
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo sanitize($email); ?>" required placeholder="student@example.com">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
            Log In
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
        Don't have an account? <a href="register.php" style="font-weight: 600;">Register Here</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
