<?php
/**
 * Skillbridg — Complete Learning Interaction Handler
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$request_id = intval($_GET['id'] ?? 0);

if ($request_id > 0) {
    try {
        $pdo = getDBConnection();
        // Strict Authorization & Lifecycle check: user is sender or receiver AND status = 'Accepted'
        $stmt = $pdo->prepare("
            UPDATE learning_requests 
            SET status = 'Completed' 
            WHERE request_id = ? 
              AND (sender_id = ? OR receiver_id = ?) 
              AND status = 'Accepted'
        ");
        $stmt->execute([$request_id, $user_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            setFlashMessage('success', 'Learning interaction marked as Completed! You can now provide feedback.');
        } else {
            setFlashMessage('danger', 'Unable to complete request. Access denied or request is not currently Accepted.');
        }
    } catch (PDOException $e) {
        error_log("Complete Request Error: " . $e->getMessage());
        setFlashMessage('danger', 'An error occurred while marking the interaction completed.');
    }
}

redirect('requests.php');
