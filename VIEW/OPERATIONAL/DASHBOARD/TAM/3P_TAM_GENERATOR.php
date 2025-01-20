<?php
// Pastikan direktori untuk menyimpan file SIL sudah ada
$silDirectory = 'SIL_FILES/';  // Sesuaikan dengan struktur folder Anda

// Pastikan direktori ada
if (!file_exists($silDirectory)) {
    mkdir($silDirectory, 0777, true);
}

// Terima data dari POST
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['noSil']) && isset($data['entries'])) {
    $noSil = htmlspecialchars($data['noSil']); // Sanitasi input
    $entries = $data['entries'];

    // Buat nama file
    $filename = $silDirectory . 'SIL_' . $noSil . '.php';

    // Cek apakah file sudah ada
    if (file_exists($filename)) {
        echo json_encode(['success' => false, 'message' => 'File already exists']);
        exit;
    }

    // Mulai buffer output
    ob_start();

    // Buat konten HTML lengkap
    echo "
    
    <?php
session_start();
if (!isset(\$_SESSION['loggedin']) || \$_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
} else if (!isset(\$_SESSION['section']) || \$_SESSION['section'] != 'OPERATIONAL' && \$_SESSION['access'] != 'ADMIN') {
    header('location: /3P_CHECK_OES/Error_access');
    die('Access denied: Invalid session section');
} else if (isset(\$_SESSION['status_user']) && \$_SESSION['status_user'] == 'locked') {
    header('location: /3P_CHECK_OES/Dashboard');
    exit();
}
\$username = \$_SESSION['nama'];
\$baseUrl = '/3P_CHECK_OES/';
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SIL Details - 3713737</title>
    <!-- Script Dependencies -->
    <script src='<?= \$baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js'></script>
    <script src='<?= \$baseUrl; ?>ASSET/jquery-3.7.1.js'></script>
    <script src='<?= \$baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js'></script>
    <link rel='stylesheet' href='<?= \$baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css'>
    <link rel='stylesheet' href='<?= \$baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css'>
    <style>
        .form-control-custom {
            height: 30px;
            padding: 2px 5px;
            font-size: 12px;
        }

        .label-custom {
            font-size: 12px;
            margin-bottom: 2px;
        }

        .text-left {
            text-align: left;
        }

        .process-guide {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .guide-step {
            flex: 1;
            text-align: center;
            font-weight: bold;
            padding: 5px;
            border-radius: 5px;
            margin: 0 2px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>
    <div class='container-fluid'>
        <div class='row'>
            <div class='col-2 bg-light p-3' style='height: 100vh;'>
                <div class='kanban-section'>
                    <h5>Kanban</h5>
                    <div class='card mb-2'>
                        <div class='card-body p-2'>
                            <h6 class='card-title mb-1'>Open</h6>
                            <div class='progress'>
                                <div class='progress-bar bg-warning' role='progressbar' style='width: 25%' aria-valuenow='25' aria-valuemin='0' aria-valuemax='100'>25%</div>
                            </div>
                        </div>
                    </div>
                    <div class='card mb-2'>
                        <div class='card-body p-2'>
                            <h6 class='card-title mb-1'>In Progress</h6>
                            <div class='progress'>
                                <div class='progress-bar bg-primary' role='progressbar' style='width: 50%' aria-valuenow='50' aria-valuemin='0' aria-valuemax='100'>50%</div>
                            </div>
                        </div>
                    </div>
                    <div class='card mb-2'>
                        <div class='card-body p-2'>
                            <h6 class='card-title mb-1'>Completed</h6>
                            <div class='progress'>
                                <div class='progress-bar bg-success' role='progressbar' style='width: 25%' aria-valuenow='25' aria-valuemin='0' aria-valuemax='100'>25%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-10 p-4'>
                <div class='card'>
                    <div class='card-header bg-primary text-white d-flex justify-content-between align-items-center'>
                        <h3 class='mb-0' id='noSil'>3713737</h3>
                        <a href='<?= \$baseUrl; ?>OPERATIONAL/TAM' class='btn btn-secondary btn-sm'>
                            <i class='fas fa-arrow-left'></i> Back to List
                        </a>
                    </div>
                    <div class='card-body'>
                        <form id='silForm'>
                            <div class='table-responsive'>
                                <table class='table table-bordered table-hover'>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Part Number</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id='dataTable'>
                                        <!-- Data akan diisi oleh JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        <button type='button' class='btn btn-success' onclick='submitJob()'>Finish</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='modal fade' id='continueModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header bg-primary text-white'>
                    <h5 class='modal-title'>Continue Process</h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                </div>
                <div class='modal-body'>
                    <form id='continueForm'>
                        <div class='row'>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Part Number</label>
                                <input type='text' class='form-control' id='modalPartNumber' readonly>
                            </div>

                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Scan Kanban</label>
                                <input type='text' class='form-control' id='inputScanKanban' placeholder='Scan Kanban'>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Scan Case</label>
                                <input type='text' class='form-control' id='inputScanCase' class='cektohok' placeholder='Scan Case Label'>
                            </div>
                            <div class='col-md-12 mb-3'>
                                <p class='jumlahScanKanban'>Scanned: <span id='scannedCount'>0</span> / <span id='totalCount'>0</span></p>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Supplier Label</label>
                                <input type='text' class='form-control' id='modalSupplierLabel' readonly placeholder='Scan Kanban (Customer PN)'>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Qty</label>
                                <input type='text' class='form-control' id='modalQuantitySupplier' placeholder='Masukkan Quantity Label'>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Scan Label</label>
                                <input type='text' class='form-control' id='inputScanLabel' placeholder='Scan Supplier Label'>
                            </div>
                            <div class='col-md-12 mb-3'>
                                <p class='jumlahScanLabel'>Scanned: <span id='scannedLabelCount'>0</span> | <span id='totalLabelCount'>0</span></p>
                            </div>
                            <div class='process-guide bg-light border-top'>
                                <div id='kanban-scan-process' class='guide-step'>
                                    <i class='fas fa-barcode'></i> SCAN KANBAN
                                </div>
                                <div id='case-scan-process' class='guide-step'>
                                    <i class='fa-sharp-duotone fa-solid fa-box'></i> SCAN CASE
                                </div>
                                <div id='supplier-label-guide' class='guide-step'>
                                    <i class='fas fa-tag'></i> SCAN LABEL
                                </div>
                                <div id='save-data-process' class='guide-step'>
                                    <i class='fa-solid fa-database'></i> SAVE DATA
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class='modal-footer'>
                    <button type='submit' class='btn btn-primary' id='saveButton' onclick='saveData()'>
                        <i class='fas fa-save'></i> Save
                    </button>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                </div>
            </div>
        </div>
    </div>
    
<script src='<?= \$baseUrl; ?>/JS/3P_INTERLOCK.js'></script>
       <script>
        let partNumberOri = '';
        let totalScanKanbanOri = 0;
        let qtyLabelOri = 0; //diambil dari nilai total. / bisa juga nilai input label.
        let totalScanLabelOri = 0; //diambil dari inputanlabel
        let progressScanKanbanOri = 0;
        let labelScanningEnabled = false;
        let contentKanban = '';
        let noSil = document.getElementById('noSil').textContent;
        let clearLabelTimeoutId;
        let clearTimeoutId;
        let contentLabel = '';
        let totalTimesScan = 0;
        let repeaterScanning = 0;
        let noSilOri;
        let currentStep = 0;
        let qtyOriSil;
        //Database Zone
        let qtyKanbanOri = 0; //qty Kanban yang akan diambil dari label
        let labelOri = '';
        let kanbanItemDB; //Config
        let delDateDB; //OK
        let kanbanIdDB; //Config
        let partNumberDB; //Config
        let manifestKanbanDB; // no need
        let convertDelDateDB; // Config yyyymmdd
        let processCodeDB; // no need
        let poNumberDB; // Config
        let vendorCodeDB; // Config
        let caseLabelDB = ''; //Config
        let caseLabelContentDB = '';
        let currentSteps = '';
        let usernameLogin = '<?= \$username; ?>';



        function updateProcessGuide() {
            const steps = [
                'kanban-scan-process',
                'case-scan-process',
                'supplier-label-guide',
                'save-data-process',
            ];

            steps.forEach((step, index) => {
                const element = document.getElementById(step);
                if (index < currentStep) {
                    element.style.backgroundColor = 'green';
                    element.style.color = 'white';
                } else if (index === currentStep) {
                    element.style.backgroundColor = 'red';
                    element.style.color = 'white';
                } else {
                    element.style.backgroundColor = '#cccccc';
                    element.style.color = 'black';
                }
                if (currentSteps === 99 && step === 'case-scan-process') {
                    element.style.backgroundColor = 'green';
                    element.style.color = 'white';
                }
            });
    
        }

        $(document).ready(function() {
            const noSils = noSil; // Ganti dengan nilai noSil yang sesuai
            noSilOri = document.getElementById('noSil').textContent;
            getData(noSils);
        });

        function getData(noSils) {
            $.ajax({
                type: 'GET',
                url: '/3P_CHECK_OES/CONTROLLER/TAM/3P_TAM_SHOW.php',
                data: {
                    noSil: noSils
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status !== 'error') {
                        populateTable(response);
                    } else {
                        console.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        function populateTable(data) {
            const tableBody = $('#dataTable');
            tableBody.empty(); // Clear the table before adding data

            data.forEach(function(item, index) {
                const row = $('<tr>');

                // Append cells to the row
                row.append($('<td>').text(index + 1));
                row.append($('<td>').text(item.PART_NUMBER));
                row.append($('<td>').text(item.QUANTITY));
                row.append($('<td>').text(item.STATUS));

                // Create the 'Continue' button
                const continueButton = $('<button>')
                    .addClass('btn btn-primary')
                    .attr('type', 'button')
                    .attr('onclick', 'handleModalOpen(this)')
                    .text('Continue');

                // Check the status and hide the button if the status is 'CLOSED'
                if (item.STATUS === 'CLOSED') {
                    continueButton.hide(); // Hide the button
                    row.addClass('closed-row'); // Add the class to change the background color
                }

                row.append($('<td>').append(continueButton));
                tableBody.append(row);
            });
        }

        // Contoh pemanggilan fungsi getData

        // Event listener untuk inputScanKanban
        document.getElementById('inputScanKanban').addEventListener('input', function() {
            const kanbanContent = this.value; // Ambil nilai dari inputScanKanban

            // Hapus timeout sebelumnya jika ada input baru
            clearTimeout(clearLabelTimeoutId);

            // Set timeout untuk memeriksa nilai setelah 1 detik
            clearLabelTimeoutId = setTimeout(() => {
                // Panggil processScan dengan nilai yang diambil
                processScan(kanbanContent);
            }, 500);
        });

        document.getElementById('inputScanCase').addEventListener('input', function() {
            const scanContent = this.value; // Ambil nilai dari inputScanKanban
            caseLabelContentDB = scanContent;


            // Hapus timeout sebelumnya jika ada input baru
            clearTimeout(clearLabelTimeoutId);

            // Set timeout untuk memeriksa nilai setelah 1 detik
            clearLabelTimeoutId = setTimeout(() => {
                // Panggil processScan dengan nilai yang diambil
                caseScan(scanContent);
            }, 500);
        });

        document.getElementById('inputScanLabel').addEventListener('input', function() {
            if (!labelScanningEnabled) {
                alert('Please scan Kanban first!');
                this.value = '';
                return;
            }

            const scannedLabel = this.value;
            clearTimeout(clearLabelTimeoutId);
            clearLabelTimeoutId = setTimeout(() => {
                handleLabelScan(scannedLabel, this); // Panggil fungsi baru
            }, 500); // Delay 1 second
        });


        // Fungsi processScan
        function processScan(kanbanContent) {
            const totalQuantity = parseInt(document.getElementById('totalCount').textContent);
            const partNumber = partNumberOri;
            totalScanKanban = totalQuantity;
            const quantityFromScan = parseInt(kanbanContent.substring(91, 98));
            const supplierLabel = kanbanContent.substring(51, 75).trim().replace(/-/g, ''); //OKOK
            const itemNo = kanbanContent.substring(144, 147).trim(); //OKOK
            const PONumber = kanbanContent.substring(106, 125).trim(); //OKOK
            const kanbanID = kanbanContent.substring(134, 143).trim(); //
            const deliveryDate = kanbanContent.substring(126, 134).trim(); // Original format dd-mm-yy
            const [day, month, year] = deliveryDate.split('-');
            const formattedDeliveryDate = `20\${year}\${month}\${day}`; // Convert to yyyymmdd format
            const kanbanItem = kanbanContent.substring(144, 147).trim().replace(/0/g, ''); //
            // const manifestKanban = kanbanContent.substring(106, 125).trim(); //
            const partNumberTAM = kanbanContent.substring(76, 91).trim(); // OK
            const vendorCode = kanbanContent.substring(148, 152).trim(); // OK
            let currentScannedCount = parseInt(document.getElementById('scannedCount').textContent) || 0;
            // Pastikan kanbanContent tidak kosong
            if (!kanbanContent) {
                alert('Error: Kanban content is empty.');
                return;

            }

            clearLabelTimeoutId = setTimeout(() => {
                // Validasi apakah konten kanban mencakup nomor bagian
                if (!kanbanContent.includes(partNumber)) {
                    alert('Error: Scanned value does not match the part number.');
                    document.getElementById('inputScanKanban').value = '';
                    return;
                }
                //postpone
                if (kanbanContent.includes(partNumber)) {
                    // Disable the input immediately upon success
                    swal.fire({
                        icon: 'success',
                        title: 'Scan Berhasil',
                        text: `Scan Kanban Berhasil`,
                        showConfirmButton: false,
                        timer: 1000,
                        willClose: () => {
                            totalScanKanbanOri = qtyOriSil / quantityFromScan;
                            document.getElementById('inputScanKanban').disabled = true;
                            document.getElementById('scannedCount').textContent = currentScannedCount;
                            document.getElementById('modalSupplierLabel').value = supplierLabel; // Set label supplier
                            document.getElementById('totalLabelCount').textContent = quantityFromScan; // Set total label count
                            document.getElementById('scannedLabelCount').textContent = '0'; // Set jumlah label yang sudah dipindai 
                            document.getElementById('totalCount').textContent = totalScanKanbanOri; // Set total count

                            if (caseLabelDB == '' || caseLabelContentDB === '') {
                                // document.getElementById('inputScanCase').focus();
                                document.getElementById('inputScanCase').disabled = false; // Aktifkan input label
                                document.getElementById('inputScanLabel').disabled = true; // Aktifkan input label
                                document.getElementById('inputScanCase').focus();
                                currentStep = 1;
                                updateProcessGuide();
                            } else {
                                document.getElementById('inputScanLabel').disabled = false; // Aktifkan input label
                                document.getElementById('inputScanLabel').focus();
                                currentStep = 2;
                                  updateProcessGuide();
                            }
                            if (contentLabel !== '') {
                                document.getElementById('modalQuantitySupplier').disabled = true; // Set jumlah KanbcontentKanban supplier
                            } else if (contentLabel === '') {
                                document.getElementById('modalQuantitySupplier').value = '1';
                            } 
                        }
                    });
                }
                //--------------------------------------------------------------------------------

                qtyLabelOri = quantityFromScan,
                    kanbanIdDB = kanbanID,
                    delDateDB = formattedDeliveryDate,
                    poNumberDB = PONumber,
                    vendorCodeDB = vendorCode,
                    partNumberDB = partNumberTAM,
                    labelOri = supplierLabel
                kanbanItemDB = kanbanItem;
                if (contentKanban === '') {
                    contentKanban = kanbanContent;
                } else {
                    if (totalQuantity > 1) {
                        contentKanban += ',&' + kanbanContent;
                    }
                }

                // Validasi jumlah yang diekstrak
                if (isNaN(quantityFromScan) || quantityFromScan <= 0) {
                    alert('Error: Invalid quantity extracted from scan.');
                    return;
                }

                // Mendapatkan jumlah yang sudah dipindai


                // Update UI
                currentScannedCount += 1; // Update jumlah yang sudah dipindai
                progressScanKanbanOri = currentScannedCount;


                labelScanningEnabled = true; // Aktifkan pemindaian label
                if (progressScanKanbanOri < totalScanKanban) {
                    resetKanbanInput(); // Reset input kanban jika perlu
                }

                // Clear kanban input
                document.getElementById('inputScanKanban').value = '';
            }, 1000);
        }

        function caseScan(scanContent) {
            const caseLabel = scanContent.substring(0, 1)


            clearLabelTimeoutId = setTimeout(() => {
                if (caseLabel === kanbanIdDB) {
                    caseLabelDB = caseLabel;

                    Swal.fire({
                        icon: 'success',
                        title: 'Scan Berhasil',
                        text: `Scan Case Label Succes`,
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => {
                            document.getElementById('inputScanCase').disabled = true; // Aktifkan input label
                            document.getElementById('inputScanCase').setAttribute('readonly', caseLabelContentDB); // Aktifkan input label
                            document.getElementById('inputScanLabel').disabled = false; // Aktifkan input label
                            document.getElementById('inputScanLabel').focus();
                            document.getElementById('modalQuantitySupplier').disabled = false; // Set jumlah KanbcontentKan
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Scan Gagal',
                        text: `Check dan Pastikan Label Case Sesuai Kanban`,
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => {
                            location.reload();
                        }
                    });
                }


                currentStep = 2;
                updateProcessGuide();
            }, 1000);
        }

        // Event listener for label scanning
        function handleLabelScan(scannedLabel, inputElement) {
            const modalSupplierLabel = document.getElementById('modalSupplierLabel').value;
            let modalQuantitySupplier = parseInt(document.getElementById('modalQuantitySupplier').value) || 1;
            const totalLabelCount = parseInt(document.getElementById('totalLabelCount').textContent);
            let currentScannedLabelCount = parseInt(document.getElementById('scannedLabelCount').textContent) || 0;
            let modifiedQty = modalQuantitySupplier.toString().padStart(7, '0');
            const numericPart = modalSupplierLabel.match(/\d+/)[0];

            if (scannedLabel.includes(numericPart) && scannedLabel.length <= modalSupplierLabel.length + 10) {
                // Jika ini adalah pemindaian pertama
                currentScannedLabelCount += modalQuantitySupplier;
                totalScanLabelOri = currentScannedLabelCount;
                qtyLabelOri = modalQuantitySupplier;
                // Validasi jika jumlah yang dipindai melebihi total
                if (currentScannedLabelCount > totalLabelCount) {
                    alert(`Error: Cannot exceed total label count of \${totalLabelCount}`);
                    return;
                }

                // Update jumlah yang sudah dipindai di UI
                document.getElementById('scannedLabelCount').textContent = currentScannedLabelCount;

                // Cek kondisi untuk menampilkan Swal.fire
                if (currentScannedLabelCount <= totalLabelCount) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Scan Berhasil',
                        text: `Scan Label Berhasil`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }

                // Jika semua label sudah dipindai
                if (currentScannedLabelCount === totalLabelCount) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Scan Berhasil',
                        text: `Scan Label Terpenuhi`,
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => {
                            totalTimesScan = totalScanKanbanOri * qtyLabelOri;
                            inputElement.disabled = true; // Nonaktifkan input label
                            labelScanningEnabled = false; // Nonaktifkan pemindaian label
                            if (progressScanKanbanOri < totalScanKanbanOri) {
                                resetKanbanInput(); // Reset input kanban jika perlu
                                document.getElementById('inputScanKanban').focus(); // Fokus pada input kanban
                                  currentSteps = 99;
                                  currentStep = 0;
                                  updateProcessGuide();
                            } else if (progressScanKanbanOri === totalScanKanbanOri) {
                                currentStep = 3;
                                updateProcessGuide();
                                document.getElementById('saveButton').disabled = false;
                            }
                        }
                    });
                }

                // Clear input setelah pemindaian
                inputElement.value = '';

                // Simpan label
                if (contentLabel === '') {
                    contentLabel = scannedLabel + ' ' + modifiedQty; // Simpan label pertama
                } else if (totalLabelCount > 1) {
                    contentLabel += ',&' + scannedLabel + ' ' + modifiedQty; // Tambahkan label berikutnya
                }
            } else {
                swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Scan Label Salah',
                    showConfirmButton: false,
                    timer: 1500,
                    willClose: () => {
                        inputElement.value = '';
                    }
                });
            }

        }

        function handleModalOpen(button) {
            const row = button.closest('tr'); // Ambil baris terdekat dari tombol yang ditekan
            const partNumber = row.cells[1].textContent; // Ambil nomor bagian dari kolom kedua
            const quantity = row.cells[2].textContent; // Ambil jumlah dari kolom ketiga
            partNumberOri = row.cells[1].textContent;
            qtyOriSil = quantity;
            // Set nilai ke modal
            document.getElementById('modalPartNumber').value = partNumber;
            document.getElementById('saveButton').disabled = true;
            document.getElementById('inputScanLabel').disabled = true;
            document.getElementById('inputScanCase').disabled = true;
            document.getElementById('modalQuantitySupplier').disabled = true;

            currentStep = 0;
            updateProcessGuide();

            // Buka modal
            const continueModal = new bootstrap.Modal(document.getElementById('continueModal'));
            continueModal.show();

            // Fokus ke inputScanKanban setelah modal terbuka
            continueModal._element.addEventListener('shown.bs.modal', function() {
                document.getElementById('inputScanKanban').focus();
            });
        }

        function resetKanbanInput() {
            document.getElementById('inputScanKanban').disabled = false;
            repeaterScanning = 1;
        }




        function saveData() {

            repeaterScanning = 0;
            const date = new Date();
            // Format dengan opsi
            const options = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour12: false, // Gunakan 24 jam
            };

            const optionsFull = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false, // Gunakan 24 jam

            };

            const formattedDate = date.toLocaleString('id-ID', options);
            const formattedTime = date.toLocaleString('id-ID', optionsFull);

            if (!noSil || !partNumberOri) {
                Swal.fire('Error', 'Harap lengkapi semua data yang diperlukan', 'error');
                return;
            }

            // Siapkan objek data untuk dikirim
            const saveToDatabase = {
                noSil: noSil, //
                qtyKanban: qtyKanbanOri, //
                totalKanban: totalScanKanbanOri, //
                qtyLabel: qtyLabelOri || 0, // Traceability qtyLabel
                contentScanKanban: contentKanban, // Traceability Actual Label Content
                contentScanLabel: contentLabel, // Traceability Actual Label Content
                prepareTime: formattedDate, // Traceability Actual dd/mm/yyyy
                actualTime: formattedTime, // Traceability Actual Jam
                customer: 'TAM',
                saveButton: true, //

                //CHARACTERISTIC ZONE
                partNumber: partNumberDB, // PartNUmber
                customerLabel: labelOri, // Customer Label
                totalLabel: totalTimesScan, // Qty ORI SIL
                KanbanId: kanbanIdDB, // franchise & case
                kanbanItem: kanbanItemDB, // Item No
                delDates: delDateDB, // shipment date
                PONumber: poNumberDB, // PO Number
                labelItem: vendorCodeDB,
                caseNo: caseLabelContentDB,
                userName: '<?= \$username; ?>'
                // processCode: processCodeDB, // Process Code
            };
            console.log(saveToDatabase);

            // Tampilkan data di console.log untuk debugging
            console.log('Data yang akan dikirim:', saveToDatabase);

            // Konfirmasi sebelum mengirim
            Swal.fire({
                title: 'Konfirmasi Pengiriman Data',
                html: '<div>' +
                    '<p><strong>No SIL:</strong> ' + saveToDatabase.noSil + '</p>' +
                    '<p><strong>Part Number:</strong> ' + saveToDatabase.partNumber + '</p>' +
                    '<p><strong>Qty Kanban:</strong> ' + saveToDatabase.qtyKanban + '</p>' +
                    '<p><strong>Total Kanban:</strong> ' + saveToDatabase.totalKanban + '</p>' +
                    '<p><strong>Customer Label:</strong> ' + saveToDatabase.customerLabel + '</p>' +
                    '<p><strong>Qty Label:</strong> ' + saveToDatabase.qtyLabel + '</p>' +
                    '<p><strong>Total Label:</strong> ' + saveToDatabase.totalLabel + '</p>' +
                    '<p><strong>Total Label:</strong> ' + saveToDatabase.delDates + '</p>' +
                    '</div>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim data ke server
                    $.ajax({
                        url: '/3P_CHECK_OES/CONTROLLER/TAM/3P_TAM_CONTROL.php',
                        type: 'POST',
                        dataType: 'json',
                        data: saveToDatabase,
                        beforeSend: function() {
                            // Tampilkan loading
                            Swal.fire({
                                title: 'Mengirim Data...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            console.log('Response dari server:', response);

                            if (response.status === 'success') {
                                currentStep = 3;
                                updateProcessGuide();
                                // Ubah warna blok menjadi hijau
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message || 'Data berhasil disimpan',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {

                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message || 'Gagal mengirim data ke Database',
                                    confirmButtonText: 'Tutup'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Tangani error AJAX
                            console.error('Error AJAX:', status, error);
                            console.error('Response Error:', xhr.responseText);

                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Sistem',
                                text: `Terjadi kesalahan: error`,
                                footer: '<pre>' + xhr.responseText + '</pre>',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    });
                }
            });
        }


        function submitJob() {
            const noSil = document.getElementById('noSil').textContent;
            const tableBody = document.getElementById('dataTable');
            const rows = tableBody.getElementsByTagName('tr'); // Get all rows in the table
            const date = new Date();
            // Format dengan opsi
            const options = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false, // Gunakan 24 jam

            };

            const formattedDate = date.toLocaleString('id-ID', options);


            let entireData = []; // Clear the array before populating it

            // Iterate through each row
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];

                // Ensure the row has the expected number of cells
                if (row.cells.length >= 4) {
                    let partNumberData = row.cells[1].textContent;
                    let qtyData = row.cells[2].textContent;
                    let statusData = row.cells[3].textContent;

                    // Push the data into the entireData array
                    entireData.push({
                        noSil: noSil,
                        partNumber: partNumberData,
                        qty: qtyData,
                        status: statusData,
                        timePrep: formattedDate
                    });
                }
            }
            console.log(entireData);


            // Check if entireData is not empty before sending
            if (entireData.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'No data to submit.',
                    confirmButtonText: 'OK'
                });
                return; // Exit the function if there's no data
            }

            // Send the data via AJAX
            $.ajax({
                url: '/3P_CHECK_OES/CONTROLLER/TAM/3P_TAM_CONTROL.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    nosil: noSil,
                    dataSil: JSON.stringify(entireData) // Convert entireData to JSON string
                },
                success: function(data) {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '/3P_CHECK_OES/VIEW/OPERATIONAL/DASHBOARD/TAM/3P_TAM_DELETE.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        numSil: noSil
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            row.remove();
                                            swal.fire({
                                                icon: 'success',
                                                title: 'SIL Deleted',
                                                text: `SIL has been deleted.`,
                                                confirmButtonText: 'OK'
                                            });
                                        } else {
                                            swal.fire({
                                                icon: 'success',
                                                title: 'Error',
                                                text: response.message || 'Failed to delete SIL.',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        let errorMessage = 'Unknown error occurred';
                                        try {
                                            const errorResponse = JSON.parse(xhr.responseText);
                                            errorMessage = errorResponse.message || errorMessage;
                                        } catch (e) {
                                            errorMessage = xhr.responseText || 'Unable to parse error response';
                                        }

                                        swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: errorMessage,
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                });
                                location.href = '<?= \$baseUrl; ?>OPERATIONAL/TAM';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX errors
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText); // Log the response text for more details
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while sending data.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    </script>
</body>

</html>";

    // Tulis konten ke file
    if (file_put_contents($filename, ob_get_clean()) === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to create file']);
        exit;
    }

    // Kirim respons
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
