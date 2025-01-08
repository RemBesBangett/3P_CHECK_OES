<?php
session_start();
include "../../MODEL/LOGIN/3P_LOGIN_MODEL.php";



if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    if (isset($_POST['access']) && $_POST['access'] == 'guest') {
        $_SESSION['loggedin'] = true;
        $_SESSION['access'] = 'VIEW';
        $_SESSION['role'] = 'VIEW';
        $_SESSION['nama'] = 'Guest';
        $_SESSION['guest'] = true;
        echo json_encode(['status' => 'success', 'access' => 'VIEW']);
    } else {
        $result = handleLogin($_POST['nama'], $_POST['password']);
        echo json_encode($result);
    }
    exit();
}