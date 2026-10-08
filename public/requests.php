<?php
/**
 * Skillbridg — Learning Requests Management Portal
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$pdo = getDBConnection();

// Fetch Received Requests (Incoming)
$stmtReceived = $pdo->prepare("
    SELECT lr.request_id, lr.status, lr.message, lr.created_at,
           u.user_id as sender_id, u.name as sender_name, u.email as sender_email,
           s.skill_name
    FROM learning_requests lr
    JOIN users u ON lr.sender_id = u.user_id
    JOIN skills s ON lr.skill_id = s.skill_id
    WHERE lr.receiver_id = :user_id
    ORDER BY lr.created_at DESC
");
$stmtReceived->execute(['user_id' => $user_id]);
$received_requests = $stmtReceived->fetchAll();

// Fetch Sent Requests (Outgoing)
$stmtSent = $pdo->prepare("
    SELECT lr.request_id, lr.status, lr.message, lr.created_at,
           u.user_id as receiver_id, u.name as receiver_name, u.email as receiver_email,
           s.skill_name,
           f.feedback_id
    FROM learning_requests lr
    JOIN users u ON lr.receiver_id = u.user_id
    JOIN skills s ON lr.skill_id = s.skill_id
    LEFT JOIN feedback f ON lr.request_id = f.request_id
    WHERE lr.sender_id = :user_id
    ORDER BY lr.created_at DESC
");
$stmtSent->execute(['user_id' => $user_id]);
$sent_requests = $stmtSent->fetchAll();

$page_title = "Learning Requests";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700;">Learning Requests</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Manage incoming requests and track your learning interactions.</p>
</div>

<div class="grid-2">
    <!-- Received Requests Column -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Received Requests 📥</h2>
            <span class="status-badge badge-pending"><?php echo count($received_requests); ?> Total</span>
        </div>

        <?php if (empty($received_requests)): ?>
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-icon">📥</div>
                <h4 class="empty-title">No incoming requests</h4>
                <p class="empty-desc">When other students request help with skills you teach, they will appear here.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($received_requests as $req): ?>
                    <div style="padding: 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-main);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div>
                                <strong style="font-size: 1rem; color: var(--text-main);"><?php echo sanitize($req['sender_name']); ?></strong>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    Wants to learn: <strong style="color: var(--primary);"><?php echo sanitize($req['skill_name']); ?></strong>
                                </div>
                            </div>
                            <?php echo renderStatusBadge($req['status']); ?>
                        </div>

                        <?php if (!empty($req['message'])): ?>
                            <p style="font-size: 0.875rem; color: var(--text-main); margin: 0.5rem 0; font-style: italic; background: var(--surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                                "<?php echo sanitize($req['message']); ?>"
                            </p>
                        <?php endif; ?>

                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted);">
                            <span>Sent on <?php echo date('M d, Y', strtotime($req['created_at'])); ?></span>
                            
                            <div style="display: flex; gap: 0.4rem;">
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <a href="accept-request.php?id=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-success">Accept</a>
                                    <a href="reject-request.php?id=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-danger confirm-delete" data-confirm="Are you sure you want to reject this request?">Reject</a>
                                <?php elseif ($req['status'] === 'Accepted'): ?>
                                    <a href="complete-request.php?id=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-primary">Mark Completed</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sent Requests Column -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Sent Requests 📤</h2>
            <span class="status-badge badge-completed"><?php echo count($sent_requests); ?> Total</span>
        </div>

        <?php if (empty($sent_requests)): ?>
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-icon">📤</div>
                <h4 class="empty-title">No sent requests</h4>
                <p class="empty-desc">Find a skill provider and send learning requests to get started.</p>
                <a href="find-skills.php" class="btn btn-sm btn-primary">Search Skills</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($sent_requests as $req): ?>
                    <div style="padding: 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-main);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div>
                                <strong style="font-size: 1rem; color: var(--text-main);"><?php echo sanitize($req['receiver_name']); ?></strong>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    Requested skill: <strong style="color: var(--primary);"><?php echo sanitize($req['skill_name']); ?></strong>
                                </div>
                            </div>
                            <?php echo renderStatusBadge($req['status']); ?>
                        </div>

                        <?php if (!empty($req['message'])): ?>
                            <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0.5rem 0; font-style: italic;">
                                "<?php echo sanitize($req['message']); ?>"
                            </p>
                        <?php endif; ?>

                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted);">
                            <span>Requested on <?php echo date('M d, Y', strtotime($req['created_at'])); ?></span>

                            <div style="display: flex; gap: 0.4rem;">
                                <?php if ($req['status'] === 'Accepted'): ?>
                                    <a href="complete-request.php?id=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-primary">Mark Completed</a>
                                <?php elseif ($req['status'] === 'Completed'): ?>
                                    <?php if (empty($req['feedback_id'])): ?>
                                        <a href="feedback.php?request_id=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-success">Give Feedback ⭐</a>
                                    <?php else: ?>
                                        <span class="status-badge badge-completed">Feedback Given ✓</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
