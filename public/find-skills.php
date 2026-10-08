<?php
/**
 * Skillbridg — Skill Discovery & Peer Search Engine
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$current_user_id = getCurrentUserId();
$search_query = trim($_GET['q'] ?? '');
$category_filter = trim($_GET['category'] ?? '');

$pdo = getDBConnection();

// Fetch categories for filter dropdown
$stmtCat = $pdo->query("SELECT DISTINCT category FROM skills ORDER BY category ASC");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

// Build search query for peer providers (students who offer skill_type = 'teach')
$sql = "
    SELECT 
        u.user_id, u.name, u.email, u.department, u.semester, u.bio,
        s.skill_id, s.skill_name, s.category,
        us.proficiency_level
    FROM user_skills us
    JOIN users u ON us.user_id = u.user_id
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.skill_type = 'teach'
      AND u.user_id != :current_user_id
";

$params = ['current_user_id' => $current_user_id];

if (!empty($search_query)) {
    $sql .= " AND (s.skill_name LIKE :query OR u.name LIKE :query OR s.category LIKE :query)";
    $params['query'] = '%' . $search_query . '%';
}

if (!empty($category_filter)) {
    $sql .= " AND s.category = :category";
    $params['category'] = $category_filter;
}

$sql .= " ORDER BY s.skill_name ASC, u.name ASC";

$stmtSearch = $pdo->prepare($sql);
$stmtSearch->execute($params);
$results = $stmtSearch->fetchAll();

$page_title = "Find Skills & Peer Providers";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700;">Find Skills & Peers</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Search for a skill to discover college peers who can teach you.</p>
</div>

<!-- Search & Filter Bar -->
<div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
    <form action="find-skills.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" name="q" class="form-control" value="<?php echo sanitize($search_query); ?>" placeholder="Search by skill name (e.g. Python, MySQL, Web Dev)...">
        </div>

        <div style="flex: 1; min-width: 180px;">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo sanitize($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="min-width: 120px;">🔍 Search</button>
        <?php if (!empty($search_query) || !empty($category_filter)): ?>
            <a href="find-skills.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Search Results -->
<?php if (empty($results)): ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3 class="empty-title">No skill providers found</h3>
        <p class="empty-desc">
            <?php if (!empty($search_query)): ?>
                No student providers matched your search for "<strong><?php echo sanitize($search_query); ?></strong>". Try searching for another skill.
            <?php else: ?>
                There are currently no peer skill providers listed.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div style="margin-bottom: 1rem; font-size: 0.9rem; color: var(--text-muted);">
        Found <strong><?php echo count($results); ?></strong> peer provider<?php echo count($results) > 1 ? 's' : ''; ?>:
    </div>

    <div class="grid-3">
        <?php foreach ($results as $item): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                            <?php echo strtoupper(substr($item['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 0.1rem; color: var(--text-main);">
                                <?php echo sanitize($item['name']); ?>
                            </h3>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                <?php echo !empty($item['department']) ? sanitize($item['department']) : 'Student'; ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-main); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.25rem;">Can Teach:</div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <strong style="color: var(--primary); font-size: 1rem;"><?php echo sanitize($item['skill_name']); ?></strong>
                            <span class="skill-tag teach" style="font-size: 0.75rem; padding: 0.15rem 0.5rem;">
                                <?php echo sanitize($item['proficiency_level']); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($item['bio'])): ?>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo sanitize($item['bio']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <a href="student.php?id=<?php echo $item['user_id']; ?>" class="btn btn-sm btn-secondary" style="flex: 1;">View Profile</a>
                    <a href="send-request.php?receiver_id=<?php echo $item['user_id']; ?>&skill_id=<?php echo $item['skill_id']; ?>" class="btn btn-sm btn-primary" style="flex: 1;">Send Request</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
