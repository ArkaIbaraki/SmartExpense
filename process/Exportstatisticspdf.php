<?php

declare(strict_types=1);

/**
 * process/exportStatisticsPDF.php
 * -----------------------------------------------------------
 * Export statistik expense ke PDF, sesuai dengan filter bulan/tahun
 * yang sedang aktif di pages/admin/statExpense.php.
 *
 * Sengaja TIDAK memakai class Statistics.php, karena Statistics.php
 * menghitung dari SELURUH data (tanpa filter bulan/tahun), sedangkan
 * halaman statExpense.php menampilkan data yang sudah difilter per
 * bulan/tahun via Expense::getAll(). Supaya PDF konsisten dengan apa
 * yang dilihat user di halaman, kita replikasi logika yang sama.
 *
 * SETUP: sama seperti exportHistoryPDF.php
 * AKSES: process/exportStatisticsPDF.php?month=7&year=2026
 */

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../classes/Expense.php';
require_once __DIR__ . '/../classes/Category.php';

$currentUser = auth_current_user();
$userId = (int) $currentUser['id'];
$expenseModel = new Expense($userId);
$categories = Category::all();

$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

$currentMonth = (int) date('m');
$currentYear = (int) date('Y');

$selectedMonth = (int) ($_GET['month'] ?? $currentMonth);
$selectedYear = (int) ($_GET['year'] ?? $currentYear);

$selectedMonth = ($selectedMonth >= 1 && $selectedMonth <= 12) ? $selectedMonth : $currentMonth;
$selectedYear = ($selectedYear >= 2020 && $selectedYear <= 2100) ? $selectedYear : $currentYear;

// --- Filter expense sesuai bulan & tahun terpilih (logika sama seperti statExpense.php) ---
$allExpenses = $expenseModel->getAll();

$expenses = array_filter(
    $allExpenses,
    static function (array $expense) use ($selectedMonth, $selectedYear): bool {
        $date = strtotime((string) $expense['expense_date']);

        return (int) date('n', $date) === $selectedMonth
            && (int) date('Y', $date) === $selectedYear;
    }
);

$totalTransaction = count($expenses);
$totalExpense = 0;
foreach ($expenses as $expense) {
    $totalExpense += (float) $expense['amount'];
}

// --- Breakdown per kategori (logika sama seperti statExpense.php) ---
$categoryStatistics = [];
foreach ($categories as $category) {
    $categoryStatistics[(int) $category['id']] = [
        'name' => $category['name'],
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

// Cari yang terbesar & terkecil dalam periode terpilih
$largest = null;
$smallest = null;
foreach ($expenses as $expense) {
    if ($largest === null || (float) $expense['amount'] > (float) $largest['amount']) {
        $largest = $expense;
    }
    if ($smallest === null || (float) $expense['amount'] < (float) $smallest['amount']) {
        $smallest = $expense;
    }
}

$averageDaily = 0.0;
$daysInMonth = (int) date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
if ($daysInMonth > 0) {
    $averageDaily = $totalExpense / $daysInMonth;
}

// =============================================================
// PDF
// =============================================================
class StatisticsPDF extends FPDF
{
    public string $periodeLabel = '';

    public function Header(): void
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'SmartExpense', 0, 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Laporan Statistik Pengeluaran', 0, 1, 'C');

        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 6, 'Periode: ' . $this->periodeLabel, 0, 1, 'C');

        $this->Ln(4);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function SectionTitle(string $title): void
    {
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(50, 50, 50);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 8, '  ' . $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);
    }

    public function SummaryRow(string $label, string $value): void
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell(80, 7, $label, 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 7, $value, 0, 1, 'L');
    }

    public function CategoryTableHeader(): void
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(70, 8, 'Kategori', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Transaksi', 1, 0, 'C', true);
        $this->Cell(60, 8, 'Total', 1, 1, 'C', true);
    }

    public function CategoryRow(string $nama, int $transaksi, float $total): void
    {
        $this->SetFont('Arial', '', 9);
        $this->Cell(70, 7, $nama, 1);
        $this->Cell(40, 7, (string) $transaksi, 1, 0, 'C');
        $this->Cell(60, 7, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');
    }
}

$pdf = new StatisticsPDF();
$pdf->periodeLabel = $months[$selectedMonth] . ' ' . $selectedYear;
$pdf->AliasNbPages();
$pdf->AddPage();

// --- Ringkasan umum ---
$pdf->SectionTitle('Ringkasan Umum');
$pdf->SummaryRow('Total Transaksi', number_format($totalTransaction, 0, ',', '.'));
$pdf->SummaryRow('Total Pengeluaran', 'Rp ' . number_format($totalExpense, 0, ',', '.'));
$pdf->SummaryRow('Rata-rata Pengeluaran per Hari', 'Rp ' . number_format($averageDaily, 0, ',', '.'));

// --- Pengeluaran terbesar & terkecil dalam periode ---
if ($totalTransaction > 0) {
    $pdf->SectionTitle('Pengeluaran Ekstrem Bulan Ini');
    if ($largest !== null) {
        $pdf->SummaryRow(
            'Terbesar',
            $largest['name'] . ' - Rp ' . number_format((float) $largest['amount'], 0, ',', '.')
            . ' (' . date('d/m/Y', strtotime((string) $largest['expense_date'])) . ')'
        );
    }
    if ($smallest !== null) {
        $pdf->SummaryRow(
            'Terkecil',
            $smallest['name'] . ' - Rp ' . number_format((float) $smallest['amount'], 0, ',', '.')
            . ' (' . date('d/m/Y', strtotime((string) $smallest['expense_date'])) . ')'
        );
    }
}

// --- Breakdown per kategori ---
$pdf->SectionTitle('Statistik Kategori');
$hasCategoryData = false;
foreach ($categoryStatistics as $stat) {
    if ($stat['count'] > 0) {
        $hasCategoryData = true;
        break;
    }
}

if (!$hasCategoryData) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 8, 'Belum ada statistik pada periode ini.', 0, 1);
} else {
    $pdf->CategoryTableHeader();
    foreach ($categoryStatistics as $stat) {
        if ($stat['count'] === 0) {
            continue;
        }
        $pdf->CategoryRow((string) $stat['name'], (int) $stat['count'], (float) $stat['total']);
    }
}

$filename = 'statistik-expense-' . $selectedYear . '-' . str_pad((string) $selectedMonth, 2, '0', STR_PAD_LEFT) . '.pdf';
$pdf->Output('D', $filename);