<?php
/**
 * Skillbridg — Edit Student Profile
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$pdo = getDBConnection();
$errors = [];

// Fetch current user details
$stmt = $pdo->prepare("SELECT name, email, bio, department, semester FROM users WHERE user_id = :id LIMIT 1");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('danger', 'User profile not found.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $bio        = trim($_POST['bio'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $semester   = trim($_POST['semester'] ?? '');

    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE users 
                SET name = :name, bio = :bio, department = :department, semester = :semester
                WHERE user_id = :user_id
            ");
            $updateStmt->execute([
                'name'       => $name,
                'bio'        => $bio ?: null,
                'department' => $department ?: null,
                'semester'   => $semester ?: null,
                'user_id'    => $user_id
            ]);

            // Update session name if changed
            $_SESSION['user_name'] = $name;

            setFlashMessage('success', 'Profile updated successfully.');
            redirect('profile.php');
        } catch (PDOException $e) {
            error_log("Edit Profile Error: " . $e->getMessage());
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

$page_title = "Edit Profile";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width: 600px;">
    <h2 class="form-title">Edit Profile</h2>
    <p class="form-subtitle">Update your personal information and bio</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-left: 1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo sanitize($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="edit-profile.php" method="POST">
        <div class="form-group">
            <label for="name" class="form-label">Full Name *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo sanitize($_POST['name'] ?? $user['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address (Read-only)</label>
            <input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled style="background-color: var(--bg-main);">
        </div>

        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label for="department" class="form-label">Department</label>
                <input type="text" name="department" id="department" class="form-control" value="<?php echo sanitize($_POST['department'] ?? $user['department']); ?>" placeholder="e.g. MCA, IT, CSE">
            </div>

            <div class="form-group" style="flex: 1;">
                <label for="semester" class="form-label">Semester</label>
                <input type="text" name="semester" id="semester" class="form-control" value="<?php echo sanitize($_POST['semester'] ?? $user['semester']); ?>" placeholder="e.g. Semester 1">
            </div>
        </div>

        <div class="form-group">
            <label for="bio" class="form-label">About Me / Bio</label>
            <textarea name="bio" id="bio" class="form-control" placeholder="Write a short summary of your technical background, interest, and goals..."><?php echo sanitize($_POST['bio'] ?? $user['bio']); ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
            <a href="profile.php" class="btn btn-secondary" style="flex: 1;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
