<?php $currentUser = auth_current_user(); ?>
<aside class="sd-sidebar p-3 p-lg-4">
    <div class="sd-brand d-flex align-items-center gap-3 mb-4">
        <div class="d-inline-flex align-items-center justify-content-center">
            <img src="../assets/images/logo-smart-expense.png"
                alt="Smart Expense Logo"
                width="50"
                height="50"
                class="img-fluid">
        </div>
        <div class="sd-brand-text">
            <div class="fw-bold">Smart Expense</div>
            <small class="text-white-50">Planner Dashboard</small>
        </div>
    </div>

    <nav class="nav flex-column gap-2">
        <a class="nav-link <?php echo ($activePage ?? '') === 'admin-dashboard' ? 'active' : ''; ?>" href="<?php echo (($currentUser['role'] ?? 'user') === 'admin') ? '/pages/admin/dashboard.php' : '/pages/dashboard.php'; ?>">
            <i class="fa-solid fa-gauge me-2 sd-nav-icon"></i>
            <span class="sd-nav-label"><?php echo (($currentUser['role'] ?? 'user') === 'admin') ? 'Dashboard Admin' : 'Dashboard'; ?></span>
        </a>
        <a class="nav-link <?php echo ($activePage ?? '') === 'add-expense' ? 'active' : ''; ?>" href="addExpense.php">
            <i class="fa-solid fa-circle-plus me-2 sd-nav-icon"></i>
            <span class="sd-nav-label">Tambah Pengeluaran</span>
        </a>
        <a class="nav-link <?php echo ($activePage ?? '') === 'history' ? 'active' : ''; ?>" href="historyExpense.php">
            <i class="fa-solid fa-clock-rotate-left me-2 sd-nav-icon"></i>
            <span class="sd-nav-label">Riwayat</span>
        </a>
        <a class="nav-link <?php echo ($activePage ?? '') === 'statistics' ? 'active' : ''; ?>" href="statExpense.php">
            <i class="fa-solid fa-chart-column me-2 sd-nav-icon"></i>
            <span class="sd-nav-label">Statistik</span>
        </a>
    </nav>

    <div class="mt-auto pt-4 sd-userbox">
        <div class="small text-white-50 mb-2">Login sebagai</div>
        <div class="fw-semibold text-white sd-nav-label"><?php echo htmlspecialchars((string) ($currentUser['name'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></div>
        <a href="/logout.php" class="btn btn-sm btn-light w-100 mt-3">
            <i class="fa-solid fa-right-from-bracket me-1"></i>
            <span class="sd-nav-label">Logout</span>
        </a>
    </div>
</aside>
