<?php
require_once '../../MODEL/DBCON/dbcon.php';

function showCustomerValue($cusName) {
    try {
        $conn = dbcon();
        
        // Gunakan prepared statement
        $tsql = "SELECT * FROM [3P_T_CUST] WHERE CUSTOMER = ?";
        $params = [$cusName];
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            throw new Exception("Query execution failed: " . print_r(sqlsrv_errors(), true));
        }
        
        $result = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        return $result;
    } catch (Exception $e) {
        // Log error atau tangani sesuai kebutuhan
        error_log($e->getMessage());
        return [];
    }
}