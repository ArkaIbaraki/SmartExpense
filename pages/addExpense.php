<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

require_once __DIR__ . '/../classes/Category.php';

$pageTitle = 'Tambah Pengeluaran';
$activePage = 'add-expense';
$categories = Category::all();
$today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
$flashMessage = $_SESSION['flash_message'] ?? null;
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['flash_message'], $_SESSION['old_input']);

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

    /* Form fields */
    .dsh .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--dsh-slate);
        margin-bottom: 0.5rem;
    }

    .dsh .form-control,
    .dsh .form-select,
    .dsh .input-group-text {
        border: 1px solid var(--dsh-border);
        border-radius: 8px;
        font-size: 0.92rem;
        color: var(--dsh-ink);
        padding: 0.6rem 0.85rem;
        background-color: var(--dsh-surface);
    }

    .dsh .input-group-text {
        background-color: var(--dsh-bg);
        color: var(--dsh-slate);
        font-weight: 600;
        border-right: none;
    }

    .dsh .input-group .form-control {
        border-left: none;
    }

    .dsh .input-group:focus-within .input-group-text {
        border-color: var(--dsh-navy);
    }

    .dsh .form-control:focus,
    .dsh .form-select:focus {
        border-color: var(--dsh-navy);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--dsh-navy) 12%, transparent);
    }

    .dsh .form-control::placeholder {
        color: var(--dsh-faint);
    }

    .dsh textarea.form-control {
        resize: vertical;
    }

    .dsh .invalid-feedback {
        font-size: 0.78rem;
    }

    /* Buttons */
    .dsh .sd-btn-primary {
        background: var(--dsh-navy);
        border: 1px solid var(--dsh-navy);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.6rem 1.4rem;
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
        padding: 0.6rem 1.4rem;
        font-size: 0.9rem;
    }

    .dsh .btn-light.border:hover {
        background: var(--dsh-bg);
        color: var(--dsh-ink);
    }

    /* Alert */
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
        <div class="sd-card p-4 p-lg-5 mx-auto" style="max-width: 900px;">
            <?php if (is_array($flashMessage)): ?>
                <div class="alert alert-<?php echo htmlspecialchars((string) $flashMessage['type'], ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h4 class="mb-1">Form Tambah Pengeluaran</h4>
                <p class="text-muted mb-0">Isi data pengeluaran baru dengan informasi yang lengkap dan valid.</p>
            </div>

            <form action="../process/addExpense.php" method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label for="name" class="form-label">Nama Pengeluaran</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="150" value="<?php echo htmlspecialchars((string) ($oldInput['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                        <div class="invalid-feedback">Nama pengeluaran wajib diisi.</div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="amount" class="form-label">Nominal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" value="<?php echo htmlspecialchars((string) ($oldInput['amount'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="invalid-feedback">Nominal harus lebih dari 0.</div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['id']; ?>" <?php echo ((int) ($oldInput['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Pilih kategori terlebih dahulu.</div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="expense_date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo htmlspecialchars((string) ($oldInput['expense_date'] ?? $today), ENT_QUOTES, 'UTF-8'); ?>" required>
                        <div class="invalid-feedback">Tanggal pengeluaran wajib diisi.</div>
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" maxlength="1000" placeholder="Tambahkan catatan bila diperlukan"><?php echo htmlspecialchars((string) ($oldInput['notes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="col-12 d-flex flex-column flex-sm-row gap-2 justify-content-end mt-2">
                        <a href="dashboard.php" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn sd-btn-primary text-white">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
(() => {
    'use strict';

    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>