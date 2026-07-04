<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Expense.php';

$currentUser = auth_current_user();
$expenseModel = new Expense((int) $currentUser['id']);
$pageTitle = 'Riwayat Pengeluaran';
$activePage = 'history';

$categories = Category::all();
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$keyword = trim((string) ($_GET['keyword'] ?? ''));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$startDate = (string) ($_GET['start_date'] ?? '');
$endDate = (string) ($_GET['end_date'] ?? '');

$filters = array_filter([
    'search' => $keyword,
    'category_id' => $categoryId,
    'date_from' => $startDate,
    'date_to' => $endDate,
]);

$perPage = 10;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$expenses = $expenseModel->getAll($filters);

$totalRows = count($expenses);

$totalPages = max(1, (int) ceil($totalRows / $perPage));

$currentPage = min($currentPage, $totalPages);

$offset = ($currentPage - 1) * $perPage;

$expenses = array_slice($expenses, $offset, $perPage);

$totalAmount = 0;

foreach ($expenses as $expense) {
    $totalAmount += (float)$expense['amount'];
}

$queryWithoutPage = $_GET;
unset($queryWithoutPage['page']);

// URL export PDF, ikut filter yang sedang aktif (search/date_from/date_to/category_id
// adalah nama parameter yang dipakai oleh process/exportHistoryPDF.php)
$exportParams = array_filter([
    'search' => $keyword,
    'category_id' => $categoryId ?: null,
    'date_from' => $startDate,
    'date_to' => $endDate,
]);
$exportUrl = '../process/exportHistoryPDF.php' . ($exportParams !== [] ? '?' . http_build_query($exportParams) : '');

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
        --dsh-teal: #1F8A70;
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
    }

    .dsh h4 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 500;
        font-size: 1.4rem;
        color: var(--dsh-ink);
        letter-spacing: -0.01em;
    }

    .dsh .text-muted {
        color: var(--dsh-muted) !important;
        font-size: 0.86rem;
    }

    .dsh .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--dsh-slate);
        margin-bottom: 0.4rem;
    }

    .dsh .form-control,
    .dsh .form-select {
        border: 1px solid var(--dsh-border);
        border-radius: 8px;
        font-size: 0.88rem;
        color: var(--dsh-ink);
        padding: 0.55rem 0.8rem;
        background-color: var(--dsh-surface);
    }

    .dsh .form-control:focus,
    .dsh .form-select:focus {
        border-color: var(--dsh-navy);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--dsh-navy) 12%, transparent);
    }

    .dsh .sd-btn-primary {
        background: var(--dsh-navy);
        border: 1px solid var(--dsh-navy);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.88rem;
        padding: 0.55rem 1.2rem;
        transition: background .15s ease, border-color .15s ease;
    }

    .dsh .sd-btn-primary:hover {
        background: #16294A;
        border-color: #16294A;
        color: #fff;
    }

    .dsh .btn-light.border {
        background: var(--dsh-surface);
        border-color: var(--dsh-border) !important;
        color: var(--dsh-slate);
        font-weight: 600;
        border-radius: 8px;
        font-size: 0.85rem;
        padding: 0.55rem 1.1rem;
    }

    .dsh .btn-light.border:hover {
        background: var(--dsh-bg);
        color: var(--dsh-ink);
    }

    /* Summary strip */
    .dsh .sd-summary {
        border-bottom: 1px solid var(--dsh-border);
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
    }

    .dsh .sd-summary .value {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.6rem;
        font-weight: 500;
        color: var(--dsh-navy);
    }

    .dsh .sd-summary .label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--dsh-muted);
    }

    /* Table */
    .dsh table {
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .dsh thead th {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--dsh-slate);
        border-bottom: 1px solid var(--dsh-border);
        background-color: var(--dsh-bg);
        padding: 0.75rem 1rem;
        white-space: nowrap;
    }

    .dsh tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--dsh-border);
        color: var(--dsh-ink);
    }

    .dsh tbody tr:last-child td {
        border-bottom: none;
    }

    .dsh tbody tr:hover {
        background-color: var(--dsh-bg);
    }

    .dsh .sd-badge-category {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--dsh-slate);
        background-color: var(--dsh-bg);
        border: 1px solid var(--dsh-border);
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
    }

    .dsh .sd-amount {
        font-weight: 600;
        color: var(--dsh-ink);
        white-space: nowrap;
    }

    .dsh .sd-notes {
        max-width: 240px;
        color: var(--dsh-muted);
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dsh .sd-action-btn {
        border: 1px solid var(--dsh-border);
        background-color: var(--dsh-surface);
        color: var(--dsh-slate);
        border-radius: 6px;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .dsh .sd-action-btn:hover {
        background-color: var(--dsh-bg);
        color: var(--dsh-ink);
        border-color: var(--dsh-navy);
    }

    .dsh .sd-action-btn.danger:hover {
        color: #B3403A;
        border-color: #B3403A;
    }

    .dsh .sd-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--dsh-muted);
    }

    .dsh .sd-empty i {
        font-size: 1.8rem;
        color: var(--dsh-faint);
        margin-bottom: 0.75rem;
        display: block;
    }

    /* Pagination */
    .dsh .pagination {
        margin: 0;
        gap: 0.25rem;
    }

    .dsh .page-link {
        border: 1px solid var(--dsh-border);
        color: var(--dsh-slate);
        border-radius: 6px;
        font-size: 0.85rem;
        padding: 0.4rem 0.75rem;
    }

    .dsh .page-item.active .page-link {
        background-color: var(--dsh-navy);
        border-color: var(--dsh-navy);
        color: #fff;
    }

    .dsh .page-item.disabled .page-link {
        color: var(--dsh-faint);
        background-color: var(--dsh-surface);
    }

    .dsh .alert {
        border: 1px solid var(--dsh-border);
        border-left: 3px solid var(--dsh-navy);
        border-radius: 8px;
        font-size: 0.88rem;
        background-color: var(--dsh-surface);
        color: var(--dsh-ink);
    }

    .dsh .alert-success {
        border-left-color: var(--dsh-teal);
    }

    .dsh .alert-danger {
        border-left-color: #B3403A;
    }
