<?php
/**
 * Skillbridg — Delete Listed Skill Handler
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$user_skill_id = intval($_GET['id'] ?? 0);

if ($user_skill_id > 0) {
    try {
        $pdo = getDBConnection();
        // Strict ownership check
        $stmt = $pdo->prepare("DELETE FROM user_skills WHERE user_skill_id = :id AND user_id = :user_id");
        $stmt->execute([
            'id'      => $user_skill_id,
            'user_id' => $user_id
        ]);

        if ($stmt->rowCount() > 0) {
            setFlashMessage('success', 'Skill removed successfully.');
        } else {
            setFlashMessage('danger', 'Unable to remove skill or access denied.');
        }
    } catch (PDOException $e) {
        error_log("Delete Skill Error: " . $e->getMessage());
        setFlashMessage('danger', 'An error occurred while removing the skill.');
    }
}

redirect('skills.php');
