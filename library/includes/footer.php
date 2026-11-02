<?php
/**
 * Footer layout component
 */
$flash = getFlash();
?>

<?php if ($flash): ?>
    <!-- Floating Flash Notification Toast -->
    <div id="flash-alert" class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-medium transition-all duration-300 max-w-md
        <?php
            switch ($flash['type']) {
                case 'success':
                    echo 'bg-emerald-50 text-emerald-800 border-emerald-200';
                    $icon = 'fa-check-circle text-emerald-500';
                    break;
                case 'error':
                    echo 'bg-rose-50 text-rose-800 border-rose-200';
                    $icon = 'fa-exclamation-circle text-rose-500';
                    break;
                case 'warning':
                    echo 'bg-amber-50 text-amber-800 border-amber-200';
                    $icon = 'fa-exclamation-triangle text-amber-500';
                    break;
                default:
                    echo 'bg-indigo-50 text-indigo-800 border-indigo-200';
                    $icon = 'fa-info-circle text-indigo-500';
                    break;
            }
        ?>">
        <i class="fas <?= $icon ?> text-lg flex-shrink-0"></i>
        <div class="flex-1"><?= htmlspecialchars($flash['message']) ?></div>
        <button onclick="document.getElementById('flash-alert').remove()" class="text-slate-400 hover:text-slate-600 ml-2">
            <i class="fas fa-times"></i>
        </button>
    </div>
<?php endif; ?>

    <!-- Main JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
