<?php
header('Content-Type: application/json');
include "/xampp/htdocs/3P_CHECK_OES//MODEL/ADM/3P_ADM_HANDLER.php";

try {
    // Pastikan request adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
        throw new Exception("Metode request tidak valid");
    }

    // Log data yang diterima
    error_log('Data diterima: ' . print_r($_POST, true));

    // Sanitasi input
    $noSil = isset($_POST['noSil']) ? trim($_POST['noSil']) : "";
    $partNumber = isset($_POST['partNumber']) ? trim($_POST['partNumber']) : "";
    $customerLabel = isset($_POST['customerLabel']) ? trim($_POST['customerLabel']) : "";
    $scanKanban = isset($_POST['contentScanKanban']) ? trim($_POST['contentScanKanban']) : "";
    $totalKanban = isset($_POST['totalKanban']) ? trim($_POST['totalKanban']) : "";
    $totalLabel = isset($_POST['totalLabel']) ? trim($_POST['totalLabel']) : "";
    $scanLabel = isset($_POST['contentScanLabel']) ? trim($_POST['contentScanLabel']) : "";
    $qtyLabel = isset($_POST['qtyLabel']) ? trim($_POST['qtyLabel']) : "";
    $qtyKanban = isset($_POST['qtyKanban']) ? trim($_POST['qtyKanban']) : "";
    $customer = isset($_POST['customer']) ? trim($_POST['customer']) : "";
    $labelItemDB = isset($_POST['labelItem']) ? trim($_POST['labelItem']) : "";
    $PONumber = isset($_POST['PONumber']) ? trim($_POST['PONumber']) : "";
    $prepareTime = isset($_POST['prepareTime']) ? trim($_POST['prepareTime']) : "";
    $actualTimes = isset($_POST['actualTime']) ? trim($_POST['actualTime']) : "";
    $delDates = isset($_POST['delDates']) ? trim($_POST['delDates']) : "";
    $dataSilAll = isset($_POST['dataSil']) ? json_decode($_POST['dataSil'], true) : []; // Decode JSON to associative array
    $dataID = isset($_POST['dataID']) ? trim ($_POST['dataID']) : "";
    $delivVan = isset($_POST['delivVan']) ? trim ($_POST['delivVan']) : "";
    $kanbanItem = isset($_POST['kanbanItem']) ? trim ($_POST['kanbanItem']) : "";
    $kanbanId = isset($_POST['KanbanId']) ? trim ($_POST['KanbanId']) : "";
    $manifestKanban = isset($_POST['manifestKanban']) ? trim ($_POST['manifestKanban']) : "";
    $noSilDelete = isset($_POST['noSilDel']) ? trim ($_POST['noSilDel']) : "";
    $username = isset($_POST['userName']) ? trim($_POST['userName']) : "";
    $remainQty = isset($_POST['remainQty']) ? trim($_POST['remainQty']) : "";
    // Cek apakah hanya $noSilDelete dan $dataSilAll yang diposting
    if (!empty($noSilDelete) && !empty($dataSilAll)) {
        // Panggil fungsi untuk menyimpan ke database
        $results = finishOperational($noSilDelete, $dataSilAll);

        // Kirim response
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'data' => $_POST
        ]);
    } else {
        // Panggil fungsi untuk menyimpan ke database
       
        $result = sendDatabase(
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
        );

        // Kirim response
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'data' => $_POST
        ]);
    }
} catch (Exception $e) {
    // Tangani error
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
