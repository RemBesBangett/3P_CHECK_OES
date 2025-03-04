<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
} else if (!isset($_SESSION['section']) || $_SESSION['section'] != 'PC - GENBA' && $_SESSION['access'] != 'ADMIN') {
    header('location: /3P_CHECK_OES/Error_access');
    die('Access denied: Invalid session section');
} else if (isset($_SESSION['status_user']) && $_SESSION['status_user'] == 'locked') {
    header('location: /3P_CHECK_OES/Dashboard');
    exit();
}
$username = $_SESSION['nama'];
$status = $_SESSION['status_user'];
include '../../GENERAL/TEMPLATE/3P_Header.php';
include 'C:/xampp/htdocs/3P_CHECK_OES/CONTROLLER/PC_GENBA/3P_KBN_CONTROLLER.php';
$customerList = showCustomer();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3P Export</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl; ?>ASSET/qrious.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <style>
        .form-control {
            margin-bottom: 10px;
        }

        #showSisa {
            font-weight: bold;
            font-size: 25pt;
            text-align: left;
            text-decoration: underline;
            color: red;
            -webkit-animation: bounce 3s infinite;
            animation: bounce 1s infinite;
        }

        .namQty.sisa-qty {
            background-color: yellow;
            padding: 2px;
            border-radius: 3px;
            display: inline-block;
        }

        @-webkit-keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -webkit-transform: translateY(-20px);
                transform: translateY(-20px);
            }

            60% {
                -webkit-transform: translateY(-15px);
                transform: translateY(-15px);
            }
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -webkit-transform: translateY(-20px);
                transform: translateY(-20px);
            }

            60% {
                -webkit-transform: translateY(-15px);
                transform: translateY(-15px);
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <a href="<?= $baseUrl; ?>/PC-GENBA" class="btn btn-warning">Back</a>
        <div class="card shadow">
            <div class="card-body text-center">
                <h1 class="card-title">QR Code Generator</h1>
                <form id="qrForm">
                    <div class="row">
                        <div class="col-md-6">
                            <select name="customerName" id="customerName" onclick="valueDropDown()" class="form-control">
                                <option value="" id="dummy">Pilih Customer</option>
                                <?php foreach ($customerList as $index => $cust): ?>
                                    <option value="<?= $cust['CUSTOMER'] ?>"><?= $cust['CUSTOMER'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" id="customerNumber" class="form-control" placeholder="Customer Number" maxlength="25" required />
                            <input type="text" id="descriptionCust" class="form-control" placeholder="Description Part" />
                            <input type="text" id="densoNumber" class="form-control" placeholder="Denso Number" maxlength="15" required />
                            <input type="text" id="qtyRequest" class="form-control" placeholder="Quantity Request" required />

                        </div>
                        <div class="col-md-6">
                            <input type="number" id="qtyKanban" class="form-control" placeholder="Qty" required min="1" oninput="calculationSeq()" />
                            <input type="text" id="processKanban" class="form-control" placeholder="Process Code" maxlength="4" />
                            <input type="text" id="seqKanban" class="form-control" placeholder="Seq" maxlength="4" required />
                            <input type="text" id="customerPO" class="form-control" placeholder="Customer PO" maxlength="20" required />
                            <div class="row">
                                <div class="col">
                                    <input type="date" id="dateKanban" class="form-control" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p id="showSisa"></p>
                    <button type="submit" id="generateBtn" class="btn btn-success mt-3">Buat QR Code</button>
                </form>

                <div class="mt-4">
                    <canvas id="qrCanvas" class="border" style="display: none;"></canvas>
                </div>
                <div id="qrCodeContainer" class="mt-3" style="display: none;">
                    <h5>QR Code Anda:</h5>
                    <canvas id="qrCanvasDisplay"></canvas>
                </div>
                <div id="generatedHtmlContainer"></div>
            </div>
        </div>
    </div>

    <script>
        let densoQr = 'DISC50600200000100610002101251041511207123051520731';
        let descriptionCustomer;
        let typeKanban;
        let nameCustomer;
        let dateUse;
        let seqKanban = '0';
        let qtyKanban = '0';
        let totalSeq;

        document.addEventListener('DOMContentLoaded', function() {
            const customerSelect = document.getElementById('customerName');
            const kanbanSelect = document.getElementById('typeGen');
            customerSelect.addEventListener('change', valueDropDown);

        });

        function valueDropDown() {
            let customerName = document.getElementById('customerName').value;
            nameCustomer = customerName;
            if (customerName.trim() !== '') {
                // Nonaktifkan input saat proses
                document.getElementById('dummy').disabled = true;

                // Gunakan fetch API modern daripada jQuery AJAX
                fetch('<?= $baseUrl; ?>CONTROLLER/PC_GENBA/3P_KBN_CONTROLLER.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `customerName=${encodeURIComponent(customerName)}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(response => {
                        if (response.success && response.data.length > 0) {
                            // Buat elemen select baru
                            const selectElement = document.createElement('select');
                            selectElement.id = 'densoNumber';
                            selectElement.classList.add('form-control', 'mt-2');

                            // Tambahkan opsi default
                            const defaultOption = document.createElement('option');
                            defaultOption.text = 'Pilih Denso Number';
                            defaultOption.value = '';
                            selectElement.appendChild(defaultOption);

                            // Tambahkan opsi dari hasil pencarian
                            response.data.forEach((customerData, index) => {
                                const option = document.createElement('option');
                                option.value = customerData.PN_DENSO; // Sesuaikan dengan struktur data Anda
                                option.text = `${customerData.PN_DENSO}`;
                                selectElement.appendChild(option);
                            });

                            // Tambahkan event listener untuk select
                            selectElement.addEventListener('change', function() {
                                const selectedData = response.data.find(
                                    data => data.PN_DENSO === this.value
                                );

                                if (selectedData) {
                                    // Perbarui input terkait dengan data yang dipilih
                                    document.getElementById('customerNumber').value = selectedData.PN_CUSTOMER || '';
                                    document.getElementById('descriptionCust').value = selectedData.DESCRIPTION || '';

                                    // Jika Anda ingin menambahkan input lain, tambahkan di sini
                                    // document.getElementById('namaInputLain').value = selectedData.FIELD_LAIN || '';
                                }
                            });

                            // Hapus select sebelumnya jika ada
                            const existingSelect = document.getElementById('densoNumber');
                            if (existingSelect) {
                                existingSelect.remove();
                            }

                            // Sisipkan select setelah input customerName
                            document.getElementById('customerName').after(selectElement);

                            Swal.fire({
                                icon: 'success',
                                title: 'Data Ditemukan',
                                text: `Ditemukan ${response.data.length} part number untuk ${customerName}`,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            // Tangani kasus tidak ada data
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tidak Ada Data',
                                text: 'Tidak ditemukan part number untuk customer yang dipilih',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            // Bersihkan input terkait
                            document.getElementById('customerNumber').value = '';
                            document.getElementById('descriptionCust').value = '';
                        }
                    })
                    .catch(error => {
                        clearCustomerInputs();

                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: 'Terjadi masalah saat mengambil data customer',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        console.error('Fetch Error:', error);
                    })
                    .finally(() => {
                        // Aktifkan kembali input setelah proses selesai
                        document.getElementById('dummy').disabled = false;
                    });
            }
        }

        function clearCustomerInputs() {
            // Bersihkan input
            document.getElementById('customerNumber').value = '';
            document.getElementById('descriptionCust').value = '';

            // Hapus select jika ada
            const existingSelect = document.getElementById('densoNumber');
            if (existingSelect) {
                existingSelect.remove();
            }
        }
        document.getElementById('qrForm').addEventListener('submit', function(e) {
            e.preventDefault();
            generateQrValue();
        });

        function calculationSeq() {
            const qtyKanbans = parseInt(document.getElementById('qtyKanban').value.trim());
            const qtyRequests = parseInt(document.getElementById('qtyRequest').value.trim());

            // Menghitung jumlah dokumen penuh dan sisa
            const fullDocs = Math.floor(qtyRequests / qtyKanbans);
            const remainder = qtyRequests % qtyKanbans;
            const seqLeft = fullDocs + (remainder > 0 ? 1 : 0);

            // Menampilkan hasil di elemen yang sesuai
            document.getElementById('seqKanban').value = seqLeft;

            // Jika ada sisa, tampilkan dengan SweetAlert
            if (remainder > 0) {
                Swal.fire({
                    title: 'Dokumen Sisa',
                    html: `
                <p>Total Dokumen: ${seqLeft}</p>
                <p>Dokumen Penuh: ${fullDocs} x ${qtyKanbans} pcs</p>
                <p>Dokumen Terakhir: 1 x ${remainder} pcs</p>
            `,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
                document.getElementById('showSisa').innerHTML =
                    `Seq ${seqLeft}: ${remainder} pcs <span style="color:red;">(Sisa)</span>`;
            } else {
                document.getElementById('showSisa').textContent = '';
            }

            seqKanban = seqLeft;
            qtyKanban = qtyKanbans;
        }

        function generateQrValue() {

            const datePicker = document.getElementById('dateKanban').value;
            const date = new Date();
            const optionsFull = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            };

            // Parsing tanggal
            const [year, month, day] = datePicker.split('-');
            const formattedDateMod = `${month}-${day}-${year.slice(-2)}`;
            dateUse = formattedDateMod;

            const formattedTime = date.toLocaleString('id-ID', optionsFull);

            // Validate inputs
            const requiredFields = document.querySelectorAll('[required]');
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Harap isi semua field yang diperlukan!',
                    });
                    return;
                }
            }

            // Get input values
            const qtyKanbans = parseInt(document.getElementById('qtyKanban').value.trim());
            const qtyRequests = parseInt(document.getElementById('qtyRequest').value.trim());
            const customerNumber = document.getElementById('customerNumber').value.trim();
            const densoNumber = document.getElementById('densoNumber').value.trim();
            const processKanban = document.getElementById('processKanban').value.trim();
            const customerPO = document.getElementById('customerPO').value.trim();
            const descriptionCust = document.getElementById('descriptionCust').value.trim();
            descriptionCustomer = descriptionCust;

            // Hitung jumlah dokumen penuh dan sisa
            const fullDocs = Math.floor(qtyRequests / qtyKanbans);
            const remainder = qtyRequests % qtyKanbans;
            totalSeq = fullDocs + (remainder > 0 ? 1 : 0);

            // Array untuk menyimpan HTML yang dihasilkan
            const generatedHtmlPages = [];

            // Generate QR Code dan HTML untuk setiap seq
            for (let seq = 1; seq <= totalSeq; seq++) {
                // Tentukan qty untuk seq ini
                const currentQty = seq <= fullDocs ? qtyKanbans : remainder;

                // Skip jika qty 0
                if (currentQty === 0) continue;

                // Format inputs
                let qtyMod = currentQty.toString().padStart(7, "0");
                let seqModKanban = seq.toString().padStart(4, ' ');
                let cusModKanban = customerPO.padEnd(20, ' ');
                let customerModNumber = customerNumber.padEnd(25, ' ');
                let densoModNumber = densoNumber.padEnd(15, ' ');
                let processKanbanMod = processKanban.padEnd(4, ' ');

                // Generate QR Code value
                var printQr = densoQr + customerModNumber + densoModNumber + qtyMod + processKanbanMod + seqModKanban + cusModKanban + formattedDateMod;

                // Generate QR Code
                const qrCanvas = document.getElementById('qrCanvas');
                const qr = new QRious({
                    element: qrCanvas,
                    value: printQr,
                    size: 200
                });

                // Prepare QR Value object
                const qrValue = {
                    customerNumber: customerNumber,
                    densoNumber: densoNumber,
                    qtyKanban: currentQty,
                    processKanban: processKanban,
                    seqKanban: seq.toString(),
                    customerPO: customerPO,
                    date: datePicker,
                    namaProduk: nameCustomer,
                    description: descriptionCust,
                    namaUser: '<?= $username ?>',
                    tanggalPrint: formattedTime,
                };
                console.log(qrValue);

                // Generate HTML untuk setiap halaman
                const generatedHtml = generateHtml(qrCanvas.toDataURL(), qrValue, remainder);
                generatedHtmlPages.push(generatedHtml);
            }

            // Tampilkan semua HTML yang dihasilkan
            document.getElementById('generatedHtmlContainer').innerHTML = generatedHtmlPages.join('');

            // Cetak semua halaman
            printMultiplePages(generatedHtmlPages);
        }

        function printMultiplePages(htmlPages) {
            const iframe = document.createElement('iframe');
            iframe.style.visibility = 'hidden';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            document.body.appendChild(iframe);

            // Gabungkan semua halaman HTML
            const combinedHtml = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                @media print {
                                    @page {
                                        size: 10cm 8.5cm;
                                        margin: 0; /* Pastikan tidak ada margin */
                                    }
                                    body {
                                        margin: 0; /* Pastikan tidak ada margin */
                                        padding: 0; /* Pastikan tidak ada padding */
                                    }
                                    .page {
                                        margin: 0; /* Pastikan tidak ada margin */
                                        padding: 0; /* Pastikan tidak ada padding */
                                        overflow: hidden; /* Pastikan tidak ada overflow */
                                        width: 10cm; /* Pastikan lebar sesuai */
                                        height: 8.5cm; /* Pastikan tinggi sesuai */
                                    }
                                }
                                body {
                                    width: 10cm;
                                    height: 8.5cm;
                                    margin: 0; /* Pastikan tidak ada margin */
                                    padding: 0; /* Pastikan tidak ada padding */
                                }
                            </style>
                        </head>
                        <body>
                            ${htmlPages.map(html => `
                                <div class="page">
                                    ${html}
                                </div>
                            `).join('')}
                        </body>
                        </html>
                    `;

            // Tulis HTML kombinasi ke iframe
            iframe.contentDocument.write(combinedHtml);
            iframe.contentDocument.close();

            // Tunggu gambar dimuat
            setTimeout(() => {
                // Trigger cetak
                iframe.contentWindow.print();

                // Hapus iframe setelah mencetak
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 1000);
            }, 500);
        }

        function generateHtml(qrImageUrl, qrValue, remainder) {
            // Tambahkan kondisi untuk mendeteksi sisa qty
            const isSisaQty = qrValue.qtyKanban < 10;
            const isLastSeq = qrValue.seqKanban === (totalSeq.toString());

            return `<!DOCTYPE html>
                        <html lang="en">

                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Identitas Barang</title>
                            <style>
                            @page {
                                size: 10cm 8.5cm;
                                margin: 0; /* Pastikan tidak ada margin */
                            }
                            body {
                                width: 10cm;
                                height: 8.5cm;
                                margin: 0; /* Pastikan tidak ada margin */
                                padding: 0; /* Pastikan tidak ada padding */
                                overflow: hidden; /* Pastikan tidak ada overflow */
                            }
                            .card-Label {
                                background-color: #f0f8ff;
                                border: 2px solid #0066cc;
                                border-radius: 10px;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                                font-family: 'Arial', sans-serif;
                                width: 10cm;
                                height: 8.5cm;
                                display: flex;
                                flex-direction: column;
                                justify-content: center; /* Rata tengah secara vertikal */
                                align-items: center; /* Rata tengah secara horizontal */
                                overflow: hidden;
                                font-size: 0.9rem;
                                margin: 0;
                                padding: 5px;
                                box-sizing: border-box;
                            }
                            .header {
                                text-align: center;
                                margin-bottom: 5px;
                                border-bottom: 2px solid #0066cc;
                                padding-bottom: 3px;
                            }
                            .header h1 {
                                color: #0066cc;
                                font-size: 1.2rem;
                                margin: 0;
                            }


                        .content-wrapper {
                            display: flex;
                            flex-direction: column;
                            flex-grow: 1;

                        }

                        .container {
                            padding: 0;
                            margin: 0;
                        }

                        .row {
                            display: flex;
                            gap: 5px;
                            margin-bottom: 3px;
                        }

                        .col {
                            text-align: center;
                            max-width: 120px;
                        }

                        .judul-fot {
                            background-color: #e6f2ff;
                            border-radius: 3px;
                            padding: 2px;
                            font-size: 11px;
                            margin-bottom: 5px;
                            color: #0066cc;
                            margin-top: 0px;
                            width: 100px;
                            font-weight: bold;
                        }

                        .namMod,
                        .namQty,
                        .namSeq {
                            margin: 0;
                            font-size: 1.2rem;
                            font-weight: bold;
                            
                        }
                        
                        .namDel,
                        .namProd {
                            margin-top: 0px;
                            font-size: .8rem;
                            font-weight: bold;
                        }

                        .qr-code {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            margin-left: 20px;
                        }

                        .qr-code img {
                            width: 90px;
                            height: 90px;
                        }

                        .nomer-pelanggan,
                        .nomer-produk {
                            margin: 0;
                            font-size: 0.9rem;
                            text-align: center;
                            font-size: 18px;
                        }

                        #firstlayer {
                            margin-bottom: -60px;
                        }

                        .Identitas {
                            margin-bottom: 0px;
                        }

                        .nomer-produk {
                            margin-bottom: 10px;
                            font-size: 30px;
                        }

                        .info-user {
                            font-size: 0.7rem;
                            margin-top: 2px;
                        }
                       .sisa-qty {
                            background-color: yellow;
                            color: black;
                            padding: 0px 5px;
                            display: inline-block;
                            font-weight: bold;
                        }
                        
                        .last-seq-qty {
                        font-size: 1.0rem;
                            color: black;
                            padding: 3px 5px;
                            display: inline-block;
                            font-weight: bold;
                        }
                       .sisa-indicator {
                        font-size: 0.7em;
                        color: red;
                        font-weight: bold;
                        margin: 0px 2px;
                        }
                    </style>
                </head>

                <body>
                    <div class="card-Label">
                        <div class="header">
                            <h1>${qrValue.namaProduk}</h1>
                            <h1>KANBAN CARD</h1>
                        </div>
                        <div class="content-wrapper">
                            <div>
                                <p class="Identitas">Nomor Pelanggan:</p>
                                <h5 class="nomer-pelanggan">${qrValue.customerNumber}</h5>
                            </div>
                            <div>
                                <p class="Identitas">Nomor Produk:</p>
                                <h3 class="nomer-produk">${qrValue.densoNumber}</h3>
                            </div>
                            <div class="container" style="border-top: solid 1px black;">
                                <div class="row" id="firstlayer">
                                    <div class="col">
                                        <p class="judul-fot">Nama Produk</p>
                                        <h5 class="namProd">${qrValue.namaProduk}</h5>
                                    </div>
                                    <div class="col">
                                        <p class="judul-fot">Tanggal Delivery</p>
                                        <h6 class="namDel">${qrValue.date}</h6>
                                    </div>
                                    <div class="col qr-code">
                                        <p class="judul-fot">QR Code</p>
                                        <img src="${qrImageUrl}" alt="QR Code" />
                                    </div>
                                </div>
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col">
                        <p class="judul-fot">Qty</p>
                        <h6 class="namQty" style="justify-content: center;">
                                    ${isLastSeq 
                                        ? `<span class="last-seq-qty">${qrValue.qtyKanban} pcs</span>` 
                                        : (isSisaQty 
                                            ? `<span class="sisa-qty">${qrValue.qtyKanban} pcs</span>` 
                                            : `${qrValue.qtyKanban} pcs`)
                                    }
                                </h6>
                                <p class="sisa-indicator">
                                    ${isLastSeq 
                                        ? (remainder > 0 
                                            ? `<span>(Criple)</span>` 
                                            : `<span></span>`) 
                                        : (isSisaQty 
                                            ? `<span></span>` 
                                            : '')
                                    }
                                </p>
                                 </div>
                                    <div class="col">
                                       <p class="judul-fot">Seq</p>
                                       <h6 class="namSeq">${qrValue.seqKanban}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="info-user">${qrValue.tanggalPrint} || ${qrValue.namaUser}</p>
                    </div>
                </body>

                </html>`;
        }
    </script>
</body>

</html>