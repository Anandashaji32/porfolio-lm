<?php
/**
 * Athenaeum - Library Management System Landing Page
 */
require_once __DIR__ . '/includes/auth.php';

$pdo = getDBConnection();
$currentUser = getCurrentUser();

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Search and Filter
$search = trim($_GET['search'] ?? '');
$categoryFilter = intval($_GET['cat'] ?? 0);

$query = "SELECT b.*, c.name as category_name 
          FROM books b 
          LEFT JOIN categories c ON b.category_id = c.id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $query .= " AND b.category_id = ?";
    $params[] = $categoryFilter;
}

$query .= " ORDER BY b.id DESC LIMIT 12";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

// Get counts for hero stats
$totalBooksCount = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$availableBooksCount = $pdo->query("SELECT SUM(available_copies) FROM books")->fetchColumn() ?: 0;
$membersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();

$pageTitle = 'Explore Knowledge & Books';
require_once __DIR__ . '/includes/header.php';
?>

<div class="flex-1">
    <!-- Top Nav Ribbon for Portfolio Navigation -->
    <div class="bg-slate-900 border-b border-slate-800 py-2.5 px-4 sm:px-8 flex items-center justify-between text-xs text-slate-300">
        <a href="../index.php" class="inline-flex items-center gap-2 text-teal-400 hover:text-teal-300 font-bold transition">
            <i class="fas fa-arrow-left text-[11px]"></i> Back to Anandha's Developer Portfolio
        </a>
        <div class="flex items-center gap-3">
            <span class="text-slate-400 hidden sm:inline">Athenaeum Library System</span>
            <?php if (!isLoggedIn()): ?>
                <a href="login.php" class="px-3 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition">
                    System Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-indigo-900 via-indigo-800 to-slate-900 text-white overflow-hidden py-16 sm:py-24">
        <!-- Abstract Glows -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-indigo-200 text-xs font-semibold backdrop-blur-sm border border-white/10 mb-4">
                <i class="fas fa-sparkles text-amber-300"></i> Next-Gen Library Catalog System
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">
                Discover Your Next <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-300">Masterpiece</span>
            </h1>
            <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-300 mb-10 leading-relaxed">
                Seamlessly search, borrow, and manage hundreds of titles across engineering, literature, science, and world history.
            </p>

            <!-- Hero Search Form -->
            <form action="index.php" method="GET" class="max-w-2xl mx-auto">
                <div class="flex items-center bg-white rounded-2xl p-2 shadow-2xl ring-1 ring-black/5">
                    <div class="pl-3 text-slate-400">
                        <i class="fas fa-search text-lg"></i>
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search books by title, author name, or ISBN..." 
                           class="w-full px-4 py-2.5 text-slate-800 bg-transparent text-sm sm:text-base focus:outline-none placeholder-slate-400">
                    <?php if ($categoryFilter > 0): ?>
                        <input type="hidden" name="cat" value="<?= $categoryFilter ?>">
                    <?php endif; ?>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-2 text-sm sm:text-base flex-shrink-0">
                        <span>Search</span>
                    </button>
                </div>
            </form>

            <!-- Quick Metrics -->
            <div class="grid grid-cols-3 max-w-lg mx-auto gap-4 mt-12 pt-8 border-t border-white/10 text-center">
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white"><?= number_format($totalBooksCount) ?>+</div>
                    <div class="text-xs text-indigo-200 font-medium mt-1">Unique Titles</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white"><?= number_format($availableBooksCount) ?></div>
                    <div class="text-xs text-indigo-200 font-medium mt-1">Copies on Shelf</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white"><?= number_format($membersCount) ?>+</div>
                    <div class="text-xs text-indigo-200 font-medium mt-1">Active Readers</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content: Catalog & Categories -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Categories Filter Bar -->
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Explore Catalog</h2>
                <p class="text-sm text-slate-500">Filter by category or browse all available volumes</p>
            </div>

            <!-- Categories Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 max-w-full">
                <a href="index.php" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium transition whitespace-nowrap <?= ($categoryFilter === 0) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    All Genres
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?cat=<?= $cat['id'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium transition whitespace-nowrap flex items-center gap-2 <?= ($categoryFilter === (int)$cat['id']) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                        <i class="<?= htmlspecialchars($cat['icon']) ?> text-xs"></i>
                        <span><?= htmlspecialchars($cat['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Books Grid -->
        <?php if (empty($books)): ?>
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">No books found</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">We couldn't find any titles matching your query. Try searching with different keywords or reset the filter.</p>
                <a href="index.php" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    <i class="fas fa-undo"></i> Reset Filters
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($books as $b): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between custom-shadow-hover transition duration-200">
                        <div>
                            <!-- Cover Image -->
                            <div class="relative h-48 bg-slate-100 overflow-hidden">
                                <img src="<?= htmlspecialchars($b['cover_image']) ?>" 
                                     alt="<?= htmlspecialchars($b['title']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                                     onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'">
                                <div class="absolute top-3 right-3">
                                    <?php if ($b['available_copies'] > 0): ?>
                                        <span class="badge badge-success shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                            <?= $b['available_copies'] ?> Available
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger shadow-sm">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($b['category_name'])): ?>
                                    <div class="absolute bottom-3 left-3">
                                        <span class="px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-xs font-medium">
                                            <?= htmlspecialchars($b['category_name']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="p-5">
                                <h3 class="font-bold text-slate-800 text-base line-clamp-1 hover:text-indigo-600 transition" title="<?= htmlspecialchars($b['title']) ?>">
                                    <?= htmlspecialchars($b['title']) ?>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">By <?= htmlspecialchars($b['author']) ?></p>
                                
                                <p class="text-xs text-slate-600 mt-3 line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars($b['description'] ?: 'A premier book available for checkout in our library collection.') ?>
                                </p>

                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-slate-400"></i>
                                        <?= htmlspecialchars($b['rack_location'] ?: 'General Stacks') ?>
                                    </span>
                                    <span>ISBN: <?= htmlspecialchars($b['isbn'] ?: 'N/A') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action -->
                        <div class="p-5 pt-0">
                            <?php if (isLoggedIn()): ?>
                                <?php if (isAdmin()): ?>
                                    <a href="books.php?manage=1" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                        <i class="fas fa-cog"></i> Manage Book
                                    </a>
                                <?php else: ?>
                                    <?php if ($b['available_copies'] > 0): ?>
                                        <a href="actions/issue_action.php?action=member_borrow&book_id=<?= $b['id'] ?>" 
                                           onclick="return confirm('Do you want to borrow &quot;<?= addslashes(htmlspecialchars($b['title'])) ?>&quot; for 14 days?')"
                                           class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition shadow-md shadow-indigo-600/10">
                                            <i class="fas fa-book-reader"></i> Borrow Book
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed">
                                            Currently Unavailable
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition">
                                    <i class="fas fa-lock text-xs"></i> Sign in to Borrow
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Call To Action for Guests -->
        <?php if (!isLoggedIn()): ?>
            <div class="mt-16 bg-gradient-to-r from-indigo-700 to-indigo-900 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden shadow-2xl">
                <div class="relative z-10 max-w-2xl">
                    <h3 class="text-2xl sm:text-3xl font-extrabold mb-3">Ready to dive into endless knowledge?</h3>
                    <p class="text-indigo-200 text-sm sm:text-base mb-6">Create a free student membership today to start checking out books, tracking loan history, and building your academic reading lists.</p>
                    <div class="flex items-center gap-4 flex-wrap">
                        <a href="register.php" class="px-6 py-3 rounded-xl bg-white text-indigo-900 font-bold text-sm hover:bg-indigo-50 transition shadow-lg">
                            Register as Member
                        </a>
                        <a href="login.php" class="px-6 py-3 rounded-xl bg-indigo-800 text-white font-semibold text-sm hover:bg-indigo-950 transition border border-white/20">
                            Librarian / Member Sign In
                        </a>
                    </div>
                </div>
                <div class="absolute right-0 bottom-0 opacity-10 text-white text-9xl transform translate-x-8 translate-y-8 pointer-events-none">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
