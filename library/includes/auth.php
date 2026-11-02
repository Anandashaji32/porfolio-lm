<?php
/**
 * Authentication and Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'member',
        'phone' => $_SESSION['user_phone'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? 'assets/images/default-avatar.png'
    ];
}

function isAdmin() {
    return isLoggedIn() && (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please sign in to access this page.');
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'Access denied. Administrator privileges required.');
        header('Location: dashboard.php');
        exit;
    }
}

function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function logActivity($userId, $action, $details = '') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $action, $details]);
    } catch (Exception $e) {
        // Silently ignore logging failures
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Automatically update overdue status & fines for issues
 */
function updateOverdueStatuses() {
    try {
        $pdo = getDBConnection();
        $today = date('Y-m-d');
        // Any issued item with due_date < today that is still 'issued' -> set status to 'overdue'
        // Fine: $1.00 per day overdue
        $sql = "SELECT id, due_date FROM issues WHERE status = 'issued' AND due_date < ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$today]);
        $overdues = $stmt->fetchAll();

        foreach ($overdues as $row) {
            $days = (strtotime($today) - strtotime($row['due_date'])) / (60 * 60 * 24);
            $fine = max(0, round($days * 1.0, 2));
            $uStmt = $pdo->prepare("UPDATE issues SET status = 'overdue', fine_amount = ? WHERE id = ?");
            $uStmt->execute([$fine, $row['id']]);
        }
    } catch (Exception $e) {
        // Continue smoothly
    }
}

// Check overdues periodically
updateOverdueStatuses();
