<?php
header('Content-Type: application/json');
session_start();
require_once '../koneksi.php';
require_once '../auth.php';

// Hanya admin yang boleh
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$program_id = intval($input['program_id'] ?? 0);
$title = trim($input['title'] ?? '');
$category = trim($input['category'] ?? '');
$target_amount = floatval($input['target_amount'] ?? 0);
$description = trim($input['description'] ?? '');
$image_url = trim($input['image_url'] ?? '');
$status = trim($input['status'] ?? 'active');

if (!$program_id || !$title || !$category || $target_amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

if (!in_array($status, ['active', 'completed', 'cancelled'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

$stmt = $koneksi->prepare("UPDATE programs SET title = ?, category = ?, description = ?, target_amount = ?, image_url = ?, status = ? WHERE id = ?");
$stmt->bind_param('sssdsi', $title, $category, $description, $target_amount, $image_url, $status, $program_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Program berhasil diperbarui']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui program']);
}
?>
