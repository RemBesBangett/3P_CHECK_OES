<?php
include '/xampp/htdocs/3P_CHECK_OES/MODEL/INTERAKTIF/3P_INTERLOCK_MODEL.php';

if (isset($_GET['userLogin'])) {
    $user = $_GET['userLogin'];

    $conn = dbcon();
    $tsql = "SELECT * FROM [3P_M_USER] WHERE NAMA = ?";
    $params = [$user];
    $stmt = sqlsrv_query($conn, $tsql, $params);
    
    if ($stmt) {
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        
        // Ambil status pengguna dari data
        if (!empty($data)) {
            $userStatus = $data[0]['STATUS_USER']; // Misalkan kolom status ada di sini
            // Perbarui sesi dengan status terbaru
            session_start();
            $_SESSION['status_user'] = $userStatus;
        }

        sqlsrv_close($conn);
        echo json_encode($data);
    } else {
        // Jika query gagal, kirimkan pesan error
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    }
} else {
    // Jika userLogin tidak diset, kirimkan pesan error
    echo json_encode(['status' => 'error', 'message' => 'User  login not set']);
}