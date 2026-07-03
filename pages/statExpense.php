<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../classes/Expense.php';
require_once __DIR__ . '/../classes/Category.php';

$pageTitle = 'Statistik Pengeluaran';
$activePage = 'statistics';

$currentUser = auth_current_user();
$expenseModel = new Expense((int) $currentUser['id']);
$categories = Category::all();


$currentMonth = (int) date('m');
$currentYear = (int) date('Y');

$selectedMonth = (int) ($_GET['month'] ?? $currentMonth);
$selectedYear = (int) ($_GET['year'] ?? $currentYear);

$selectedMonth = ($selectedMonth >= 1 && $selectedMonth <= 12)
    ? $selectedMonth
    : $currentMonth;

$selectedYear = ($selectedYear >= 2020 && $selectedYear <= 2100)
    ? $selectedYear
    : $currentYear;


$allExpenses = $expenseModel->getAll();


$expenses = array_filter(
    $allExpenses,
    static function (array $expense) use ($selectedMonth, $selectedYear): bool {

        $date = strtotime((string) $expense['expense_date']);

        return
            (int) date('n', $date) === $selectedMonth &&
            (int) date('Y', $date) === $selectedYear;
    }
);

$totalTransaction = count($expenses);

$totalExpense = 0;

foreach ($expenses as $expense) {
    $totalExpense += (float) $expense['amount'];
}

$todayExpense = $expenseModel->getTodayTotal();

$monthExpense = $expenseModel->getMonthTotal();

$categoryStatistics = [];

foreach ($categories as $category) {

    $categoryStatistics[(int) $category['id']] = [
        'id' => (int) $category['id'],
        'name' => $category['name'],
        'icon' => $category['icon'],
        'color' => $category['color'],
        'total' => 0,
        'count' => 0,
    ];
}

foreach ($expenses as $expense) {

    $id = (int) $expense['category_id'];

    if (!isset($categoryStatistics[$id])) {
        continue;
    }

    $categoryStatistics[$id]['total'] += (float) $expense['amount'];
    $categoryStatistics[$id]['count']++;
}

$chartLabels = [];
$chartAmounts = [];

foreach ($categoryStatistics as $stat) {

    if ($stat['count'] === 0) {
        continue;
    }

    $chartLabels[] = $stat['name'];
    $chartAmounts[] = $stat['total'];
}

$months = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];

$years = [];

