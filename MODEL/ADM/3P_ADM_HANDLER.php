<?php

require_once "C:/xampp/htdocs/3P_CHECK_OES/MODEL/DBCON/dbcon.php";
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
    $username,
    $remainQty
 ) {
   
    try {
        $conn = dbcon();
        $tsql = "INSERT INTO [3P_T_HISTORY] 
                    (NO_SIL, PART_NUMBER, CUSTOMER_LABEL, KANBAN_CONTENT, TOTAL_KANBAN, TOTAL_LABEL, LABEL_CONTENT, QTY_LABEL, QTY_KANBAN, CUSTOMER, ITEM_VENDOR, PO_NUMBER, PREPARE_DATE, PREPARE_TIME, DELIVERY_DATE, STATUS, USER_ENTRY, REMAIN_QTY, DATA_ID, DELIVERY_VANNING, MANIFEST, KANBAN_ITEM, KANBAN_ID, CASE_LABEL) 
                 VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?, ?, ?, ?, ?)";

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
            $username,
            $remainQty,
            '',
            '',
            '',
            '',
            '',
            ''
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
    $params = [$noSil, 'ADM ASSYST'];
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
            'ADM' // Assuming TIME_ENTRY is set to 0 for now
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

function deleteSilFile($fileToDelete)
{
    $conn = dbcon();

    // Pastikan $fileToDelete adalah array
    if (!is_array($fileToDelete)) {
        $fileToDelete = [$fileToDelete]; // Ubah menjadi array jika bukan
    }

    // Mulai transaksi
    sqlsrv_begin_transaction($conn);

    $processedRows = 0;
    $processedStatuses = [];

    try {
        foreach ($fileToDelete as $singleFileToDelete) {
            // Ambil semua data dari NO_SIL yang ingin dihapus
            $statusQuery = "SELECT * FROM [3P_T_DATA-SIL] WHERE NO_SIL = ?";
            $statusParams = [$singleFileToDelete];
            $statusStmt = sqlsrv_query($conn, $statusQuery, $statusParams);

            if ($statusStmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            // Cek apakah ada data
            $rowCount = sqlsrv_num_rows($statusStmt);
            if ($rowCount === 0) {
                sqlsrv_free_stmt($statusStmt);
                continue; // Lewati jika tidak ada data
            }

            // Simpan data yang akan diproses
            $dataToProcess = [];
            while ($row = sqlsrv_fetch_array($statusStmt, SQLSRV_FETCH_ASSOC)) {
                $dataToProcess[] = $row;
            }

            // Hapus semua data dari tabel [3P_T_DATA-SIL] dengan NO_SIL yang sama
            $deleteQuery = "DELETE FROM [3P_T_DATA-SIL] WHERE NO_SIL = ?";
            $deleteParams = [$singleFileToDelete];
            $deleteStmt = sqlsrv_query($conn, $deleteQuery, $deleteParams);

            if ($deleteStmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            // Proses setiap baris data
            foreach ($dataToProcess as $row) {
                // Ambil informasi yang diperlukan
                $status = $row['STATUS'];
                $partNumber = $row['PART_NUMBER'];
                $quantity = $row['QUANTITY'];
                $timeEntry = $row['TIME_ENTRY'];
                $customer = $row['CUSTOMER'];

                // Tentukan tabel tujuan berdasarkan status
                if ($status === 'CLOSED') {
                    $insertQuery = "INSERT INTO [3P_T_DATA-REG] (NO_SIL, PART_NUMBER, QUANTITY, STATUS, TIME_ENTRY, CUSTOMER) VALUES (?, ?, ?, ?, ?, ?)";
                } elseif ($status === 'OPEN') {
                    $insertQuery = "INSERT INTO [3P_T_DATA-BO] (NO_SIL, PART_NUMBER, QUANTITY, STATUS, TIME_ENTRY, CUSTOMER) VALUES (?, ?, ?, ?, ?, ?)";
                } else {
                    // Lewati jika status tidak dikenali
                    continue;
                }

                // Siapkan parameter untuk insert
                $insertParams = [$singleFileToDelete, $partNumber, $quantity, $status, $timeEntry, $customer];
                $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

                if ($insertStmt === false) {
                    throw new Exception(print_r(sqlsrv_errors(), true));
                }

                $processedRows++;
                $processedStatuses[] = $status;

                // Bebaskan statement insert
                sqlsrv_free_stmt($insertStmt);
            }

            // Bebaskan statement delete
            sqlsrv_free_stmt($deleteStmt);
            sqlsrv_free_stmt($statusStmt);
        }

        // Commit transaksi
        sqlsrv_commit($conn);
        sqlsrv_close($conn);

        // Kembalikan informasi proses
        return [
            'message' => "Processed $processedRows rows for NO_SIL: " . implode(', ', $fileToDelete),
            'processedStatuses' => array_unique($processedStatuses)
        ];
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        sqlsrv_rollback($conn);

        // Bebaskan statement
        sqlsrv_free_stmt($statusStmt);
        sqlsrv_close($conn);

        // Kembalikan pesan kesalahan
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }
}
