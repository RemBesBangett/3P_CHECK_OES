<?php
session_start();
include "../../MODEL/LOGIN/3P_LOGIN_MODEL.php";

// Fungsi untuk mencatat log
function logMessage($message) {
    error_log($message, 3, 'logfile.log'); // Ganti dengan path yang sesuai
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    // Log data yang diterima
    logMessage("Data diterima: " . json_encode($_POST) . "\n");

    if (isset($_POST['access']) && $_POST['access'] == 'guest') {
        $_SESSION['loggedin'] = true;
        $_SESSION['access'] = 'VIEW';
        $_SESSION['role'] = 'VIEW';
        $_SESSION['nama'] = 'Guest';
        $_SESSION['guest'] = true;

        // Log data yang dikembalikan
        logMessage("Data dikembalikan: " . json_encode(['status' => 'success', 'access' => 'VIEW']) . "\n");

        echo json_encode(['status' => 'success', 'access' => 'VIEW']);
    } else {
        $result = handleLogin($_POST['nama'], $_POST['password']);

        // Log hasil dari fungsi handleLogin
        logMessage("Hasil login: " . json_encode($result) . "\n");

        echo json_encode($result);
    }
    exit();
}