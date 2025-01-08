<?php
include '../../MODEL/ADM/3P_ADM_HANDLER.php';


if (isset($_GET['noSil'])) {
    $noSil = $_GET['noSil'];

    $conn = dbcon();
    $tsql = "SELECT * FROM [3P_T_DATA-SIL] WHERE NO_SIL = ? AND CUSTOMER = ?";
    $params = [$noSil, 'ADM ASSYST'];
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        echo json_encode($data);
    }
}
