<?php

require dirname(__DIR__, 2) . '/MODEL/DBCON/dbcon.php';


function getAllHistory()
{
    try {
        $conn = dbcon();

        // Gunakan prepared statement untuk keamanan
        $tsql = "SELECT * FROM [3P_T_HISTORY]";
        $stmt = sqlsrv_query($conn, $tsql);

        if ($stmt === false) {
            throw new Exception("Gagal mengeksekusi query: " . print_r(sqlsrv_errors(), true));
        }

        $history = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $history[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        return [
            'success' => true,
            'data' => $history,
            'count' => count($history)
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    } 
}
