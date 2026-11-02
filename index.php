<?php
/**
 * Anandha Shaji - Developer Portfolio Website
 * Features linked Athenaeum Library Management System and Portfolio Login Portal.
 */
require_once __DIR__ . '/portfolio_db.php';

$pdo = getPortfolioDB();
$isLoggedIn = isPortfolioLoggedIn();
$currentUser = getPortfolioUser();
$flash = getPortfolioFlash();

// Fetch Profile Data
$profile = $pdo->query("SELECT * FROM portfolio_profile WHERE id = 1")->fetch() ?: [
    'full_name' => 'Anandha Shaji',
    'headline' => 'Full-Stack Developer & Software Engineer',
    'bio' => 'Passionate software craftsman building scalable, secure, and user-friendly web applications, database architectures, and full-stack systems.',
    'about' => 'I am a dedicated software engineer specializing in backend PHP development, relational database engineering (MySQL/SQLite), and modern responsive frontend design with Tailwind CSS and JavaScript. I enjoy building real-world software solutions with clean architecture, enterprise-grade authentication, and seamless user experiences.',
    'email' => 'anandha.shaji@example.com',
    'phone' => '+91 98765 43210',
    'location' => 'India',
    'github_url' => 'https://github.com',
    'linkedin_url' => 'https://linkedin.com'
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile['full_name']) ?> | Full-Stack Developer Portfolio</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
            overflow-x: hidden;
        }
        .glass-card {
            background: rgba(18, 24, 38, 0.7);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card-hover:hover {
            border-color: rgba(45, 212, 191, 0.4);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(20, 184, 166, 0.15);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .text-gradient {
            background: linear-gradient(135deg, #a5b4fc 0%, #38bdf8 50%, #2dd4bf 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .text-gradient-purple {
            background: linear-gradient(135deg, #c084fc 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0b0f19; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="relative selection:bg-teal-500 selection:text-slate-950">

    <!-- Ambient Glowing Orbs -->
    <div class="fixed top-0 left-1/4 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-1/4 right-10 w-[450px] h-[450px] bg-teal-500/10 rounded-full blur-[130px] pointer-events-none -z-10"></div>

    <!-- Global Flash Notification Toast -->
    <?php if ($flash): ?>
        <div id="flash-toast" class="fixed top-20 right-5 z-50 p-4 rounded-2xl shadow-2xl flex items-center gap-3 text-xs sm:text-sm font-semibold max-w-md border <?= ($flash['type'] === 'success') ? 'bg-emerald-950/90 border-emerald-700 text-emerald-200' : 'bg-rose-950/90 border-rose-700 text-rose-200' ?> transition-all duration-300">
            <i class="fas <?= ($flash['type'] === 'success') ? 'fa-check-circle' : 'fa-info-circle' ?> text-lg"></i>
            <span class="flex-1"><?= htmlspecialchars($flash['message']) ?></span>
            <button onclick="document.getElementById('flash-toast').remove()" class="text-slate-400 hover:text-white ml-2">&times;</button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('flash-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 400);
                }
            }, 6000);
        </script>
    <?php endif; ?>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-lg border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-500 via-sky-500 to-teal-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-lg shadow-teal-500/20 group-hover:scale-105 transition-transform">
                    AS
                </div>
                <div>
                    <span class="text-base sm:text-lg font-black text-white tracking-tight flex items-center gap-2">
                        <?= htmlspecialchars($profile['full_name']) ?>
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    </span>
                    <span class="text-[11px] block text-slate-400 font-medium tracking-wide">Software Engineer</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-300">
                <a href="#about" class="hover:text-teal-400 transition">About</a>
                <a href="#featured-project" class="text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Library Project
                </a>
                <a href="#projects" class="hover:text-teal-400 transition">Projects</a>
                <a href="#skills" class="hover:text-teal-400 transition">Tech Stack</a>
                <a href="#contact" class="hover:text-teal-400 transition">Contact</a>
            </nav>

            <!-- Actions (Login / Portal) -->
            <div class="flex items-center gap-3">
                <!-- Jump into Library Direct Demo Button -->
                <a href="library/index.php" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-950/80 hover:bg-indigo-900/90 text-indigo-300 border border-indigo-700/60 text-xs font-bold transition shadow-sm">
                    <i class="fas fa-book-reader text-indigo-400"></i>
                    <span>Library System</span>
                </a>

                <!-- Portfolio Login / Dashboard Pill -->
                <?php if ($isLoggedIn): ?>
                    <a href="portfolio_admin.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-400 text-slate-950 font-bold text-xs shadow-md shadow-teal-500/20 hover:opacity-95 transition">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Dashboard</span>
                    </a>
                <?php else: ?>
                    <a href="portfolio_login.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-teal-400 border border-teal-500/30 font-bold text-xs transition shadow-sm hover:border-teal-400">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Portfolio Login</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-400 hover:text-white focus:outline-none">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu" class="hidden md:hidden px-4 pt-3 pb-6 border-t border-slate-800 bg-slate-950/95 space-y-3 text-sm">
            <a href="#about" class="block py-2 text-slate-300 hover:text-teal-400">About</a>
            <a href="#featured-project" class="block py-2 text-indigo-400 font-semibold">Featured: Library System</a>
            <a href="#projects" class="block py-2 text-slate-300 hover:text-teal-400">All Projects</a>
            <a href="#skills" class="block py-2 text-slate-300 hover:text-teal-400">Skills</a>
            <a href="#contact" class="block py-2 text-slate-300 hover:text-teal-400">Contact</a>
            <a href="library/index.php" class="block py-2 text-indigo-300 font-bold">Open Library App</a>
            <?php if ($isLoggedIn): ?>
                <a href="portfolio_admin.php" class="block py-2 text-teal-400 font-bold">Admin Dashboard</a>
            <?php else: ?>
                <a href="portfolio_login.php" class="block py-2 text-teal-400 font-bold">Portfolio Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-20 pb-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Text -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-300 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-ping"></span>
                        Available for Software Engineering Roles & Projects
                    </div>

                    <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-[1.15]">
                        Crafting High-Performance <br>
                        <span class="text-gradient">Web Systems & Code</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Hello! I am <strong class="text-white"><?= htmlspecialchars($profile['full_name']) ?></strong>, a full-stack engineer specializing in robust PHP backend architecture, relational databases, clean APIs, and elegant user interfaces.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="#featured-project" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-500 via-sky-500 to-teal-400 text-slate-950 font-extrabold text-sm hover:opacity-95 transition shadow-xl shadow-teal-500/20 flex items-center gap-2.5">
                            <i class="fas fa-rocket"></i>
                            <span>View Featured Project</span>
                        </a>

                        <a href="portfolio_login.php" class="px-6 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm transition border border-slate-700/80 flex items-center gap-2">
                            <i class="fas fa-lock text-teal-400"></i>
                            <span>Portfolio Login</span>
                        </a>

                        <a href="#contact" class="px-5 py-3.5 rounded-2xl bg-slate-900/60 hover:bg-slate-800 text-slate-300 font-semibold text-sm transition border border-slate-800 flex items-center gap-2">
                            <i class="fas fa-envelope"></i>
                            <span>Get In Touch</span>
                        </a>
                    </div>

                    <!-- Tech Pill Quick Ribbon -->
                    <div class="pt-6 border-t border-slate-800/80 flex items-center justify-center lg:justify-start gap-3 flex-wrap text-xs text-slate-400">
                        <span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Core Stack:</span>
                        <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-mono flex items-center gap-1.5">
                            <i class="fab fa-php text-indigo-400 text-sm"></i> PHP 8.2
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-mono flex items-center gap-1.5">
                            <i class="fas fa-database text-sky-400 text-xs"></i> MySQL / SQLite
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-mono flex items-center gap-1.5">
                            <i class="fab fa-js-square text-amber-400 text-sm"></i> JavaScript
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-mono flex items-center gap-1.5">
                            <i class="fab fa-css3-alt text-teal-400 text-sm"></i> Tailwind CSS
                        </span>
                    </div>
                </div>

                <!-- Right Hero Visual Card -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto max-w-sm sm:max-w-md">
                        <!-- Background Glow behind visual -->
                        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-sky-500 to-teal-400 rounded-3xl blur-xl opacity-20"></div>

                        <div class="glass-card rounded-3xl p-6 sm:p-7 relative z-10 space-y-6">
                            
                            <!-- Profile Avatar Header -->
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-sky-600 to-teal-500 text-white flex items-center justify-center text-2xl font-black shadow-xl shadow-teal-500/20">
                                    AS
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($profile['full_name']) ?></h3>
                                    <p class="text-xs text-teal-400 font-semibold"><?= htmlspecialchars($profile['headline']) ?></p>
                                    <p class="text-[11px] text-slate-400 mt-0.5"><i class="fas fa-map-marker-alt text-slate-500 mr-1"></i> <?= htmlspecialchars($profile['location']) ?></p>
                                </div>
                            </div>

                            <!-- Live Project Snapshot Preview -->
                            <div class="rounded-2xl bg-slate-950/90 border border-slate-800 p-4 space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400 font-medium flex items-center gap-1.5">
                                        <i class="fas fa-code-branch text-teal-400"></i> Primary System
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">100% Operational</span>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs">
                                            <i class="fas fa-book-reader"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-white">Athenaeum Library</div>
                                            <div class="text-[10px] text-slate-400">Full-Stack PHP System</div>
                                        </div>
                                    </div>
                                    <a href="library/index.php" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold transition">
                                        Launch
                                    </a>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-center text-xs pt-1">
                                    <div class="p-2 rounded-xl bg-slate-900/50 border border-slate-800/80">
                                        <div class="text-slate-400 text-[10px]">Role Security</div>
                                        <div class="font-bold text-white text-xs mt-0.5">Admin + Member</div>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-900/50 border border-slate-800/80">
                                        <div class="text-slate-400 text-[10px]">Database Engine</div>
                                        <div class="font-bold text-teal-400 text-xs mt-0.5">SQLite / MySQL</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Highlights List -->
                            <div class="space-y-2 text-xs text-slate-300">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-check-circle text-teal-400 text-xs"></i>
                                    <span>Built with clean PHP PDO architecture</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-check-circle text-teal-400 text-xs"></i>
                                    <span>Automated circulation & late fine calculations</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-check-circle text-teal-400 text-xs"></i>
                                    <span>Portfolio Admin Portal with Inquiries Inbox</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION: FEATURED PROJECT SPOTLIGHT (THE LIBRARY MANAGEMENT SYSTEM) -->
    <section id="featured-project" class="py-24 relative bg-slate-900/40 border-y border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold mb-3">
                    <i class="fas fa-star text-amber-400"></i> Featured Full-Stack Application
                </span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                    Athenaeum <span class="text-gradient">Library Management System</span>
                </h2>
                <p class="text-sm sm:text-base text-slate-300 mt-3 leading-relaxed">
                    A comprehensive, production-grade library circulation & inventory management portal with dual-role authentication, real-time catalog search, and automated late fine tracking.
                </p>
            </div>

            <!-- Spotlight Card -->
            <div class="glass-card rounded-3xl border border-indigo-500/30 overflow-hidden shadow-2xl bg-gradient-to-br from-indigo-950/20 via-slate-900/90 to-slate-950">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 sm:p-10 lg:p-12 items-center">
                    
                    <!-- Left Details -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Badges -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-bold">
                                Native PHP Backend
                            </span>
                            <span class="px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-bold">
                                SQLite / MySQL PDO
                            </span>
                            <span class="px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 text-xs font-bold">
                                Tailwind CSS UI
                            </span>
                            <span class="px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs font-bold">
                                Role-Based Access Control
                            </span>
                        </div>

                        <!-- Title & Problem Statement -->
                        <h3 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                            Complete Digital Circulation & Catalog Platform
                        </h3>

                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Designed to streamline university and institutional library operations. Features role-adaptive dashboards for both **Librarians** and **Student Readers**, real-time inventory checking, automated fines for overdue volumes ($1.00/day), shelf and rack coordinate mapping (e.g. `Rack CS-01`), and comprehensive audit logs.
                        </p>

                        <!-- Key Architecture Features Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                            <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                    <i class="fas fa-user-lock"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">Dual-Role Authentication</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Admin Librarian portal vs. Member borrow history with Bcrypt passwords.</p>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">Automated Fine Engine</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Calculates days overdue and assesses fees automatically upon return.</p>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">Dynamic Catalog Search</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Instant filtering by keyword, ISBN, genre, and real-time stock levels.</p>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">Zero-Setup SQLite Engine</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Auto-inits schema and seed data on first run; switchable to MySQL in config.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <a href="library/index.php" class="px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                                <i class="fas fa-play"></i> Launch Public Catalog
                            </a>

                            <a href="library/login.php" class="px-5 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-indigo-500/30 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                                <i class="fas fa-key"></i> System Login Page
                            </a>
                        </div>
                    </div>

                    <!-- Right Demo Credentials & Visual Showcase -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="rounded-3xl bg-slate-950 border border-slate-800 p-6 space-y-5">
                            
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                                    <i class="fas fa-terminal text-teal-400"></i> Reviewer Demo Credentials
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-teal-500/10 text-teal-400 text-[10px] font-mono">1-Click Auto-Fill Ready</span>
                            </div>

                            <!-- Librarian Demo Box -->
                            <div class="p-3.5 rounded-2xl bg-indigo-950/30 border border-indigo-800/40 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-indigo-300 flex items-center gap-1.5">
                                        <i class="fas fa-shield-alt text-indigo-400"></i> Librarian (Admin)
                                    </span>
                                    <a href="library/login.php" class="text-[11px] text-indigo-400 hover:underline font-semibold">Test Login &rarr;</a>
                                </div>
                                <div class="text-xs font-mono text-slate-300">
                                    Email: <span class="text-white font-semibold">admin@library.com</span><br>
                                    Password: <span class="text-white font-semibold">admin123</span>
                                </div>
                                <p class="text-[10px] text-slate-400">Access: Catalog management, add/edit books, issue/return circulation desk, member directory.</p>
                            </div>

                            <!-- Student Demo Box -->
                            <div class="p-3.5 rounded-2xl bg-teal-950/30 border border-teal-800/40 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-teal-300 flex items-center gap-1.5">
                                        <i class="fas fa-user-graduate text-teal-400"></i> Student Member
                                    </span>
                                    <a href="library/login.php" class="text-[11px] text-teal-400 hover:underline font-semibold">Test Login &rarr;</a>
                                </div>
                                <div class="text-xs font-mono text-slate-300">
                                    Email: <span class="text-white font-semibold">student@library.com</span><br>
                                    Password: <span class="text-white font-semibold">student123</span>
                                </div>
                                <p class="text-[10px] text-slate-400">Access: Personal dashboard, self-service borrowing, return history, countdown to due date.</p>
                            </div>

                            <!-- Quick Stats Indicator -->
                            <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-check-circle text-emerald-400"></i> Zero External Dependencies
                                </span>
                                <span class="font-mono text-slate-500">Pure PHP + SQLite</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- SECTION: MORE PORTFOLIO PROJECTS -->
    <section id="projects" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-400">Portfolio Work</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-1">Highlighted Engineering Projects</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md">
                    Explore systems and applications developed across full-stack web, secure APIs, and database engineering.
                </p>
            </div>

            <!-- Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Project 1: CloudVault -->
                <div class="glass-card glass-card-hover rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <span class="text-[11px] font-bold text-sky-400 uppercase tracking-wider">Cloud Storage System</span>
                        <h3 class="text-lg font-bold text-white mt-1">CloudVault: Secure File Management</h3>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Encrypted document storage platform with folder hierarchy, role permissions, temporary public download links, and versioning.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">PHP</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">MySQL</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">REST API</span>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Architecture Project</span>
                        <a href="#contact" class="text-teal-400 hover:text-teal-300 font-semibold flex items-center gap-1">
                            Inquire <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Project 2: Nexus API Gateway -->
                <div class="glass-card glass-card-hover rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <span class="text-[11px] font-bold text-indigo-400 uppercase tracking-wider">API Architecture</span>
                        <h3 class="text-lg font-bold text-white mt-1">Nexus Microservice API Router</h3>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Lightweight PHP routing engine with JWT authentication middleware, rate limiting, request validation, and JSON logging.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">PHP 8</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">JWT Auth</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">Microservices</span>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Backend System</span>
                        <a href="#contact" class="text-teal-400 hover:text-teal-300 font-semibold flex items-center gap-1">
                            Inquire <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Project 3: DevConnect -->
                <div class="glass-card glass-card-hover rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <span class="text-[11px] font-bold text-teal-400 uppercase tracking-wider">Collaboration App</span>
                        <h3 class="text-lg font-bold text-white mt-1">DevConnect Developer Forum</h3>
                        <p class="text-xs text-slate-400 mt-2.5 leading-relaxed">
                            Interactive code sharing platform with syntax highlighting, markdown rendering, threaded comments, and user reputation scores.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">JavaScript</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">Tailwind</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 text-[10px] font-mono">PHP & SQL</span>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Full-Stack App</span>
                        <a href="#contact" class="text-teal-400 hover:text-teal-300 font-semibold flex items-center gap-1">
                            Inquire <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: SKILLS & TECHNICAL EXPERTISE -->
    <section id="skills" class="py-24 relative bg-slate-900/40 border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-teal-400">Technical Expertise</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-1">Skills & Core Technologies</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2">Proficiencies across the complete development lifecycle</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Category 1: Backend -->
                <div class="glass-card rounded-3xl p-6 space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Backend & APIs</h3>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-center gap-2"><i class="fab fa-php text-indigo-400"></i> PHP 8.x (OOP & PDO)</li>
                        <li class="flex items-center gap-2"><i class="fas fa-lock text-indigo-400"></i> Auth, Sessions & CSRF</li>
                        <li class="flex items-center gap-2"><i class="fas fa-exchange-alt text-indigo-400"></i> RESTful API Design</li>
                        <li class="flex items-center gap-2"><i class="fab fa-python text-indigo-400"></i> Python & Scripting</li>
                    </ul>
                </div>

                <!-- Category 2: Databases -->
                <div class="glass-card rounded-3xl p-6 space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-lg">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Database Systems</h3>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-center gap-2"><i class="fas fa-table text-sky-400"></i> MySQL / MariaDB</li>
                        <li class="flex items-center gap-2"><i class="fas fa-file-invoice text-sky-400"></i> SQLite Embeds</li>
                        <li class="flex items-center gap-2"><i class="fas fa-project-diagram text-sky-400"></i> Schema & Indexing</li>
                        <li class="flex items-center gap-2"><i class="fas fa-shield-virus text-sky-400"></i> SQL Prepared Statements</li>
                    </ul>
                </div>

                <!-- Category 3: Frontend -->
                <div class="glass-card rounded-3xl p-6 space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-lg">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Frontend & UI</h3>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-center gap-2"><i class="fab fa-html5 text-teal-400"></i> Semantic HTML5</li>
                        <li class="flex items-center gap-2"><i class="fab fa-css3-alt text-teal-400"></i> Tailwind CSS & Glass UI</li>
                        <li class="flex items-center gap-2"><i class="fab fa-js-square text-teal-400"></i> Modern ES6+ JavaScript</li>
                        <li class="flex items-center gap-2"><i class="fas fa-mobile-alt text-teal-400"></i> Responsive Layouts</li>
                    </ul>
                </div>

                <!-- Category 4: Tools & DevOps -->
                <div class="glass-card rounded-3xl p-6 space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Tools & Workflow</h3>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-center gap-2"><i class="fab fa-git-alt text-purple-400"></i> Git & GitHub</li>
                        <li class="flex items-center gap-2"><i class="fas fa-terminal text-purple-400"></i> CLI & PowerShell</li>
                        <li class="flex items-center gap-2"><i class="fas fa-server text-purple-400"></i> XAMPP & Apache</li>
                        <li class="flex items-center gap-2"><i class="fas fa-network-wired text-purple-400"></i> Localtunnel & Deployment</li>
                    </ul>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: ABOUT ME -->
    <section id="about" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card rounded-3xl p-8 sm:p-12 border border-slate-800">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <div class="lg:col-span-4 text-center lg:text-left">
                        <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-indigo-500 via-sky-500 to-teal-400 text-slate-950 flex items-center justify-center text-4xl font-black shadow-xl shadow-teal-500/20 mx-auto lg:mx-0 mb-4">
                            AS
                        </div>
                        <h3 class="text-2xl font-bold text-white"><?= htmlspecialchars($profile['full_name']) ?></h3>
                        <p class="text-xs text-teal-400 font-semibold mt-0.5"><?= htmlspecialchars($profile['headline']) ?></p>
                        <p class="text-xs text-slate-400 mt-2"><i class="fas fa-map-marker-alt text-slate-500 mr-1"></i> Based in <?= htmlspecialchars($profile['location']) ?></p>
                    </div>

                    <div class="lg:col-span-8 space-y-4 text-xs sm:text-sm text-slate-300 leading-relaxed">
                        <h4 class="text-lg font-bold text-white">About My Engineering Journey</h4>
                        <p><?= nl2br(htmlspecialchars($profile['about'])) ?></p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-3">
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center">
                                <div class="text-xl font-black text-teal-400">100%</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Commitment to Code Quality</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center">
                                <div class="text-xl font-black text-indigo-400">Zero-Config</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">SQLite & MySQL Architecture</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center col-span-2 sm:col-span-1">
                                <div class="text-xl font-black text-sky-400">Full-Stack</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">End-to-End Solutions</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: INTERACTIVE CONTACT FORM -->
    <section id="contact" class="py-24 relative bg-slate-900/40 border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Contact Info Column -->
                <div class="lg:col-span-5 space-y-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-teal-400">Get In Touch</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-1">Let's Connect & Collaborate</h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                            Have an exciting project, open engineering opportunity, or question about my Athenaeum Library Management System? Drop me a message below!
                        </p>
                    </div>

                    <!-- Contact Details -->
                    <div class="space-y-3 text-xs sm:text-sm">
                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 font-medium">Direct Email</span>
                                <div class="font-bold text-white"><?= htmlspecialchars($profile['email']) ?></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 font-medium">Phone / WhatsApp</span>
                                <div class="font-bold text-white"><?= htmlspecialchars($profile['phone']) ?></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 font-medium">Location</span>
                                <div class="font-bold text-white"><?= htmlspecialchars($profile['location']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Portfolio Admin Shortcut -->
                    <div class="p-4 rounded-2xl bg-teal-950/20 border border-teal-800/30 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-teal-300">Portfolio Owner?</div>
                            <div class="text-[11px] text-slate-400">Log in to view incoming messages in your inbox.</div>
                        </div>
                        <a href="portfolio_login.php" class="px-3 py-1.5 rounded-xl bg-teal-500 text-slate-950 font-bold text-xs hover:bg-teal-400 transition">
                            Admin Login
                        </a>
                    </div>
                </div>

                <!-- Contact Message Form -->
                <div class="lg:col-span-7">
                    <div class="glass-card rounded-3xl p-6 sm:p-10 border border-slate-800">
                        <h3 class="text-lg font-bold text-white mb-2">Send a Message</h3>
                        <p class="text-xs text-slate-400 mb-6">Submissions are saved directly to the portfolio inbox.</p>

                        <form action="portfolio_action.php" method="POST" class="space-y-4 text-xs sm:text-sm">
                            <input type="hidden" name="action" value="contact">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-300 font-semibold mb-1">Your Name <span class="text-teal-400">*</span></label>
                                    <input type="text" name="name" required placeholder="John Doe"
                                           class="w-full p-3 bg-slate-900 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-400">
                                </div>
                                <div>
                                    <label class="block text-slate-300 font-semibold mb-1">Your Email Address <span class="text-teal-400">*</span></label>
                                    <input type="email" name="email" required placeholder="john@example.com"
                                           class="w-full p-3 bg-slate-900 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-400">
                                </div>
                            </div>

                            <div>
                                <label class="block text-slate-300 font-semibold mb-1">Subject</label>
                                <input type="text" name="subject" placeholder="Project Inquiry / Job Opportunity"
                                       class="w-full p-3 bg-slate-900 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-400">
                            </div>

                            <div>
                                <label class="block text-slate-300 font-semibold mb-1">Message Content <span class="text-teal-400">*</span></label>
                                <textarea name="message" rows="4" required placeholder="Write your message or inquiry here..."
                                          class="w-full p-3 bg-slate-900 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-400"></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-indigo-500 via-sky-500 to-teal-400 hover:opacity-95 text-slate-950 font-bold transition flex items-center justify-center gap-2 shadow-lg shadow-teal-500/20">
                                <i class="fas fa-paper-plane"></i>
                                <span>Send Message to Anandha</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-12 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-white"><?= htmlspecialchars($profile['full_name']) ?></span>
                <span>• Full-Stack Engineer Portfolio</span>
            </div>

            <div class="flex items-center gap-4 text-sm text-slate-400">
                <a href="portfolio_login.php" class="hover:text-teal-400 transition" title="Portfolio Admin Login">
                    <i class="fas fa-user-lock"></i>
                </a>
                <a href="library/index.php" class="hover:text-indigo-400 transition" title="Library Management System">
                    <i class="fas fa-book-reader"></i>
                </a>
                <a href="<?= htmlspecialchars($profile['github_url']) ?>" target="_blank" class="hover:text-white transition">
                    <i class="fab fa-github"></i>
                </a>
                <a href="<?= htmlspecialchars($profile['linkedin_url']) ?>" target="_blank" class="hover:text-white transition">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>

            <div>
                &copy; <?= date('Y') ?> All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Script -->
    <script>
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
    </script>

</body>
</html>
