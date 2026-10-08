<?php
/**
 * Skillbridg — Edit Listed Skill
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$user_skill_id = intval($_GET['id'] ?? 0);
$pdo = getDBConnection();
$errors = [];

// Strict ownership check
$stmt = $pdo->prepare("
    SELECT us.user_skill_id, us.skill_type, us.proficiency_level, s.skill_name
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_skill_id = :id AND us.user_id = :user_id
    LIMIT 1
");
$stmt->execute([
    'id'      => $user_skill_id,
    'user_id' => $user_id
]);
$skill = $stmt->fetch();

if (!$skill) {
    setFlashMessage('danger', 'Skill record not found or access denied.');
    redirect('skills.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill_type        = trim($_POST['skill_type'] ?? 'teach');
    $proficiency_level = trim($_POST['proficiency_level'] ?? 'Intermediate');

    if (!in_array($skill_type, ['teach', 'learn'])) {
        $errors[] = "Invalid skill type selected.";
    }

    if (!in_array($proficiency_level, ['Beginner', 'Intermediate', 'Advanced'])) {
        $proficiency_level = 'Intermediate';
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE user_skills
                SET skill_type = :skill_type, proficiency_level = :proficiency
                WHERE user_skill_id = :id AND user_id = :user_id
            ");
            $updateStmt->execute([
                'skill_type'  => $skill_type,
                'proficiency' => $proficiency_level,
                'id'          => $user_skill_id,
                'user_id'     => $user_id
            ]);

            setFlashMessage('success', 'Skill details updated successfully.');
            redirect('skills.php');
        } catch (PDOException $e) {
            error_log("Edit Skill Error: " . $e->getMessage());
            $errors[] = "Failed to update skill.";
        }
    }
}

$page_title = "Edit Skill — " . sanitize($skill['skill_name']);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width: 500px;">
    <h2 class="form-title">Edit Skill</h2>
    <p class="form-subtitle">Update details for <strong><?php echo sanitize($skill['skill_name']); ?></strong></p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-left: 1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo sanitize($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="edit-skill.php?id=<?php echo $user_skill_id; ?>" method="POST">
        <div class="form-group">
            <label for="skill_type" class="form-label">Skill Type</label>
            <select name="skill_type" id="skill_type" class="form-control" required>
                <option value="teach" <?php echo $skill['skill_type'] === 'teach' ? 'selected' : ''; ?>>🎓 Teach</option>
                <option value="learn" <?php echo $skill['skill_type'] === 'learn' ? 'selected' : ''; ?>>📖 Learn</option>
            </select>
        </div>

        <div class="form-group">
            <label for="proficiency_level" class="form-label">Proficiency Level</label>
            <select name="proficiency_level" id="proficiency_level" class="form-control">
                <option value="Beginner" <?php echo $skill['proficiency_level'] === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                <option value="Intermediate" <?php echo $skill['proficiency_level'] === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                <option value="Advanced" <?php echo $skill['proficiency_level'] === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
            </select>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
            <a href="skills.php" class="btn btn-secondary" style="flex: 1;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
