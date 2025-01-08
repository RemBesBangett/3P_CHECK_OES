<?php
include "../../MODEL/USER/3P_USER_MODEL.php";

// Initialize a message variable
$message = '';

// Periksa jenis operasi
if (isset($_GET['deleteMbut'])) {
    // Validasi NPK dengan lebih ketat
    $npk = filter_input(INPUT_GET, 'npk', FILTER_SANITIZE_STRING);

    if ($npk) {
        try {
            $response = deleteUser($npk);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // NPK tidak valid
        $response = [
            'success' => false,
            'message' => 'NPK tidak valid'
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
// Cek operasi add
elseif (
    isset($_POST['addNPK']) &&
    isset($_POST['addNama']) &&
    isset($_POST['addPassword']) &&
    isset($_POST['addAccess']) &&
    isset($_POST['addStatus']) &&
    isset($_POST['addSection']) &&
    isset($_POST['addLine']) &&
    isset($_POST['addLeader'])
) {
    // Get all data with filters
    $npk = $_POST['addNPK'];
    $nama = $_POST['addNama'];
    $password = $_POST['addPassword'];
    $access = $_POST['addAccess'];
    $status = $_POST['addStatus'];
    $section = $_POST['addSection'];
    $line = $_POST['addLine'];
    $leader = $_POST['addLeader'];

    // Function to check if NPK or Name is already registered
    function checkUserExists($npk, $nama)
    {
        $conn = dbcon();
        $query = "SELECT COUNT(*) as count FROM [3P_M_USER] WHERE npk = ? OR nama = ?";
        $params = array($npk, $nama);
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['count'] > 0;
    }

    // Check if NPK or Name is already registered
    if (checkUserExists($npk, $nama)) {
        $message = 'Failed to add data. Use another name or NPK to register the user.';
    } else {
        // If not registered, proceed with insert
        $result = insertUser($npk, $nama, $password, $access, $status, $section, $line, $leader);
        if ($result['success']) {
            $message = 'Data successfully saved. Do you want to continue?';
        } else {
            $message = $result['message'];
        }
    }
}
// Cek operasi edit
elseif (
    isset($_POST['editNPK']) &&
    isset($_POST['editNama']) &&
    isset($_POST['editPassword']) &&
    isset($_POST['editAccess']) &&
    isset($_POST['editStatus']) &&
    isset($_POST['editSection']) &&
    isset($_POST['editLine']) &&
    isset($_POST['editLeader'])
) {
    // Get all data with filters
    $npk = $_POST['editNPK'];
    $nama = $_POST['editNama'];
    $password = $_POST['editPassword'];
    $access = $_POST['editAccess'];
    $status = $_POST['editStatus'];
    $section = $_POST['editSection'];
    $line = $_POST['editLine'];
    $leader = $_POST['editLeader'];

    // Function to check if the user exists
    function checkUserExistsForEdit($npk)
    {
        $conn = dbcon();
        $query = "SELECT COUNT(*) as count FROM [3P_M_USER] WHERE npk = ?";
        $params = array($npk);
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['count'] > 0;
    }

    // Check if the user exists
    if (!checkUserExistsForEdit($npk)) {
        $message = 'User not found. Please check the NPK.';
    } else {
        // If the user exists, proceed with the update
        $result = updateUser($npk, $nama, $password, $access, $status, $section, $line, $leader);
        if ($result['success']) {
            $message = 'User data successfully updated.';
        } else {
            $message = $result['message'];
        }
    }
} else {
    // Tangani kasus default atau error
    $response = [
        'status' => 'error',
        'message' => 'Aksi tidak valid'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
