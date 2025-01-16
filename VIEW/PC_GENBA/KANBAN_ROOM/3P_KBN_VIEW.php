<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
}
$usernameLogin = $_SESSION['nama'];
include '../../GENERAL/TEMPLATE/3P_Header.php';
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
        #showSisa{
            font-weight: bold;
            font-size: 25pt;
            text-align: left;
            text-decoration: underline;
            color : red;
            -webkit-animation: bounce 3s infinite;
            animation: bounce 1s infinite;
        }
    @-webkit-keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
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
        0%, 20%, 50%, 80%, 100% {
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
        <div class="card shadow">
            <div class="card-body text-center">
                <h1 class="card-title">QR Code Generator</h1>
                <form id="qrForm">
                    <div class="row">
                        <div class="col-md-6">
                            <select name="customerName" id="customerName" onclick="valueDropDown()" class="form-control">
                                <option value="" id="dummy">Pilih Customer</option>
                                <option value="PT. MESIN ISUZU INDONESIA">PT. MESIN ISUZU INDONESIA</option>
                                <option value="PT. SUBANG AUTOCOMP INDONESIA">PT. SUBANG AUTOCOMP INDONESIA</option>
                                <option value="PT. AISAN NASMOCO INDUSTRI">PT. AISAN NASMOCO INDUSTRI</option>
                                <option value="PT. ASAHIMAS FLAT GLASS">PT. ASAHIMAS FLAT GLASS</option>
                                <option value="PT. FURUKAWA AUTOMOTIVE SYSTEM">PT. FURUKAWA AUTOMOTIVE SYSTEM</option>
                                <option value="PT. INDOPRIMA GEMILANG">PT. INDOPRIMA GEMILANG</option>
                                <option value="PT. MIKUNI INDONESIA">PT. MIKUNI INDONESIA</option>
                                <option value="PT. SUMI INDO WIRING SYSTEMS">PT. SUMI INDO WIRING SYSTEMS</option>
                                <option value="PT. TRIJAYA UNION">PT. TRIJAYA UNION</option>
                                <option value="PT. KAWASAKI MOTOR INDONESIA">PT. KAWASAKI MOTOR INDONESIA</option>
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
            const seqLeft = fullDocs + 1;

            // Menampilkan hasil di elemen yang sesuai
            document.getElementById('seqKanban').value = fullDocs;
            // Jika ada sisa, tampilkan dengan SweetAlert
            if (remainder > 0 || seqLeft == 0) {
                Swal.fire({
                    title: 'Dokumen Sisa',
                    text: `Jumlah dokumen sisa: 1 (berisi ${remainder} pcs, Seq ke ${seqLeft})`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
                document.getElementById('showSisa').textContent = `Sisa Kanban: 1 Kanban (berisi ${remainder} pcs, Seq ke ${seqLeft})`
            } else {
                document.getElementById('showSisa').textContent ='';
            }
            seqKanban = fullDocs;
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
                hour12: false, // Gunakan 24 jam
            };

            // Parsing tanggal
            const [year, month, day] = datePicker.split('-');

            // Ubah format
            const formattedDateMod = `${month}-${day}-${year.slice(-2)}`;
            dateUse = formattedDateMod;

            const formattedTime = date.toLocaleString('id-ID', optionsFull);
            const qrCanvas = document.getElementById('qrCanvas');
            const qrCanvasDisplay = document.getElementById('qrCanvasDisplay');
            const qrCodeContainer = document.getElementById('qrCodeContainer');

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
            const customerNumber = document.getElementById('customerNumber').value.trim();
            const densoNumber = document.getElementById('densoNumber').value.trim();

            const processKanban = document.getElementById('processKanban').value.trim();
            const customerPO = document.getElementById('customerPO').value.trim();
            const descriptionCust = document.getElementById('descriptionCust').value.trim();
            descriptionCustomer = descriptionCust;


            // Ambil jumlah seq yang akan digenerate
            const seqCount = parseInt(seqKanban);

            // Array untuk menyimpan HTML yang dihasilkan
            const generatedHtmlPages = [];

            // Generate QR Code dan HTML untuk setiap seq
            for (let seq = 1; seq <= seqCount; seq++) {
                // Format inputs
                let qtyMod = qtyKanban.toString().padStart(7, "0");
                let seqModKanban = seq.toString().padStart(4, ' ');
                let cusModKanban = customerPO.padEnd(20, ' ');
                let customerModNumber = customerNumber.padEnd(25, ' ');
                let densoModNumber = densoNumber.padEnd(15, ' ');
                let processKanbanMod = processKanban.padEnd(4, ' ');

                // Generate QR Code value
                var printQr = densoQr + customerModNumber + densoModNumber + qtyMod + processKanbanMod + seqModKanban + cusModKanban + formattedDateMod;

                // Generate QR Code
                const qr = new QRious({
                    element: qrCanvas,
                    value: printQr,
                    size: 200
                });

                // Prepare QR Value object
                const qrValue = {
                    customerNumber: customerNumber,
                    densoNumber: densoNumber,
                    qtyKanban: qtyKanban,
                    processKanban: processKanban,
                    seqKanban: seq.toString(), // Gunakan seq aktual
                    customerPO: customerPO,
                    date: datePicker,
                    namaProduk: nameCustomer,
                    description: descriptionCust,
                    namaUser: '<?= $usernameLogin ?>',
                    tanggalPrint: formattedTime,
                };
                console.log(qrValue);


                // Generate HTML untuk setiap halaman
                const generatedHtml = generateHtml(qrCanvas.toDataURL(), qrValue);
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
                                body {
                                    margin: 0;
                                    padding: 0;
                                }
                                .page {
                                    width: 10cm;
                                    height: 8.5cm;
                                    page-break-after: always;
                                    margin: 0;
                                    padding: 0;
                                    overflow: hidden;
                                }
                                .page:last-child {
                                    page-break-after: avoid;
                                }
                            }
                            @page {
                                size: 10cm 8.5cm;
                                margin: 0;
                            }
                            body {
                                width: 10cm;
                                height: 8.5cm;
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

        function generateHtml(qrImageUrl, qrValue) {
            return `<!DOCTYPE html>
                    <html lang="en">

                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Identitas Barang</title>
                        <style>
                            @page {
                                size: 10cm 8.5cm;
                                margin: 0;
                            }

                            body {
                                width: 10cm;
                                height: 8.5cm;
                                margin: 0;
                                padding: 0;
                                overflow: hidden;
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

                            .namProd,
                            .namMod,
                            .namQty,
                            .namDel,
                            .namSeq {
                                margin: 0;
                                font-size: 0.8rem;
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
                                border: 1px solid #000000;
                                border-radius: 5px;
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
                                margin-bottom: 20px;
                                font-size: 30px;
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
                                            <h6 class="namQty" style="justify-content: center;">${qrValue.qtyKanban}</h6>
                                        </div>
                                        <div class="col">
                                            <p class="judul-fot">Seq</p>
                                            <h6 class="namSeq">${qrValue.seqKanban}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>${qrValue.tanggalPrint} || ${qrValue.namaUser}</p>
                        </div>
                    </body>

                    </html>`;
        }

        // function printLabel(generatedHtml) {
        //     const iframe = document.createElement('iframe');
        //     iframe.style.visibility = 'hidden';
        //     iframe.style.position = 'fixed';
        //     iframe.style.right = '0';
        //     iframe.style.bottom = '0';
        //     document.body.appendChild(iframe);

        //     // Write the label HTML to the iframe
        //     iframe.contentDocument.write(generatedHtml);
        //     iframe.contentDocument.close();

        //     // Set up print styles
        //     const style = iframe.contentDocument.createElement('style');
        //     style.textContent = `
        //             @page {
        //             size: 1000mm 800mm; // Ubah ukuran menjadi 90mm x 60mm
        //             margin: 0;
        //             }
        //             body {
        //             width: 1000mm; // Ubah lebar menjadi 90mm
        //             height: 800mm; // Ubah tinggi menjadi 60mm
        //             }
        //             .container {
        //             transform: scale(0);
        //             transform-origin: center center;
        //             }
        //             `;
        //     iframe.contentDocument.head.appendChild(style);

        //     // Wait for images to load
        //     setTimeout(() => {
        //         // Trigger print
        //         iframe.contentWindow.print();

        //         // Remove the iframe after printing
        //         setTimeout(() => {
        //             document.body.removeChild(iframe);
        //         }, 1000);
        //     }, 500);
        //     window.print();
        // }
    </script>
</body>

</html>