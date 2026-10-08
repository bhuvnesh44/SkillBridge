<?php
/**
 * Skillbridg — Student Registration
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireGuest();

$errors = [];
$name = '';
$email = '';
$department = '';
$semester = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $department = trim($_POST['department'] ?? '');
    $semester   = trim($_POST['semester'] ?? '');

    // Server-side validation
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_pw) {
        $errors[] = "Passwords do not match.";
    }

    // Check for duplicate account email
    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with this email address already exists.";
        }
    }

    // Process registration if validation passes
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, department, semester)
                VALUES (:name, :email, :password, :department, :semester)
            ");
            $stmt->execute([
                'name'       => $name,
                'email'      => $email,
                'password'   => $hashed_password,
                'department' => $department ?: null,
                'semester'   => $semester ?: null
            ]);

            setFlashMessage('success', 'Registration successful! You can now log in.');
            redirect('login.php');
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $errors[] = "An error occurred during registration. Please try again.";
        }
    }
}

$page_title = "Register Student Account";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle">Join Skillbridg to share and learn peer skills</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-left: 1.25rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" id="registerForm">
        <div class="form-group">
            <label for="name" class="form-label">Full Name *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo sanitize($name); ?>" required placeholder="e.g. Rahul Sharma">
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo sanitize($email); ?>" required placeholder="student@example.com">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password * (Min 6 chars)</label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="••••••••">
        </div>

        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label for="department" class="form-label">Department</label>
                <input type="text" name="department" id="department" class="form-control" value="<?php echo sanitize($department); ?>" placeholder="e.g. MCA, IT">
            </div>

            <div class="form-group" style="flex: 1;">
                <label for="semester" class="form-label">Semester</label>
                <input type="text" name="semester" id="semester" class="form-control" value="<?php echo sanitize($semester); ?>" placeholder="e.g. Semester 1">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
            Register Account
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
        Already have an account? <a href="login.php" style="font-weight: 600;">Log In</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
