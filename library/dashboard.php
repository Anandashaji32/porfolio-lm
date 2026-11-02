<?php
/**
 * Main User Dashboard (Role-adaptive: Admin / Member)
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();
$isAdmin = isAdmin();

if ($isAdmin) {
    // Admin Analytics
    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn() ?: 0;
    $totalCopies = $pdo->query("SELECT SUM(total_copies) FROM books")->fetchColumn() ?: 0;
    $availableCopies = $pdo->query("SELECT SUM(available_copies) FROM books")->fetchColumn() ?: 0;
    $activeLoans = $pdo->query("SELECT COUNT(*) FROM issues WHERE status = 'issued'")->fetchColumn() ?: 0;
    $overdueLoans = $pdo->query("SELECT COUNT(*) FROM issues WHERE status = 'overdue'")->fetchColumn() ?: 0;
    $totalMembers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn() ?: 0;
    $totalFines = $pdo->query("SELECT SUM(fine_amount) FROM issues WHERE status IN ('issued', 'overdue')")->fetchColumn() ?: 0;

    // Recent Issues / Returns
    $recentIssues = $pdo->query("
        SELECT i.*, b.title as book_title, b.isbn, u.name as member_name, u.email as member_email 
        FROM issues i 
        JOIN books b ON i.book_id = b.id 
        JOIN users u ON i.user_id = u.id 
        ORDER BY i.id DESC LIMIT 6
    ")->fetchAll();

    // Books dropdown for quick issue modal
    $availableBooksList = $pdo->query("SELECT id, title, available_copies FROM books WHERE available_copies > 0 ORDER BY title ASC")->fetchAll();
    $activeMembersList = $pdo->query("SELECT id, name, email FROM users WHERE role = 'member' AND status = 'active' ORDER BY name ASC")->fetchAll();

    // Recent System Activity Logs
    $activityLogs = $pdo->query("
        SELECT a.*, u.name as user_name 
        FROM activity_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.id DESC LIMIT 6
    ")->fetchAll();
} else {
    // Member Analytics
    $userId = $currentUser['id'];
    $myActiveLoans = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id = ? AND status = 'issued'");
    $myActiveLoans->execute([$userId]);
    $activeLoansCount = $myActiveLoans->fetchColumn() ?: 0;

    $myOverdueLoans = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id = ? AND status = 'overdue'");
    $myOverdueLoans->execute([$userId]);
    $overdueCount = $myOverdueLoans->fetchColumn() ?: 0;

    $myReturnedLoans = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id = ? AND status = 'returned'");
    $myReturnedLoans->execute([$userId]);
    $returnedCount = $myReturnedLoans->fetchColumn() ?: 0;

    $myFines = $pdo->prepare("SELECT SUM(fine_amount) FROM issues WHERE user_id = ?");
    $myFines->execute([$userId]);
    $finesAmount = $myFines->fetchColumn() ?: 0.0;

    // Currently Borrowed Books for this member
    $currentBooksStmt = $pdo->prepare("
        SELECT i.*, b.title as book_title, b.author, b.cover_image, b.rack_location 
        FROM issues i 
        JOIN books b ON i.book_id = b.id 
        WHERE i.user_id = ? AND i.status IN ('issued', 'overdue')
        ORDER BY i.due_date ASC
    ");
    $currentBooksStmt->execute([$userId]);
    $myCurrentBooks = $currentBooksStmt->fetchAll();
}

$pageTitle = $isAdmin ? 'Librarian Overview' : 'Member Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-8">
        
        <!-- Welcome Hero Banner -->
        <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-indigo-100 text-xs font-semibold backdrop-blur-md mb-2">
                        <i class="fas fa-calendar-day text-amber-300"></i> <?= date('l, F j, Y') ?>
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Hello, <?= htmlspecialchars($currentUser['name']) ?>! 👋
                    </h1>
                    <p class="text-indigo-100 text-sm mt-1 max-w-xl">
                        <?= $isAdmin 
                            ? 'Welcome to the Library Administration Portal. Monitor catalog transactions, circulation, and member requests.' 
                            : 'Welcome to your reading dashboard. Track borrowed books, return schedules, and browse new arrivals.' ?>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <?php if ($isAdmin): ?>
                        <button data-modal-target="quick-issue-modal" class="px-4 py-2.5 rounded-xl bg-white text-indigo-700 font-semibold text-xs sm:text-sm hover:bg-indigo-50 transition shadow-md flex items-center gap-2">
                            <i class="fas fa-hand-holding"></i> Quick Issue Book
                        </button>
                        <a href="books.php?manage=1" class="px-4 py-2.5 rounded-xl bg-indigo-500/40 border border-white/20 text-white font-semibold text-xs sm:text-sm hover:bg-indigo-500/60 transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add Book
                        </a>
                    <?php else: ?>
                        <a href="books.php" class="px-4 py-2.5 rounded-xl bg-white text-indigo-700 font-semibold text-xs sm:text-sm hover:bg-indigo-50 transition shadow-md flex items-center gap-2">
                            <i class="fas fa-book-open"></i> Browse Book Catalog
                        </a>
                        <a href="my_books.php" class="px-4 py-2.5 rounded-xl bg-indigo-500/40 border border-white/20 text-white font-semibold text-xs sm:text-sm hover:bg-indigo-500/60 transition flex items-center gap-2">
                            <i class="fas fa-bookmark"></i> My Loans
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Background subtle pattern -->
            <div class="absolute -right-8 -bottom-8 opacity-10 text-white text-9xl pointer-events-none">
                <i class="fas fa-university"></i>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <!-- ADMIN DASHBOARD METRICS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Titles -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Catalog Titles</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($totalBooks) ?></h3>
                        <p class="text-xs text-slate-400 mt-1"><?= number_format($totalCopies) ?> total volume copies</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-book"></i>
                    </div>
                </div>

                <!-- Available on Shelf -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Available Shelf</p>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format($availableCopies) ?></h3>
                        <p class="text-xs text-emerald-600 font-medium mt-1">Ready for checkout</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Borrows</p>
                        <h3 class="text-2xl font-bold text-sky-600 mt-1"><?= number_format($activeLoans) ?></h3>
                        <p class="text-xs text-slate-400 mt-1">Currently with readers</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                </div>

                <!-- Overdue Books -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overdue Returns</p>
                        <h3 class="text-2xl font-bold text-rose-600 mt-1"><?= number_format($overdueLoans) ?></h3>
                        <p class="text-xs text-rose-500 font-medium mt-1">Pending return</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Circulation & Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Transactions (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 custom-shadow overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Recent Circulation Activity</h2>
                            <p class="text-xs text-slate-500">Live feed of books issued and returned</p>
                        </div>
                        <a href="issues.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                            View All <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase">
                                    <th class="py-3 px-4">Book Title</th>
                                    <th class="py-3 px-4">Borrower</th>
                                    <th class="py-3 px-4">Due Date</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($recentIssues)): ?>
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400">No circulation records yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentIssues as $iss): ?>
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-3 px-4 font-medium text-slate-900 max-w-[200px] truncate" title="<?= htmlspecialchars($iss['book_title']) ?>">
                                                <?= htmlspecialchars($iss['book_title']) ?>
                                            </td>
                                            <td class="py-3 px-4 text-slate-600">
                                                <div class="font-medium text-slate-800 text-xs"><?= htmlspecialchars($iss['member_name']) ?></div>
                                                <div class="text-[11px] text-slate-400"><?= htmlspecialchars($iss['member_email']) ?></div>
                                            </td>
                                            <td class="py-3 px-4 text-xs text-slate-600">
                                                <?= date('M d, Y', strtotime($iss['due_date'])) ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <?php if ($iss['status'] === 'issued'): ?>
                                                    <span class="badge badge-info">Borrowed</span>
                                                <?php elseif ($iss['status'] === 'overdue'): ?>
                                                    <span class="badge badge-danger">Overdue ($<?= number_format($iss['fine_amount'], 2) ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Returned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <?php if ($iss['status'] !== 'returned'): ?>
                                                    <a href="actions/issue_action.php?action=return&id=<?= $iss['id'] ?>" 
                                                       onclick="return confirm('Mark this book as returned?')"
                                                       class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold transition">
                                                        <i class="fas fa-undo text-[10px]"></i> Return
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-xs text-slate-400"><i class="fas fa-check text-emerald-500"></i> Done</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- System Logs (1 col) -->
                <div class="bg-white rounded-3xl border border-slate-200 custom-shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">Audit Log</h2>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-md font-medium">Recent</span>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($activityLogs)): ?>
                            <p class="text-xs text-slate-400">No activity recorded yet.</p>
                        <?php else: ?>
                            <?php foreach ($activityLogs as $log): ?>
                                <div class="flex items-start gap-3 text-xs border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-clock text-[11px]"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-800"><?= htmlspecialchars($log['action']) ?></span>
                                            <span class="text-[10px] text-slate-400"><?= date('H:i, M d', strtotime($log['created_at'])) ?></span>
                                        </div>
                                        <p class="text-slate-500 mt-0.5"><?= htmlspecialchars($log['details']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Issue Modal -->
            <div id="quick-issue-modal" class="modal">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative">
                    <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="mb-5">
                        <h3 class="text-xl font-bold text-slate-900">Issue Book to Member</h3>
                        <p class="text-xs text-slate-500 mt-1">Select a book and registered reader to issue a copy.</p>
                    </div>

                    <form action="actions/issue_action.php" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="issue">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Select Book</label>
                            <select name="book_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Choose Book from Stacks --</option>
                                <?php foreach ($availableBooksList as $bk): ?>
                                    <option value="<?= $bk['id'] ?>"><?= htmlspecialchars($bk['title']) ?> (<?= $bk['available_copies'] ?> in stock)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Select Member</label>
                            <select name="user_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Choose Member --</option>
                                <?php foreach ($activeMembersList as $mem): ?>
                                    <option value="<?= $mem['id'] ?>"><?= htmlspecialchars($mem['name']) ?> (<?= htmlspecialchars($mem['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Due Return Date</label>
                            <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Notes (Optional)</label>
                            <input type="text" name="notes" placeholder="e.g. Standard 14-day checkout"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" data-modal-close class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold transition">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20">Confirm Issue</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- MEMBER DASHBOARD METRICS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Active Loans -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Borrows</p>
                        <h3 class="text-2xl font-bold text-indigo-600 mt-1"><?= number_format($activeLoansCount) ?></h3>
                        <p class="text-xs text-slate-400 mt-1">Currently checked out</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-book-reader"></i>
                    </div>
                </div>

                <!-- Overdue Books -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overdue</p>
                        <h3 class="text-2xl font-bold <?= $overdueCount > 0 ? 'text-rose-600' : 'text-slate-800' ?> mt-1"><?= number_format($overdueCount) ?></h3>
                        <p class="text-xs <?= $overdueCount > 0 ? 'text-rose-500 font-medium' : 'text-slate-400' ?> mt-1">
                            <?= $overdueCount > 0 ? 'Requires immediate return' : 'All clear!' ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl <?= $overdueCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-400' ?> flex items-center justify-center text-xl">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <!-- Returned Books -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Returned</p>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format($returnedCount) ?></h3>
                        <p class="text-xs text-emerald-600 font-medium mt-1">Completed readings</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>

                <!-- Outstanding Fines -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 custom-shadow flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fines Assessed</p>
                        <h3 class="text-2xl font-bold <?= $finesAmount > 0 ? 'text-amber-600' : 'text-slate-800' ?> mt-1">$<?= number_format($finesAmount, 2) ?></h3>
                        <p class="text-xs text-slate-400 mt-1">Standard rate: $1.00/day</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>

            <!-- Member Current Loans List -->
            <div class="bg-white rounded-3xl border border-slate-200 custom-shadow overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">My Currently Borrowed Books</h2>
                        <p class="text-xs text-slate-500">Books currently checked out in your custody</p>
                    </div>
                    <a href="books.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20">
                        <i class="fas fa-plus"></i> Borrow Another Book
                    </a>
                </div>

                <div class="p-6">
                    <?php if (empty($myCurrentBooks)): ?>
                        <div class="text-center py-10">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-800">You have no active loans right now</h3>
                            <p class="text-xs text-slate-500 mt-1">Browse our collection of hundreds of titles and checkout what interests you!</p>
                            <a href="books.php" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-xl hover:bg-indigo-700 transition">
                                Explore Catalog
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($myCurrentBooks as $book): ?>
                                <?php 
                                    $isOverdue = ($book['status'] === 'overdue');
                                    $daysRemaining = ceil((strtotime($book['due_date']) - time()) / (60 * 60 * 24));
                                ?>
                                <div class="p-4 rounded-2xl border <?= $isOverdue ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-white' ?> flex gap-4">
                                    <img src="<?= htmlspecialchars($book['cover_image']) ?>" 
                                         alt="<?= htmlspecialchars($book['book_title']) ?>" 
                                         class="w-20 h-28 object-cover rounded-xl shadow-sm flex-shrink-0"
                                         onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'">
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                                    Issued: <?= date('M d', strtotime($book['issue_date'])) ?>
                                                </span>
                                                <?php if ($isOverdue): ?>
                                                    <span class="badge badge-danger">Overdue ($<?= number_format($book['fine_amount'], 2) ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info"><?= $daysRemaining ?> days left</span>
                                                <?php endif; ?>
                                            </div>
                                            <h4 class="font-bold text-slate-900 text-sm mt-1 line-clamp-1"><?= htmlspecialchars($book['book_title']) ?></h4>
                                            <p class="text-xs text-slate-500 mt-0.5">By <?= htmlspecialchars($book['author']) ?></p>
                                        </div>

                                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                            <span class="text-slate-500">
                                                Due date: <strong class="<?= $isOverdue ? 'text-rose-600' : 'text-slate-800' ?>"><?= date('M d, Y', strtotime($book['due_date'])) ?></strong>
                                            </span>
                                            <span class="text-slate-400 text-[11px]"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($book['rack_location'] ?: 'Stacks') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
