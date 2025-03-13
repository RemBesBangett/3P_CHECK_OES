<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
} else if (isset($_SESSION['status_user']) && $_SESSION['status_user'] == 'locked') {
    header('location: /3P_CHECK_OES/Dashboard');
    exit();
}


include '../../GENERAL/TEMPLATE/3P_Header.php';
$baseUrl = '/3P_CHECK_OES/';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zip File Extractor</title>
    <link rel="stylesheet" href="<?= $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <script src="<?= $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl; ?>ASSET/jszip.min.js"></script>
    <script src="<?= $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?= $baseUrl; ?>ASSET/popper.min.js"></script>
    <style>
        .file-list {
            margin-top: 20px;
        }

        .modal-content {
            padding: 20px;
        }

        #tutorialGan {
            width: 500px;
        }

        p {
            font-weight: bold;
            font-style: italic;
            color: gray;
        }

        .container {
            width: 100%;
            margin: auto;

        }
    </style>
</head>

<body>
    <div class="container">
        <a href="<?= $baseUrl; ?>/PC-GENBA" class="btn btn-warning">
            <i class="fa-duotone fa-solid fa-backward"></i> Kembali
        </a>
        <h1 class="text-center">Zip File Extractor</h1>
        <div class="row">
            <div class="col" id="tutorialGan">
                <h5 style="font-family: 'Times New Roman', Times, serif; font-style: italic; font-weight: bold;">Tutorial</h5>
                <p>1. Tekan Choose File dan Cari File Sesuai Lokasi Folder.</p>
                <p>2. Pilih Beberapa File Dengan Format .ZIP.</p>
                <p>3. Cek Indikator Read File Pada Menu Utama.</p>
                <p>4. Preview Jika Diperlukan Untuk Memastikan Setiap File Benar.</p>
                <p>5. Download File.</p>
            </div>
            <div class="col align-items-center">
                <input type="file" id="zipFileInput" accept=".zip" multiple class="form-control">
                <div class="file-list" id="fileListContainer"></div>
                <button id="downloadButton" class="btn btn-primary" style="display:none;">Download Combined Text File</button>

            </div>
        </div>


        <!-- Modal -->
        <div id="myModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">File Content Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <pre id="modalContent"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.getElementById('zipFileInput').addEventListener('change', function(event) {
            const files = event.target.files;
            const fileListContainer = document.getElementById('fileListContainer');
            fileListContainer.innerHTML = '<h2>Extracted .txt Files:</h2><ul class="list-group"></ul>';
            const ul = fileListContainer.querySelector('ul');
            let combinedContent = '';
            let fileReadPromises = [];
            let isFirstFile = true; // Flag to check if it's the first file

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                fileReadPromises.push(new Promise((resolve) => {
                    reader.onload = function(e) {
                        const zip = new JSZip();
                        zip.loadAsync(e.target.result).then(function(contents) {
                            const txtFiles = Object.keys(contents.files).filter(function(filename) {
                                return filename.endsWith('.txt');
                            });

                            Promise.all(txtFiles.map(function(filename) {
                                return zip.file(filename).async('string').then(function(content) {
                                    // Split content into lines
                                    const lines = content.split('\n');

                                    // If it's not the first file, remove the first line
                                    if (!isFirstFile) {
                                        lines.shift(); // Remove the first line
                                    } else {
                                        isFirstFile = false; // Set the flag to false after the first file
                                    }

                                    // Join the lines back into a single string
                                    combinedContent += lines.join('\n'); // Add a newline at the end

                                    const li = document.createElement('li');
                                    li.className = "list-group-item";
                                    li.textContent = filename + " - Successfully read"; // Show success status

                                    // Create a preview button
                                    const previewButton = document.createElement('button');
                                    previewButton.className = "btn btn-info btn-sm float-right";
                                    previewButton.textContent = "Preview";
                                    previewButton.onclick = function() {
                                        document.getElementById('modalContent').textContent = content; // Set modal content
                                        $('#myModal').modal('show'); // Show modal
                                    };

                                    li.appendChild(previewButton);
                                    ul.appendChild(li);
                                });
                            })).then(function() {
                                resolve(); // Resolve the promise when all files are processed
                            });
                        });
                    };
                    reader.readAsArrayBuffer(file);
                }));
            }

            Promise.all(fileReadPromises).then(function() {
                // Show download button after processing all files
                document.getElementById('downloadButton').style.display = 'block';
            });

            document.getElementById('downloadButton').addEventListener('click', function() {
                const blob = new Blob([combinedContent], {
                    type: 'text/plain'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'combined.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
        });
    </script>
</body>

</html>