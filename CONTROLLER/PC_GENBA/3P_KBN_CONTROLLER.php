<?php
// Sertakan file model
require_once 'C:/xampp/htdocs/3P_CHECK_OES/MODEL/PC_GENBA/3P_KBN_MODEL.php';

// Tangani request AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $cusName = filter_input(INPUT_POST, 'customerName', FILTER_SANITIZE_STRING);

    if (!empty($cusName)) {
        try {
            // Ambil data customer
            $result = showCustomerValue($cusName);

            // Persiapkan respons
            $response = [
                'success' => !empty($result),
                'data' => $result,
                'message' => empty($result) 
                    ? 'Tidak ada data ditemukan untuk customer tersebut.' 
                    : 'Data berhasil dimuat'
            ];

            // Kirim respons JSON
            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            // Tangani kesalahan
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    } else {
        // Nama customer tidak valid
        header('HTTP/1.1 400 Bad Request');
        echo json_encode([
            'success' => false,
            'message' => 'Nama customer tidak valid.'
        ]);
    }
    exit();
}