for ($year = date('Y'); $year >= 2020; $year--) {
    $years[] = $year;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .sts {
        --sts-bg: #F6F7F9;
        --sts-card: #FFFFFF;
        --sts-border: #E6EAF0;
        --sts-text: #14213D;
        --sts-muted: #6B7280;
        --sts-primary: #1D3557;
        --sts-primary-hover: #16304F;
        --sts-success: #1F8A70;
        --sts-warning: #B8862B;
        --sts-radius: 12px;

        background: var(--sts-bg);
        min-height: 100vh;
        color: var(--sts-text);
        font-family: 'Inter', sans-serif;
    }

    .sts h3,
    .sts h4,
    .sts h5 {
        font-family: 'Fraunces', serif;
        color: var(--sts-text);
        letter-spacing: -.02em;
    }

    .sts .text-muted {
        color: var(--sts-muted) !important;
    }

    .sts .sd-card {
        background: var(--sts-card);
        border: 1px solid var(--sts-border);
        border-radius: var(--sts-radius);
        box-shadow: 0 10px 30px rgba(20,33,61,.04);
    }

    .sts .filter-card {
        padding: 1.5rem;
    }

    .sts .form-label {
        font-size: .76rem;
        font-weight: 700;
        color: #52606D;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: .45rem;
    }

    .sts .form-select,
    .sts .form-control {
        border: 1px solid var(--sts-border);
        border-radius: 9px;
        padding: .65rem .85rem;
        font-size: .92rem;
        transition: .2s;
    }

    .sts .form-select:focus,
    .sts .form-control:focus {
        border-color: var(--sts-primary);
        box-shadow: 0 0 0 .18rem rgba(29,53,87,.12);
    }

    .sts .btn-filter {
        background: var(--sts-primary);
        border: none;
        color: white;
        border-radius: 9px;
        font-weight: 600;
        padding: .68rem 1.4rem;
        transition: .2s;
    }

    .sts .btn-filter:hover {
        background: var(--sts-primary-hover);
        color: white;
    }

    .sts .summary-card {
        padding: 1.6rem;
        height: 100%;
        transition: .25s;
    }

    .sts .summary-card:hover {
        transform: translateY(-4px);
    }

    .sts .summary-icon {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 1rem;
        background: rgba(29,53,87,.08);
        color: var(--sts-primary);
    }

    .sts .summary-title {
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--sts-muted);
        font-weight: 700;
    }

    .sts .summary-value {
        font-family: 'Fraunces', serif;
        font-size: 1.85rem;
        margin-top: .45rem;
        margin-bottom: .25rem;
        color: var(--sts-text);
    }

    .sts .summary-desc {
        font-size: .85rem;
        color: var(--sts-muted);
    }

    .sts .section-title {
        font-family: 'Fraunces', serif;
        font-size: 1.35rem;
        margin-bottom: .3rem;
    }

    .sts .section-subtitle {
        color: var(--sts-muted);
        font-size: .9rem;
    }

    .sts .chart-card {
        padding: 1.6rem;
        height: 100%;
    }

    .sts .chart-title {
        font-weight: 600;
        margin-bottom: 1.2rem;
        color: var(--sts-text);
    }

    .sts canvas {
        max-width: 100%;
    }

    .sts .table-card {
        padding: 1.5rem;
    }

    .sts table {
        margin-bottom: 0;
    }

    .sts table thead th {
        background: #F8FAFC;
        color: #52606D;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-size: .78rem;
        border-bottom: 1px solid var(--sts-border);
        padding: .9rem;
    }

    .sts table tbody td {
        vertical-align: middle;
        padding: .95rem;
        border-color: #EEF1F5;
        font-size: .92rem;
    }

    .sts table tbody tr:hover {
        background: #FAFBFD;
    }

    .sts .badge-soft {
        background: rgba(29,53,87,.08);
        color: var(--sts-primary);
        border-radius: 999px;
        padding: .45rem .8rem;
        font-size: .75rem;
        font-weight: 600;
    }

    .sts .empty-state {
        text-align: center;
        padding: 4rem 1rem;
    }

    .sts .empty-state i {
        font-size: 3rem;
        color: #CBD5E1;
        margin-bottom: 1rem;
    }

    .sts .empty-state h5 {
        margin-bottom: .45rem;
    }

    .sts .empty-state p {
        color: var(--sts-muted);
        margin-bottom: 0;
    }


    @media (max-width: 992px) {

        .sts .summary-value {
            font-size: 1.55rem;
        }

        .sts .chart-card,
        .sts .table-card,
        .sts .summary-card,
        .sts .filter-card {
            padding: 1.25rem;
        }

    }

    @media (max-width: 768px) {

        .sts .summary-card {
            text-align: center;
        }

        .sts .summary-icon {
            margin-inline: auto;
        }

        .sts .section-title {
            font-size: 1.2rem;
        }

    }
</style>

