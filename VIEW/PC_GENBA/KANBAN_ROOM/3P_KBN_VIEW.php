<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
}
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
                            <input type="text" id="customerNumber" class="form-control" placeholder="Customer Number" maxlength="25" required />
                            <input type="text" id="densoNumber" class="form-control" placeholder="Denso Number" maxlength="15" required />
                            <input type="number" id="qtyKanban" class="form-control" placeholder="Qty" required min="1" />
                            <input type="text" id="processKanban" class="form-control" placeholder="Process" maxlength="4" required />
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="seqKanban" class="form-control" placeholder="Seq" maxlength="4" required />
                            <input type="text" id="customerPO" class="form-control" placeholder="Customer PO" maxlength="20" required />
                            <div class="row">
                                <div class="col">
                                    <input type="date" id="dateKanban" class="form-control" required />
                                </div>
                            </div>
                            <input type="text" id="nameProduct" class="form-control" placeholder="Name Product" required />
                        </div>
                    </div>
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

        document.getElementById('qrForm').addEventListener('submit', function(e) {
            e.preventDefault();
            generateQrValue();
        });

        function generateQrValue() {
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
            const qtyKanban = document.getElementById('qtyKanban').value.trim();
            const processKanban = document.getElementById('processKanban').value.trim();
            const seqKanban = document.getElementById('seqKanban').value.trim();
            const customerPO = document.getElementById('customerPO').value.trim();
            // const monthKanban = document.getElementById('monthKanban').value.trim();
            // const dayKanban = document.getElementById('dayKanban').value.trim();
            // const yearKanban = document.getElementById('yearKanban').value.trim();
            const nameProduct = document.getElementById('nameProduct').value.trim();
            const datePicker = document.getElementById('dateKanban').value;
            console.log(datePicker);
            

            // Format inputs
            let qtyMod = qtyKanban.padStart(7, "0");
            let seqModKanban = seqKanban.padStart(4, ' ');
            let cusModKanban = customerPO.padEnd(20, ' ');
            let customerModNumber = customerNumber.padEnd(25, ' ');
            let densoModNumber = densoNumber.padEnd(15, ' ');

            // let formattedDate = `${dayKanban}-${monthKanban}-${yearKanban}`;
            

            // Generate QR Code value
            var printQr = densoQr + customerModNumber + densoModNumber + qtyMod + processKanban + seqModKanban + cusModKanban + datePicker;

            // Generate QR Code
            const qr = new QRious({
                element: qrCanvas,
                value: printQr,
                size: 200
            });

            // Display QR Code
            qrCanvasDisplay.width = qrCanvas.width;
            qrCanvasDisplay.height = qrCanvas.height;
            const ctx = qrCanvasDisplay.getContext('2d');
            ctx.drawImage(qrCanvas, 0, 0);
            qrCodeContainer.style.display = 'block';

            // Prepare QR Value object
            const qrValue = {
                customerNumber: customerNumber,
                densoNumber: densoNumber,
                qtyKanban: qtyKanban,
                processKanban: processKanban,
                seqKanban: seqKanban,
                customerPO: customerPO,
                date: datePicker,
                namaProduk: nameProduct,
            };

            // Generate and display HTML
            const generatedHtml = generateHtml(qrCanvas.toDataURL(), qrValue); // Pass QR Code image URL
            document.getElementById('generatedHtmlContainer').innerHTML = generatedHtml; // Display HTML in the element

            // Call function to print label
            printLabel(generatedHtml);
        }

        function generateHtml(qrImageUrl, qrValue) {
            return `<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Identitas Barang</title>
                    <style>
                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            .card-Label {
                                width: 9.5cm;
                                height: 8cm;
                                page-break-after: always;
                                margin: 0;
                                padding: 10px;
                            }
                        }
                        html, body {
                            height: 100%;
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
                            width: 9.5cm;
                            height: 8cm;
                            padding: 10px;
                            display: flex;
                            flex-direction: column;
                            overflow: hidden;
                            font-size: 0.9rem;
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
                            gap: 5px;
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
                            flex: 1;
                            text-align: center;
                        }
                        .judul-fot {
                            background-color: #e6f2ff;
                            border-radius: 3px;
                            padding: 2px;
                            font-size: 0.6rem;
                            margin-bottom: 2px;
                            color: #0066cc;
                        }
                        .namProd, .namMod, .namQty, .namDel, .namSeq {
                            margin: 0;
                            font-size: 0.8rem;
                            font-weight: bold;
                        }
                        .qr-code {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                        }
                        .qr-code img {
                            max-width: 60px;
                            max-height: 60px;
                            border: 1px solid #0066cc;
                            border-radius: 5px;
                        }
                        .nomer-pelanggan, .nomer-produk {
                            margin: 0;
                            font-size: 0.9rem;
                        }
                    </style>
                </head>
                <body>
                    <div class="card-Label">
                        <div class="header">
                            <h1>${qrValue.customer}</h1>
                            <h1>Identitas Barang</h1>
                        </div>
                        <div class="content-wrapper">
                            <div>
                                <p class="Identitas">Nomer Pelanggan:</p>
                                <h5 class="nomer-pelanggan">${qrValue.customerNumber}</h5>
                            </div>
                            <div>
                                <p class="Identitas">Nomer Produk:</p>
                                <h3 class="nomer-produk">${qrValue.densoNumber}</h3>
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col">
                                        <p class="judul-fot">Nama Produk</p>
                                        <h5 class="namProd">${qrValue.namaProduk}</h5>
                                    </div>
                                    <div class="col">
                                        <p class="judul-fot">Model</p>
                                        <h5 class="namMod">${qrValue.processKanban}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col">
                                        <p class="judul-fot">Qty</p>
                                        <h6 class="namQty">${qrValue.qtyKanban}</h6>
                                    </div>
                                    <div class="col">
                                        <p class="judul-fot">Tanggal Delivery</p>
                                        <h6 class="namDel">${qrValue.date}</h6>
                                        <p class="judul-fot">Seq</p>
                                        <h6 class="namSeq">${qrValue.seqKanban}</h6>
                                    </div>
                                    <div class="col qr-code">
                                        <p class="judul-fot">QR Code</p>
                                        <img src="${qrImageUrl}" alt="QR Code" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>`;
        }

        function printLabel(generatedHtml) {
            const iframe = document.createElement('iframe');
            iframe.style.visibility = 'hidden';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            document.body.appendChild(iframe);

            // Write the label HTML to the iframe
            iframe.contentDocument.write(generatedHtml);
            iframe.contentDocument.close();

            // Set up print styles
            const style = iframe.contentDocument.createElement('style');
            style.textContent = `
                    @page {
                    size: 950mm 850mm; // Ubah ukuran menjadi 90mm x 60mm
                    margin: 0;
                    }
                    body {
                    width: 950mm; // Ubah lebar menjadi 90mm
                    height: 850mm; // Ubah tinggi menjadi 60mm
                    }
                    .container {
                    transform: scale(1);
                    transform-origin: center center;
                    }
                    `;
            iframe.contentDocument.head.appendChild(style);

            // Wait for images to load
            setTimeout(() => {
                // Trigger print
                iframe.contentWindow.print();

                // Remove the iframe after printing
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 1000);
            }, 500);
        }
    </script>
</body>

</html>