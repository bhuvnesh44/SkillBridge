<?php
/**
 * Skillbridg — Post-Completion Peer Feedback
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$request_id = intval($_GET['request_id'] ?? $_POST['request_id'] ?? 0);
$pdo = getDBConnection();
$errors = [];

// Strict Authorization & Lifecycle Verification
$stmtReq = $pdo->prepare("
    SELECT lr.request_id, lr.sender_id, lr.receiver_id, lr.status,
           u.name as provider_name, s.skill_name,
           f.feedback_id
    FROM learning_requests lr
    JOIN users u ON lr.receiver_id = u.user_id
    JOIN skills s ON lr.skill_id = s.skill_id
    LEFT JOIN feedback f ON lr.request_id = f.request_id
    WHERE lr.request_id = :request_id AND lr.sender_id = :user_id
    LIMIT 1
");
$stmtReq->execute([
    'request_id' => $request_id,
    'user_id'    => $user_id
]);
$request = $stmtReq->fetch();

// Check if request exists and belongs to current user as learner
if (!$request) {
    setFlashMessage('danger', 'Learning request not found or access denied.');
    redirect('requests.php');
}

// Business Rule Check: Status must be Completed
if ($request['status'] !== 'Completed') {
    setFlashMessage('danger', 'Feedback can only be provided after a learning interaction is marked Completed.');
    redirect('requests.php');
}

// Business Rule Check: Duplicate feedback prevention
if (!empty($request['feedback_id'])) {
    setFlashMessage('warning', 'You have already submitted feedback for this interaction.');
    redirect('requests.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating        = intval($_POST['rating'] ?? 5);
    $feedback_text = trim($_POST['feedback_text'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5 stars.";
    }

    if (empty($feedback_text)) {
        $errors[] = "Please provide a brief feedback comment.";
    }

    if (empty($errors)) {
        try {
            $stmtInsert = $pdo->prepare("
                INSERT INTO feedback (request_id, given_by_user_id, received_by_user_id, rating, feedback_text)
                VALUES (:request_id, :giver_id, :receiver_id, :rating, :text)
            ");
            $stmtInsert->execute([
                'request_id'  => $request_id,
                'giver_id'    => $user_id,
                'receiver_id' => $request['receiver_id'],
                'rating'      => $rating,
                'text'        => $feedback_text
            ]);

            setFlashMessage('success', 'Thank you! Your feedback has been recorded successfully.');
            redirect('requests.php');
        } catch (PDOException $e) {
            error_log("Feedback Submission Error: " . $e->getMessage());
            $errors[] = "Failed to store feedback. Please try again.";
        }
    }
}

$page_title = "Provide Feedback";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width: 550px;">
    <h2 class="form-title">Submit Feedback</h2>
    <p class="form-subtitle">How was your peer learning experience with <strong><?php echo sanitize($request['provider_name']); ?></strong>?</p>

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
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Completed Interaction:</div>
        <div style="font-size: 1rem; font-weight: 600; color: var(--text-main);">
            Skill: <span style="color: var(--primary);"><?php echo sanitize($request['skill_name']); ?></span>
        </div>
        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.2rem;">
            Skill Provider: <strong><?php echo sanitize($request['provider_name']); ?></strong>
        </div>
    </div>

    <form action="feedback.php" method="POST">
        <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">

        <div class="form-group">
            <label for="rating" class="form-label">Rating *</label>
            <select name="rating" id="rating" class="form-control" required>
                <option value="5">⭐⭐⭐⭐⭐ 5 Stars — Excellent</option>
                <option value="4">⭐⭐⭐⭐ 4 Stars — Very Good</option>
                <option value="3">⭐⭐⭐ 3 Stars — Average</option>
                <option value="2">⭐⭐ 2 Stars — Needs Improvement</option>
                <option value="1">⭐ 1 Star — Poor</option>
            </select>
        </div>

        <div class="form-group">
            <label for="feedback_text" class="form-label">Feedback Comments *</label>
            <textarea name="feedback_text" id="feedback_text" class="form-control" required placeholder="Describe what you learned, how helpful the provider was, and any constructive notes..."><?php echo sanitize($_POST['feedback_text'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Feedback</button>
            <a href="requests.php" class="btn btn-secondary" style="flex: 1;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
