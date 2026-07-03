<?php $currentUser = auth_current_user(); ?>
<aside class="sd-sidebar sd-admin-sidebar p-3 p-lg-4">
    <div class="sd-brand d-flex align-items-center gap-3 mb-4">
        <div class="sd-admin-badge">
            <i class="fa-solid fa-user-shield fs-4"></i>
        </div>
        <div class="sd-brand-text">
            <div class="fw-bold">Admin Panel</div>
            <small class="text-white-50">Smart Expense</small>
        </div>
    </div>

    <nav class="nav flex-column gap-2">
        <a class="nav-link <?php echo ($activePage ?? '') === 'admin-dashboard' ? 'active' : ''; ?>" href="/pages/admin/dashboard.php">
            <i class="fa-solid fa-gauge me-2 sd-nav-icon"></i>
            <span class="sd-nav-label">Overview</span>
        </a>
        <a class="nav-link" href="/pages/dashboard.php">
            <i class="fa-solid fa-chart-line me-2 sd-nav-icon"></i>
            <span class="sd-nav-label">Dashboard User</span>
        </a>
        <a class="nav-link" href="/pages/admin/users.php">
            <i class="fa-solid fa-users me-2 sd-nav-icon"></i>
            <span class="sd-nav-label">Kelola User</span>
        </a>
    </nav>

    <div class="mt-auto pt-4 sd-userbox">
        <div class="small text-white-50 mb-2">Login sebagai</div>
        <div class="fw-semibold text-white sd-nav-label"><?php echo htmlspecialchars((string) ($currentUser['name'] ?? 'Admin'), ENT_QUOTES, 'UTF-8'); ?></div>
        <a href="/logout.php" class="btn btn-sm btn-light w-100 mt-3">
            <i class="fa-solid fa-right-from-bracket me-1"></i>
            <span class="sd-nav-label">Logout</span>
        </a>
    </div>
</aside>
