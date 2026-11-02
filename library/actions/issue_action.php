<?php
/**
 * Book Issue & Return Action Handler
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Issue a Book (Admin Only)
if ($action === 'issue' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();

    $bookId = intval($_POST['book_id'] ?? 0);
    $userId = intval($_POST['user_id'] ?? 0);
    $dueDate = $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'));
    $notes = trim($_POST['notes'] ?? '');

    // Validate book
    $stmtBook = $pdo->prepare("SELECT title, available_copies FROM books WHERE id = ?");
    $stmtBook->execute([$bookId]);
    $book = $stmtBook->fetch();

    if (!$book) {
        setFlash('error', 'Selected book does not exist.');
        header('Location: ../issues.php');
        exit;
    }

    if ($book['available_copies'] <= 0) {
        setFlash('error', "No copies of '{$book['title']}' are currently available for issue.");
        header('Location: ../issues.php');
        exit;
    }

    // Validate user
    $stmtUser = $pdo->prepare("SELECT name, status FROM users WHERE id = ?");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch();

    if (!$user || $user['status'] !== 'active') {
        setFlash('error', 'Selected member is either inactive or does not exist.');
        header('Location: ../issues.php');
        exit;
    }

    // Record issue and decrement copies
    $issueDate = date('Y-m-d');
    $pdo->beginTransaction();
    try {
        $stmtIssue = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, fine_amount, status, notes) VALUES (?, ?, ?, ?, 0.0, 'issued', ?)");
        $stmtIssue->execute([$bookId, $userId, $issueDate, $dueDate, $notes]);

        $stmtDec = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $stmtDec->execute([$bookId]);

        $pdo->commit();
        logActivity($_SESSION['user_id'], 'Book Issued', "Issued '{$book['title']}' to member '{$user['name']}'. Due date: $dueDate");
        setFlash('success', "Book '{$book['title']}' successfully issued to {$user['name']}.");
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Failed to issue book: ' . $e->getMessage());
    }

    header('Location: ../issues.php');
    exit;
}

// Return a Book (Admin Only)
if ($action === 'return') {
    requireAdmin();

    $issueId = intval($_GET['id'] ?? 0);
    $stmtIssue = $pdo->prepare("SELECT i.*, b.title as book_title, u.name as user_name FROM issues i 
                                JOIN books b ON i.book_id = b.id 
                                JOIN users u ON i.user_id = u.id 
                                WHERE i.id = ?");
    $stmtIssue->execute([$issueId]);
    $issue = $stmtIssue->fetch();

    if (!$issue) {
        setFlash('error', 'Loan transaction record not found.');
        header('Location: ../issues.php');
        exit;
    }

    if ($issue['status'] === 'returned') {
        setFlash('warning', 'This book has already been marked as returned.');
        header('Location: ../issues.php');
        exit;
    }

    $returnDate = date('Y-m-d');
    $fine = 0.0;
    if ($returnDate > $issue['due_date']) {
        $days = (strtotime($returnDate) - strtotime($issue['due_date'])) / (60 * 60 * 24);
        $fine = max(0, round($days * 1.0, 2)); // $1 per day
    }

    $pdo->beginTransaction();
    try {
        $stmtUp = $pdo->prepare("UPDATE issues SET return_date = ?, fine_amount = ?, status = 'returned' WHERE id = ?");
        $stmtUp->execute([$returnDate, $fine, $issueId]);

        $stmtInc = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
        $stmtInc->execute([$issue['book_id']]);

        $pdo->commit();
        logActivity($_SESSION['user_id'], 'Book Returned', "Processed return of '{$issue['book_title']}' from {$issue['user_name']}. Fine: \${$fine}");
        
        $msg = "Book '{$issue['book_title']}' marked as returned.";
        if ($fine > 0) {
            $msg .= " Overdue fine assessed: \${$fine}.";
        }
        setFlash('success', $msg);
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Error processing return: ' . $e->getMessage());
    }

    header('Location: ../issues.php');
    exit;
}

// Member Self-Service Borrow Request
if ($action === 'member_borrow') {
    $bookId = intval($_POST['book_id'] ?? $_GET['book_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    // Check active loan limit for member (max 3 books at a time)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id = ? AND status IN ('issued', 'overdue')");
    $stmtCount->execute([$userId]);
    if ($stmtCount->fetchColumn() >= 3) {
        setFlash('warning', 'You have reached the maximum limit of 3 concurrent active book loans. Please return a book first.');
        header('Location: ../my_books.php');
        exit;
    }

    // Check availability
    $stmtBook = $pdo->prepare("SELECT title, available_copies FROM books WHERE id = ?");
    $stmtBook->execute([$bookId]);
    $book = $stmtBook->fetch();

    if (!$book || $book['available_copies'] <= 0) {
        setFlash('error', 'Sorry, this book is currently out of stock.');
        header('Location: ../books.php');
        exit;
    }

    $issueDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+14 days'));

    $pdo->beginTransaction();
    try {
        $stmtIssue = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, status, notes) VALUES (?, ?, ?, ?, 'issued', 'Self-borrowed via online catalog')");
        $stmtIssue->execute([$bookId, $userId, $issueDate, $dueDate]);

        $stmtDec = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $stmtDec->execute([$bookId]);

        $pdo->commit();
        logActivity($userId, 'Book Borrowed', "Member borrowed '{$book['title']}'. Due: $dueDate");
        setFlash('success', "You have successfully borrowed '{$book['title']}'. Due back on " . date('M d, Y', strtotime($dueDate)) . ".");
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Failed to borrow book: ' . $e->getMessage());
    }

    header('Location: ../my_books.php');
    exit;
}

header('Location: ../dashboard.php');
exit;
