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

$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$target_amount = floatval($_POST['target_amount'] ?? 0);
$description = trim($_POST['description'] ?? '');
$image_url = trim($_POST['image_url'] ?? '');

if (!$title || !$category || $target_amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$stmt = $koneksi->prepare("INSERT INTO programs (title, category, description, target_amount, image_url, status) VALUES (?, ?, ?, ?, ?, 'active')");
$stmt->bind_param('sssds', $title, $category, $description, $target_amount, $image_url);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Program berhasil ditambahkan']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menambah program']);
}
?>