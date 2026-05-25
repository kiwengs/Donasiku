<?php
/**
 * Auth Helper
 * File ini membantu dalam pengecekan session dan authentikasi user
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Cek apakah user adalah user biasa
 * @return bool
 */
function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Get user data dari session
 * @return array|null
 */
function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Redirect jika belum login
 * @param string $redirectTo - URL redirect setelah login
 */
function requireLogin($redirectTo = null) {
    if (!isLoggedIn()) {
        if ($redirectTo) {
            header('Location: login.php?redirect=' . urlencode($redirectTo));
        } else {
            header('Location: login.php');
        }
        exit;
    }
}

/**
 * Redirect jika belum admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Anda tidak memiliki akses sebagai admin'
        ]);
        exit;
    }
}

/**
 * Cek session via AJAX
 * Gunakan untuk verifikasi saat halaman dimuat
 */
function checkSessionStatus() {
    if (isLoggedIn()) {
        return [
            'logged_in' => true,
            'user' => getUserData()
        ];
    }
    
    return [
        'logged_in' => false,
        'user' => null
    ];
}
?>
