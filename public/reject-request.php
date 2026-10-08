<?php
/**
 * Skillbridg — Reject Learning Request Handler
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
            SET status = 'Rejected' 
            WHERE request_id = :id AND receiver_id = :user_id AND status = 'Pending'
        ");
        $stmt->execute([
            'id'      => $request_id,
            'user_id' => $user_id
        ]);

        if ($stmt->rowCount() > 0) {
            setFlashMessage('info', 'Learning request rejected.');
        } else {
            setFlashMessage('danger', 'Unable to reject request. Either access was denied or request is no longer pending.');
        }
    } catch (PDOException $e) {
        error_log("Reject Request Error: " . $e->getMessage());
        setFlashMessage('danger', 'An error occurred while rejecting the request.');
    }
}

redirect('requests.php');
