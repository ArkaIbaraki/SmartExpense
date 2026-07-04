<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Expense.php';

$currentUser = auth_current_user();
$expenseModel = new Expense((int) $currentUser['id']);

function redirect_back_with_flash(string $type, string $message): never
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
    header('Location: ../pages/historyExpense.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back_with_flash('danger', 'Metode permintaan tidak valid.');
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$expenseDate = trim((string) ($_POST['expense_date'] ?? ''));
$amountRaw = trim((string) ($_POST['amount'] ?? ''));
$notes = trim((string) ($_POST['notes'] ?? ''));

// Validasi dasar
if ($id <= 0) {
    redirect_back_with_flash('danger', 'Data pengeluaran tidak ditemukan.');
}

if ($name === '') {
    redirect_back_with_flash('danger', 'Nama pengeluaran wajib diisi.');
}

if ($categoryId <= 0) {
    redirect_back_with_flash('danger', 'Kategori wajib dipilih.');
}

$dateObj = DateTime::createFromFormat('Y-m-d', $expenseDate);
if (!$dateObj || $dateObj->format('Y-m-d') !== $expenseDate) {
    redirect_back_with_flash('danger', 'Format tanggal tidak valid.');
}

if (!is_numeric($amountRaw) || (float) $amountRaw <= 0) {
    redirect_back_with_flash('danger', 'Nominal harus berupa angka lebih dari 0.');
}
$amount = (float) $amountRaw;

// Pastikan kategori memang ada
$categoryExists = false;
foreach (Category::all() as $category) {
    if ((int) $category['id'] === $categoryId) {
        $categoryExists = true;
        break;
    }
}
if (!$categoryExists) {
    redirect_back_with_flash('danger', 'Kategori tidak valid.');
}

// Pastikan data milik user yang sedang login (cegah IDOR).
// $expenseModel sudah dibuat dengan scope user_id saat ini (lihat constructor),
// jadi getAll() hanya mengembalikan data milik user ini.
$ownedExpense = null;
foreach ($expenseModel->getAll([]) as $row) {
    if ((int) $row['id'] === $id) {
        $ownedExpense = $row;
        break;
    }
}

if ($ownedExpense === null) {
    redirect_back_with_flash('danger', 'Anda tidak memiliki akses untuk mengubah data ini.');
}

try {
    $expenseModel->update($id, [
        'name' => $name,
        'category_id' => $categoryId,
        'expense_date' => $expenseDate,
        'amount' => $amount,
        'notes' => $notes !== '' ? $notes : null,
    ]);

    redirect_back_with_flash('success', 'Data pengeluaran berhasil diperbarui.');
} catch (Throwable $e) {
    redirect_back_with_flash('danger', 'Gagal memperbarui data pengeluaran. Silakan coba lagi.');
}