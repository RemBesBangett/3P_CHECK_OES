<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout ');
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

    $fileToDelete = $_POST['numSil'];
    $filePath = BASE_PATH . 'SIL_' . $fileToDelete . '.php';

    // Log path file
    error_log("Attempting to delete file: " . $filePath);

    // Periksa apakah file ada
    if (!file_exists($filePath)) {
        throw new Exception('File not found');
    }

    // Coba hapus file dengan izin yang tepat
    if (is_writable($filePath)) {
        if (unlink($filePath)) {
           
            echo 'success';
  
        } else {
            throw new Exception('Failed to delete file');
        }
    } else {
        throw new Exception('File not writable');
    }
} catch (Exception $e) {
    // Log error
    error_log("Error: " . $e->getMessage());
    
    // Kirim respon sesuai kesalahan
}
?>