<?php

declare(strict_types=1);

/**
 * process/exportHistoryPDF.php
 * -----------------------------------------------------------
 * Export history expense milik user yang sedang login ke PDF.
 * Menggunakan FPDF (native, tanpa Composer) + class Expense.php
 * yang sudah ada.
 *
 * SETUP:
 * 1. Extract FPDF dari https://www.fpdf.org/en/download.php
 *    ke: SmartExpense/libs/fpdf/fpdf.php
 * 2. File ini ditaruh di: SmartExpense/process/exportHistoryPDF.php
 *
 * AKSES:
 *   process/exportHistoryPDF.php
 *   process/exportHistoryPDF.php?date_from=2026-01-01&date_to=2026-07-01
 *   process/exportHistoryPDF.php?category_id=3
 *   process/exportHistoryPDF.php?search=makan
 */

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../classes/Expense.php';

$currentUser = auth_current_user();
$userId = (int) $currentUser['id'];

// --- Ambil filter dari query string (opsional) ---
$filters = [
    'search'      => $_GET['search'] ?? null,
    'category_id' => $_GET['category_id'] ?? null,
    'date_from'   => $_GET['date_from'] ?? null,
    'date_to'     => $_GET['date_to'] ?? null,
    'sort_by'     => 'expense_date',
    'sort_order'  => 'DESC',
];
$filters = array_filter($filters, static fn ($v) => $v !== null && $v !== '');

$expenseModel = new Expense($userId);
$expenses = $expenseModel->getAll($filters);
$totalAmount = array_sum(array_map(static fn (array $row): float => (float) $row['amount'], $expenses));

// =============================================================
// PDF
// =============================================================
class HistoryPDF extends FPDF
{
    public string $periode = '';

    private const COL_TANGGAL = 25;
    private const COL_KATEGORI = 35;
    private const COL_NAMA = 55;
    private const COL_CATATAN = 45;
    private const COL_JUMLAH = 30;

    public function Header(): void
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'SmartExpense', 0, 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Laporan History Expense', 0, 1, 'C');

        if ($this->periode !== '') {
            $this->SetFont('Arial', 'I', 9);
            $this->Cell(0, 6, $this->periode, 0, 1, 'C');
        }

        $this->Ln(4);

        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(self::COL_TANGGAL, 8, 'Tanggal', 1, 0, 'C', true);
        $this->Cell(self::COL_KATEGORI, 8, 'Kategori', 1, 0, 'C', true);
        $this->Cell(self::COL_NAMA, 8, 'Nama', 1, 0, 'C', true);
        $this->Cell(self::COL_CATATAN, 8, 'Catatan', 1, 0, 'C', true);
        $this->Cell(self::COL_JUMLAH, 8, 'Jumlah', 1, 1, 'C', true);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function RowExpense(string $tanggal, string $kategori, string $nama, string $catatan, float $jumlah): void
    {
        $this->SetFont('Arial', '', 8);

        $x = $this->GetX();
        $y = $this->GetY();

        // Hitung tinggi baris berdasarkan konten terpanjang (nama/catatan)
        $lineHeight = 5;
        $namaLines = $this->countLines($nama, self::COL_NAMA);
        $catatanLines = $this->countLines($catatan, self::COL_CATATAN);
        $rowHeight = max($namaLines, $catatanLines, 1) * $lineHeight;

        $this->Cell(self::COL_TANGGAL, $rowHeight, $tanggal, 1);
        $this->Cell(self::COL_KATEGORI, $rowHeight, $kategori, 1);

        $xNama = $this->GetX();
        $yNama = $this->GetY();
        $this->MultiCell(self::COL_NAMA, $lineHeight, $nama, 1);
        $this->SetXY($xNama + self::COL_NAMA, $yNama);

        $xCatatan = $this->GetX();
        $this->MultiCell(self::COL_CATATAN, $lineHeight, $catatan !== '' ? $catatan : '-', 1);
        $this->SetXY($xCatatan + self::COL_CATATAN, $yNama);

        $this->Cell(self::COL_JUMLAH, $rowHeight, 'Rp ' . number_format($jumlah, 0, ',', '.'), 1, 0, 'R');

        $this->SetXY($x, $y + $rowHeight);
    }

    private function countLines(string $text, float $width): int
    {
        // Estimasi kasar jumlah baris untuk MultiCell (cukup akurat untuk font 8pt Arial)
        $charsPerLine = max(1, (int) ($width / 1.9));
        return max(1, (int) ceil(mb_strlen($text) / $charsPerLine));
    }
}

$pdf = new HistoryPDF();
$pdf->AliasNbPages();

if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
    $pdf->periode = 'Periode: ' . date('d M Y', strtotime($filters['date_from']))
        . ' - ' . date('d M Y', strtotime($filters['date_to']));
}

$pdf->AddPage();

if (empty($expenses)) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Tidak ada data pengeluaran untuk ditampilkan.', 0, 1, 'C');
} else {
    foreach ($expenses as $row) {
        $pdf->RowExpense(
            date('d/m/Y', strtotime((string) $row['expense_date'])),
            (string) $row['category_name'],
            (string) $row['name'],
            (string) ($row['notes'] ?? ''),
            (float) $row['amount']
        );
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(140, 8, 'TOTAL', 1, 0, 'R');
    $pdf->Cell(30, 8, 'Rp ' . number_format($totalAmount, 0, ',', '.'), 1, 1, 'R');
}

$filename = 'history-expense-' . date('Y-m-d_His') . '.pdf';
$pdf->Output('D', $filename);