<?php
require "../../MODEL/DBCON/dbcon.php";

function sendDatabase(
    $noSil,
    $partNumber,
    $customerLabel,
    $scanKanban,
    $totalKanban,
    $totalLabel,
    $scanLabel,
    $qtyLabel,
    $qtyKanban,
    $customer,
    $labelItemDB,
    $PONumber,
    $prepareTime,
    $actualTimes,
    $delDates,
    $dataID,
    $delivVan,
    $manifestKanban,
    $kanbanId,
    $kanbanItem
 ) {
   
    try {
        $conn = dbcon();
        $tsql = "INSERT INTO [3P_T_HISTORY] 
                    (NO_SIL, PART_NUMBER, CUSTOMER_LABEL, KANBAN_CONTENT, TOTAL_KANBAN, TOTAL_LABEL, LABEL_CONTENT, QTY_LABEL, QTY_KANBAN, CUSTOMER, ITEM_VENDOR, PO_NUMBER, PREPARE_DATE, PREPARE_TIME, DELIVERY_DATE, STATUS, DATA_ID, DELIVERY_VANNING, MANIFEST, KANBAN_ID, KANBAN_ITEM) 
                 VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $noSil,
            $partNumber,
            $customerLabel,
            $scanKanban,
            $totalKanban,
            $totalLabel,
            $scanLabel,
            $qtyLabel,
            $qtyKanban,
            $customer,
            $labelItemDB,
            $PONumber,
            $prepareTime,
            $actualTimes,
            $delDates,
            'CLOSED',
            'D',
            $delivVan,
            $manifestKanban,
            $kanbanId,
            $kanbanItem
        ];

        $stmt = sqlsrv_prepare($conn, $tsql, $params);
        if (!$stmt) {
            throw new Exception("Error preparing SQL statement: " . print_r(sqlsrv_errors(), true));
        }

        if (!sqlsrv_execute($stmt)) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        $tsqlUpdate = "UPDATE [3P_T_DATA-SIL] SET STATUS = 'CLOSED' WHERE NO_SIL = ? AND PART_NUMBER = ? AND STATUS = 'OPEN'";
        $paramsUpdate = [$noSil, $partNumber];
        $stmtUpdate = sqlsrv_prepare($conn, $tsqlUpdate, $paramsUpdate);
        if (!$stmtUpdate) {
            throw new Exception("Error preparing SQL statement for update: " . print_r(sqlsrv_errors(), true));
        }

        if (!sqlsrv_execute($stmtUpdate)) {
            throw new Exception("Error executing update query: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_commit($conn);
        return true;
    } catch (Exception $e) {
        if ($conn) {
            sqlsrv_rollback($conn);
        }
        error_log($e->getMessage());
        return false;
    } finally {
        if ($conn) {
            sqlsrv_close($conn);
        }
    }
}

function getSilData($noSil)
{
    $conn = dbcon();
    $tsql = "SELECT * FROM [3P_T_DATA-SIL] WHERE NO_SIL = ? AND CUSTOMER = ?";
    $params = [$noSil, 'TMMIN'];
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    return $data;
}

function finishOperational($noSil, $dataSilAll)
{
    $conn = dbcon(); // Ensure this returns a valid connection

    foreach ($dataSilAll as $dataSil) {
        // Extract values from the current dataSil item
        $noSils = $dataSil['noSil'] ?? null; // Use null coalescing operator to avoid undefined index
        $partNumber = $dataSil['partNumber'] ?? null; // Use null coalescing operator to avoid undefined index
        $quantity = $dataSil['qty'] ?? null;
        $status = $dataSil['status'] ?? null;
        $time = $dataSil['timePrep'] ?? null;

        // Prepare the SQL statement based on the status
        if ($status === 'CLOSED') {
            $tsql = "INSERT INTO [3P_T_DATA-REG] (NO_SIL, PART_NUMBER, QUANTITY, STATUS, TIME_ENTRY, CUSTOMER) VALUES (?, ?, ?, ?, ?, ?)";
        } elseif ($status === 'OPEN') {
            $tsql = "INSERT INTO [3P_T_DATA-BO] (NO_SIL, PART_NUMBER, QUANTITY, STATUS, TIME_ENTRY, CUSTOMER) VALUES (?, ?, ?, ?, ?, ?)";
        } else {
            // If status is neither CLOSED nor OPEN, you can choose to skip or handle it
            continue; // Skip this iteration if status is not recognized
        }

        // Prepare parameters for the SQL query
        $params = [
            $noSils, 
            $partNumber,
            $quantity,
            $status,
            $time,
            'TAM' // Assuming TIME_ENTRY is set to 0 for now
        ];

        // Execute the query
        $stmt = sqlsrv_query($conn, $tsql, $params);

        // Check if the query was successful
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true)); // Display errors if any
        }

        // If insertion was successful, delete the corresponding record from [3P_T_DATA-SIL]
        $deleteSql = "DELETE FROM [3P_T_DATA-SIL] WHERE NO_SIL = ? AND PART_NUMBER = ?";
        $deleteParams = [$noSils, $partNumber];

        $deleteStmt = sqlsrv_query($conn, $deleteSql, $deleteParams);

        // Check if the delete query was successful
        if ($deleteStmt === false) {
            die(print_r(sqlsrv_errors(), true)); // Display errors if any
        }
    }

    // Close the connection if needed
    sqlsrv_close($conn);
}
