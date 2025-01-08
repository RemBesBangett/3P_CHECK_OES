<?php
require "../../MODEL/DBCON/dbcon.php"; 

function handleLogin($nama, $password)
{
    $conn = dbcon();

    error_log("Received NAMA: " . $nama);
    error_log("Received Password: " . $password);

    $sql = "SELECT NPK, ACCESS, STATUS, LINE, SECTION FROM [3P_M_USER] WHERE NAMA = ? AND PASSWORD = ?";  
    $params = array($nama, $password);

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        error_log("SQL error: " . print_r(sqlsrv_errors(), true));
        return ["status" => "error", "message" => "Database error"];
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $userRole = strtoupper($row['ACCESS']);
        $accountStatus = $row['STATUS'];
        $line = $row['LINE'];
        $npk = $row['NPK'];
        $section = $row['SECTION']; // Retrieve section from the query result

        if ($accountStatus == 'EXPIRED') {
            error_log("Account expired for user: " . $nama);
            return ["status" => "fail", "message" => "Account expired"];
        } else {
            $_SESSION['loggedin'] = true;
            $_SESSION['nama'] = $nama;
            $_SESSION['guest'] = false;
            $_SESSION['access'] = $userRole;
            $_SESSION['role'] = $userRole;
            $_SESSION['line'] = $line;
            $_SESSION['npk'] = $npk;
            $_SESSION['section'] = $section; // Store section in session

            error_log("User successfully logged in: " . $nama . " with role: " . $userRole);
            return ["status" => "success", "access" => $userRole, "nama" => $nama, "line" => $line, "npk" => $npk, "section" => $section];
        }
    } else {
        error_log("Login attempt failed for user: " . $nama);
        return ["status" => "fail", "message" => "Invalid credentials"];
    }
}
