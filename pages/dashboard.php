<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../classes/Expense.php';
require_once __DIR__ . '/../classes/Statistics.php';
require_once __DIR__ . '/../classes/Category.php';

$expenseModel = new Expense();
$statisticsModel = new Statistics();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

$todayTotal = $expenseModel->getTodayTotal();
$monthTotal = $expenseModel->getMonthTotal();
$grandTotal = $expenseModel->getTotalAmount();
$totalTransactions = $expenseModel->countAll();
$latestExpenses = $expenseModel->getLatest(5);
$categoryBreakdown = $statisticsModel->getCategoryBreakdown();
$weeklyGrowth = $statisticsModel->getWeeklyGrowthPercentage();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .dsh {
        --dsh-ink: #14213D;
        --dsh-muted: #6B7280;
        --dsh-faint: #9CA3AF;
        --dsh-bg: #F6F7F9;
        --dsh-surface: #FFFFFF;
        --dsh-border: #E7E9EE;
        --dsh-navy: #1D3557;
        --dsh-blue: #2C6BB3;
        --dsh-teal: #1F8A70;
        --dsh-amber: #B8862B;
        --dsh-slate: #52606D;
        --dsh-radius: 10px;
        background: var(--dsh-bg);
        color: var(--dsh-ink);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .dsh .sd-card {
        background: var(--dsh-surface);
        border: 1px solid var(--dsh-border);
        border-radius: var(--dsh-radius);
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .dsh .sd-card:hover {
        box-shadow: 0 2px 10px rgba(20, 33, 61, 0.05);
    }

    /* Stat cards */
    .dsh .stat-card {
        position: relative;
        overflow: hidden;
        border-top: 2px solid var(--dsh-accent, var(--dsh-navy));
    }

    .dsh .stat-card.accent-blue   { --dsh-accent: var(--dsh-blue); }
    .dsh .stat-card.accent-navy   { --dsh-accent: var(--dsh-navy); }
    .dsh .stat-card.accent-teal   { --dsh-accent: var(--dsh-teal); }
    .dsh .stat-card.accent-slate  { --dsh-accent: var(--dsh-slate); }

    .dsh .stat-eyebrow {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--dsh-muted);
        margin-bottom: 0.6rem;
    }

    .dsh .stat-value {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 500;
        font-size: 1.65rem;
        line-height: 1.2;
        color: var(--dsh-ink);
        letter-spacing: -0.01em;
    }

    .dsh .stat-value .stat-currency {
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--dsh-muted);
        margin-right: 0.2rem;
    }

    .dsh .sd-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--dsh-accent, var(--dsh-navy)) 10%, white);
        color: var(--dsh-accent, var(--dsh-navy));
        font-size: 0.95rem;
    }

    /* Section headers */
    .dsh h5 {
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 1rem;
        color: var(--dsh-ink);
        letter-spacing: -0.01em;
    }

    .dsh .text-muted {
        color: var(--dsh-muted) !important;
        font-size: 0.86rem;
    }

    .dsh .btn-outline-primary {
        --bs-btn-color: var(--dsh-navy);
        --bs-btn-border-color: var(--dsh-border);
        --bs-btn-hover-bg: var(--dsh-navy);
        --bs-btn-hover-border-color: var(--dsh-navy);
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.4rem 0.9rem;
    }

    /* Table */
    .dsh .table thead th {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--dsh-faint);
        border-bottom: 1px solid var(--dsh-border);
        padding-bottom: 0.75rem;
    }

    .dsh .table tbody td {
        border-bottom: 1px solid var(--dsh-border);
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .dsh .table tbody tr:last-child td {
        border-bottom: none;
    }

    .dsh .table tbody tr:hover {
        background: var(--dsh-bg);
    }

    .dsh .table .fw-semibold {
        color: var(--dsh-ink);
        font-weight: 600;
    }

    .dsh .table td small.text-muted {
        font-size: 0.78rem;
    }

    .dsh .badge.text-bg-light {
        background: #EEF2F7 !important;
        border: none !important;
        color: var(--dsh-slate);
        font-weight: 600;
        font-size: 0.74rem;
        padding: 0.4em 0.75em;
    }

    .dsh .table td.text-end.fw-semibold {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 500;
        font-size: 0.95rem;
        color: var(--dsh-ink);
    }

    /* Insight cards */
    .dsh .insight-item {
        background: var(--dsh-surface);
        border: 1px solid var(--dsh-border);
        border-left: 3px solid var(--dsh-blue);
        border-radius: 8px;
        padding: 0.9rem 1rem;
        font-size: 0.88rem;
        color: var(--dsh-slate);
        line-height: 1.5;
    }

    /* Chart placeholders */
    .dsh .chart-placeholder {
        background: var(--dsh-bg);
        border: 1px dashed var(--dsh-border) !important;
        border-radius: var(--dsh-radius);
        color: var(--dsh-faint);
    }

    .dsh .chart-placeholder i {
        color: var(--dsh-faint);
        opacity: 0.7;
    }

    .dsh .chart-placeholder div {
        font-size: 0.82rem;
    }
</style>

<main class="sd-main dsh">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="p-3 p-lg-4">
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-blue p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Total Hari Ini</p>
                            <h3 class="stat-value mb-0 mt-2"><span class="stat-currency">Rp</span><?php echo number_format($todayTotal, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-sun"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-navy p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Total Bulan Ini</p>
                            <h3 class="stat-value mb-0 mt-2"><span class="stat-currency">Rp</span><?php echo number_format($monthTotal, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-teal p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Total Semua</p>
                            <h3 class="stat-value mb-0 mt-2"><span class="stat-currency">Rp</span><?php echo number_format($grandTotal, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-wallet"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-slate p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Jumlah Transaksi</p>
                            <h3 class="stat-value mb-0 mt-2"><?php echo number_format($totalTransactions, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-receipt"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-8">
                <div class="sd-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Transaksi Terbaru</h5>
                            <p class="text-muted mb-0">Lima pengeluaran terakhir yang tersimpan.</p>
                        </div>
                        <a href="historyExpense.php" class="btn btn-sm btn-outline-primary">Lihat Riwayat</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($latestExpenses === []): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($latestExpenses as $expense): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) $expense['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars((string) ($expense['notes'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-light border">
                                                    <?php echo htmlspecialchars((string) $expense['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime((string) $expense['expense_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end fw-semibold">Rp <?php echo number_format((float) $expense['amount'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="sd-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Insight Singkat</h5>
                            <p class="text-muted mb-0">Ringkasan tren mingguan pengeluaran Anda.</p>
                        </div>
                        <a href="statExpense.php" class="btn btn-sm btn-outline-primary">Buka Statistik</a>
                    </div>

                    <?php if ($weeklyGrowth === null): ?>
                        <p class="text-muted mb-0">Belum cukup data untuk menghitung tren mingguan.</p>
                    <?php else: ?>
                        <?php $trendLabel = $weeklyGrowth >= 0 ? 'meningkat' : 'menurun'; ?>
                        <div class="insight-item mb-3">
                            Pengeluaran minggu ini <?php echo $trendLabel; ?> <?php echo number_format(abs($weeklyGrowth), 2, ',', '.'); ?>% dibanding minggu sebelumnya.
                        </div>
                        <p class="text-muted mb-0">Gunakan halaman statistik untuk melihat detail grafik lengkap dan perbandingan tiap minggu.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-12 col-xl-6">
                <div class="sd-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Kategori Paling Dominan</h5>
                            <p class="text-muted mb-0">Ringkasan kategori dengan pengeluaran terbesar.</p>
                        </div>
                        <a href="statExpense.php" class="btn btn-sm btn-outline-primary">Lihat Grafik</a>
                    </div>

                    <?php if ($categoryBreakdown === []): ?>
                        <p class="text-muted mb-0">Belum ada data kategori untuk ditampilkan.</p>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach (array_slice($categoryBreakdown, 0, 3) as $category): ?>
                                <div class="p-3 rounded-4 border bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="fw-semibold"><?php echo htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <span class="badge text-bg-light border">
                                            <?php echo number_format((float) $category['percentage'], 2, ',', '.'); ?>%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo (float) $category['percentage']; ?>%; background-color: <?php echo htmlspecialchars((string) ($category['color'] ?? '#1D3557'), ENT_QUOTES, 'UTF-8'); ?>;" aria-valuenow="<?php echo (float) $category['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="mt-2 text-muted">Rp <?php echo number_format((float) $category['total_amount'], 0, ',', '.'); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>