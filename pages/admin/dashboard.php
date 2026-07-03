<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
auth_require_admin();

require_once __DIR__ . '/../../classes/Database.php';

$pageTitle = 'Dashboard Admin';
$activePage = 'admin-dashboard';
$currentUser = auth_current_user();
$connection = Database::getInstance()->getConnection();

$totalUsers = (int) $connection->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalTransactions = (int) $connection->query('SELECT COUNT(*) FROM expenses')->fetchColumn();
$totalAmount = (float) $connection->query('SELECT COALESCE(SUM(amount), 0) FROM expenses')->fetchColumn();
$newUsersThisWeek = (int) $connection->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
$avgTransactionPerUser = $totalUsers > 0 ? $totalTransactions / $totalUsers : 0.0;

$latestUsersStatement = $connection->query('SELECT id, name, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5');
$latestUsers = $latestUsersStatement->fetchAll();

$roleBreakdownStatement = $connection->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role ORDER BY total DESC');
$roleBreakdownRaw = $roleBreakdownStatement->fetchAll();
$roleColors = [
    'admin' => '#2C6BB3',
    'user' => '#1D3557',
];
$roleBreakdown = array_map(static function (array $row) use ($totalUsers, $roleColors): array {
    $percentage = $totalUsers > 0 ? ((int) $row['total'] / $totalUsers) * 100 : 0.0;

    return [
        'role' => $row['role'],
        'total' => (int) $row['total'],
        'percentage' => $percentage,
        'color' => $roleColors[$row['role']] ?? '#52606D',
    ];
}, $roleBreakdownRaw);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar-admin.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .adm {
        --adm-ink: #14213D;
        --adm-muted: #6B7280;
        --adm-faint: #9CA3AF;
        --adm-bg: #F6F7F9;
        --adm-surface: #FFFFFF;
        --adm-border: #E7E9EE;
        --adm-navy: #1D3557;
        --adm-blue: #2C6BB3;
        --adm-teal: #1F8A70;
        --adm-slate: #52606D;
        --adm-radius: 10px;
        background: var(--adm-bg);
        color: var(--adm-ink);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .adm .sd-card {
        background: var(--adm-surface);
        border: 1px solid var(--adm-border);
        border-radius: var(--adm-radius);
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .adm .sd-card:hover {
        box-shadow: 0 2px 10px rgba(20, 33, 61, 0.05);
    }

    .adm .stat-card {
        position: relative;
        overflow: hidden;
        border-top: 2px solid var(--adm-accent, var(--adm-navy));
    }

    .adm .stat-card.accent-blue { --adm-accent: var(--adm-blue); }
    .adm .stat-card.accent-navy { --adm-accent: var(--adm-navy); }
    .adm .stat-card.accent-teal { --adm-accent: var(--adm-teal); }
    .adm .stat-card.accent-slate { --adm-accent: var(--adm-slate); }

    .adm .stat-eyebrow {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--adm-muted);
        margin-bottom: 0.6rem;
    }

    .adm .stat-value {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 500;
        font-size: 1.65rem;
        line-height: 1.2;
        color: var(--adm-ink);
        letter-spacing: -0.01em;
    }

    .adm .stat-value .stat-currency {
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--adm-muted);
        margin-right: 0.2rem;
    }

    .adm .sd-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--adm-accent, var(--adm-navy)) 10%, white);
        color: var(--adm-accent, var(--adm-navy));
        font-size: 0.95rem;
    }

    .adm h5 {
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 1rem;
        color: var(--adm-ink);
        letter-spacing: -0.01em;
    }

    .adm .text-muted {
        color: var(--adm-muted) !important;
        font-size: 0.86rem;
    }

    .adm .btn-outline-primary {
        --bs-btn-color: var(--adm-navy);
        --bs-btn-border-color: var(--adm-border);
        --bs-btn-hover-bg: var(--adm-navy);
        --bs-btn-hover-border-color: var(--adm-navy);
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.4rem 0.9rem;
    }

    .adm .table thead th {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--adm-faint);
        border-bottom: 1px solid var(--adm-border);
        padding-bottom: 0.75rem;
    }

    .adm .table tbody td {
        border-bottom: 1px solid var(--adm-border);
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .adm .table tbody tr:last-child td {
        border-bottom: none;
    }

    .adm .table tbody tr:hover {
        background: var(--adm-bg);
    }

    .adm .table .fw-semibold {
        color: var(--adm-ink);
        font-weight: 600;
    }

    .adm .badge.text-bg-light {
        background: #EEF2F7 !important;
        border: none !important;
        color: var(--adm-slate);
        font-weight: 600;
        font-size: 0.74rem;
        padding: 0.4em 0.75em;
    }

    /* Insight card */
    .adm .insight-item {
        background: var(--adm-surface);
        border: 1px solid var(--adm-border);
        border-left: 3px solid var(--adm-blue);
        border-radius: 8px;
        padding: 0.9rem 1rem;
        font-size: 0.88rem;
        color: var(--adm-slate);
        line-height: 1.5;
    }
</style>

<main class="sd-main adm">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="p-3 p-lg-4">
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-blue p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Total User</p>
                            <h3 class="stat-value mb-0 mt-2"><?php echo number_format($totalUsers, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-navy p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Total Transaksi</p>
                            <h3 class="stat-value mb-0 mt-2"><?php echo number_format($totalTransactions, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-receipt"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-teal p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">Total Nominal</p>
                            <h3 class="stat-value mb-0 mt-2"><span class="stat-currency">Rp</span><?php echo number_format($totalAmount, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-wallet"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sd-card stat-card accent-slate p-4 h-100">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <p class="stat-eyebrow mb-0">User Baru 7 Hari</p>
                            <h3 class="stat-value mb-0 mt-2"><?php echo number_format($newUsersThisWeek, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="sd-stat-icon"><i class="fa-solid fa-user-plus"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-8">
                <div class="sd-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">User Terbaru</h5>
                            <p class="text-muted mb-0">Daftar akun terakhir yang terdaftar di sistem.</p>
                        </div>
                        <a href="../register.php" class="btn btn-sm btn-outline-primary">Tambah User</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Tanggal Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($latestUsers === []): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada user.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($latestUsers as $user): ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo htmlspecialchars((string) $user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-light border">
                                                    <?php echo htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime((string) $user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="sd-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Insight Singkat</h5>
                            <p class="text-muted mb-0">Ringkasan aktivitas sistem secara umum.</p>
                        </div>
                    </div>

                    <div class="insight-item mb-3">
                        Rata-rata setiap user tercatat memiliki <?php echo number_format($avgTransactionPerUser, 1, ',', '.'); ?> transaksi pengeluaran.
                    </div>
                    <p class="text-muted mb-0">
                        <?php if ($newUsersThisWeek > 0): ?>
                            Ada <?php echo number_format($newUsersThisWeek, 0, ',', '.'); ?> user baru mendaftar dalam 7 hari terakhir.
                        <?php else: ?>
                            Belum ada user baru yang mendaftar dalam 7 hari terakhir.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-12 col-xl-6">
                <div class="sd-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Komposisi Role User</h5>
                            <p class="text-muted mb-0">Perbandingan jumlah admin dan user biasa.</p>
                        </div>
                    </div>

                    <?php if ($roleBreakdown === []): ?>
                        <p class="text-muted mb-0">Belum ada data user untuk ditampilkan.</p>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach ($roleBreakdown as $role): ?>
                                <div class="p-3 rounded-4 border bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="fw-semibold text-capitalize"><?php echo htmlspecialchars((string) $role['role'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <span class="badge text-bg-light border">
                                            <?php echo number_format($role['percentage'], 2, ',', '.'); ?>%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $role['percentage']; ?>%; background-color: <?php echo htmlspecialchars((string) $role['color'], ENT_QUOTES, 'UTF-8'); ?>;" aria-valuenow="<?php echo $role['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="mt-2 text-muted"><?php echo number_format($role['total'], 0, ',', '.'); ?> user</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
