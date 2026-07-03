<header class="sd-topbar px-3 px-lg-4 py-3">
    <?php $currentDate = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')); ?>
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-start align-items-md-center gap-3">
            <button type="button" class="btn btn-light border rounded-circle d-inline-flex align-items-center justify-content-center sd-sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="true" data-sidebar-toggle>
                <i class="fa-solid fa-bars"></i>
            </button>
            <div>
            <h1 class="h4 mb-1"><?php echo htmlspecialchars($pageTitle ?? 'Smart Daily Expense Planner', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-muted mb-0">Kelola pengeluaran harian dengan tampilan dashboard modern.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-light border rounded-pill px-3 py-2">
                <i class="fa-regular fa-calendar me-1"></i>
                <?php echo $currentDate->format('d M Y'); ?>
            </span>
        </div>
    </div>
</header>
