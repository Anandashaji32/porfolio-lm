<?php
/**
 * Member's Personal Loan & Borrowing History
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Get all loans for this member
$stmt = $pdo->prepare("
    SELECT i.*, b.title as book_title, b.author, b.isbn, b.cover_image, b.rack_location 
    FROM issues i 
    JOIN books b ON i.book_id = b.id 
    WHERE i.user_id = ? 
    ORDER BY i.id DESC
");
$stmt->execute([$userId]);
$loans = $stmt->fetchAll();

$activeLoans = array_filter($loans, fn($l) => in_array($l['status'], ['issued', 'overdue']));
$pastLoans = array_filter($loans, fn($l) => $l['status'] === 'returned');

$pageTitle = 'My Borrowed Books';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">My Borrowed Books</h1>
                <p class="text-xs text-slate-500 mt-1">Keep track of your current loans, upcoming due dates, and past readings.</p>
            </div>

            <a href="books.php" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2 self-start sm:self-auto">
                <i class="fas fa-book-open"></i> Browse Library Catalog
            </a>
        </div>

        <!-- Section 1: Active Loans -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>Currently Checked Out</span>
                    <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold"><?= count($activeLoans) ?></span>
                </h2>
                <span class="text-xs text-slate-400">Maximum 3 books per member</span>
            </div>

            <?php if (empty($activeLoans)): ?>
                <div class="bg-white rounded-3xl p-8 text-center border border-slate-200">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-2 text-xl">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">No active books borrowed right now</p>
                    <p class="text-xs text-slate-400 mt-0.5">Explore our rich catalog to borrow your next read.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <?php foreach ($activeLoans as $loan): ?>
                        <?php 
                            $isOverdue = ($loan['status'] === 'overdue');
                            $daysRemaining = ceil((strtotime($loan['due_date']) - time()) / (60 * 60 * 24));
                        ?>
                        <div class="bg-white rounded-2xl border <?= $isOverdue ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' ?> p-5 custom-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex gap-4">
                                    <img src="<?= htmlspecialchars($loan['cover_image']) ?>" 
                                         alt="Cover" 
                                         class="w-16 h-24 object-cover rounded-xl shadow-sm flex-shrink-0"
                                         onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80'">
                                    <div class="flex-1">
                                        <div class="mb-1">
                                            <?php if ($isOverdue): ?>
                                                <span class="badge badge-danger">Overdue ($<?= number_format($loan['fine_amount'], 2) ?>)</span>
                                            <?php else: ?>
                                                <span class="badge badge-info"><?= $daysRemaining ?> days left</span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="font-bold text-slate-900 text-sm line-clamp-2"><?= htmlspecialchars($loan['book_title']) ?></h3>
                                        <p class="text-xs text-slate-500 mt-0.5">By <?= htmlspecialchars($loan['author']) ?></p>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Borrowed On:</span>
                                        <span class="font-medium"><?= date('M d, Y', strtotime($loan['issue_date'])) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Due Date:</span>
                                        <span class="font-bold <?= $isOverdue ? 'text-rose-600' : 'text-slate-800' ?>"><?= date('M d, Y', strtotime($loan['due_date'])) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Shelf Location:</span>
                                        <span class="font-medium"><?= htmlspecialchars($loan['rack_location'] ?: 'Main Stacks') ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <p class="text-[11px] text-slate-400 italic">
                                    <i class="fas fa-info-circle mr-1"></i> Return at the library circulation desk.
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section 2: Returned History -->
        <div>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900">Borrowing History</h2>
                <p class="text-xs text-slate-500">Previously returned volumes from your account</p>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 custom-shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase">
                                <th class="py-3.5 px-4">Book Title</th>
                                <th class="py-3.5 px-4">Author</th>
                                <th class="py-3.5 px-4">Borrowed Date</th>
                                <th class="py-3.5 px-4">Returned Date</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($pastLoans)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 text-xs">No completed return history yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pastLoans as $pl): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3 px-4 font-semibold text-slate-900 text-xs sm:text-sm">
                                            <?= htmlspecialchars($pl['book_title']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-600">
                                            <?= htmlspecialchars($pl['author']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-500">
                                            <?= date('M d, Y', strtotime($pl['issue_date'])) ?>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-500">
                                            <?= date('M d, Y', strtotime($pl['return_date'])) ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="badge badge-success"><i class="fas fa-check-circle text-xs mr-1"></i> Returned</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
