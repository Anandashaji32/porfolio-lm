<?php
/**
 * Book Management Action Handler
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Add New Book
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $publisher = trim($_POST['publisher'] ?? '');
    $published_year = !empty($_POST['published_year']) ? intval($_POST['published_year']) : null;
    $total_copies = max(1, intval($_POST['total_copies'] ?? 1));
    $rack_location = trim($_POST['rack_location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');

    if (empty($title) || empty($author)) {
        setFlash('error', 'Book Title and Author are required.');
        header('Location: ../books.php?manage=1');
        exit;
    }

    if (empty($cover_image)) {
        $cover_image = 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80';
    }

    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category_id, publisher, published_year, total_copies, available_copies, rack_location, description, cover_image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $title,
        $author,
        $isbn ?: null,
        $category_id,
        $publisher,
        $published_year,
        $total_copies,
        $total_copies,
        $rack_location,
        $description,
        $cover_image
    ]);

    $bookId = $pdo->lastInsertId();
    logActivity($_SESSION['user_id'], 'Book Added', "Added book: $title (ID: $bookId)");
    setFlash('success', "Book '{$title}' added successfully.");
    header('Location: ../books.php?manage=1');
    exit;
}

// Edit Existing Book
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $publisher = trim($_POST['publisher'] ?? '');
    $published_year = !empty($_POST['published_year']) ? intval($_POST['published_year']) : null;
    $total_copies = max(1, intval($_POST['total_copies'] ?? 1));
    $rack_location = trim($_POST['rack_location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();

    if (!$book) {
        setFlash('error', 'Book not found.');
        header('Location: ../books.php?manage=1');
        exit;
    }

    // Calculate copy difference
    $diff = $total_copies - $book['total_copies'];
    $newAvailable = max(0, $book['available_copies'] + $diff);

    $uStmt = $pdo->prepare("UPDATE books SET 
        title = ?, author = ?, isbn = ?, category_id = ?, publisher = ?, 
        published_year = ?, total_copies = ?, available_copies = ?, 
        rack_location = ?, description = ?, cover_image = ?
        WHERE id = ?");
    $uStmt->execute([
        $title,
        $author,
        $isbn ?: null,
        $category_id,
        $publisher,
        $published_year,
        $total_copies,
        $newAvailable,
        $rack_location,
        $description,
        $cover_image ?: $book['cover_image'],
        $id
    ]);

    logActivity($_SESSION['user_id'], 'Book Updated', "Updated book details for: $title (ID: $id)");
    setFlash('success', "Book '{$title}' updated successfully.");
    header('Location: ../books.php?manage=1');
    exit;
}

// Delete Book
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);

    // Check if book currently has active loans
    $checkLoan = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE book_id = ? AND status IN ('issued', 'overdue')");
    $checkLoan->execute([$id]);
    if ($checkLoan->fetchColumn() > 0) {
        setFlash('error', 'Cannot delete this book because copies are currently issued to members.');
        header('Location: ../books.php?manage=1');
        exit;
    }

    $stmt = $pdo->prepare("SELECT title FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $title = $stmt->fetchColumn() ?: 'Book';

    $delStmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $delStmt->execute([$id]);

    logActivity($_SESSION['user_id'], 'Book Deleted', "Deleted book: $title (ID: $id)");
    setFlash('success', "Book '{$title}' deleted successfully.");
    header('Location: ../books.php?manage=1');
    exit;
}

header('Location: ../books.php');
exit;
