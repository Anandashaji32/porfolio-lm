<?php
/**
 * Sidebar Navigation Component
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isManageBooks = isset($_GET['manage']) && $_GET['manage'] == '1';
?>

<!-- Mobile Sidebar Backdrop Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden lg:hidden"></div>

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed top-16 bottom-0 left-0 z-40 w-64 bg-white border-r border-slate-200 transition-transform -translate-x-full lg:translate-x-0 flex flex-col justify-between">
    <div class="p-4 space-y-6 overflow-y-auto">
        <!-- Main Section -->
        <div>
            <div class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">Menu</div>
            <nav class="space-y-1">
                <a href="dashboard.php" class="sidebar-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie w-5 mr-3 text-base"></i>
                    <span>Dashboard</span>
                </a>

                <a href="books.php" class="sidebar-link <?= ($currentPage === 'books' && !$isManageBooks) ? 'active' : '' ?>">
                    <i class="fas fa-book-open w-5 mr-3 text-base"></i>
                    <span>Browse Catalog</span>
                </a>

                <?php if (!isAdmin()): ?>
                <a href="my_books.php" class="sidebar-link <?= ($currentPage === 'my_books') ? 'active' : '' ?>">
                    <i class="fas fa-bookmark w-5 mr-3 text-base"></i>
                    <span>My Borrowed Books</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <?php if (isAdmin()): ?>
        <!-- Librarian Administration Section -->
        <div>
            <div class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">Management</div>
            <nav class="space-y-1">
                <a href="issues.php" class="sidebar-link <?= ($currentPage === 'issues') ? 'active' : '' ?>">
                    <i class="fas fa-exchange-alt w-5 mr-3 text-base"></i>
                    <span>Issue & Return</span>
                </a>

                <a href="books.php?manage=1" class="sidebar-link <?= ($currentPage === 'books' && $isManageBooks) ? 'active' : '' ?>">
                    <i class="fas fa-boxes w-5 mr-3 text-base"></i>
                    <span>Manage Books</span>
                </a>

                <a href="categories.php" class="sidebar-link <?= ($currentPage === 'categories') ? 'active' : '' ?>">
                    <i class="fas fa-tags w-5 mr-3 text-base"></i>
                    <span>Categories</span>
                </a>

                <a href="members.php" class="sidebar-link <?= ($currentPage === 'members') ? 'active' : '' ?>">
                    <i class="fas fa-users w-5 mr-3 text-base"></i>
                    <span>Members</span>
                </a>
            </nav>
        </div>
        <?php endif; ?>

        <!-- Account Section -->
        <div>
            <div class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">Preferences</div>
            <nav class="space-y-1">
                <a href="profile.php" class="sidebar-link <?= ($currentPage === 'profile') ? 'active' : '' ?>">
                    <i class="fas fa-user-cog w-5 mr-3 text-base"></i>
                    <span>Profile & Security</span>
                </a>
                <a href="logout.php" class="sidebar-link text-red-600 hover:bg-red-50 hover:text-red-700">
                    <i class="fas fa-sign-out-alt w-5 mr-3 text-base"></i>
                    <span>Log Out</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- System Info Badge at Bottom -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                <i class="fas fa-server"></i>
            </div>
            <div class="text-xs">
                <div class="font-semibold text-slate-700">Engine Status</div>
                <div class="text-emerald-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <?= strtoupper(DB_DRIVER) ?> Active
                </div>
            </div>
        </div>
    </div>
</aside>
