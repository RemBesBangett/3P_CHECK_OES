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
    $customers = ['TMMIN VANNING', 'ADM VANNING'];

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

    $templateFile = '../../FORMAT/TMMIN/FORMAT TMMIN.xlsx';

    // Load spreadsheet
    if (!file_exists($templateFile)) {
        throw new Exception('Template file not found.');
    }
    $spreadsheet = IOFactory::load($templateFile);
    $sheet = $spreadsheet->getActiveSheet();

    // Tulis data baris
    $row = 2;
    foreach ($getAllHistory as $history) {
        $sheet->setCellValue('A' . $row, 'D' ?? '');
        $sheet->setCellValue('B' . $row, $history['KANBAN_ID'] ?? ''); //MANIFEST NO R(ANGKA) = TMMIN
        $sheet->setCellValue('C' . $row, $history['CUSTOMER_LABEL'] ?? ''); //PARTNUMBER CUSTOMER
        $sheet->setCellValue('D' . $row, $history['KANBAN_ITEM'] ?? ''); //ITEM NO
        $sheet->setCellValue('E' . $row, $history['TOTAL_LABEL'] ?? ''); //QTY DELIVERY
        $sheet->setCellValue('F' . $row, '' ?? ''); //QTY DELIVERY
        $sheet->setCellValue('G' . $row, $history['MANIFEST'] ?? ''); //QTY DELIVERY
        $sheet->setCellValue('H' . $row, $history['DELIVERY_VANNING'] ?? ''); //QTY DELIVERY
        $sheet->setCellValue('I' . $row, $history['NO_SIL'] ?? ''); //QTY DELIVERY
        $sheet->setCellValue('J' . $row, $history['PART_NUMBER'] ?? ''); //QTY DELIVERY

        // Gaya border
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($styleArray);

        $row++;
    }

    // Nama file download
    $filename = 'export_' . date('Ymd_His') . '.xlsx';

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

        $placeholders = implode(',', array_fill(0, count($customers), '?'));
        $tsql = "SELECT * FROM [3P_T_HISTORY] WHERE PREPARE_DATE = ? AND CUSTOMER IN ($placeholders)";
        $params = array_merge([$timeExport], $customers);
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
