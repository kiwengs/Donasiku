<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../koneksi.php';

if (!isLoggedIn() || !isUser()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login sebagai user'
    ]);
    exit;
}

$userId = (int) $_SESSION['user_id'];

function getProfile($koneksi, $userId) {
    $query = "SELECT id, name, email, phone, avatar_url FROM users WHERE id = ?";
    $stmt = $koneksi->prepare($query);

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query profil');
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Profil user tidak ditemukan');
    }

    $profile = $result->fetch_assoc();
    $stmt->close();

    return $profile;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $profile = getProfile($koneksi, $userId);

        $_SESSION['name'] = $profile['name'];
        $_SESSION['email'] = $profile['email'];

        echo json_encode([
            'success' => true,
            'data' => $profile
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = isset($input['name']) ? trim($input['name']) : '';

        if ($name === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nama lengkap harus diisi'
            ]);
            exit;
        }

        $query = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $koneksi->prepare($query);

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query update profil');
        }

        $stmt->bind_param('si', $name, $userId);
        $stmt->execute();
        $stmt->close();

        $profile = getProfile($koneksi, $userId);

        $_SESSION['name'] = $profile['name'];
        $_SESSION['email'] = $profile['email'];

        echo json_encode([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $profile
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $koneksi->close();
}
?>
