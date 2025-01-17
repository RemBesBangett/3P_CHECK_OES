<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require '../../MODEL/DBCON/dbcon.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Validasi input (sama seperti sebelumnya)
    if (!isset($_POST['timePort']) || empty($_POST['timePort'])) {
        throw new Exception('Required parameters are missing.');
    }
    
    $timeExport = trim($_POST['timePort']);
    $customers = trim($_POST['customer']);

    // Validasi format tanggal
    if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $timeExport)) {
        throw new Exception('Invalid date format');
    }

    // Ambil data history
    $getAllHistory = getAllHistory($timeExport, $customers);

    // Cek apakah data ada
    if (empty($getAllHistory)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No data found for the specified date and customers.'
        ]);
        exit();
    }

    $templateFile = '../../FORMAT/ADM ASSYST/FORMAT ADM ASSYST.xlsx';

    // Load spreadsheet
    if (!file_exists($templateFile)) {
        throw new Exception('Template file not found.');
    }
    $spreadsheet = IOFactory::load($templateFile);
    $sheet = $spreadsheet->getActiveSheet();

    // Tulis data baris
    $row = 2;
    foreach ($getAllHistory as $history) {
    
        $sheet->setCellValue('A' . $row, $history['DELIVERY_DATE'] ?? ''); //MANIFEST NO R(ANGKA) = TMMIN
        $sheet->setCellValue('B' . $row, $history['PO_NUMBER'] ?? ''); //MANIFEST NO R(ANGKA) = TMMIN
        $sheet->setCellValue('C' . $row, $history['PART_NUMBER'] ?? ''); //MANIFEST NO R(ANGKA) = TMMIN
        $sheet->setCellValue('D' . $row, $history['ITEM_VENDOR'] ?? ''); //MANIFEST NO R(ANGKA) = TMMIN
        $sheet->setCellValue('E' . $row, $history['TOTAL_LABEL'] ?? ''); //MANIFEST NO R(ANGKA) = TMMIN

        // Gaya border
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($styleArray);

        $row++;
    }

    // Nama file download
    $filename = 'Upload Assys_' . date('Y-m-d/H:i') . '.xlsx';

    // Siapkan untuk download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Tulis langsung ke output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    // Tangani kesalahan
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}

// Existing getAllHistory function remains the same

// Fungsi validasi format tanggal
function validateDateFormat($date)
{
    // Sesuaikan regex dengan format dd/mm/yyyy
    return preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date);
}

// Fungsi untuk mengambil data history
function getAllHistory($timeExport, $customers)
{
    try {
        $conn = dbcon();

        // Validate connection
        if (!$conn) {
            throw new Exception('Database connection failed: ' . print_r(sqlsrv_errors(), true));
        }


        $tsql = "SELECT * FROM [3P_T_HISTORY] WHERE PREPARE_DATE = ? AND CUSTOMER = ?";
        $params = [$timeExport, $customers];
        $stmt = sqlsrv_query($conn, $tsql, $params);

        if ($stmt === false) {
            throw new Exception('Query execution failed: ' . print_r(sqlsrv_errors(), true));
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        return $data;
    } catch (Exception $e) {
        error_log('Database Error: ' . $e->getMessage());
        throw $e;
    }
}
