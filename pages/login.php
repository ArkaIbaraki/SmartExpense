<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
auth_require_guest();
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Smart Daily Expense Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}body{min-height:100vh;background:#f4f5f7;color:#1f2430}
        .auth-shell{min-height:100vh;display:grid;place-items:center;padding:1.5rem}.auth-card{width:100%;max-width:420px;border:1px solid #e3e5e9;border-radius:10px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.06);background:#fff}
        .auth-badge{width:52px;height:52px;border-radius:10px;background:#1f2430;display:inline-flex;align-items:center;justify-content:center;color:#fff}
        .auth-badge i{font-size:1.15rem}.auth-title{font-size:1.15rem;font-weight:600;color:#1f2430;letter-spacing:-.01em}
        .auth-subtitle{font-size:.85rem;color:#6b7280}.form-label{font-size:.82rem;font-weight:500;color:#374151;margin-bottom:.35rem}
        .form-control{border:1px solid #d8dade;border-radius:6px;padding:.6rem .8rem;font-size:.92rem;background:#fcfcfd;transition:border-color .15s ease,box-shadow .15s ease}
        .form-control:focus{border-color:#9ca3af;box-shadow:0 0 0 3px rgba(31,36,48,.06);background:#fff}
        .btn-corporate{background:#1f2430;border:1px solid #1f2430;color:#fff;border-radius:6px;font-size:.92rem;font-weight:500;padding:.65rem;transition:background .15s ease}
        .btn-corporate:hover{background:#333a4a;color:#fff}.auth-link{font-size:.85rem;color:#6b7280;text-decoration:none;border-bottom:1px solid transparent}
        .auth-link:hover{color:#1f2430;border-bottom-color:#1f2430}.divider-text{font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em}
        .alert{border-radius:6px;font-size:.85rem;border:1px solid transparent}.invalid-feedback{font-size:.78rem}
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="../assets/images/logo-smart-expense.png"
                    alt="Smart Expense Logo"
                    width="50"
                    height="50"
                    class="img-fluid">
                <div>
                    <h1 class="auth-title mb-1">Smart Expense</h1>
                    <p class="auth-subtitle mb-0">Masuk ke dashboard pengeluaran</p>
                </div>
            </div>
            <?php if (is_array($flashMessage)): ?>
                <div class="alert alert-<?php echo htmlspecialchars((string) $flashMessage['type'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <form action="../process/login.php" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="identifier" class="form-label">Username atau Email</label>
                    <input type="text" class="form-control" id="identifier" name="identifier" required>
                    <div class="invalid-feedback">Username atau email wajib diisi.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Password wajib diisi.</div>
                </div>
                <button type="submit" class="btn btn-corporate w-100 mt-2">Login</button>
            </form>
            <div class="d-flex align-items-center gap-2 my-4">
                <div class="flex-grow-1 border-top"></div>
                <span class="divider-text">atau</span>
                <div class="flex-grow-1 border-top"></div>
            </div>
            <div class="text-center">
                <a href="register.php" class="auth-link">Belum punya akun? <strong>Daftar</strong></a>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    'use strict';
    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
})();
</script>
</body>
</html>