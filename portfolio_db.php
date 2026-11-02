<?php
/**
 * Portfolio Database & Helper Functions
 * Uses SQLite PDO for zero-configuration, instant setup.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('PORTFOLIO_DB_FILE', __DIR__ . '/data/portfolio.db');

function getPortfolioDB() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dir = dirname(PORTFOLIO_DB_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    try {
        $pdo = new PDO("sqlite:" . PORTFOLIO_DB_FILE, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdo->exec("PRAGMA foreign_keys = ON;");

        initPortfolioTables($pdo);
        return $pdo;
    } catch (PDOException $e) {
        die("Portfolio Database Error: " . htmlspecialchars($e->getMessage()));
    }
}

function initPortfolioTables($pdo) {
    // Check if tables already exist
    try {
        $test = $pdo->query("SELECT 1 FROM portfolio_users LIMIT 1");
        return;
    } catch (Exception $e) {
        // Table doesn't exist, proceed to create
    }

    $schema = "
    CREATE TABLE IF NOT EXISTS portfolio_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'owner',
        avatar TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS portfolio_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        subject TEXT,
        message TEXT NOT NULL,
        status TEXT DEFAULT 'unread', -- 'unread', 'read'
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS portfolio_profile (
        id INTEGER PRIMARY KEY,
        full_name TEXT NOT NULL,
        headline TEXT NOT NULL,
        bio TEXT,
        about TEXT,
        email TEXT,
        phone TEXT,
        location TEXT,
        github_url TEXT,
        linkedin_url TEXT,
        resume_url TEXT
    );
    ";

    $pdo->exec($schema);

    // Seed default admin/owner: Anandha Shaji
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmtUser = $pdo->prepare("INSERT INTO portfolio_users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmtUser->execute(['Anandha Shaji', 'anandha@portfolio.com', $hash, 'owner']);

    // Seed profile data
    $stmtProfile = $pdo->prepare("INSERT INTO portfolio_profile (id, full_name, headline, bio, about, email, phone, location, github_url, linkedin_url, resume_url) 
        VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtProfile->execute([
        'Anandha Shaji',
        'Full-Stack Developer & Software Engineer',
        'Passionate software craftsman specializing in building high-performance PHP backends, database architecture, responsive web applications, and intuitive user experiences.',
        'I am a dedicated full-stack software engineer with a strong foundation in server-side PHP development, relational database modeling (MySQL/SQLite), and modern frontend interface development. I enjoy building robust web systems—from complex library circulation engines to scalable RESTful APIs—with clean code, strong security best practices, and elegant UI design.',
        'anandha.shaji@example.com',
        '+91 98765 43210',
        'India',
        'https://github.com',
        'https://linkedin.com',
        '#resume'
    ]);

    // Seed sample messages for inbox preview
    $stmtMsg = $pdo->prepare("INSERT INTO portfolio_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, ?)");
    $stmtMsg->execute([
        'Sarah Jenkins',
        'sarah.j@techrecruiting.io',
        'Full-Stack Developer Opportunity',
        'Hi Anandha, I was impressed by your Athenaeum Library Management project and backend architecture. Would love to connect regarding an engineering role!',
        'unread'
    ]);
    $stmtMsg->execute([
        'David Chen',
        'david@innovatesoft.com',
        'Freelance Web System Consultation',
        'Hello Anandha, your portfolio looks incredible. We are looking for a PHP specialist to build an inventory and member management dashboard. Let us know your availability.',
        'read'
    ]);
}

function isPortfolioLoggedIn() {
    return isset($_SESSION['portfolio_user_id']) && !empty($_SESSION['portfolio_user_id']);
}

function getPortfolioUser() {
    if (!isPortfolioLoggedIn()) return null;
    return [
        'id' => $_SESSION['portfolio_user_id'],
        'name' => $_SESSION['portfolio_user_name'] ?? 'Anandha Shaji',
        'email' => $_SESSION['portfolio_user_email'] ?? 'anandha@portfolio.com',
        'role' => $_SESSION['portfolio_user_role'] ?? 'owner'
    ];
}

function requirePortfolioLogin() {
    if (!isPortfolioLoggedIn()) {
        $_SESSION['portfolio_flash'] = ['type' => 'warning', 'message' => 'Please sign in to access the portfolio admin dashboard.'];
        header('Location: portfolio_login.php');
        exit;
    }
}

function setPortfolioFlash($type, $message) {
    $_SESSION['portfolio_flash'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

function getPortfolioFlash() {
    if (isset($_SESSION['portfolio_flash'])) {
        $flash = $_SESSION['portfolio_flash'];
        unset($_SESSION['portfolio_flash']);
        return $flash;
    }
    return null;
}
