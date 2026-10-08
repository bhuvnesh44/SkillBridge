<?php
/**
 * Skillbridg — View Student Profile
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$pdo = getDBConnection();

// Fetch user profile info
$stmt = $pdo->prepare("SELECT user_id, name, email, bio, department, semester, created_at FROM users WHERE user_id = :id LIMIT 1");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('danger', 'Profile not found.');
    redirect('dashboard.php');
}

// Fetch teach skills
$stmtTeach = $pdo->prepare("
    SELECT us.user_skill_id, us.proficiency_level, s.skill_name, s.category
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = :id AND us.skill_type = 'teach'
    ORDER BY s.skill_name ASC
");
$stmtTeach->execute(['id' => $user_id]);
$teach_skills = $stmtTeach->fetchAll();

// Fetch learn skills
$stmtLearn = $pdo->prepare("
    SELECT us.user_skill_id, us.proficiency_level, s.skill_name, s.category
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = :id AND us.skill_type = 'learn'
    ORDER BY s.skill_name ASC
");
$stmtLearn->execute(['id' => $user_id]);
$learn_skills = $stmtLearn->fetchAll();

$page_title = "My Profile";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 700;">Student Profile</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Manage your personal profile information and listed skills.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
        <a href="skills.php" class="btn btn-secondary">Manage Skills</a>
    </div>
</div>

<div class="grid-3" style="grid-template-columns: 1fr 2fr;">
    <!-- Profile Card -->
    <div class="card">
        <div style="text-align: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border); margin-bottom: 1.25rem;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); font-size: 2rem; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main);"><?php echo sanitize($user['name']); ?></h3>
            <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo sanitize($user['email']); ?></p>
        </div>

        <div style="font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <div>
                <strong style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Department:</strong>
                <span><?php echo !empty($user['department']) ? sanitize($user['department']) : 'Not specified'; ?></span>
            </div>
            <div>
                <strong style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Semester:</strong>
                <span><?php echo !empty($user['semester']) ? sanitize($user['semester']) : 'Not specified'; ?></span>
            </div>
            <div>
                <strong style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Member Since:</strong>
                <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- Bio & Skills Details -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Bio Card -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 0.75rem;">About Me</h3>
            <p style="color: var(--text-main); font-size: 0.95rem;">
                <?php echo !empty($user['bio']) ? nl2br(sanitize($user['bio'])) : '<em>No bio provided yet. Click "Edit Profile" to tell other students about your interests!</em>'; ?>
            </p>
        </div>

        <!-- Skills to Teach Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Skills I Can Teach 🎓</h3>
                <a href="add-skill.php?type=teach" class="btn btn-sm btn-secondary">+ Add Teach Skill</a>
            </div>
            <?php if (empty($teach_skills)): ?>
                <p style="font-size: 0.9rem; color: var(--text-muted);">You haven't listed any skills to teach yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <?php foreach ($teach_skills as $ts): ?>
                        <span class="skill-tag teach">
                            <strong><?php echo sanitize($ts['skill_name']); ?></strong>
                            <small style="opacity: 0.8;">(<?php echo sanitize($ts['proficiency_level']); ?>)</small>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Skills to Learn Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Skills I Want to Learn 📖</h3>
                <a href="add-skill.php?type=learn" class="btn btn-sm btn-secondary">+ Add Learn Skill</a>
            </div>
            <?php if (empty($learn_skills)): ?>
                <p style="font-size: 0.9rem; color: var(--text-muted);">You haven't listed any skills you want to learn yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <?php foreach ($learn_skills as $ls): ?>
                        <span class="skill-tag learn">
                            <strong><?php echo sanitize($ls['skill_name']); ?></strong>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
