<?php
include "../../MODEL/INTERAKTIF/3P_INTERLOCK_MODEL.php";


// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mengambil data JSON dari input
$data = json_decode(file_get_contents('php://input'), true);

// Default response
$response = [
    'status' => 'error',
    'message' => 'Aksi tidak valid'
];

// Log function
function debugLog($message)
{
    $logFile = '/3P_CHECK_OES/debug.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Validasi input dasar
    if (!isset($data) || !is_array($data)) {
        throw new Exception('Invalid input data');
    }

    // Ambil action
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'preCheck':
            // Lakukan pre-check status user
            $userSession = $data['userSession'] ?? null;

            if (empty($userSession)) {
                throw new Exception('User session tidak boleh kosong');
            }

            // Lakukan lock user status
            $statusCheck = lockUserStatus($userSession);

            if ($statusCheck['success']) {
                $response = [
                    'status' => 'requireAuth',
                    'message' => 'Verifikasi pengguna diperlukan'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => $statusCheck['message']
                ];
            }
            break;

        case 'authenticate':
            $username = $data['username'] ?? null;
            $password = $data['password'] ?? null;
            $userSession = $data['userSession'] ?? null;

            // Validasi input
            if (empty($username) || empty($password) || empty($userSession)) {
                throw new Exception('Semua field harus diisi');
            }

            // Lakukan autentikasi
            $authResult = authenticateUser($username, $password);

            if ($authResult['success']) {
                $response = [
                    'status' => 'success',
                    'message' => 'Autentikasi berhasil',
                    'data' => [
                        'username' => $username,
                        'access' => $authResult['access'],
                        'nama' => $authResult['nama']
                    ]
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => $authResult['message']
                ];
            }
            break;

        default:
            throw new Exception("Aksi tidak valid");
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    debugLog("Error: " . $e->getMessage());
}

// Mengatur header untuk respons JSON
header('Content-Type: application/json');
echo json_encode($response);