<main class="sd-main sts">

    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="p-3 p-lg-4">

        <!-- Header -->
        <div class="sd-card p-4 mb-4">

            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

                <div>
                    <h3 class="mb-2">
                        <i class="fa-solid fa-chart-column me-2"></i>
                        Statistik Pengeluaran
                    </h3>

                    <p class="section-subtitle mb-0">
                        Analisis pengeluaran berdasarkan kategori, transaksi,
                        serta total pengeluaran pada periode yang dipilih.
                    </p>
                </div>

                <span class="badge-soft">
                    <?php echo $months[$selectedMonth]; ?>
                    <?php echo $selectedYear; ?>
                </span>

            </div>

        </div>

        <!-- Filter -->
        <div class="sd-card filter-card mb-4">

            <form method="GET">

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">

                        <label class="form-label">
                            Bulan
                        </label>

                        <select
                            name="month"
                            class="form-select">

                            <?php foreach ($months as $number => $month): ?>

                                <option
                                    value="<?php echo $number; ?>"
                                    <?php echo $selectedMonth == $number ? 'selected' : ''; ?>>

                                    <?php echo htmlspecialchars($month); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Tahun
                        </label>

                        <select
                            name="year"
                            class="form-select">

                            <?php foreach ($years as $year): ?>

                                <option
                                    value="<?php echo $year; ?>"
                                    <?php echo $selectedYear == $year ? 'selected' : ''; ?>>

                                    <?php echo $year; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <button
                            type="submit"
                            class="btn btn-filter w-100">

                            <i class="fa-solid fa-filter me-2"></i>
                            Terapkan Filter

                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4">
            <!-- Card 1 -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="sd-card summary-card">

                    <div class="summary-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>

                    <div class="summary-title">
                        Total Pengeluaran
                    </div>

                    <div class="summary-value">
                        Rp <?php echo number_format($totalExpense, 0, ',', '.'); ?>
                    </div>

                    <div class="summary-desc">
                        Total nominal pada periode yang dipilih.
                    </div>

                </div>

            </div>

            <!-- Card 2 -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="sd-card summary-card">

                    <div class="summary-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </div>

                    <div class="summary-title">
                        Total Transaksi
                    </div>

                    <div class="summary-value">
                        <?php echo number_format($totalTransaction); ?>
                    </div>

                    <div class="summary-desc">
                        Jumlah transaksi pengeluaran.
                    </div>

                </div>

            </div>

            <!-- Card 3 -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="sd-card summary-card">

                    <div class="summary-icon">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>

                    <div class="summary-title">
                        Hari Ini
                    </div>

                    <div class="summary-value">
                        Rp <?php echo number_format($todayExpense, 0, ',', '.'); ?>
                    </div>

                    <div class="summary-desc">
                        Total pengeluaran hari ini.
                    </div>

                </div>

            </div>

            <!-- Card 4 -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="sd-card summary-card">

                    <div class="summary-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <div class="summary-title">
                        Bulan Ini
                    </div>

                    <div class="summary-value">
                        Rp <?php echo number_format($monthExpense, 0, ',', '.'); ?>
                    </div>

                    <div class="summary-desc">
                        Total pengeluaran bulan berjalan.
                    </div>

                </div>

            </div>

            </div>

        <!-- Grafik -->
 
       <div class="row g-4 mt-1">

        <div class="col-12 col-xl-8">

            <div class="sd-card chart-card h-100">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h5 class="chart-title mb-1">
                            Pengeluaran per Kategori
                        </h5>

                        <small class="text-muted">
                            Total nominal pengeluaran berdasarkan kategori pada periode yang dipilih.
                        </small>

                    </div>

                    <span class="badge-soft">

                        <?php echo count($chartLabels); ?>

                        Kategori

                    </span>

                </div>

                <?php if (count($chartLabels) > 0): ?>

                    <div style="height:340px;">

                        <canvas id="expenseBarChart"></canvas>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="fa-solid fa-chart-column"></i>

                        <h5>
                            Belum Ada Data
                        </h5>

                        <p>
                            Tidak terdapat data pengeluaran pada periode yang dipilih.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="col-12 col-xl-4">

            <div class="sd-card chart-card h-100">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h5 class="chart-title mb-1">
                            Distribusi Kategori
                        </h5>

                        <small class="text-muted">

                            Persentase pengeluaran setiap kategori.

                        </small>

                    </div>

                </div>

                <?php if (count($chartLabels) > 0): ?>

                    <div
                        class="d-flex align-items-center justify-content-center"
                        style="height:340px;">

                        <canvas id="expensePieChart"></canvas>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="fa-solid fa-chart-pie"></i>

                        <h5>
                            Belum Ada Data
                        </h5>

                        <p>

                            Grafik akan muncul setelah terdapat data.

                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        </div>

        <div class="row mt-4">

        <div class="col-12">

        <div class="sd-card table-card">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h5 class="chart-title mb-1">

                            Statistik Kategori

                        </h5>

                        <small class="text-muted">

                            Ringkasan total transaksi dan nominal tiap kategori.

                        </small>

                    </div>

                </div>
                <?php if (count($expenses) > 0): ?>

                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>Kategori</th>

                                <th class="text-center">Transaksi</th>

                                <th class="text-end">Total</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($categoryStatistics as $stat): ?>

                                <?php if ($stat['count'] == 0) continue; ?>

                                <tr>

                                    <td>

                                        <i class="fa-solid <?php echo htmlspecialchars($stat['icon']); ?> me-2"></i>

                                        <?php echo htmlspecialchars($stat['name']); ?>

                                    </td>

                                    <td class="text-center">

                                        <span class="badge-soft">

                                            <?php echo $stat['count']; ?>

                                        </span>

                                    </td>

                                    <td class="text-end fw-semibold">

                                        Rp <?php echo number_format($stat['total'], 0, ',', '.'); ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <i class="fa-solid fa-chart-column"></i>

                    <h5>

                        Belum Ada Statistik

                    </h5>

                    <p>

                        Tambahkan data pengeluaran terlebih dahulu agar statistik dapat ditampilkan.

                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</div>

