<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

auth_logout();
$_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Anda sudah logout.'];
header('Location: /pages/login.php');
exit;
