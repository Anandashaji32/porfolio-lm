<?php
/**
 * Header layout component
 */
require_once __DIR__ . '/auth.php';

$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? 'Library Management System';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Athenaeum Library</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 min-h-screen flex flex-col antialiased">

<?php if (isLoggedIn()): ?>
    <!-- Top Header for Authenticated Users -->
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <a href="dashboard.php" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center shadow-md shadow-indigo-100">
                        <i class="fas fa-book-reader text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-700">Athenaeum</span>
                        <span class="text-xs block text-slate-400 font-medium tracking-wide">Library System</span>
                    </div>
                </a>
            </div>

            <!-- Global Search & Actions -->
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="../index.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition border border-slate-200" title="Return to Anandha Shaji Portfolio">
                    <i class="fas fa-arrow-left text-slate-400"></i>
                    <span class="hidden sm:inline">Portfolio</span>
                </a>

                <a href="books.php" class="hidden md:flex items-center gap-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-full transition">
                    <i class="fas fa-search text-slate-400"></i>
                    <span>Quick Search Catalog...</span>
                </a>

                <!-- User Dropdown & Status -->
                <div class="relative group">
                    <div class="flex items-center gap-3 cursor-pointer py-1">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-semibold text-slate-800 leading-tight"><?= htmlspecialchars($currentUser['name']) ?></div>
                            <div class="text-xs uppercase tracking-wider font-semibold <?= $currentUser['role'] === 'admin' ? 'text-indigo-600' : 'text-emerald-600' ?>">
                                <?= htmlspecialchars($currentUser['role']) ?>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full ring-2 ring-indigo-100 bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1 hidden group-hover:block transition-all transform origin-top-right">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs text-slate-500">Signed in as</p>
                            <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
                        </div>
                        <a href="profile.php" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <i class="fas fa-id-badge text-slate-400 w-4"></i> My Profile
                        </a>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="categories.php" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <i class="fas fa-tags text-slate-400 w-4"></i> Categories
                        </a>
                        <?php endif; ?>
                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-sign-out-alt text-red-400 w-4"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>