</main>

<!-- Chart.js -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels = <?php echo json_encode(
    array_values($chartLabels),
    JSON_UNESCAPED_UNICODE
); ?>;

const values = <?php echo json_encode(
    array_values($chartAmounts),
    JSON_NUMERIC_CHECK
); ?>;

const colors = [

    "#1D3557",
    "#2C6BB3",
    "#1F8A70",
    "#B8862B",
    "#52606D",
    "#7B8FA1",
    "#457B9D",
    "#4C6FFF",
    "#3A86FF",
    "#5F6CAF"

];

const formatRupiah = (value) => {

    return "Rp " + Number(value).toLocaleString("id-ID");

};

if (document.getElementById("expenseBarChart")) {

    const ctxBar = document
        .getElementById("expenseBarChart")
        .getContext("2d");

    new Chart(ctxBar, {

        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: "Total Pengeluaran",
                data: values,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 48
            }]

        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            animation: {

                duration: 1200

            },

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    backgroundColor: "#1D3557",
                    titleColor: "#FFFFFF",
                    bodyColor: "#FFFFFF",
                    padding: 12,
                    callbacks: {
                        label(context) {
                            return formatRupiah(context.raw);
                        }
                    }
                }
            },

            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: "#52606D",
                        font: {
                            size: 12,
                            weight: "600"
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: "#52606D",
                        callback(value) {
                            return "Rp " + Number(value).toLocaleString("id-ID");
                        }
                    },
                    grid: {
                        color: "#EEF2F6"
                    }
                }
            }
        }
    });
}

if (document.getElementById("expensePieChart")) {
    const ctxPie = document
        .getElementById("expensePieChart")
        .getContext("2d");
    new Chart(ctxPie, {

        type: "doughnut",
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: "#FFFFFF",
                borderWidth: 2,
                hoverOffset: 12
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "65%",
            animation: {
                animateRotate: true,
                duration: 1200
            },

            plugins: {
                tooltip: {
                    backgroundColor: "#1D3557",
                    titleColor: "#FFFFFF",
                    bodyColor: "#FFFFFF",
                    padding: 12,
                    callbacks: {
                        label(context) {
                            const total = context.dataset.data.reduce(
                                (sum, value) => sum + value,
                                0
                            );

                            const percentage = total > 0
                                ? ((context.raw / total) * 100).toFixed(1)
                                : 0;
                            return (
                                context.label +
                                " : " +
                                formatRupiah(context.raw) +
                                " (" +
                                percentage +
                                "%)"
                            );
                        }
                    }
                },

                legend: {
                    position: "bottom",

                    labels: {
                        usePointStyle: true,
                        pointStyle: "circle",
                        padding: 18,
                        color: "#52606D",
                        font: {
                            size: 12,
                            weight: "600"
                        }
                    }
                }
            }
        }
    });
}

</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>