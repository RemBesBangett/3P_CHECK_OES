<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P%20CHECK%20OES/logout');
    exit();
}
else if (!isset($_SESSION['section']) || $_SESSION['section'] != 'PC-GENBA' && $_SESSION['access'] != 'ADMIN') {
    header('location: /3P%20CHECK%20OES/Error_access'); 
    die('Access denied: Invalid session section');
}

$baseUrl = '/3P%20CHECK%20OES/';
include '../../GENERAL/TEMPLATE/3P_Header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .current-time {
            font-size: 1.5rem;
            color: #6c757d;
        }

        .button-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px));
            gap: 20px;
        }

        .btn-custom {
            font-size: 1.2rem;
            padding: 15px;
            transition: background-color 0.3s, transform 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-custom i {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Export Data For Customer</h1>
            <div class="current-time" id="currentTime"></div>
        </div>
        <div class="button-container">
            <button type="button" class="btn btn-primary btn-custom" id="admButton" value="ADM ASSYST" onclick="openModal('ADM ASSYST')">
                <i class="fas fa-car"></i> ADM
            </button>
            <button type="button" class="btn btn-secondary btn-custom" id="tmminButton" value="TMMIN VANNING" onclick="openModal('TMMIN VANNING')">
                <i class="fas fa-industry"></i> TMMIN
            </button>
            <button type="button" class="btn btn-success btn-custom" id="tamButton" value="TAM" onclick="openModal('TAM')">
                <i class="fas fa-chart-line"></i> TAM
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateModalLabel">Input Tanggal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="dateForm">
                        <div class="mb-3">
                            <label for="inputDate" class="form-label">Masukkan Tanggal</label>
                            <input type="date" class="form-control" id="inputDate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type=" button" class="btn btn-primary" id="submitDateButton" onclick="exportData()">Export</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let customerDocument;


        function openModal(value) {
            const modal = new bootstrap.Modal(document.getElementById('dateModal'));
            modal.show();
            customerDocument = value;
        }

        document.getElementById('submitDateButton').addEventListener('click', function() {
            const date = document.getElementById('inputDate').value;
            if (date) {
                alert('Tanggal yang dipilih: ' + date);
                const modal = bootstrap.Modal.getInstance(document.getElementById('dateModal'));
                modal.hide(); // Close the modal
            } else {
                alert('Silakan masukkan tanggal sebelum mengirim.');
            }
        });

        function exportData() {
            var timePort = document.getElementById('inputDate').value;
            console.log(timePort);
            // Validasi input tanggal
            if (!timePort) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Please select a date to export data.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Konversi format tanggal dari YYYY-MM-DD ke DD/MM/YYYY
            var dateParts = timePort.split('-');
            var formattedDate = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];

            var dataToSend = {
                timePort: formattedDate,
                customer: customerDocument
            };

            // Tampilkan loading
            Swal.fire({
                title: 'Exporting Data...',
                html: 'Please wait while preparing your export...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            // Tentukan URL berdasarkan tombol yang ditekan
            var exportUrl;
            switch (customerDocument) {
                case 'ADM ASSYST':
                    exportUrl = '<?= $baseUrl; ?>CONTROLLER/ADM/3P_ADM_EXPORT.php';
                    break;
                case 'TMMIN VANNING':
                    exportUrl = '<?= $baseUrl; ?>CONTROLLER/TMMIN/3P_TMMINVAN_EXPORT.php';
                    break;
                case 'TAM':
                    exportUrl = '<?= $baseUrl; ?>CONTROLLER/TAM/3P_TAM_EXPORT.php';
                    break;
                default:
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid customer selected.',
                        confirmButtonText: 'OK'
                    });
                    return;
            }

            // Gunakan jQuery AJAX
            $.ajax({
                url: exportUrl,
                type: 'POST',
                data: dataToSend,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    Swal.close();

                    // Dapatkan nama file dari header Content-Disposition
                    var filename = 'Upload Document.xlsx';
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameMatch = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1].replace(/['"]/g, '');
                        }
                    }

                    // Buat URL untuk download
                    var downloadUrl = window.URL.createObjectURL(data);
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = filename;

                    document.body.appendChild(a);
                    a.click();

                    // Bersihkan URL objek
                    window.URL.revokeObjectURL(downloadUrl);

                    // Tampilkan pesan sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Successful',
                        text: 'File ' + filename + ' has been downloaded',
                        confirmButtonText: 'OK'
                    });
                },
                error: function(xhr, status, error) {
                    Swal.close();

                    var errorMessage = 'Unknown error occurred';

                    // Coba parsing error response
                    try {
                        // Jika response adalah text, parse sebagai JSON
                        var responseText = xhr.responseText;
                        if (responseText) {
                            var errorResponse = JSON.parse(responseText);
                            errorMessage = errorResponse.message || errorMessage;
                        }
                    } catch (e) {
                        // Jika parsing gagal, gunakan pesan error default
                        errorMessage = xhr.statusText || 'Export failed';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }


        function updateTime() {
            const now = new Date();
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            document.getElementById('currentTime').textContent = now.toLocaleTimeString([], options);
        }

        setInterval(updateTime, 1000); // Update time every second
        updateTime(); // Initial call to display time immediately
    </script>
</body>

</html>