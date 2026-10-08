<?php
/**
 * Skillbridg — Student Main Dashboard
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();
$pdo = getDBConnection();

// 1. Count Skills I Teach
$stmtTeach = $pdo->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id = ? AND skill_type = 'teach'");
$stmtTeach->execute([$user_id]);
$count_teach = $stmtTeach->fetchColumn();

// 2. Count Skills I Want to Learn
$stmtLearn = $pdo->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id = ? AND skill_type = 'learn'");
$stmtLearn->execute([$user_id]);
$count_learn = $stmtLearn->fetchColumn();

// 3. Count Pending Incoming Requests
$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM learning_requests WHERE receiver_id = ? AND status = 'Pending'");
$stmtPending->execute([$user_id]);
$count_pending = $stmtPending->fetchColumn();

// 4. Count Accepted Requests
$stmtAccepted = $pdo->prepare("SELECT COUNT(*) FROM learning_requests WHERE (receiver_id = ? OR sender_id = ?) AND status = 'Accepted'");
$stmtAccepted->execute([$user_id, $user_id]);
$count_accepted = $stmtAccepted->fetchColumn();

// 5. Count Completed Exchanges
$stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM learning_requests WHERE (receiver_id = ? OR sender_id = ?) AND status = 'Completed'");
$stmtCompleted->execute([$user_id, $user_id]);
$count_completed = $stmtCompleted->fetchColumn();

// 6. Fetch Recent Activity Feed (Latest 5 Requests)
$stmtActivity = $pdo->prepare("
    SELECT lr.request_id, lr.status, lr.created_at,
           s.skill_name,
           sender.name as sender_name,
           receiver.name as receiver_name,
           lr.sender_id, lr.receiver_id
    FROM learning_requests lr
    JOIN skills s ON lr.skill_id = s.skill_id
    JOIN users sender ON lr.sender_id = sender.user_id
    JOIN users receiver ON lr.receiver_id = receiver.user_id
    WHERE lr.sender_id = ? OR lr.receiver_id = ?
    ORDER BY lr.created_at DESC
    LIMIT 5
");
$stmtActivity->execute([$user_id, $user_id]);
$recent_activities = $stmtActivity->fetchAll();

$page_title = "Student Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700;">Welcome back, <?php echo sanitize($user_name); ?>! 👋</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Here is a dynamic overview of your peer learning and skill exchange activity.</p>
</div>

<!-- Quick Action Buttons -->
<div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
    <a href="find-skills.php" class="btn btn-primary">🔍 Find Skills & Peers</a>
    <a href="add-skill.php?type=teach" class="btn btn-secondary">+ Add Skill to Teach</a>
    <a href="requests.php" class="btn btn-secondary">📥 View Requests (<?php echo $count_pending; ?>)</a>
    <a href="profile.php" class="btn btn-secondary">👤 View Profile</a>
</div>

<!-- Dashboard Stats Grid -->
<div class="grid-4" style="margin-bottom: 2.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;">🎓</div>
        <div>
            <div class="stat-number"><?php echo $count_teach; ?></div>
            <div class="stat-label">Skills I Teach</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: #eff6ff; color: #2563eb;">📖</div>
        <div>
            <div class="stat-number"><?php echo $count_learn; ?></div>
            <div class="stat-label">Skills I Learn</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: #fffbeb; color: #d97706;">⏳</div>
        <div>
            <div class="stat-number"><?php echo $count_pending; ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: #e0e7ff; color: #4338ca;">🏆</div>
        <div>
            <div class="stat-number"><?php echo $count_completed; ?></div>
            <div class="stat-label">Completed Exchanges</div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid-2">
    <!-- Recent Activity Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Activity ⚡</h2>
            <a href="requests.php" style="font-size: 0.85rem; font-weight: 600;">View All Requests &rarr;</a>
        </div>

        <?php if (empty($recent_activities)): ?>
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-icon">🌱</div>
                <h4 class="empty-title">No recent activity</h4>
                <p class="empty-desc">Search for skills or list a new skill to get started.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.875rem;">
                <?php foreach ($recent_activities as $act): ?>
                    <?php 
                        $is_incoming = ($act['receiver_id'] == $user_id);
                        $peer_name = $is_incoming ? $act['sender_name'] : $act['receiver_name'];
                        $action_prefix = $is_incoming ? "Received from " : "Sent to ";
                    ?>
                    <div style="padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-main); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-main);">
                                <?php echo $action_prefix . sanitize($peer_name); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                Skill: <strong style="color: var(--primary);"><?php echo sanitize($act['skill_name']); ?></strong> &bull; <?php echo date('M d', strtotime($act['created_at'])); ?>
                            </div>
                        </div>
                        <?php echo renderStatusBadge($act['status']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Overview & Tips Card -->
    <div class="card">
        <h2 class="card-title" style="margin-bottom: 1rem;">Peer Learning Tips 💡</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
            <div style="padding: 0.875rem; background: var(--primary-light); border-radius: var(--radius-sm); border: 1px solid #c7d2fe; color: #3730a3;">
                <strong>1. Keep Your Profile Updated</strong>
                <p style="margin-top: 0.2rem; font-size: 0.85rem;">Specify your proficiency levels and write a brief bio so peers know your strengths.</p>
            </div>
            
            <div style="padding: 0.875rem; background: #f0fdf4; border-radius: var(--radius-sm); border: 1px solid #bbf7d0; color: #166534;">
                <strong>2. Clear Communication</strong>
                <p style="margin-top: 0.2rem; font-size: 0.85rem;">Include specific goals when sending a learning request to get quick acceptances.</p>
            </div>

            <div style="padding: 0.875rem; background: #fff7ed; border-radius: var(--radius-sm); border: 1px solid #fed7aa; color: #9a3412;">
                <strong>3. Mark Completed & Feedback</strong>
                <p style="margin-top: 0.2rem; font-size: 0.85rem;">Always mark sessions completed and leave rating reviews for your peer teachers.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
