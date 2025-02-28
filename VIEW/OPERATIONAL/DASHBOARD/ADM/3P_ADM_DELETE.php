<?php

use LDAP\Result;

include_once "C:/xampp/htdocs/3P_CHECK_OES/MODEL/ADM/3P_ADM_HANDLER.php";
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
} else if (!isset($_SESSION['section']) || $_SESSION['section'] != 'OPERATIONAL' && $_SESSION['access'] != 'ADMIN') {
    header('location: /3P_CHECK_OES/Error_access');
    die('Access denied: Invalid session section');
} else if (isset($_SESSION['status_user']) && $_SESSION['status_user'] == 'locked') {
    header('location: /3P_CHECK_OES/Dashboard');
    exit();
}

error_log("Script started");

// Gunakan konstanta atau konfigurasi path yang konsisten
define('BASE_PATH', 'C:/xampp/htdocs/3P_CHECK_OES/VIEW/OPERATIONAL/DASHBOARD/ADM/SIL_FILES/');

try {
    // Pastikan file yang diterima valid
    if (!isset($_POST['numSil'])) {
        throw new Exception('No file specified');
    }

    // Ambil data dari POST dan pastikan itu adalah array
    $fileToDelete = $_POST['numSil'];
    if (!is_array($fileToDelete)) {
        $fileToDelete = [$fileToDelete]; // Ubah menjadi array jika bukan
    }

    $results = []; // Untuk menyimpan hasil dari setiap penghapusan

    foreach ($fileToDelete as $singleFileToDelete) {
        $filePath = BASE_PATH . 'SIL_' . $singleFileToDelete . '.php';
        $result = deleteSilFile($singleFileToDelete);

        // Log path file
        error_log("Attempting to delete file: " . $filePath);

        // Periksa apakah file ada
        if (!file_exists($filePath)) {
            throw new Exception('File not found for NO_SIL: ' . $singleFileToDelete);
        }

        // Coba hapus file dengan izin yang tepat
        if (is_writable($filePath)) {
            if (unlink($filePath)) {
                $results[] = ['status' => 'success', 'message' => 'File deleted successfully for NO_SIL: ' . $singleFileToDelete];
            } else {
                throw new Exception('Failed to delete file for NO_SIL: ' . $singleFileToDelete);
            }
        } else {
            throw new Exception('File not writable for NO_SIL: ' . $singleFileToDelete);
        }
    }

    // Kirim respon sukses
    echo json_encode(['status' => 'success', 'results' => $results]);
} catch (Exception $e) {
    // Log error
    error_log("Error: " . $e->getMessage());

    // Kirim respon sesuai kesalahan
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
