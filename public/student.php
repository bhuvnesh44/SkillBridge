<?php
/**
 * Skillbridg — Peer Student Public Profile View
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$student_id = intval($_GET['id'] ?? 0);
$current_user_id = getCurrentUserId();

if ($student_id <= 0 || $student_id === $current_user_id) {
    redirect('find-skills.php');
}

$pdo = getDBConnection();

// Fetch student profile
$stmt = $pdo->prepare("SELECT user_id, name, email, bio, department, semester, created_at FROM users WHERE user_id = :id LIMIT 1");
$stmt->execute(['id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    setFlashMessage('danger', 'Student not found.');
    redirect('find-skills.php');
}

// Fetch skills this student can teach
$stmtTeach = $pdo->prepare("
    SELECT us.user_skill_id, us.proficiency_level, s.skill_id, s.skill_name, s.category
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = :id AND us.skill_type = 'teach'
    ORDER BY s.skill_name ASC
");
$stmtTeach->execute(['id' => $student_id]);
$teach_skills = $stmtTeach->fetchAll();

// Fetch feedback received by this student
$stmtFeedback = $pdo->prepare("
    SELECT f.rating, f.feedback_text, f.created_at, u.name as giver_name
    FROM feedback f
    JOIN users u ON f.given_by_user_id = u.user_id
    WHERE f.received_by_user_id = :id
    ORDER BY f.created_at DESC
");
$stmtFeedback->execute(['id' => $student_id]);
$feedbacks = $stmtFeedback->fetchAll();

$page_title = "Peer Profile — " . sanitize($student['name']);
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="find-skills.php" class="btn btn-sm btn-secondary">&larr; Back to Search</a>
</div>

<div class="grid-3" style="grid-template-columns: 1fr 2fr;">
    <!-- Student Details Card -->
    <div class="card">
        <div style="text-align: center; padding-bottom: 1rem; border-bottom: 1px solid var(--border); margin-bottom: 1.25rem;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); font-size: 2rem; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
            </div>
            <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main);"><?php echo sanitize($student['name']); ?></h2>
            <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo !empty($student['department']) ? sanitize($student['department']) : 'Student'; ?></p>
        </div>

        <div style="font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <div>
                <strong style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Semester:</strong>
                <span><?php echo !empty($student['semester']) ? sanitize($student['semester']) : 'Not specified'; ?></span>
            </div>
            <div>
                <strong style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Member Since:</strong>
                <span><?php echo date('M d, Y', strtotime($student['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- Right Side: Bio, Teachable Skills & Feedback -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Bio Card -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 0.5rem;">About Student</h3>
            <p style="color: var(--text-main); font-size: 0.95rem;">
                <?php echo !empty($student['bio']) ? nl2br(sanitize($student['bio'])) : '<em>No bio available.</em>'; ?>
            </p>
        </div>

        <!-- Skills Offerings Card -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 1rem;">Skills Offered for Peer Learning 🎓</h3>

            <?php if (empty($teach_skills)): ?>
                <p style="color: var(--text-muted); font-size: 0.9rem;">This student has not listed any skills to teach yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($teach_skills as $ts): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md);">
                            <div>
                                <strong style="font-size: 1rem; color: var(--text-main);"><?php echo sanitize($ts['skill_name']); ?></strong>
                                <span class="skill-tag teach" style="font-size: 0.75rem; margin-left: 0.5rem;">
                                    <?php echo sanitize($ts['proficiency_level']); ?>
                                </span>
                            </div>
                            <a href="send-request.php?receiver_id=<?php echo $student['user_id']; ?>&skill_id=<?php echo $ts['skill_id']; ?>" class="btn btn-sm btn-primary">
                                Send Request
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Student Feedback Reviews Card -->
        <?php if (!empty($feedbacks)): ?>
            <div class="card">
                <h3 class="card-title" style="margin-bottom: 1rem;">Peer Reviews & Feedback ⭐</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($feedbacks as $fb): ?>
                        <div style="padding: 0.875rem; background: var(--bg-main); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                                <strong style="font-size: 0.9rem; color: var(--text-main);"><?php echo sanitize($fb['giver_name']); ?></strong>
                                <span style="color: var(--warning); font-weight: 700; font-size: 0.9rem;">
                                    <?php echo str_repeat('★', $fb['rating']); ?> (<?php echo $fb['rating']; ?>/5)
                                </span>
                            </div>
                            <p style="font-size: 0.875rem; color: var(--text-muted); font-style: italic;">
                                "<?php echo sanitize($fb['feedback_text']); ?>"
                            </p>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.35rem;">
                                <?php echo date('M d, Y', strtotime($fb['created_at'])); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
