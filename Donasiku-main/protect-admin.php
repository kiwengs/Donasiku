<?php
/**
 * File Proteksi Admin
 * Include file ini di halaman admin yang memerlukan akses admin
 */

require_once __DIR__ . '/auth.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    // Redirect ke login page
    header('Location: login.php');
    exit;
}

// Cek apakah user adalah admin
if (!isAdmin()) {
    // User bukan admin, redirect ke dashboard user
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Anda tidak memiliki akses admin'
    ]);
    exit;
}
?>
