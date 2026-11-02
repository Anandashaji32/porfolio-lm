<?php
/**
 * Book Circulation (Issue & Return) Management
 */
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();

// Status Filter
$statusFilter = $_GET['status'] ?? 'active'; // 'active' (issued + overdue), 'returned', 'all'

$sql = "SELECT i.*, b.title as book_title, b.isbn, b.rack_location, u.name as member_name, u.email as member_email, u.phone as member_phone 
        FROM issues i 
        JOIN books b ON i.book_id = b.id 
        JOIN users u ON i.user_id = u.id 
        WHERE 1=1";

if ($statusFilter === 'active') {
    $sql .= " AND i.status IN ('issued', 'overdue')";
} elseif ($statusFilter === 'returned') {
    $sql .= " AND i.status = 'returned'";
} elseif ($statusFilter === 'overdue') {
    $sql .= " AND i.status = 'overdue'";
}

$sql .= " ORDER BY i.id DESC";
$issues = $pdo->query($sql)->fetchAll();

// Available books and members for issuance modal
$availableBooksList = $pdo->query("SELECT id, title, available_copies, rack_location FROM books WHERE available_copies > 0 ORDER BY title ASC")->fetchAll();
$activeMembersList = $pdo->query("SELECT id, name, email FROM users WHERE role = 'member' AND status = 'active' ORDER BY name ASC")->fetchAll();

$pageTitle = 'Circulation & Book Loans';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Circulation Management</h1>
                <p class="text-xs text-slate-500 mt-1">Issue library books to students, handle returns, and assess overdue fines.</p>
            </div>

            <button data-modal-target="issue-book-modal" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2 self-start sm:self-auto">
                <i class="fas fa-hand-holding"></i> Issue Book to Member
            </button>
        </div>

        <!-- Filter Pills & Search -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 custom-shadow flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="issues.php?status=active" class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition <?= ($statusFilter === 'active') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Active Loans
                </a>
                <a href="issues.php?status=overdue" class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition <?= ($statusFilter === 'overdue') ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Overdue Only
                </a>
                <a href="issues.php?status=returned" class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition <?= ($statusFilter === 'returned') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Returned History
                </a>
                <a href="issues.php?status=all" class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition <?= ($statusFilter === 'all') ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    All Records
                </a>
            </div>

            <!-- Live Table Filter -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" data-table-search="issues-table" placeholder="Filter this list..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <!-- Issues Table -->
        <div class="bg-white rounded-3xl border border-slate-200 custom-shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table id="issues-table" class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase">
                            <th class="py-3.5 px-4">Book Title</th>
                            <th class="py-3.5 px-4">Member Info</th>
                            <th class="py-3.5 px-4">Issue Date</th>
                            <th class="py-3.5 px-4">Due Date</th>
                            <th class="py-3.5 px-4">Status & Fine</th>
                            <th class="py-3.5 px-4 text-right">Circulation Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($issues)): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">No circulation records found for this filter.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($issues as $row): ?>
                                <?php 
                                    $isOverdue = ($row['status'] === 'overdue');
                                    $isReturned = ($row['status'] === 'returned');
                                ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1" title="<?= htmlspecialchars($row['book_title']) ?>">
                                            <?= htmlspecialchars($row['book_title']) ?>
                                        </div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                            <span>ISBN: <?= htmlspecialchars($row['isbn'] ?: 'N/A') ?></span>
                                            <span>•</span>
                                            <span>Loc: <?= htmlspecialchars($row['rack_location'] ?: 'General') ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-800 text-xs"><?= htmlspecialchars($row['member_name']) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= htmlspecialchars($row['member_email']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-600">
                                        <?= date('M d, Y', strtotime($row['issue_date'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-medium <?= $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' ?>">
                                        <?= date('M d, Y', strtotime($row['due_date'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($isReturned): ?>
                                            <span class="badge badge-success">Returned (<?= date('M d', strtotime($row['return_date'])) ?>)</span>
                                        <?php elseif ($isOverdue): ?>
                                            <span class="badge badge-danger">Overdue ($<?= number_format($row['fine_amount'], 2) ?>)</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Borrowed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <?php if (!$isReturned): ?>
                                            <a href="actions/issue_action.php?action=return&id=<?= $row['id'] ?>" 
                                               onclick="return confirm('Confirm book return for <?= addslashes(htmlspecialchars($row['member_name'])) ?>?')"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-semibold transition border border-emerald-200">
                                                <i class="fas fa-check"></i> Process Return
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 font-medium">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Issue Book Modal -->
<div id="issue-book-modal" class="modal">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative">
        <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fas fa-times"></i>
        </button>
        <div class="mb-5">
            <h3 class="text-xl font-bold text-slate-900">Issue Book to Member</h3>
            <p class="text-xs text-slate-500 mt-1">Assign a book copy to a registered library patron.</p>
        </div>

        <form action="actions/issue_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="issue">

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Book Title <span class="text-red-500">*</span></label>
                <select name="book_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    <option value="">-- Select Book from Catalog --</option>
                    <?php foreach ($availableBooksList as $bk): ?>
                        <option value="<?= $bk['id'] ?>">
                            <?= htmlspecialchars($bk['title']) ?> (<?= $bk['available_copies'] ?> copies, <?= htmlspecialchars($bk['rack_location'] ?: 'General') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Borrowing Member <span class="text-red-500">*</span></label>
                <select name="user_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    <option value="">-- Select Member Account --</option>
                    <?php foreach ($activeMembersList as $mem): ?>
                        <option value="<?= $mem['id'] ?>">
                            <?= htmlspecialchars($mem['name']) ?> (<?= htmlspecialchars($mem['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Due Return Date <span class="text-red-500">*</span></label>
                <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Notes / Loan Remarks</label>
                <input type="text" name="notes" placeholder="e.g. Standard 14-day checkout for coursework"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" data-modal-close class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Confirm Issue</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
