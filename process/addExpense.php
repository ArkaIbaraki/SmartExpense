<?php

declare(strict_types=1);

require_once __DIR__ . '/../classes/Expense.php';
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../includes/auth.php';
$currentUser = auth_current_user();
auth_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/addExpense.php');
    exit;
}

function redirectWithMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];

    header('Location: ../pages/addExpense.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$amount = trim((string) ($_POST['amount'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$expenseDate = trim((string) ($_POST['expense_date'] ?? ''));
$notes = trim((string) ($_POST['notes'] ?? ''));
$oldInput = [
    'name' => $name,
    'amount' => $amount,
    'category_id' => $categoryId,
    'expense_date' => $expenseDate,
    'notes' => $notes,
];

$_SESSION['old_input'] = $oldInput;

if ($name === '' || mb_strlen($name) > 150) {
    redirectWithMessage('danger', 'Nama pengeluaran wajib diisi dan maksimal 150 karakter.');
}

if ($amount === '' || !is_numeric($amount) || (float) $amount <= 0) {
    redirectWithMessage('danger', 'Nominal pengeluaran harus berupa angka lebih dari 0.');
}

if ($categoryId <= 0) {
    redirectWithMessage('danger', 'Kategori pengeluaran wajib dipilih.');
}

if ($expenseDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate)) {
    redirectWithMessage('danger', 'Tanggal pengeluaran tidak valid.');
}

$expenseDateObject = DateTime::createFromFormat('Y-m-d', $expenseDate);
if ($expenseDateObject === false || $expenseDateObject->format('Y-m-d') !== $expenseDate) {
    redirectWithMessage('danger', 'Tanggal pengeluaran tidak valid.');
}

if (Category::findById($categoryId) === null) {
    redirectWithMessage('danger', 'Kategori yang dipilih tidak ditemukan.');
}

try {
    $expenseModel = new Expense((int) $currentUser['id']);
    $expenseModel->create([
        'category_id' => $categoryId,
        'name' => $name,
        'amount' => $amount,
        'expense_date' => $expenseDate,
        'notes' => $notes,
    ]);

    unset($_SESSION['old_input']);
    redirectWithMessage('success', 'Pengeluaran berhasil disimpan.');
} catch (Throwable $throwable) {
    redirectWithMessage('danger', 'Gagal menyimpan pengeluaran: ' . $throwable->getMessage());
}
