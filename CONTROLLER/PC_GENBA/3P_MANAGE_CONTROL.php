<?php
require_once 'C:/xampp/htdocs/3P_CHECK_OES/MODEL/PC_GENBA/3P_KBN_MODEL.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    error_log('Data diterima: ' . print_r($_POST, true));
} else {
    $partNumber = isset($_POST['partNumber']) ? trim($_POST['partNumber']) : '';
    $partCust = isset($_POST['partCust']) ? trim($_POST['partCust']) : '';
    $descPart =  isset($_POST['descPart']) ? trim($_POST['descPart']) : '';
    $custName = isset($_POST['custName']) ? trim($_POST['custName']) : '';
    $partNumberEdit = isset($_POST['partNumberEdit']) ? trim($_POST['partNumberEdit']) : "";
    $partCustEdit = isset($_POST['partCustEdit']) ? trim($_POST['partCustEdit']) : "";
    $descPartEdit = isset($_POST['descPartEdit']) ? trim($_POST['descPartEdit']) : "";
    $custNameEdit = isset($_POST['custNameEdit']) ? trim($_POST['custNameEdit']) : "";
    $partNumberDensoDel = isset($_POST['partNumberDensoDel']) ? trim($_POST['partNumberDensoDel']) : "";

    if ($partNumberEdit === '') {
        if ($partNumber !== '' && $partCust !== '' && $descPart !== '' && $custName !== '') {
            $result = addDataCustomer($partNumber, $partCust, $descPart, $custName);
        } else {
            $result = ['error' => 'All fields are required.'];
        }
        echo json_encode($result);
    } else {
        $result = editDataCustomer($partNumberEdit, $partCustEdit, $descPartEdit, $custNameEdit);
    }
    if ($partNumberDensoDel !== ''){
        $result = deleteDataCustomer($partNumberDensoDel);
    }
    echo json_encode($result);
}
