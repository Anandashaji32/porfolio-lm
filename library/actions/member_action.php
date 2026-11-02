<?php
/**
 * Member Management Action Handler
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Toggle member status (active / suspended)
if ($action === 'toggle_status') {
    $id = intval($_GET['id'] ?? 0);

    // Prevent suspending the current admin
    if ($id == $_SESSION['user_id']) {
        setFlash('warning', 'You cannot change your own account status.');
        header('Location: ../members.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT name, status FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user) {
        $newStatus = ($user['status'] === 'active') ? 'suspended' : 'active';
        $upStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upStmt->execute([$newStatus, $id]);

        logActivity($_SESSION['user_id'], 'Member Status Updated', "Changed {$user['name']}'s status to $newStatus");
        setFlash('success', "Member {$user['name']} status updated to {$newStatus}.");
    }

    header('Location: ../members.php');
    exit;
}

// Add New Member (Librarian created)
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? 'member123';
    $role = in_array($_POST['role'] ?? '', ['admin', 'member']) ? $_POST['role'] : 'member';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email)) {
        setFlash('error', 'Name and Email are required.');
        header('Location: ../members.php');
        exit;
    }

    // Check email uniqueness
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        setFlash('error', 'A user with this email already exists.');
        header('Location: ../members.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$name, $email, $hash, $role, $phone, $address]);

    logActivity($_SESSION['user_id'], 'Member Added', "Created user account for $name ($role)");
    setFlash('success', "Account for {$name} created successfully with temporary password.");
    header('Location: ../members.php');
    exit;
}

header('Location: ../members.php');
exit;
