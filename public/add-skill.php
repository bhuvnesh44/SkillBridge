<?php
/**
 * Skillbridg — Add Skill to Student Profile
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$pdo = getDBConnection();
$errors = [];

$default_type = $_GET['type'] ?? 'teach';
if (!in_array($default_type, ['teach', 'learn'])) {
    $default_type = 'teach';
}

// Fetch existing master skills for dropdown autocomplete
$stmtSkills = $pdo->query("SELECT skill_id, skill_name, category FROM skills ORDER BY skill_name ASC");
$master_skills = $stmtSkills->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $existing_skill_id = intval($_POST['existing_skill_id'] ?? 0);
    $custom_skill_name = trim($_POST['custom_skill_name'] ?? '');
    $skill_type        = trim($_POST['skill_type'] ?? 'teach');
    $proficiency_level = trim($_POST['proficiency_level'] ?? 'Intermediate');
    $category          = trim($_POST['category'] ?? 'General');

    // Validation
    if (!in_array($skill_type, ['teach', 'learn'])) {
        $errors[] = "Invalid skill type selected.";
    }

    if (!in_array($proficiency_level, ['Beginner', 'Intermediate', 'Advanced'])) {
        $proficiency_level = 'Intermediate';
    }

    if ($existing_skill_id <= 0 && empty($custom_skill_name)) {
        $errors[] = "Please select an existing skill or enter a new skill name.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $skill_id = 0;

            if (!empty($custom_skill_name)) {
                // Check if custom skill name already exists in master table
                $stmtCheck = $pdo->prepare("SELECT skill_id FROM skills WHERE LOWER(skill_name) = LOWER(:name) LIMIT 1");
                $stmtCheck->execute(['name' => $custom_skill_name]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    $skill_id = $existing['skill_id'];
                } else {
                    // Create new master skill
                    $stmtInsertSkill = $pdo->prepare("INSERT INTO skills (skill_name, category) VALUES (:name, :category)");
                    $stmtInsertSkill->execute([
                        'name'     => $custom_skill_name,
                        'category' => $category ?: 'General'
                    ]);
                    $skill_id = $pdo->lastInsertId();
                }
            } else {
                $skill_id = $existing_skill_id;
            }

            // Insert or update mapping in user_skills
            $stmtUserSkill = $pdo->prepare("
                INSERT INTO user_skills (user_id, skill_id, skill_type, proficiency_level)
                VALUES (:user_id, :skill_id, :skill_type, :proficiency)
                ON DUPLICATE KEY UPDATE proficiency_level = VALUES(proficiency_level)
            ");
            $stmtUserSkill->execute([
                'user_id'     => $user_id,
                'skill_id'    => $skill_id,
                'skill_type'  => $skill_type,
                'proficiency' => $proficiency_level
            ]);

            $pdo->commit();
            setFlashMessage('success', 'Skill added successfully!');
            redirect('skills.php');

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Add Skill Error: " . $e->getMessage());
            $errors[] = "Failed to add skill. Please try again.";
        }
    }
}

$page_title = "Add Skill";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width: 550px;">
    <h2 class="form-title">Add Skill</h2>
    <p class="form-subtitle">Add a skill to your profile to teach or learn</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-left: 1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo sanitize($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="add-skill.php" method="POST">
        <div class="form-group">
            <label for="skill_type" class="form-label">I want to *</label>
            <select name="skill_type" id="skill_type" class="form-control" required>
                <option value="teach" <?php echo $default_type === 'teach' ? 'selected' : ''; ?>>🎓 Teach this skill to other students</option>
                <option value="learn" <?php echo $default_type === 'learn' ? 'selected' : ''; ?>>📖 Learn this skill from a peer provider</option>
            </select>
        </div>

        <div class="form-group">
            <label for="existing_skill_id" class="form-label">Select Existing Skill</label>
            <select name="existing_skill_id" id="existing_skill_id" class="form-control">
                <option value="0">-- Select from master skill list --</option>
                <?php foreach ($master_skills as $ms): ?>
                    <option value="<?php echo $ms['skill_id']; ?>">
                        <?php echo sanitize($ms['skill_name']); ?> (<?php echo sanitize($ms['category']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="text-align: center; margin: 1rem 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
            — OR ADD A NEW SKILL —
        </div>

        <div class="form-group">
            <label for="custom_skill_name" class="form-label">New Skill Name</label>
            <input type="text" name="custom_skill_name" id="custom_skill_name" class="form-control" placeholder="e.g. Flutter, React, Technical Writing">
        </div>

        <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <select name="category" id="category" class="form-control">
                <option value="Computer Science">Computer Science</option>
                <option value="Data Science">Data Science</option>
                <option value="Design">Design</option>
                <option value="Soft Skills">Soft Skills</option>
                <option value="Languages">Languages</option>
                <option value="General">General</option>
            </select>
        </div>

        <div class="form-group">
            <label for="proficiency_level" class="form-label">Proficiency Level</label>
            <select name="proficiency_level" id="proficiency_level" class="form-control">
                <option value="Beginner">Beginner</option>
                <option value="Intermediate" selected>Intermediate</option>
                <option value="Advanced">Advanced</option>
            </select>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Add Skill</button>
            <a href="skills.php" class="btn btn-secondary" style="flex: 1;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
