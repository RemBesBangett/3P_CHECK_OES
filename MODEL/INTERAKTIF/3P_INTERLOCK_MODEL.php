<?php
require '../../MODEL/DBCON/dbcon.php';

function authentication($username, $password)
{
    $conn = dbcon();
    $tsql = "SELECT * FROM [3P_M_USER] WHERE (ACCESS = 'LEADER' OR ACCESS = 'ADMIN') AND NAMA = ? AND PASSWORD = ?";

    $params = array($username, $password);
    $stmt = sqlsrv_query($conn, $tsql, $params);

    if ($stmt === false) {
        return ['success' => false, 'message' => 'Database query failed: ' . print_r(sqlsrv_errors(), true)];
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // User found with matching credentials and either LEADER or ADMIN access
        return [
            'success' => true,
            'message' => 'Authentication successful',
            'access' => $row['ACCESS'],
            'nama' => $row['NAMA']
        ];
    } else {
        // No matching user found
        return ['success' => false, 'message' => 'Invalid username or password for LEADER or ADMIN access'];
    }
}