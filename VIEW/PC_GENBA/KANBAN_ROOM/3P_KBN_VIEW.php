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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body text-center">
                <h1 class="card-title">QR Code Generator</h1>
                <div class="mb-3">
                    <input type="text" id="customerNumber" class="form-control" placeholder="Customer Number" maxlength="25"/>
                    <input type="text" id="densoNumber" class="form-control" placeholder="Denso Number" maxlength="15"/>
                    <input type="text" id="qtyKanban" class="form-control" placeholder="Qty" />
                    <input type="text" id="processKanban" class="form-control" placeholder="Process" maxlength="4"/>
                    <input type="text" id="seqKanban" class="form-control" placeholder="Seq" maxlength="4"/>
                    <input type="text" id="customerPO" class="form-control" placeholder="Customer PO" maxlength="20"/>
                    <input type="text" id="monthKanban" class="form-control" placeholder="Month" maxlength="2"/>
                    <input type="text" id="dayKanban" class="form-control" placeholder="Day" maxlength="2"/>
                    <input type="text" id="yearKanban" class="form-control" placeholder="Year" maxlength="2"/>
                </div>
                <button id="generateBtn" class="btn btn-success" onclick="generateQrValue()">Buat QR Code</button>
                <div class="mt-4">
                    <canvas id="qrCanvas" class="border" style="display: none;"></canvas>
                </div>
                <div id="qrCodeContainer" class="mt-3" style="display: none;">
                    <h5>QR Code Anda:</h5>
                    <canvas id="qrCanvasDisplay"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        let densoQr = 'DISC50600200000100610002101251041511207123051520731';


        function generateQrValue() {
            const qrCanvas = document.getElementById('qrCanvas');
            const qrCanvasDisplay = document.getElementById('qrCanvasDisplay');
            const qrCodeContainer = document.getElementById('qrCodeContainer');
            const customerNumber = document.getElementById('customerNumber').value;
            const densoNumber = document.getElementById('densoNumber').value;
            const qtyKanban = document.getElementById('qtyKanban').value;
            const processKanban = document.getElementById('processKanban').value;
            const seqKanban = document.getElementById('seqKanban').value;
            const customerPO = document.getElementById('customerPO').value;
            const monthKanban = document.getElementById('monthKanban').value;
            const dayKanban = document.getElementById('dayKanban').value;
            const yearKanban = document.getElementById('yearKanban').value;


            let qtyMod = qtyKanban.padStart(7, "0");
            let seqModKanban = seqKanban.padStart(4, ' ');
            let cusModKanban = customerPO.padEnd(20, ' ');
            let customerModNumber = customerNumber.padEnd(25, ' ');
            let densoModNumber = densoNumber.padEnd(15, ' ');

            var printQr = densoQr + customerModNumber + densoModNumber +  qtyMod + processKanban  + seqModKanban + cusModKanban  + dayKanban + '-' + monthKanban + '-' + yearKanban;
            if (customerNumber.trim() === "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Silakan masukkan teks atau URL!',
                });
                return;
            }

            const qr = new QRious({
                element: qrCanvas,
                value: printQr,
                size: 200
            });
            // Menampilkan QR Code
            qrCanvasDisplay.width = qrCanvas.width;
            qrCanvasDisplay.height = qrCanvas.height;
            const ctx = qrCanvasDisplay.getContext('2d');
            ctx.drawImage(qrCanvas, 0, 0);
            qrCodeContainer.style.display = 'block';
        }
    </script>
</body>

</html>