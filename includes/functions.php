<?php
/**
 * Skillbridg — Reusable Core Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize string output for safe HTML rendering (XSS Prevention)
 *
 * @param string|null $data
 * @return string
 */
function sanitize($data) {
    if ($data === null) return '';
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect browser to a specified URL and terminate execution
 *
 * @param string $url
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set a session flash message (success, danger, warning, info)
 *
 * @param string $type
 * @param string $message
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display session flash message if present, then clear it
 *
 * @return void
 */
function displayFlashMessages() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $type = sanitize($flash['type']);
        $message = sanitize($flash['message']);
        
        echo "<div class='alert alert-{$type}' role='alert'>
                <span>{$message}</span>
                <button class='alert-close' onclick='this.parentElement.remove();'>&times;</button>
              </div>";
              
        unset($_SESSION['flash_message']);
    }
}

/**
 * Render a CSS status badge for learning request lifecycle states
 *
 * @param string $status
 * @return string
 */
function renderStatusBadge($status) {
    $status = sanitize($status);
    $badgeClass = 'badge-secondary';
    
    switch ($status) {
        case 'Pending':
            $badgeClass = 'badge-pending';
            break;
        case 'Accepted':
            $badgeClass = 'badge-accepted';
            break;
        case 'Rejected':
            $badgeClass = 'badge-rejected';
            break;
        case 'Completed':
            $badgeClass = 'badge-completed';
            break;
    }
    
    return "<span class='status-badge {$badgeClass}'>" . $status . "</span>";
}
