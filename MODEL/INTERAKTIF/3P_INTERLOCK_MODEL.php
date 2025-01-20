<?php
require '../../MODEL/DBCON/dbcon.php';

function authenticateUser($username, $password)
{
    $conn = dbcon();
    sqlsrv_begin_transaction($conn);

    try {
        $tsql = "SELECT * FROM [3P_M_USER] WHERE (ACCESS = 'LEADER' OR ACCESS = 'ADMIN') AND NAMA = ?";
        $params = array($username);
        $stmt = sqlsrv_query($conn, $tsql, $params);

        if ($stmt === false) {
            throw new Exception('Database query failed: ' . print_r(sqlsrv_errors(), true));
        }

        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Verifikasi password
            if ($row['PASSWORD'] === $password) { // Gantilah ini dengan password_verify jika menggunakan hashing
                $tsqlupdate = "UPDATE [3P_M_USER] SET STATUS_USER = 'OPEN' WHERE NAMA = ?";
                $paramsupdate = [$username];
                $stmtupdate = sqlsrv_query($conn, $tsqlupdate, $paramsupdate);

                if ($stmtupdate === false) {
                    throw new Exception('Database query failed: ' . print_r(sqlsrv_errors(), true));
                }

                sqlsrv_commit($conn);

                return [
                    'success' => true,
                    'message' => 'Authentication successful',
                    'access' => $row['ACCESS'],
                    'nama' => $row['NAMA']
                ];
            } else {
                throw new Exception('Invalid username or password for LEADER or ADMIN access');
            }
        } else {
            throw new Exception('Invalid username or password for LEADER or ADMIN access');
        }
    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        if ($stmt) sqlsrv_free_stmt($stmt);
        if ($stmtupdate) sqlsrv_free_stmt($stmtupdate);
        if ($conn) sqlsrv_close($conn);
    }
}

function lockUserStatus($userSession)
{
    $conn = dbcon();

    // Validasi input
    if (empty($userSession)) {
        return ['success' => false, 'message' => 'Username tidak boleh kosong'];
    }

    // Mulai transaksi untuk keamanan
    sqlsrv_begin_transaction($conn);

    try {
        // Query untuk mengecek apakah user ada
        $checkQuery = "SELECT COUNT(*) AS UserCount FROM [3P_M_USER] WHERE NAMA = ?";
        $checkParams = [$userSession];
        $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);

        if ($checkStmt === false) {
            throw new Exception('Gagal memeriksa keberadaan user: ' . print_r(sqlsrv_errors(), true));
        }

        $checkResult = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

        if ($checkResult['UserCount'] == 0) {
            throw new Exception('User tidak ditemukan');
        }

        // Query untuk mengupdate status user
        $updateQuery = "UPDATE [3P_M_USER] SET STATUS_USER = 'LOCKED' WHERE NAMA = ?";
        $updateParams = [$userSession];
        $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

        if ($updateStmt === false) {
            throw new Exception('Gagal mengunci status user: ' . print_r(sqlsrv_errors(), true));
        }

        // Commit transaksi
        sqlsrv_commit($conn);

        return [
            'success' => true,
            'message' => 'Status user berhasil dikunci'
        ];
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        sqlsrv_rollback($conn);

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    } finally {
        // Pastikan statement dan koneksi ditutup
        if ($checkStmt) sqlsrv_free_stmt($checkStmt);
        if ($updateStmt) sqlsrv_free_stmt($updateStmt);
        if ($conn) sqlsrv_close($conn);
    }
}
