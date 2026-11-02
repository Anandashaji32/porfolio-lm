<?php
/**
 * Books Catalog & Management Page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();
$isAdmin = isAdmin();
$isManage = ($isAdmin && isset($_GET['manage']) && $_GET['manage'] == '1');

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Filters
$search = trim($_GET['search'] ?? '');
$categoryFilter = intval($_GET['cat'] ?? 0);
$stockFilter = $_GET['stock'] ?? '';

$sql = "SELECT b.*, c.name as category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $sql .= " AND b.category_id = ?";
    $params[] = $categoryFilter;
}

if ($stockFilter === 'available') {
    $sql .= " AND b.available_copies > 0";
} elseif ($stockFilter === 'out') {
    $sql .= " AND b.available_copies = 0";
}

$sql .= " ORDER BY b.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$pageTitle = $isManage ? 'Manage Catalog Books' : 'Browse Catalog';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    <?= $isManage ? 'Books Inventory Management' : 'Library Catalog' ?>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    <?= $isManage ? 'Create, modify, and track volume stock across library racks.' : 'Explore hundreds of titles available for loan in the university collection.' ?>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <?php if ($isAdmin): ?>
                    <?php if ($isManage): ?>
                        <a href="books.php" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 text-xs font-semibold transition flex items-center gap-2">
                            <i class="fas fa-th-large"></i> Grid View
                        </a>
                    <?php else: ?>
                        <a href="books.php?manage=1" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 text-xs font-semibold transition flex items-center gap-2">
                            <i class="fas fa-tasks"></i> Table View
                        </a>
                    <?php endif; ?>
                    <button data-modal-target="add-book-modal" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add New Book
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 custom-shadow">
            <form action="books.php" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <?php if ($isManage): ?>
                    <input type="hidden" name="manage" value="1">
                <?php endif; ?>

                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search title, author, or ISBN..."
                           class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>

                <div class="sm:col-span-3">
                    <select name="cat" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($categoryFilter === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <select name="stock" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                        <option value="">Any Status</option>
                        <option value="available" <?= ($stockFilter === 'available') ? 'selected' : '' ?>>Available Only</option>
                        <option value="out" <?= ($stockFilter === 'out') ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
                        Filter
                    </button>
                    <a href="books.php<?= $isManage ? '?manage=1' : '' ?>" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset">
                        <i class="fas fa-undo text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        <?php if ($isManage): ?>
            <!-- LIBRARIAN INVENTORY TABLE VIEW -->
            <div class="bg-white rounded-3xl border border-slate-200 custom-shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase">
                                <th class="py-3.5 px-4">Book Details</th>
                                <th class="py-3.5 px-4">Category</th>
                                <th class="py-3.5 px-4">ISBN</th>
                                <th class="py-3.5 px-4 text-center">Copies (Avail / Tot)</th>
                                <th class="py-3.5 px-4">Rack / Shelf</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($books)): ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">No books found in inventory.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($books as $b): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <img src="<?= htmlspecialchars($b['cover_image']) ?>" 
                                                     alt="Cover" 
                                                     class="w-10 h-14 object-cover rounded-lg shadow-sm flex-shrink-0"
                                                     onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'">
                                                <div>
                                                    <div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1" title="<?= htmlspecialchars($b['title']) ?>">
                                                        <?= htmlspecialchars($b['title']) ?>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500">By <?= htmlspecialchars($b['author']) ?></div>
                                                    <?php if (!empty($b['publisher'])): ?>
                                                        <div class="text-[10px] text-slate-400"><?= htmlspecialchars($b['publisher']) ?> (<?= $b['published_year'] ?>)</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs font-medium text-slate-600">
                                            <?= htmlspecialchars($b['category_name'] ?: 'Uncategorized') ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs font-mono text-slate-500">
                                            <?= htmlspecialchars($b['isbn'] ?: 'N/A') ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $b['available_copies'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                                <?= $b['available_copies'] ?> / <?= $b['total_copies'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-slate-600 font-medium">
                                            <i class="fas fa-map-pin text-slate-400 mr-1 text-[10px]"></i>
                                            <?= htmlspecialchars($b['rack_location'] ?: 'Unassigned') ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button" 
                                                        onclick='openEditModal(<?= json_encode($b) ?>)'
                                                        class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 transition" title="Edit Book">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                                <a href="actions/book_action.php?action=delete&id=<?= $b['id'] ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this book? This action cannot be undone.')"
                                                   class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition" title="Delete Book">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- CARD GRID VIEW (FOR MEMBERS AND BROWSING) -->
            <?php if (empty($books)): ?>
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fas fa-books"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No matching books found</h3>
                    <p class="text-xs text-slate-500 mt-1">Try relaxing your filter parameters or search terms.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($books as $b): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between custom-shadow-hover transition duration-200">
                            <div>
                                <div class="relative h-44 bg-slate-100 overflow-hidden">
                                    <img src="<?= htmlspecialchars($b['cover_image']) ?>" 
                                         alt="<?= htmlspecialchars($b['title']) ?>" 
                                         class="w-full h-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'">
                                    <div class="absolute top-2.5 right-2.5">
                                        <?php if ($b['available_copies'] > 0): ?>
                                            <span class="badge badge-success shadow-sm">
                                                <?= $b['available_copies'] ?> on Shelf
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger shadow-sm">All Checked Out</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($b['category_name'])): ?>
                                        <div class="absolute bottom-2.5 left-2.5">
                                            <span class="px-2 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white text-[10px] font-semibold">
                                                <?= htmlspecialchars($b['category_name']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="p-4">
                                    <h3 class="font-bold text-slate-800 text-sm line-clamp-1" title="<?= htmlspecialchars($b['title']) ?>">
                                        <?= htmlspecialchars($b['title']) ?>
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">By <?= htmlspecialchars($b['author']) ?></p>
                                    <p class="text-xs text-slate-600 mt-2 line-clamp-2 leading-relaxed">
                                        <?= htmlspecialchars($b['description'] ?: 'Available for student and faculty checkout.') ?>
                                    </p>

                                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                        <span><i class="fas fa-map-marker-alt text-slate-400"></i> <?= htmlspecialchars($b['rack_location'] ?: 'General') ?></span>
                                        <span>ISBN: <?= htmlspecialchars($b['isbn'] ?: 'N/A') ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 pt-0">
                                <?php if (!$isAdmin): ?>
                                    <?php if ($b['available_copies'] > 0): ?>
                                        <a href="actions/issue_action.php?action=member_borrow&book_id=<?= $b['id'] ?>" 
                                           onclick="return confirm('Borrow &quot;<?= addslashes(htmlspecialchars($b['title'])) ?>&quot; for 14 days?')"
                                           class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition shadow-md shadow-indigo-600/10">
                                            <i class="fas fa-book-reader"></i> Borrow Book
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="w-full py-2 px-3 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed">
                                            Unavailable
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="books.php?manage=1" class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                        <i class="fas fa-cog"></i> Manage Inventory
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>
</div>

<?php if ($isAdmin): ?>
    <!-- Add Book Modal -->
    <div id="add-book-modal" class="modal">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative max-h-[90vh] overflow-y-auto">
            <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
            <div class="mb-5">
                <h3 class="text-xl font-bold text-slate-900">Add Book to Catalog</h3>
                <p class="text-xs text-slate-500 mt-1">Enter catalog metadata to register a new book and shelf location.</p>
            </div>

            <form action="actions/book_action.php" method="POST" class="space-y-3.5">
                <input type="hidden" name="action" value="add">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Book Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Artificial Intelligence: A Modern Approach"
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Author <span class="text-red-500">*</span></label>
                        <input type="text" name="author" required placeholder="Stuart Russell, Peter Norvig"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">ISBN</label>
                        <input type="text" name="isbn" placeholder="978-0134610993"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Category</label>
                        <select name="category_id" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Total Copies</label>
                        <input type="number" name="total_copies" min="1" value="3" required
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Publisher</label>
                        <input type="text" name="publisher" placeholder="Pearson"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Rack / Shelf Location</label>
                        <input type="text" name="rack_location" placeholder="Rack CS-04"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Cover Image URL</label>
                    <input type="url" name="cover_image" placeholder="https://images.unsplash.com/photo-..."
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief synopsis or overview of book contents..."
                              class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Save Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="edit-book-modal" class="modal">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative max-h-[90vh] overflow-y-auto">
            <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
            <div class="mb-5">
                <h3 class="text-xl font-bold text-slate-900">Edit Book Information</h3>
                <p class="text-xs text-slate-500 mt-1">Update book title, author, category, or inventory levels.</p>
            </div>

            <form action="actions/book_action.php" method="POST" class="space-y-3.5">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-book-id">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Book Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="edit-book-title" required
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Author <span class="text-red-500">*</span></label>
                        <input type="text" name="author" id="edit-book-author" required
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">ISBN</label>
                        <input type="text" name="isbn" id="edit-book-isbn"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Category</label>
                        <select name="category_id" id="edit-book-category" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Total Copies</label>
                        <input type="number" name="total_copies" id="edit-book-copies" min="1" required
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Publisher</label>
                        <input type="text" name="publisher" id="edit-book-publisher"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Rack / Shelf Location</label>
                        <input type="text" name="rack_location" id="edit-book-rack"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Cover Image URL</label>
                    <input type="url" name="cover_image" id="edit-book-cover"
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" id="edit-book-desc" rows="2"
                              class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(book) {
        document.getElementById('edit-book-id').value = book.id;
        document.getElementById('edit-book-title').value = book.title;
        document.getElementById('edit-book-author').value = book.author;
        document.getElementById('edit-book-isbn').value = book.isbn || '';
        document.getElementById('edit-book-category').value = book.category_id || '';
        document.getElementById('edit-book-copies').value = book.total_copies;
        document.getElementById('edit-book-publisher').value = book.publisher || '';
        document.getElementById('edit-book-rack').value = book.rack_location || '';
        document.getElementById('edit-book-cover').value = book.cover_image || '';
        document.getElementById('edit-book-desc').value = book.description || '';
        
        document.getElementById('edit-book-modal').classList.add('active');
    }
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
