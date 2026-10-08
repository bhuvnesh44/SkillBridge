<?php
/**
 * Skillbridg — Manage Student Skills
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$pdo = getDBConnection();

// Fetch Teach Skills
$stmtTeach = $pdo->prepare("
    SELECT us.user_skill_id, us.proficiency_level, s.skill_name, s.category
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = :id AND us.skill_type = 'teach'
    ORDER BY s.skill_name ASC
");
$stmtTeach->execute(['id' => $user_id]);
$teach_skills = $stmtTeach->fetchAll();

// Fetch Learn Skills
$stmtLearn = $pdo->prepare("
    SELECT us.user_skill_id, us.proficiency_level, s.skill_name, s.category
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = :id AND us.skill_type = 'learn'
    ORDER BY s.skill_name ASC
");
$stmtLearn->execute(['id' => $user_id]);
$learn_skills = $stmtLearn->fetchAll();

$page_title = "My Skills";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 700;">My Listed Skills</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Manage the skills you can teach and skills you want to learn.</p>
    </div>
    <a href="add-skill.php" class="btn btn-primary">+ Add New Skill</a>
</div>

<div class="grid-2">
    <!-- Teach Skills Section -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Skills I Can Teach 🎓</h2>
            <a href="add-skill.php?type=teach" class="btn btn-sm btn-secondary">+ Add</a>
        </div>
        
        <?php if (empty($teach_skills)): ?>
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-icon">🎓</div>
                <h4 class="empty-title">No teaching skills listed</h4>
                <p class="empty-desc">Share what you know with fellow students.</p>
                <a href="add-skill.php?type=teach" class="btn btn-sm btn-primary">Add Skill to Teach</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Proficiency</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teach_skills as $skill): ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($skill['skill_name']); ?></strong>
                                    <br><small style="color: var(--text-muted);"><?php echo sanitize($skill['category']); ?></small>
                                </td>
                                <td>
                                    <span class="skill-tag teach" style="font-size: 0.75rem;">
                                        <?php echo sanitize($skill['proficiency_level']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="edit-skill.php?id=<?php echo $skill['user_skill_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="delete-skill.php?id=<?php echo $skill['user_skill_id']; ?>" class="btn btn-sm btn-danger confirm-delete" data-confirm="Are you sure you want to remove this skill from your profile?">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Learn Skills Section -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Skills I Want to Learn 📖</h2>
            <a href="add-skill.php?type=learn" class="btn btn-sm btn-secondary">+ Add</a>
        </div>

        <?php if (empty($learn_skills)): ?>
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-icon">📖</div>
                <h4 class="empty-title">No learning requirements listed</h4>
                <p class="empty-desc">Specify what you want to learn to discover peer providers.</p>
                <a href="add-skill.php?type=learn" class="btn btn-sm btn-primary">Add Skill to Learn</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Category</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($learn_skills as $skill): ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($skill['skill_name']); ?></strong>
                                </td>
                                <td>
                                    <span class="skill-tag learn" style="font-size: 0.75rem;">
                                        <?php echo sanitize($skill['category']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="edit-skill.php?id=<?php echo $skill['user_skill_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="delete-skill.php?id=<?php echo $skill['user_skill_id']; ?>" class="btn btn-sm btn-danger confirm-delete" data-confirm="Are you sure you want to remove this skill from your learning goals?">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
