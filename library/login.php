<?php
/**
 * Modern Login Page with One-Click Demo Access
 */
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Sign In to Your Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center p-4 sm:p-6 lg:p-8 relative">
    <!-- Subtle Background Glows -->
    <div class="absolute top-10 left-1/4 w-72 h-72 bg-indigo-200/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 right-1/4 w-72 h-72 bg-sky-200/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-2.5 mb-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center shadow-lg shadow-indigo-200 transition group-hover:scale-105">
                    <i class="fas fa-book-reader text-xl"></i>
                </div>
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Welcome Back</h1>
            <p class="text-sm text-slate-500 mt-1">Sign in to Athenaeum Library Management System</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 custom-shadow">
            <form action="actions/auth_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <input type="email" id="email" name="email" required autofocus
                               placeholder="you@library.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-slate-800">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Password
                        </label>
                        <span class="text-xs text-indigo-600 hover:text-indigo-800 cursor-pointer" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye text-xs mr-1" id="pw-icon"></i> Show
                        </span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-slate-800">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm transition shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2 mt-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Sign In to Athenaeum</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <i class="fas fa-key text-amber-500 mr-1"></i> Quick Demo Access
                    </span>
                    <span class="text-[10px] text-slate-400">Click to Autofill</span>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Demo Admin Button -->
                    <button type="button" onclick="fillCredentials('admin@library.com', 'admin123')"
                            class="p-2.5 rounded-xl border border-indigo-100 bg-indigo-50/60 hover:bg-indigo-100/70 text-left transition text-xs">
                        <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                            <i class="fas fa-shield-alt text-indigo-600"></i> Librarian
                        </div>
                        <div class="text-[11px] text-indigo-700/80 truncate mt-0.5">admin@library.com</div>
                    </button>

                    <!-- Demo Student Button -->
                    <button type="button" onclick="fillCredentials('student@library.com', 'student123')"
                            class="p-2.5 rounded-xl border border-emerald-100 bg-emerald-50/60 hover:bg-emerald-100/70 text-left transition text-xs">
                        <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                            <i class="fas fa-user-graduate text-emerald-600"></i> Student
                        </div>
                        <div class="text-[11px] text-emerald-700/80 truncate mt-0.5">student@library.com</div>
                    </button>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                Don't have an account yet? 
                <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Register now</a>
            </div>
        </div>

        <!-- Back Links -->
        <div class="text-center mt-6 flex items-center justify-center gap-4 text-xs font-medium">
            <a href="index.php" class="text-slate-500 hover:text-indigo-600 transition inline-flex items-center gap-1.5">
                <i class="fas fa-book-reader text-[10px]"></i> Public Catalog
            </a>
            <span class="text-slate-300">•</span>
            <a href="../index.php" class="text-indigo-600 hover:text-indigo-800 font-semibold transition inline-flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to Portfolio
            </a>
        </div>
    </div>
</div>

<script>
function fillCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
