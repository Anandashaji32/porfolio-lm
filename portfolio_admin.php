<?php
/**
 * Portfolio Admin Portal Dashboard
 */
require_once __DIR__ . '/portfolio_db.php';
requirePortfolioLogin();

$pdo = getPortfolioDB();
$currentUser = getPortfolioUser();
$flash = getPortfolioFlash();

// Fetch Profile info
$profile = $pdo->query("SELECT * FROM portfolio_profile WHERE id = 1")->fetch() ?: [];

// Fetch Inquiries / Messages
$messages = $pdo->query("SELECT * FROM portfolio_messages ORDER BY id DESC")->fetchAll();
$totalMessages = count($messages);
$unreadCount = count(array_filter($messages, fn($m) => $m['status'] === 'unread'));

// Check Library DB stats for linked project status
$libraryBookCount = 0;
$libraryUserCount = 0;
$libraryDbPath = __DIR__ . '/library/data/library.db';
if (file_exists($libraryDbPath)) {
    try {
        $libPdo = new PDO("sqlite:" . $libraryDbPath);
        $libraryBookCount = $libPdo->query("SELECT COUNT(*) FROM books")->fetchColumn() ?: 0;
        $libraryUserCount = $libPdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Dashboard | Anandha Shaji</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
        }
        .glass-card {
            background: rgba(18, 24, 38, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-slate-900/80 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="index.php" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 via-sky-500 to-teal-400 text-slate-950 flex items-center justify-center font-black text-sm shadow-md shadow-teal-500/20">
                        AS
                    </div>
                    <div>
                        <span class="font-bold text-sm text-white tracking-wide">Anandha Shaji</span>
                        <span class="text-[10px] block text-teal-400 uppercase font-semibold tracking-wider">Admin Portal</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 hover:text-white transition flex items-center gap-1.5 border border-slate-700">
                    <i class="fas fa-globe text-teal-400"></i>
                    <span>Live Portfolio</span>
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                </a>

                <a href="library/index.php" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-indigo-950/60 hover:bg-indigo-900 text-xs font-semibold text-indigo-300 transition flex items-center gap-1.5 border border-indigo-800">
                    <i class="fas fa-book-reader text-indigo-400"></i>
                    <span>Library System</span>
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                </a>

                <a href="portfolio_logout.php" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold transition border border-rose-500/20">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sign Out
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Flash Alert -->
        <?php if ($flash): ?>
            <div class="p-4 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between border <?= ($flash['type'] === 'success') ? 'bg-emerald-950/60 border-emerald-800 text-emerald-300' : 'bg-rose-950/60 border-rose-800 text-rose-300' ?>">
                <div class="flex items-center gap-3">
                    <i class="fas <?= ($flash['type'] === 'success') ? 'fa-check-circle' : 'fa-info-circle' ?> text-lg"></i>
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900/60 via-slate-900 to-teal-950/60 border border-slate-800 p-6 sm:p-8">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-300 text-xs font-semibold mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span> Portfolio Control Center
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Welcome back, <?= htmlspecialchars($currentUser['name']) ?></h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                        Monitor recruiter inquiries, update your profile credentials, and manage the linked Athenaeum Library Management System.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="#messages" class="px-4 py-2.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-teal-500/10">
                        <i class="fas fa-inbox"></i> View Inquiries (<?= $unreadCount ?> new)
                    </a>
                    <a href="#profile-edit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs sm:text-sm transition border border-slate-700 flex items-center gap-2">
                        <i class="fas fa-user-edit"></i> Edit Bio
                    </a>
                </div>
            </div>
        </div>

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Unread Inquiries -->
            <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Unread Messages</span>
                    <h3 class="text-2xl font-black text-teal-400 mt-1"><?= $unreadCount ?></h3>
                    <p class="text-[11px] text-slate-400 mt-0.5"><?= $totalMessages ?> total messages</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
            </div>

            <!-- Linked Library Status -->
            <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Library Project</span>
                    <h3 class="text-2xl font-black text-indigo-400 mt-1">Active</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5"><?= $libraryBookCount ?> books • <?= $libraryUserCount ?> users</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xl">
                    <i class="fas fa-book"></i>
                </div>
            </div>

            <!-- Total Showcase Projects -->
            <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Portfolio Projects</span>
                    <h3 class="text-2xl font-black text-sky-400 mt-1">4</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">1 Featured • 3 Showcase</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-xl">
                    <i class="fas fa-laptop-code"></i>
                </div>
            </div>

            <!-- Backend Engine Status -->
            <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Engine Stack</span>
                    <h3 class="text-2xl font-black text-emerald-400 mt-1">PHP 8.2</h3>
                    <p class="text-[11px] text-emerald-400 mt-0.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> SQLite PDO Online
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl">
                    <i class="fas fa-server"></i>
                </div>
            </div>
        </div>

        <!-- Linked Library Project Spotlight Card -->
        <div class="glass-card rounded-3xl p-6 border-indigo-500/30 bg-gradient-to-br from-indigo-950/40 to-slate-900 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-white">Athenaeum Library Management System</h2>
                        <span class="badge px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Primary Linked Project</span>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 max-w-xl">
                        Integrated full-stack system with dual-role authentication (Librarian/Student), book circulation desk, catalog search, automated late fine engine, and shelf rack locations.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0 w-full md:w-auto">
                <a href="library/index.php" target="_blank" class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20">
                    <i class="fas fa-play"></i> Open Library App
                </a>
                <a href="library/login.php" target="_blank" class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition border border-slate-700 flex items-center justify-center gap-2">
                    <i class="fas fa-key"></i> Library Login
                </a>
            </div>
        </div>

        <!-- Section 1: Inquiries / Messages Inbox -->
        <div id="messages" class="glass-card rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>Recruiter & Visitor Inquiries</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="px-2 py-0.5 rounded-full bg-teal-500 text-slate-950 font-black text-xs"><?= $unreadCount ?> unread</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Messages sent via the portfolio contact form</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-900/80 border-b border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-5">Sender</th>
                            <th class="py-3.5 px-5">Subject & Message</th>
                            <th class="py-3.5 px-5">Date Received</th>
                            <th class="py-3.5 px-5">Status</th>
                            <th class="py-3.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500 text-xs">No inquiries received yet. Submit a test inquiry via the contact form on your portfolio!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <tr class="hover:bg-slate-900/40 transition <?= ($msg['status'] === 'unread') ? 'bg-teal-950/10' : '' ?>">
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-white flex items-center gap-2">
                                            <?php if ($msg['status'] === 'unread'): ?>
                                                <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($msg['name']) ?>
                                        </div>
                                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-[11px] text-teal-400 hover:underline"><?= htmlspecialchars($msg['email']) ?></a>
                                    </td>
                                    <td class="py-4 px-5 max-w-md">
                                        <div class="font-semibold text-slate-200"><?= htmlspecialchars($msg['subject'] ?: 'Inquiry') ?></div>
                                        <p class="text-xs text-slate-400 mt-1 line-clamp-2 leading-relaxed"><?= htmlspecialchars($msg['message']) ?></p>
                                    </td>
                                    <td class="py-4 px-5 text-xs text-slate-400 whitespace-nowrap">
                                        <?= date('M d, Y • H:i', strtotime($msg['created_at'])) ?>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <?php if ($msg['status'] === 'unread'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">New / Unread</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-400">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="portfolio_action.php?action=toggle_msg&id=<?= $msg['id'] ?>" 
                                               class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition" title="Toggle Read Status">
                                                <i class="fas <?= ($msg['status'] === 'unread') ? 'fa-check' : 'fa-undo' ?> mr-1"></i>
                                                <?= ($msg['status'] === 'unread') ? 'Mark Read' : 'Unread' ?>
                                            </a>
                                            <a href="portfolio_action.php?action=delete_msg&id=<?= $msg['id'] ?>" 
                                               onclick="return confirm('Delete this message permanently?')"
                                               class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs transition" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
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

        <!-- Section 2: Edit Profile & Password -->
        <div id="profile-edit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Bio Form -->
            <div class="lg:col-span-2 glass-card rounded-3xl p-6 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-white">Customize Portfolio Identity</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Edit headline, biography, and contact info displayed on your public portfolio</p>
                </div>

                <form action="portfolio_action.php" method="POST" class="space-y-4 text-xs sm:text-sm">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Full Legal Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name'] ?? 'Anandha Shaji') ?>" required
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Professional Headline</label>
                            <input type="text" name="headline" value="<?= htmlspecialchars($profile['headline'] ?? 'Full-Stack Developer & Software Engineer') ?>" required
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Short Bio (Hero Section)</label>
                        <textarea name="bio" rows="2" class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">About Me Detailed Paragraph</label>
                        <textarea name="about" rows="3" class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400"><?= htmlspecialchars($profile['about'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Public Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? 'anandha.shaji@example.com') ?>"
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Phone</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '+91 98765 43210') ?>"
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Location</label>
                            <input type="text" name="location" value="<?= htmlspecialchars($profile['location'] ?? 'India') ?>"
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">GitHub Profile URL</label>
                            <input type="url" name="github_url" value="<?= htmlspecialchars($profile['github_url'] ?? 'https://github.com') ?>"
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">LinkedIn Profile URL</label>
                            <input type="url" name="linkedin_url" value="<?= htmlspecialchars($profile['linkedin_url'] ?? 'https://linkedin.com') ?>"
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold transition flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Profile Details
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white">Security Settings</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Update your portfolio admin password</p>

                    <form action="portfolio_action.php" method="POST" class="mt-6 space-y-4 text-xs sm:text-sm">
                        <input type="hidden" name="action" value="update_password">

                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Admin Email</label>
                            <input type="text" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly
                                   class="w-full p-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-500 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">New Password (Min 6 chars)</label>
                            <input type="password" name="new_password" required minlength="6" placeholder="••••••••"
                                   class="w-full p-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold transition border border-slate-700 flex items-center justify-center gap-2 mt-4">
                            <i class="fas fa-lock"></i> Update Password
                        </button>
                    </form>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800 text-[11px] text-slate-400">
                    <i class="fas fa-shield-alt text-teal-400 mr-1"></i> Passwords are encrypted using PHP `password_hash()` with modern Argon2/Bcrypt.
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        &copy; <?= date('Y') ?> Anandha Shaji • Portfolio Administration System
    </footer>

</body>
</html>
