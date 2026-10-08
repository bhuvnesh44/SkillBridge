<?php
/**
 * Skillbridg — Send Learning Request
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$sender_id = getCurrentUserId();
$receiver_id = intval($_GET['receiver_id'] ?? $_POST['receiver_id'] ?? 0);
$skill_id    = intval($_GET['skill_id'] ?? $_POST['skill_id'] ?? 0);
$pdo = getDBConnection();
$errors = [];

// Business Rule Check 1: User cannot send request to themselves
if ($receiver_id === $sender_id) {
    setFlashMessage('danger', 'You cannot send a learning request to yourself.');
    redirect('find-skills.php');
}

// Fetch Receiver Details & Skill Details
$stmtReceiver = $pdo->prepare("SELECT user_id, name, email, department FROM users WHERE user_id = :id LIMIT 1");
$stmtReceiver->execute(['id' => $receiver_id]);
$receiver = $stmtReceiver->fetch();

$stmtSkill = $pdo->prepare("SELECT skill_id, skill_name, category FROM skills WHERE skill_id = :id LIMIT 1");
$stmtSkill->execute(['id' => $skill_id]);
$skill = $stmtSkill->fetch();

if (!$receiver || !$skill) {
    setFlashMessage('danger', 'Invalid student provider or skill selection.');
    redirect('find-skills.php');
}

// Business Rule Check 2: Verify the skill belongs to receiver's teach skills
$stmtCheckTeach = $pdo->prepare("
    SELECT user_skill_id FROM user_skills 
    WHERE user_id = :receiver_id AND skill_id = :skill_id AND skill_type = 'teach'
    LIMIT 1
");
$stmtCheckTeach->execute([
    'receiver_id' => $receiver_id,
    'skill_id'    => $skill_id
]);

if (!$stmtCheckTeach->fetch()) {
    setFlashMessage('danger', 'The selected student does not offer this skill for teaching.');
    redirect('find-skills.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    // Check for existing pending or accepted request
    $stmtExisting = $pdo->prepare("
        SELECT request_id, status FROM learning_requests
        WHERE sender_id = :sender_id AND receiver_id = :receiver_id AND skill_id = :skill_id AND status IN ('Pending', 'Accepted')
        LIMIT 1
    ");
    $stmtExisting->execute([
        'sender_id'   => $sender_id,
        'receiver_id' => $receiver_id,
        'skill_id'    => $skill_id
    ]);
    
    if ($stmtExisting->fetch()) {
        $errors[] = "You already have an active or pending request with this student for this skill.";
    }

    if (empty($errors)) {
        try {
            $stmtInsert = $pdo->prepare("
                INSERT INTO learning_requests (sender_id, receiver_id, skill_id, status, message)
                VALUES (:sender_id, :receiver_id, :skill_id, 'Pending', :message)
            ");
            $stmtInsert->execute([
                'sender_id'   => $sender_id,
                'receiver_id' => $receiver_id,
                'skill_id'    => $skill_id,
                'message'     => $message ?: null
            ]);

            setFlashMessage('success', 'Learning request sent successfully to ' . sanitize($receiver['name']) . '!');
            redirect('requests.php');
        } catch (PDOException $e) {
            error_log("Send Request Error: " . $e->getMessage());
            $errors[] = "Failed to send request. Please try again.";
        }
    }
}

$page_title = "Send Learning Request";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width: 550px;">
    <h2 class="form-title">Send Learning Request</h2>
    <p class="form-subtitle">Request peer learning support from <strong><?php echo sanitize($receiver['name']); ?></strong></p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-left: 1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo sanitize($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="padding: 1rem; background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.35rem;">Request Details:</div>
        <div style="font-size: 1rem; font-weight: 600; color: var(--text-main);">
            Skill: <span style="color: var(--primary);"><?php echo sanitize($skill['skill_name']); ?></span>
        </div>
        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.2rem;">
            Skill Provider: <strong><?php echo sanitize($receiver['name']); ?></strong> (<?php echo !empty($receiver['department']) ? sanitize($receiver['department']) : 'Student'; ?>)
        </div>
    </div>

    <form action="send-request.php" method="POST">
        <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
        <input type="hidden" name="skill_id" value="<?php echo $skill_id; ?>">

        <div class="form-group">
            <label for="message" class="form-label">Message / Learning Goal (Optional)</label>
            <textarea name="message" id="message" class="form-control" placeholder="Introduce yourself and explain what specific topics or goals you would like help with..."><?php echo sanitize($_POST['message'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Send Learning Request</button>
            <a href="find-skills.php" class="btn btn-secondary" style="flex: 1;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
