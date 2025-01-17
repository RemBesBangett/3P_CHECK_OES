<?php

include "../../MODEL/INTERAKTIF/3P_INTERLOCK_MODEL.php.php";

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data) && !empty($data)) {
    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];
        $result = authentication($username, $password);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username or password not provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No data received']);
}