</style>

<main class="sd-main dsh">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="p-3 p-lg-4">
        <?php if (is_array($flashMessage)): ?>
            <div class="alert alert-<?php echo htmlspecialchars((string) $flashMessage['type'], ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="sd-card p-4 p-lg-5">
            <div class="mb-4">
                <h4 class="mb-1">Riwayat Pengeluaran</h4>
                <p class="text-muted mb-0">Lihat, cari, dan kelola seluruh catatan pengeluaran yang sudah tersimpan.</p>
            </div>

            <!-- Filter -->
            <form action="historyExpense.php" method="get" class="row g-3 mb-4">
                <div class="col-12 col-lg-4">
                    <label for="keyword" class="form-label">Cari</label>
                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Nama atau catatan" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-12 col-lg-2">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Semua</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int) $category['id']; ?>" <?php echo $categoryId === (int) $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-6 col-lg-2">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-12 col-lg-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn sd-btn-primary text-white flex-fill">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
                    </button>
                    <a href="historyExpense.php" class="btn btn-light border" title="Reset filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>

            <div class="mb-4">
                <a href="<?php echo htmlspecialchars($exportUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light border">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>

            <!-- Summary -->
            <div class="row sd-summary g-3">
                <div class="col-6 col-lg-3">
                    <div class="label">Total Transaksi</div>
                    <div class="value"><?php echo number_format($totalRows, 0, ',', '.'); ?></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="label">Total Pengeluaran</div>
                    <div class="value">Rp<?php echo number_format($totalAmount, 0, ',', '.'); ?></div>
                </div>
            </div>

            <!-- Table -->
            <?php if ($expenses === []): ?>
                <div class="sd-empty">
                    <i class="fa-regular fa-folder-open"></i>
                    <div class="fw-semibold mb-1">Belum ada data</div>
                    <div>Tidak ditemukan riwayat pengeluaran sesuai filter yang dipilih.</div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Catatan</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime((string) $expense['expense_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) $expense['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="sd-badge-category"><?php echo htmlspecialchars((string) $expense['category_name'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td class="sd-notes" title="<?php echo htmlspecialchars((string) ($expense['notes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo $expense['notes'] !== null && $expense['notes'] !== '' ? htmlspecialchars((string) $expense['notes'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                                    </td>
                                    <td class="text-end sd-amount">Rp<?php echo number_format((float) $expense['amount'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="editExpense.php?id=<?php echo (int) $expense['id']; ?>" class="sd-action-btn" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="../process/deleteExpense.php?id=<?php echo (int) $expense['id']; ?>"
                                               class="sd-action-btn danger"
                                               title="Hapus"
                                               onclick="return confirm('Hapus data pengeluaran ini?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                        <div class="text-muted">
                            Halaman <?php echo $currentPage; ?> dari <?php echo $totalPages; ?>
                        </div>
                        <ul class="pagination">
                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query(array_merge($queryWithoutPage, ['page' => max(1, $currentPage - 1)])), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query(array_merge($queryWithoutPage, ['page' => $i])), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query(array_merge($queryWithoutPage, ['page' => min($totalPages, $currentPage + 1)])), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>