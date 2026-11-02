<?php
/**
 * Members Management (Admin Only)
 */
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();

$search = trim($_GET['search'] ?? '');

$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM issues WHERE user_id = u.id AND status IN ('issued', 'overdue')) as active_loans_count,
        (SELECT SUM(fine_amount) FROM issues WHERE user_id = u.id) as total_fines
        FROM users u 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY u.role ASC, u.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

$pageTitle = 'Library Members Directory';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Members Directory</h1>
                <p class="text-xs text-slate-500 mt-1">Manage student accounts, librarian staff roles, and loan privileges.</p>
            </div>

            <button data-modal-target="add-member-modal" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2 self-start sm:self-auto">
                <i class="fas fa-user-plus"></i> Add New Member
            </button>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 custom-shadow flex items-center justify-between">
            <form action="members.php" method="GET" class="flex items-center gap-3 w-full sm:w-80">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, or phone..."
                           class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold hover:bg-indigo-700 transition">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="members.php" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl" title="Clear Search">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                <?php endif; ?>
            </form>
            <span class="text-xs text-slate-400 hidden sm:block"><?= count($members) ?> registered accounts</span>
        </div>

        <!-- Members Table -->
        <div class="bg-white rounded-3xl border border-slate-200 custom-shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase">
                            <th class="py-3.5 px-4">Member Name</th>
                            <th class="py-3.5 px-4">Contact Info</th>
                            <th class="py-3.5 px-4">Role</th>
                            <th class="py-3.5 px-4 text-center">Active Loans</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($members as $m): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            <?= strtoupper(substr($m['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs sm:text-sm"><?= htmlspecialchars($m['name']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($m['address'] ?: 'No address specified') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-xs font-medium text-slate-700"><?= htmlspecialchars($m['email']) ?></div>
                                    <div class="text-[11px] text-slate-400"><?= htmlspecialchars($m['phone'] ?: 'N/A') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($m['role'] === 'admin'): ?>
                                        <span class="badge badge-purple"><i class="fas fa-shield-alt text-[10px] mr-1"></i> Librarian</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><i class="fas fa-user-graduate text-[10px] mr-1"></i> Student</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $m['active_loans_count'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= $m['active_loans_count'] ?> active
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($m['status'] === 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ($m['id'] != $currentUser['id']): ?>
                                        <a href="actions/member_action.php?action=toggle_status&id=<?= $m['id'] ?>" 
                                           onclick="return confirm('Toggle account status for <?= addslashes(htmlspecialchars($m['name'])) ?>?')"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg <?= $m['status'] === 'active' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' ?> transition">
                                            <?= $m['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Add Member Modal -->
<div id="add-member-modal" class="modal">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 custom-shadow border border-slate-200 relative">
        <button data-modal-close class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fas fa-times"></i>
        </button>
        <div class="mb-5">
            <h3 class="text-xl font-bold text-slate-900">Add New Library Member</h3>
            <p class="text-xs text-slate-500 mt-1">Directly enroll a student or staff member into the system.</p>
        </div>

        <form action="actions/member_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Emily Clark"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="emily@university.edu"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Temporary Password</label>
                    <input type="password" name="password" value="member123" required
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">System Role</label>
                    <select name="role" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                        <option value="member">Student / Member</option>
                        <option value="admin">Librarian / Admin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="+1-555-0144"
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Department</label>
                    <input type="text" name="address" placeholder="e.g. Physics Dept"
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" data-modal-close class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Create Account</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
