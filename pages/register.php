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
    <title>Register | Smart Daily Expense Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%); }
        .auth-shell { min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; }
        .auth-card { width: 100%; max-width: 440px; border: 0; border-radius: 24px; box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25); }
        .auth-badge { width: 58px; height: 58px; border-radius: 18px; background: rgba(29, 78, 216, 0.1); display: inline-flex; align-items: center; justify-content: center; color: #1d4ed8; }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="auth-badge"><i class="fa-solid fa-user-plus fs-4"></i></div>
                <div>
                    <h1 class="h4 mb-1">Buat Akun</h1>
                    <p class="text-muted mb-0">Daftar untuk mulai memakai aplikasi</p>
                </div>
            </div>

            <?php if (is_array($flashMessage)): ?>
                <div class="alert alert-<?php echo htmlspecialchars((string) $flashMessage['type'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="../process/register.php" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="invalid-feedback">Username wajib diisi.</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Email wajib valid.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    <div class="invalid-feedback">Password minimal 6 karakter.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                    <div class="invalid-feedback">Konfirmasi password wajib diisi.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Daftar</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php">Sudah punya akun? Login</a>
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
