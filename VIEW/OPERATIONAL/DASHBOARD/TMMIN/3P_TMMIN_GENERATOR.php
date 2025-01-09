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
            header('location: /3P_CHECK_OES/Logout');
            exit();
        };
    \$baseUrl = '/3P CHECK OES/';
    ?>
    <!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SIL Details - " . htmlspecialchars($noSil) . "</title>
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
                        <h3 class='mb-0' id='noSil'>" . htmlspecialchars($noSil) . "</h3>
                        <a href='<?= \$baseUrl; ?>OPERATIONAL/TMMIN' class='btn btn-secondary btn-sm'>
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
                                <input type='text' class='form-control' id='inputScanKanban' placeholder='Enter scanned quantity'>
                            </div>
                            <div class='col-md-12 mb-3'>
                                <p class='jumlahScanKanban'>Scanned: <span id='scannedCount'>0</span> / <span id='totalCount'>0</span></p>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Supplier Label</label>
                                <input type='text' class='form-control' id='modalSupplierLabel' readonly>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Qty</label>
                                <input type='text' class='form-control' id='modalQuantitySupplier'>
                            </div>
                            <div class='col-md-4 mb-3'>
                                <label class='form-label'>Scan Label</label>
                                <input type='text' class='form-control' id='inputScanLabel' placeholder='Enter scanned label'>
                            </div>
                            <div class='col-md-12 mb-3'>
                                <p class='jumlahScanLabel'>Scanned: <span id='scannedLabelCount'>0</span> | <span id='totalLabelCount'>0</span></p>
                            </div>
                            <div class='process-guide bg-light border-top'>
                                <div id='kanban-scan-process' class='guide-step'>
                                    <i class='fas fa-barcode'></i> SCAN KANBAN
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

    <script>
        let partNumberOri = '';
        let qtyKanbanOri = 0; //qty Kanban yang akan diambil dari label
        let totalScanKanbanOri = 0;
        let labelOri = '';
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
        //postpone zone
        let kanbanItemDB;
        let delDateDB;
        let kanbanIdDB;
        let partNumberDB;
        let manifestKanbanDB;
        let convertDelDateDB;
        // ------------------------------



        function updateProcessGuide() {
            const steps = [
                'kanban-scan-process',
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
                url: '/3P_CHECK_OES/CONTROLLER/TMMIN/3P_TMMIN_SHOW.php',
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
            const supplierLabel = kanbanContent.substring(51, 63).trim().replace(/-/g, '');
            const itemNo = kanbanContent.substring(143, 145).trim();
            const PONumber = kanbanContent.substring(106, 116).trim();
            const kanbanID = kanbanContent.substring(134, 142).trim(); //5
            const deliveryDate = kanbanContent.substring(126, 134).trim(); //6 
            const kanbanItem = kanbanContent.substring(144, 147).trim().replace(/0/g, ''); //3
            const manifestKanban = kanbanContent.substring(106, 116).trim(); //1
            const partNumberTMMIN = kanbanContent.substring(76, 90).trim(); //9

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
                            document.getElementById('inputScanLabel').disabled = false; // Aktifkan input label
                            document.getElementById('modalSupplierLabel').value = supplierLabel; // Set label supplier
                            document.getElementById('totalLabelCount').textContent = quantityFromScan; // Set total label count
                            document.getElementById('scannedLabelCount').textContent = '0'; // Set jumlah label yang sudah dipindai 
                            document.getElementById('inputScanLabel').focus(); // Fokus pada input label
                            document.getElementById('totalCount').textContent = totalScanKanbanOri; // Set total count
                            currentStep = 1;
                            updateProcessGuide();
                            if (contentLabel !== '') {
                                document.getElementById('modalQuantitySupplier').disabled = true; // Set jumlah KanbcontentKanban supplier
                            } else if (contentLabel === '') {
                                document.getElementById('modalQuantitySupplier').value = '1';
                            }
                        }
                    });
                }

                //postpone zone

                // Ekstrak jumlah dari pemindaian



                manifestKanbanDB = manifestKanban; //manifest kanban
                partNumberDB = partNumberTMMIN; //part number
                qtyKanbanOri = quantityFromScan;
                labelOri = supplierLabel; //customer label
                kanbanItemDB = kanbanItem; //Kanban Item
                delDateDB = deliveryDate; //ETD dd/mm/yyyy
                kanbanIdDB = kanbanID; //Kanban ID

                console.log('Kanban ID:', kanbanID);
                console.log('Manifest Kanban:', manifestKanbanDB);
                console.log('Part Number:', partNumberDB);
                console.log('Qty Kanban:', qtyKanbanOri);
                console.log('Customer Label:', labelOri);
                console.log('Kanban Item:', kanbanItemDB);
                console.log('Delivery Date:', delDateDB);

                //--------------------------------------------------------------------------------
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
                let currentScannedCount = parseInt(document.getElementById('scannedCount').textContent) || 0;

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
                    alert(`Error: Cannot exceed total label count of ${totalLabelCount}`);
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
                            } else if (progressScanKanbanOri === totalScanKanbanOri) {
                                currentStep = 2;
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
            if (/R[0-9]/.test(manifestKanbanDB)) {
                customerAuto = 'ADM VANNING';
            } else {
                customerAuto = 'TMMIN VANNING';
            }
            if (/^[A-Za-z]{2}/.test(manifestKanbanDB)) {
                customerAuto = 'TMMIN VANNING';
            } else if (/^[A-Za-z][0-9]/.test(manifestKanbanDB)) {
                customerAuto = 'ADM VANNING';
            }
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

            let stringDate = delDateDB;
            const dateParts = stringDate.split('-');
            const days = dateParts[0];
            const months = dateParts[1];
            let years = dateParts[2];

            if (years.length == 2) {
                years = '20' + years;
            }

            const deliveFormated = days + '.' + months + '.' + years;

            const formattedDate = date.toLocaleString('id-ID', options);
            const formattedTime = date.toLocaleString('id-ID', optionsFull);

            if (!noSil || !partNumberOri) {
                Swal.fire('Error', 'Harap lengkapi semua data yang diperlukan', 'error');
                return;
            }

            // Siapkan objek data untuk dikirim
            const saveToDatabase = {
                noSil: noSil, //SUPLIER REF NO#1
                partNumber: partNumberOri, //SUPLIER REF NO#2
                qtyKanban: qtyKanbanOri, //OK
                totalKanban: totalScanKanbanOri, //OK
                customerLabel: labelOri, //OK
                qtyLabel: qtyLabelOri || 0, // OK
                totalLabel: totalTimesScan, // OK Qty Delivery
                contentScanKanban: contentKanban, // OK
                contentScanLabel: contentLabel, // OK
                customer: customerAuto,
                saveButton: true, //SOLID
                prepareTime: formattedDate, //OK
                KanbanId: kanbanIdDB, //OK
                kanbanItem: kanbanItemDB, //OK
                actualTime: formattedTime, //OK 
                delDates: delDateDB, //ETD dd/mm/yyyy
                delivVan: deliveFormated,
                dataID: 'D',
                manifestKanban: manifestKanbanDB,
            };

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
                        url: '/3P_CHECK_OES/CONTROLLER/TMMIN/3P_TMMIN_CONTROL.php',
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

                                        location.href = '/3P_CHECK_OES/OPERATIONAL/TMMIN';
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
                url: '/3P_CHECK_OES/CONTROLLER/TMMIN/3P_TMMIN_CONTROL.php',
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
                                    url: '/3P_CHECK_OES/VIEW/OPERATIONAL/DASHBOARD/TMMIN/3P_TMMIN_DELETE.php',
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
                                location.href = '/3P_CHECK_OES/OPERATIONAL/TMMIN';
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
