<?php

include "../../MODEL/TAM/3P_TAM_HANDLER.php";


$conn = dbcon();

$jsonData = file_get_contents('php://input'); 
$data = json_decode($jsonData, true);

$noSil = $data['noSil'];
$entries = $data['entries'];
$timeStamps = $data['timeStamp'];
// Menyimpan data ke database
foreach ($entries as $entry) {
    $partNumber = $entry['partNumber'];
    $quantity = $entry['quantity'];

    $sql = "INSERT INTO [3P_T_DATA-SIL] (NO_SIL, PART_NUMBER, QUANTITY, STATUS, TIME_ENTRY, CUSTOMER) VALUES (?, ?, ?, ?, ?, ?)";
    $params = array($noSil, $partNumber, $quantity, 'OPEN', $timeStamps, 'TAM');
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Menutup koneksi
sqlsrv_close($conn);

// Mengembalikan respons
echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully']);
?>