<?php
/**
 * Skillbridg — Accept Learning Request Handler
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
        // Strict Authorization & Lifecycle check: receiver_id = user_id AND status = 'Pending'
        $stmt = $pdo->prepare("
            UPDATE learning_requests 
            SET status = 'Accepted' 
            WHERE request_id = :id AND receiver_id = :user_id AND status = 'Pending'
        ");
        $stmt->execute([
            'id'      => $request_id,
            'user_id' => $user_id
        ]);

        if ($stmt->rowCount() > 0) {
            setFlashMessage('success', 'Learning request accepted successfully!');
        } else {
            setFlashMessage('danger', 'Unable to accept request. Either access was denied or request is no longer pending.');
        }
    } catch (PDOException $e) {
        error_log("Accept Request Error: " . $e->getMessage());
        setFlashMessage('danger', 'An error occurred while accepting the request.');
    }
}

redirect('requests.php');
