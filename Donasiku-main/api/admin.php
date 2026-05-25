<?php
header('Content-Type: application/json');
session_start();
require_once '../koneksi.php';
require_once '../auth.php';

// Cek apakah user sudah login sebagai admin
requireAdmin();

$action = isset($_GET['action']) ? $_GET['action'] : 'transactions';

try {
    // ===== GET ALL TRANSACTIONS FOR ADMIN =====
    if ($action === 'transactions') {
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        
        $query = "SELECT 
                    t.id,
                    t.trx_code,
                    t.amount,
                    t.payment_method,
                    t.message,
                    t.status,
                    t.created_at,
                    u.name as donor_name,
                    u.email as donor_email,
                    p.title as program_title
                  FROM transactions t
                  JOIN users u ON t.user_id = u.id
                  JOIN programs p ON t.program_id = p.id";
        
        if ($status && in_array($status, ['pending', 'success', 'failed'])) {
            $query .= " WHERE t.status = '$status'";
        }
        
        $query .= " ORDER BY t.created_at DESC";
        
        $result = $koneksi->query($query);
        
        if (!$result) {
            throw new Exception("Query error: " . $koneksi->error);
        }
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $transactions,
            'count' => count($transactions)
        ]);
    }
    
    // ===== GET PROGRAM STATISTICS =====
    else if ($action === 'stats') {
        // Get program stats
        $programQuery = "SELECT 
                          COUNT(*) as total_programs,
                          SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_programs,
                          SUM(collected_amount) as total_collected,
                          SUM(target_amount) as total_target
                        FROM programs";
        
        $programResult = $koneksi->query($programQuery);
        $programStats = $programResult->fetch_assoc();
        
        // Get transaction stats
        $transactionQuery = "SELECT 
                              COUNT(*) as total_transactions,
                              SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_success,
                              SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                              SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count
                            FROM transactions";
        
        $transactionResult = $koneksi->query($transactionQuery);
        $transactionStats = $transactionResult->fetch_assoc();
        
        // Get donor stats dari tabel users
        $donorQuery = "SELECT COUNT(*) as total_donors FROM users WHERE role = 'user'";
        $donorResult = $koneksi->query($donorQuery);
        $donorStats = $donorResult->fetch_assoc();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'programs' => $programStats,
                'transactions' => $transactionStats,
                'donors' => $donorStats
            ]
        ]);
    }

    // ===== GET ALL PROGRAMS =====
    else if ($action === 'programs') {
        $query = "SELECT * FROM programs ORDER BY created_at DESC";
        $result = $koneksi->query($query);

        if (!$result) {
            throw new Exception("Query error: " . $koneksi->error);
        }

        $programs = [];
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $programs,
            'count' => count($programs)
        ]);
    }

    // ===== GET DONATUR USERS =====
    else if ($action === 'users') {
        $query = "SELECT id, name, email, phone, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC";
        $result = $koneksi->query($query);

        if (!$result) {
            throw new Exception("Query error: " . $koneksi->error);
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $users,
            'count' => count($users)
        ]);
    }

    // ===== GET ADMIN USERS =====
    else if ($action === 'admins') {
        $query = "SELECT id, name, email, avatar_url, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC";
        $result = $koneksi->query($query);

        if (!$result) {
            throw new Exception("Query error: " . $koneksi->error);
        }

        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $admins,
            'count' => count($admins)
        ]);
    }
    
    // ===== UPDATE TRANSACTION STATUS =====
    else if ($action === 'verify') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method tidak diizinkan'
            ]);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['transaction_id']) || !isset($input['new_status'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'transaction_id dan new_status harus diisi'
            ]);
            exit;
        }
        
        $transaction_id = (int)$input['transaction_id'];
        $new_status = $input['new_status'];
        $admin_id = $_SESSION['user_id'];
        
        if (!in_array($new_status, ['success', 'failed'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Status tidak valid'
            ]);
            exit;
        }
        
        try {
            $koneksi->begin_transaction();
            
            // Get transaction data
            $getTrx = "SELECT * FROM transactions WHERE id = ?";
            $getTrxStmt = $koneksi->prepare($getTrx);
            $getTrxStmt->bind_param('i', $transaction_id);
            $getTrxStmt->execute();
            $trxResult = $getTrxStmt->get_result();
            
            if ($trxResult->num_rows === 0) {
                throw new Exception("Transaksi tidak ditemukan");
            }
            
            $trxData = $trxResult->fetch_assoc();
            
            $oldStatus = $trxData['status'];

            // Update transaction status
            $updateTrx = "UPDATE transactions SET status = ?, verified_by = ? WHERE id = ?";
            $updateStmt = $koneksi->prepare($updateTrx);
            $updateStmt->bind_param('sii', $new_status, $admin_id, $transaction_id);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Gagal update status transaksi");
            }
            
            // Sinkronkan collected_amount agar tidak dobel saat verifikasi ulang
            if ($oldStatus !== 'success' && $new_status === 'success') {
                $updateProgram = "UPDATE programs SET collected_amount = collected_amount + ? WHERE id = ?";
                $updateProgStmt = $koneksi->prepare($updateProgram);
                $updateProgStmt->bind_param('di', $trxData['amount'], $trxData['program_id']);
                
                if (!$updateProgStmt->execute()) {
                    throw new Exception("Gagal update program amount");
                }
            } else if ($oldStatus === 'success' && $new_status === 'failed') {
                $updateProgram = "UPDATE programs SET collected_amount = GREATEST(collected_amount - ?, 0) WHERE id = ?";
                $updateProgStmt = $koneksi->prepare($updateProgram);
                $updateProgStmt->bind_param('di', $trxData['amount'], $trxData['program_id']);

                if (!$updateProgStmt->execute()) {
                    throw new Exception("Gagal update program amount");
                }
            }
            
            $koneksi->commit();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Status transaksi berhasil diupdate',
                'data' => [
                    'transaction_id' => $transaction_id,
                    'status' => $new_status
                ]
            ]);
            
        } catch (Exception $e) {
            $koneksi->rollback();
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$koneksi->close();
?>
