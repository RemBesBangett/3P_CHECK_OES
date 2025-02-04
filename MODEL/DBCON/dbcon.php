<?php
date_default_timezone_set('Asia/Jakarta'); // Set timezone ke WIB (GMT+7)
function dbcon()
{
    $serverName = "localhost\\SQLEXPRESS";
    $connectionInfo = [
        "Database" => "3P_CHECK_OES",
        "CharacterSet" => "UTF-8" // Menentukan set karakter
    ];

    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        echo "Connection is failed.<br>";
        die(print_r(sqlsrv_errors(), true)); 
    }
    return $conn;
}

// function addLogEntry($message, $npk, $nama, $line, $status)
// {
//     // Menggunakan fungsi dbcon() untuk koneksi database
//     $conn = dbcon();

//     // Menyiapkan data log
//     $timestamp = date("Y-m-d H:i:s");

//     // Menulis ke file log
//     $logFile = 'C:\\Logs\\your-application\\actions.log';
//     $logEntry = "{$timestamp} - NPK: {$npk}, Nama: {$nama}, Line: {$line}, Activity: {$message}, Status: {$status}\n";

//     // Pastikan direktori log ada
//     if (!file_exists(dirname($logFile))) {
//         mkdir(dirname($logFile), 0777, true); // Buat direktori jika tidak ada
//     }

//     // Menulis entri log ke file
//     if (file_put_contents($logFile, $logEntry, FILE_APPEND) === false) {
//         error_log("Failed to write to log file: $logFile");
//     }

//     // Menyimpan log ke database
//     $sql = "INSERT INTO HISTORY_PROCESS (NPK, NAMA, LINE, ACTIVITY, STATUS, DATE) VALUES (?, ?, ?, ?, ?, ?)";
//     $params = array($npk, $nama, $line, $message, $status, $timestamp);
//     $stmt = sqlsrv_query($conn, $sql, $params);

//     if ($stmt === false) {
//         // Menangani kesalahan SQL
//         error_log('SQL error: ' . print_r(sqlsrv_errors(), true));
//     }

//     // Menutup statement dan koneksi
//     sqlsrv_free_stmt($stmt);
//     sqlsrv_close($conn);
// }

// function addLogEntryLog($message)
// {
//     $timestamp = date("Y-m-d H:i:s");
//     $npk = $_SESSION['npk'] ?? 'guest'; // Gunakan 'guest' jika npk tidak diset di session

//     // Log ke file
//     $logFile = 'C:\\Logs\\your-application\\logfile.log'; // Sesuaikan path jika perlu
//     $logEntry = "{$timestamp} - {$message}\n";
//     file_put_contents($logFile, $logEntry, FILE_APPEND);
// }
