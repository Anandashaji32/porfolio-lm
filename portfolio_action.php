<?php
/**
 * Portfolio Action Handlers (Auth, Contact Messages, Profile Updates)
 */
require_once __DIR__ . '/portfolio_db.php';
$pdo = getPortfolioDB();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Portfolio Login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setPortfolioFlash('error', 'Please provide both email and password.');
        header('Location: portfolio_login.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM portfolio_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['portfolio_user_id'] = $user['id'];
        $_SESSION['portfolio_user_name'] = $user['name'];
        $_SESSION['portfolio_user_email'] = $user['email'];
        $_SESSION['portfolio_user_role'] = $user['role'];

        setPortfolioFlash('success', 'Welcome to your Portfolio Admin Portal, ' . htmlspecialchars($user['name']) . '!');
        header('Location: portfolio_admin.php');
        exit;
    } else {
        setPortfolioFlash('error', 'Invalid email or password. Please try again.');
        header('Location: portfolio_login.php');
        exit;
    }
}

// Portfolio Contact Form Submission
if ($action === 'contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Portfolio Inquiry');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        setPortfolioFlash('error', 'Please fill out all required fields before sending your message.');
        header('Location: index.php#contact');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setPortfolioFlash('error', 'Please enter a valid email address.');
        header('Location: index.php#contact');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO portfolio_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
    $stmt->execute([$name, $email, $subject, $message]);

    setPortfolioFlash('success', 'Thank you, ' . htmlspecialchars($name) . '! Your message was received successfully. I will get back to you promptly.');
    header('Location: index.php#contact');
    exit;
}

// Admin: Toggle Message Read/Unread
if ($action === 'toggle_msg') {
    requirePortfolioLogin();
    $id = intval($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("SELECT status FROM portfolio_messages WHERE id = ?");
    $stmt->execute([$id]);
    $msg = $stmt->fetch();

    if ($msg) {
        $newStatus = ($msg['status'] === 'unread') ? 'read' : 'unread';
        $up = $pdo->prepare("UPDATE portfolio_messages SET status = ? WHERE id = ?");
        $up->execute([$newStatus, $id]);
        setPortfolioFlash('success', "Message marked as {$newStatus}.");
    }
    header('Location: portfolio_admin.php');
    exit;
}

// Admin: Delete Message
if ($action === 'delete_msg') {
    requirePortfolioLogin();
    $id = intval($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("DELETE FROM portfolio_messages WHERE id = ?");
    $stmt->execute([$id]);
    setPortfolioFlash('success', 'Message deleted from inbox.');
    header('Location: portfolio_admin.php');
    exit;
}

// Admin: Update Profile Information
if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePortfolioLogin();

    $fullName = trim($_POST['full_name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $github = trim($_POST['github_url'] ?? '');
    $linkedin = trim($_POST['linkedin_url'] ?? '');

    $stmt = $pdo->prepare("UPDATE portfolio_profile SET 
        full_name = ?, headline = ?, bio = ?, about = ?, 
        email = ?, phone = ?, location = ?, github_url = ?, linkedin_url = ? 
        WHERE id = 1");
    $stmt->execute([$fullName, $headline, $bio, $about, $email, $phone, $location, $github, $linkedin]);

    $_SESSION['portfolio_user_name'] = $fullName;
    setPortfolioFlash('success', 'Portfolio profile updated successfully!');
    header('Location: portfolio_admin.php');
    exit;
}

// Admin: Change Password
if ($action === 'update_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePortfolioLogin();

    $newPass = $_POST['new_password'] ?? '';
    if (strlen($newPass) < 6) {
        setPortfolioFlash('error', 'New password must be at least 6 characters long.');
        header('Location: portfolio_admin.php');
        exit;
    }

    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE portfolio_users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $_SESSION['portfolio_user_id']]);

    setPortfolioFlash('success', 'Admin password changed successfully!');
    header('Location: portfolio_admin.php');
    exit;
}

header('Location: index.php');
exit;
