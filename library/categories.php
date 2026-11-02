<?php
/**
 * Categories Management (Admin Only)
 */
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();

// Fetch categories with book count
$sql = "SELECT c.*, COUNT(b.id) as book_count 
        FROM categories c 
        LEFT JOIN books b ON c.id = b.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC";
$categories = $pdo->query($sql)->fetchAll();

$pageTitle = 'Category Management';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Book Categories</h1>
                <p class="text-xs text-slate-500 mt-1">Organize books into genres, academic subjects, and collections.</p>
            </div>

            <button data-modal-target="add-category-modal" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2 self-start sm:self-auto">
                <i class="fas fa-plus"></i> Add New Category
            </button>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($categories as $cat): ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 custom-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                                <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                <?= $cat['book_count'] ?> titles
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mt-3"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                            <?= htmlspecialchars($cat['description'] ?: 'General collection category.') ?>
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="books.php?cat=<?= $cat['id'] ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                            Browse Books &rarr;
                        </a>
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick='openEditCatModal(<?= json_encode($cat) ?>)' class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <a href="actions/category_action.php?action=delete&id=<?= $cat['id'] ?>" 
                               onclick="return confirm('Delete this category? Associated books will become uncategorized.')" 
                               class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-50 transition">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>
</div>

<!-- Add Category Modal -->
<div id="add-category-modal" class="modal">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative">
        <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fas fa-times"></i>
        </button>
        <div class="mb-5">
            <h3 class="text-xl font-bold text-slate-900">Create New Category</h3>
            <p class="text-xs text-slate-500 mt-1">Add a genre or subject classification for catalog books.</p>
        </div>

        <form action="actions/category_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Artificial Intelligence"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">FontAwesome Icon Class</label>
                <input type="text" name="icon" value="fas fa-book" placeholder="fas fa-atom"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Category overview..."
                          class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="edit-category-modal" class="modal">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative">
        <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fas fa-times"></i>
        </button>
        <div class="mb-5">
            <h3 class="text-xl font-bold text-slate-900">Edit Category</h3>
            <p class="text-xs text-slate-500 mt-1">Modify classification details.</p>
        </div>

        <form action="actions/category_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-cat-id">

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit-cat-name" required
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">FontAwesome Icon Class</label>
                <input type="text" name="icon" id="edit-cat-icon"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" id="edit-cat-desc" rows="3"
                          class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCatModal(cat) {
    document.getElementById('edit-cat-id').value = cat.id;
    document.getElementById('edit-cat-name').value = cat.name;
    document.getElementById('edit-cat-icon').value = cat.icon || 'fas fa-book';
    document.getElementById('edit-cat-desc').value = cat.description || '';
    document.getElementById('edit-category-modal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
