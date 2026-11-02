<?php
/**
 * User Profile & Security Settings
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pdo = getDBConnection();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$pageTitle = 'Account Profile & Security';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="lg:pl-64 flex-1">
    <main class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Profile & Security</h1>
            <p class="text-xs text-slate-500 mt-1">Manage your identity credentials, contact details, and account password.</p>
        </div>

        <!-- Profile Card -->
        <div class="bg-white rounded-3xl border border-slate-200 custom-shadow p-6 sm:p-8">
            <div class="flex items-center gap-5 pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center font-extrabold text-2xl shadow-lg shadow-indigo-100">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($user['name']) ?></h2>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge badge-purple">Librarian Admin</span>
                        <?php else: ?>
                            <span class="badge badge-info">Student Member</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="text-[11px] text-slate-400 mt-1">
                        <i class="fas fa-id-card mr-1"></i> Member ID: #ATH-<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?>
                        <span class="mx-1">•</span>
                        Enrolled: <?= date('M Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>

            <form action="actions/auth_action.php" method="POST" class="mt-6 space-y-5">
                <input type="hidden" name="action" value="update_profile">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Email Address (Read-only)</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly
                               class="w-full p-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Phone Contact</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+1-555-0100"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Department / Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Department of Computer Science"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Update Password</label>
                    <p class="text-xs text-slate-400 mb-2">Leave blank to keep your current password.</p>
                    <input type="password" name="new_password" minlength="6" placeholder="Enter new password (optional)"
                           class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800">
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Profile Changes
                    </button>
                </div>
            </form>
        </div>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
