<?php
/**
 * Authentication Action Handler (Login, Register, Profile Update)
 */
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Login Processing
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Please enter both email and password.');
        header('Location: ../login.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'suspended') {
            setFlash('error', 'Your account has been suspended. Please contact the administrator.');
            header('Location: ../login.php');
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_avatar'] = $user['avatar'];

        logActivity($user['id'], 'User Login', 'User successfully logged into the system.');
        setFlash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
        header('Location: ../dashboard.php');
        exit;
    } else {
        setFlash('error', 'Invalid email address or password.');
        header('Location: ../login.php');
        exit;
    }
}

// Register Processing
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        setFlash('error', 'Please complete all required fields.');
        header('Location: ../register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please provide a valid email address.');
        header('Location: ../register.php');
        exit;
    }

    if (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters long.');
        header('Location: ../register.php');
        exit;
    }

    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        setFlash('error', 'An account with that email already exists. Please sign in.');
        header('Location: ../register.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address, status) VALUES (?, ?, ?, 'member', ?, ?, 'active')");
    $stmt->execute([$name, $email, $hashedPassword, $phone, $address]);

    $newUserId = $pdo->lastInsertId();
    logActivity($newUserId, 'User Registration', 'New member account created.');

    setFlash('success', 'Registration successful! You can now sign in with your credentials.');
    header('Location: ../login.php');
    exit;
}

// Profile & Password Update
if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $userId = $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($name)) {
        setFlash('error', 'Name cannot be empty.');
        header('Location: ../profile.php');
        exit;
    }

    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            setFlash('error', 'New password must be at least 6 characters.');
            header('Location: ../profile.php');
            exit;
        }
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $hashed, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $userId]);
    }

    $_SESSION['user_name'] = $name;
    $_SESSION['user_phone'] = $phone;

    logActivity($userId, 'Profile Updated', 'User updated profile information.');
    setFlash('success', 'Profile updated successfully.');
    header('Location: ../profile.php');
    exit;
}

header('Location: ../index.php');
exit;
