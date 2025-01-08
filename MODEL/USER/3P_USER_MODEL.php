<?php
require_once "C:/xampp/htdocs/3P CHECK OES/MODEL/DBCON/dbcon.php";

function showAllUser()
{
    // Establish database connection
    $conn = dbcon();

    // SQL query to select all users
    $tsql = "SELECT * FROM [3P_M_USER]";

    // Execute the query
    $stmt = sqlsrv_query($conn, $tsql);

    // Periksa apakah statement gagal dipersiapkan
    if ($stmt === false) {
        // Dapatkan error untuk debugging
        $errors = sqlsrv_errors();
        if ($errors != null) {
            foreach ($errors as $error) {
                error_log("SQLSRV Error: " . $error['message']);
            }
        }
        return false;
    }

    $data = []; // Inisialisasi array kosong untuk menyimpan hasil

    // Fetch data
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row; // Tambahkan setiap baris ke array $data
    }

    // Menutup statement dan koneksi database
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    // Mengembalikan data sebagai array
    return $data;
}

function insertUser($npk, $nama, $password, $access, $status, $section, $line, $leader)
{
    $conn = dbcon();
    $tsql = "INSERT INTO [3P_M_USER] (NPK, NAMA, PASSWORD, ACCESS, STATUS, SECTION, LINE, LEADER) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $params = array($npk, $nama, $password, $access, $status, $section, $line, $leader);

    $stmt = sqlsrv_query($conn, $tsql, $params);

    if ($stmt === false) {
        return ['success' => false, 'message' => 'Failed to add new user: ' . print_r(sqlsrv_errors(), true)];
    }

    sqlsrv_free_stmt($stmt);
    return ['success' => true, 'message' => 'New user successfully added'];
}


function updateUser($npk, $nama, $password, $access, $status, $section, $line, $leader)
{
    $conn = dbcon();
    $tsql = "UPDATE [3P_M_USER] SET NAMA = ?, PASSWORD = ?, ACCESS = ?, STATUS = ?, SECTION = ?, LINE = ?, LEADER = ? WHERE NPK = ?";
    $params = array($nama, $password, $access, $status, $section, $line, $leader, $npk);

    $stmt = sqlsrv_query($conn, $tsql, $params);

    if ($stmt === false) {
        return ['success' => false, 'message' => 'Failed to update user: ' . print_r(sqlsrv_errors(), true)];
    }

    sqlsrv_free_stmt($stmt);
    return ['success' => true, 'message' => 'User  successfully updated'];
}

function deleteUser($npk)
{
    // Validasi input NPK lebih ketat
    if (empty($npk) || !preg_match('/^[A-Z0-9]+$/', $npk)) {
        return [
            'success' => false,
            'message' => 'Format NPK tidak valid'
        ];
    }

    $conn = dbcon();
    
    try {
        // Mulai transaksi
        sqlsrv_begin_transaction($conn);

        // Cek apakah user ada
        $checkSql = "SELECT COUNT(*) AS user_count FROM [3P_M_USER] WHERE NPK = ?";
        $checkParams = array($npk);
        $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);
        
        if ($checkStmt === false) {
            throw new Exception('Gagal memeriksa pengguna: ' . print_r(sqlsrv_errors(), true));
        }
        
        $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($checkStmt);
        
        // Jika user tidak ditemukan
        if ($row['user_count'] == 0) {
            throw new Exception('Pengguna tidak ditemukan');
        }

        // Query delete
        $tsql = "DELETE FROM [3P_M_USER] WHERE NPK = ?";
        $params = array($npk);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            throw new Exception('Gagal menghapus pengguna: ' . print_r(sqlsrv_errors(), true));
        }
        
        $rowsAffected = sqlsrv_rows_affected($stmt);
        sqlsrv_free_stmt($stmt);
        
        // Commit transaksi
        sqlsrv_commit($conn);
        
        // Tambahan validasi rows affected
        if ($rowsAffected === 0) {
            throw new Exception('Tidak ada pengguna yang dihapus');
        }
        
        return [
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ];
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        sqlsrv_rollback($conn);
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    } finally {
        sqlsrv_close($conn);
    }
} 