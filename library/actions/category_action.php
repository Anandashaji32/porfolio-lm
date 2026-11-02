<?php
/**
 * Category Management Action Handler
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Add Category
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-book');

    if (empty($name)) {
        setFlash('error', 'Category name is required.');
        header('Location: ../categories.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $icon]);
        logActivity($_SESSION['user_id'], 'Category Added', "Added category: $name");
        setFlash('success', "Category '{$name}' created successfully.");
    } catch (Exception $e) {
        setFlash('error', 'Category name already exists or invalid data.');
    }

    header('Location: ../categories.php');
    exit;
}

// Edit Category
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-book');

    if (empty($name)) {
        setFlash('error', 'Category name cannot be empty.');
        header('Location: ../categories.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
        $stmt->execute([$name, $description, $icon, $id]);
        logActivity($_SESSION['user_id'], 'Category Updated', "Updated category: $name (ID: $id)");
        setFlash('success', "Category '{$name}' updated successfully.");
    } catch (Exception $e) {
        setFlash('error', 'Error updating category: ' . $e->getMessage());
    }

    header('Location: ../categories.php');
    exit;
}

// Delete Category
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);

    // Get name
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();

    if ($cat) {
        // Disassociate books
        $pdo->prepare("UPDATE books SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$id]);
        logActivity($_SESSION['user_id'], 'Category Deleted', "Deleted category: {$cat['name']}");
        setFlash('success', "Category '{$cat['name']}' deleted.");
    }

    header('Location: ../categories.php');
    exit;
}

header('Location: ../categories.php');
exit;
