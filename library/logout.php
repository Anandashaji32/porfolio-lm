<?php
/**
 * Logout Handler
 */
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION['user_name'] ?? 'User';

// Log out activity if logged in
if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'User Logout', 'User logged out of the application.');
}

// Unset all session values
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start new session just to show flash message
session_start();
setFlash('info', 'You have been successfully signed out.');

header('Location: login.php');
exit;
