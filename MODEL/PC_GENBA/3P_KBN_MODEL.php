<?php
require_once 'C:/xampp/htdocs/3P_CHECK_OES/MODEL/DBCON/dbcon.php';

function showCustomerValue($cusName)
{
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

function showCustomer()
{
    $conn = dbcon();
    $tsql = 'SELECT DISTINCT CUSTOMER FROM [3P_T_CUST]';
    $stmt = sqlsrv_query($conn, $tsql);
    if ($stmt === false) {
        throw new Exception("Query execution failed: " . print_r(sqlsrv_errors(), true));
    }
    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    return $data;
}

function showAllData()
{
    $conn = dbcon();
    $tsql = 'SELECT * FROM [3P_T_CUST]';
    $stmt = sqlsrv_query($conn, $tsql);
    if ($stmt === false) {
        throw new Exception("Query execution failed: " . print_r(sqlsrv_errors(), true));
    }
    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    return $data;
}



function addDataCustomer($partNumber, $partCust, $descPart, $custName)
{
    try {
        $conn = dbcon();

        // Gunakan prepared statement
        $tsql = "INSERT INTO [3P_T_CUST] (PN_DENSO, PN_CUSTOMER, DESCRIPTION, CUSTOMER) VALUES (?, ?, ?, ?)";
        $params = [$partNumber, $partCust, $descPart, $custName];
        $stmt = sqlsrv_query($conn, $tsql, $params);

        if ($stmt === false) {
            throw new Exception("Query execution failed: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        return true;
    } catch (Exception $e) {
        // Log error atau tangani sesuai kebutuhan
        error_log($e->getMessage());
        return false;
    }
}


function editDataCustomer($partNumberEdit, $partCustEdit, $descPartEdit, $custNameEdit)
{
    $conn = dbcon(); // Function to connect to the database
    $tsql = "UPDATE [3P_T_CUST] SET PN_CUSTOMER = ?, DESCRIPTION = ?, CUSTOMER = ? WHERE PN_DENSO = ?";
    $params = [$partCustEdit, $descPartEdit, $custNameEdit, $partNumberEdit];

    $stmt = sqlsrv_query($conn, $tsql, $params);

    if ($stmt === false) {
        return ['success' => false, 'message' => 'Failed to update user: ' . print_r(sqlsrv_errors(), true)];
    }

    sqlsrv_free_stmt($stmt);
    return ['success' => true, 'message' => 'User  successfully updated'];
}


function deleteDataCustomer($partNumberDensoDel)
{
    $conn = dbcon();
    $tsql = 'DELETE FROM [3P_T_CUST] WHERE PN_DENSO = ?';
    $params = [$partNumberDensoDel];
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt === false) {
        return ['success' => false, 'message' => 'Failed to delete user: ' .
            print_r(sqlsrv_errors(), true)];
    }
    sqlsrv_free_stmt($stmt);
    return ['success' => true, 'message' => 'User successfully deleted'];
}
