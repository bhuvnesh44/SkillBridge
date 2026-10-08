<?php
/**
 * Skillbridg — Authentication & Authorization Helpers
 */

require_once __DIR__ . '/functions.php';

/**
 * Check if the user is currently logged in
 *
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current authenticated user ID
 *
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current authenticated user name
 *
 * @return string
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Student';
}

/**
 * Protect page: Enforce authentication requirement
 * Redirects guest users to login page
 *
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('danger', 'Please log in to access this page.');
        redirect('login.php');
    }
}

/**
 * Protect page: Restrict access to guest users only (e.g., login, register)
 * Redirects logged in users to dashboard
 *
 * @return void
 */
function requireGuest() {
    if (isLoggedIn()) {
        redirect('dashboard.php');
    }
}

/**
 * Initialize user session upon successful login
 *
 * @param array $user
 * @return void
 */
function loginUser($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
}

/**
 * Destroy current user session and log out
 *
 * @return void
 */
function logoutUser() {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}
