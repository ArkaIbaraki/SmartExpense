<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

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

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect_back_with_flash('danger', 'Data pengeluaran tidak ditemukan.');
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
    redirect_back_with_flash('danger', 'Anda tidak memiliki akses untuk menghapus data ini.');
}

try {
    $expenseModel->delete($id);

    redirect_back_with_flash('success', 'Data pengeluaran berhasil dihapus.');
} catch (Throwable $e) {
    redirect_back_with_flash('danger', 'Gagal menghapus data pengeluaran. Silakan coba lagi.');
}