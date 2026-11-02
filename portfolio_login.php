<?php
/**
 * Portfolio Login Portal
 */
require_once __DIR__ . '/portfolio_db.php';

if (isPortfolioLoggedIn()) {
    header('Location: portfolio_admin.php');
    exit;
}

$flash = getPortfolioFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Portal Login | Anandha Shaji</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
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
        }
        .glass-panel {
            background: rgba(18, 24, 38, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden bg-slate-950">

    <!-- Ambient Glowing Orbs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-sky-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Header Branding -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-3 group mb-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 via-sky-500 to-teal-400 text-white flex items-center justify-center text-xl font-black shadow-lg shadow-teal-500/20 group-hover:scale-105 transition-transform">
                    AS
                </div>
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Portfolio Admin Access</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1.5">Sign in to manage portfolio messages, projects & bio</p>
        </div>

        <!-- Flash Alert Message -->
        <?php if ($flash): ?>
            <div class="mb-5 p-4 rounded-2xl text-xs font-semibold flex items-center gap-3 border <?= ($flash['type'] === 'success') ? 'bg-emerald-950/60 border-emerald-800 text-emerald-300' : 'bg-rose-950/60 border-rose-800 text-rose-300' ?>">
                <i class="fas <?= ($flash['type'] === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle' ?> text-base flex-shrink-0"></i>
                <div><?= htmlspecialchars($flash['message']) ?></div>
            </div>
        <?php endif; ?>

        <!-- Login Card -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl">
            <form action="portfolio_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <input type="email" id="email" name="email" required autofocus placeholder="anandha@portfolio.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300">Password</label>
                        <span class="text-xs text-teal-400 hover:text-teal-300 cursor-pointer" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye text-xs mr-1" id="pw-icon"></i> Show
                        </span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 via-sky-500 to-teal-400 hover:opacity-95 text-slate-950 font-bold rounded-xl text-sm transition shadow-lg shadow-teal-500/20 flex items-center justify-center gap-2 mt-4">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Authenticate into Dashboard</span>
                </button>
            </form>

            <!-- 1-Click Demo Fill Box -->
            <div class="mt-6 pt-5 border-t border-slate-800">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <i class="fas fa-key text-amber-400 mr-1"></i> Quick Evaluator Login
                    </span>
                    <span class="text-[10px] text-teal-400">1-Click Autofill</span>
                </div>

                <button type="button" onclick="fillAdminCredentials()"
                        class="w-full p-2.5 rounded-xl border border-teal-500/30 bg-teal-500/10 hover:bg-teal-500/20 text-left transition flex items-center justify-between group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/20 text-teal-300 flex items-center justify-center text-xs">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white group-hover:text-teal-300 transition">Anandha Shaji (Owner)</div>
                            <div class="text-[11px] text-slate-400">anandha@portfolio.com / admin123</div>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-xs text-slate-500 group-hover:text-teal-300 transition group-hover:translate-x-0.5"></i>
                </button>
            </div>

            <!-- Jump to Library System -->
            <div class="mt-4 p-2.5 rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Looking for the Library System?</span>
                <a href="library/login.php" class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                    <span>Library Login</span> <i class="fas fa-external-link-alt text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Back to Public Portfolio Link -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-xs font-semibold text-slate-400 hover:text-white transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to Anandha's Public Portfolio
            </a>
        </div>
    </div>

    <script>
    function fillAdminCredentials() {
        document.getElementById('email').value = 'anandha@portfolio.com';
        document.getElementById('password').value = 'admin123';
    }

    function togglePasswordVisibility() {
        const pw = document.getElementById('password');
        const icon = document.getElementById('pw-icon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pw.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
