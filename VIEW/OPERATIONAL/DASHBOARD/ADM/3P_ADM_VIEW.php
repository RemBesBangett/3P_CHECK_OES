<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
}

include "../../../GENERAL/TEMPLATE/3P_Header.php";
$baseUrl = '/3P_CHECK_OES/';
// Function to read files from folder
function getSilFiles($directory)
{
    $files = scandir($directory);
    $silFiles = [];

    foreach ($files as $file) {
        if (preg_match('/^SIL_(.+)\.php$/', $file)) {
            $silFiles[] = $file;
        }
    }

    // Sort by file creation time
    usort($silFiles, function ($a, $b) use ($directory) {
        return filemtime($directory . '/' . $a) - filemtime($directory . '/' . $b);
    });

    return $silFiles;
}

// Get SIL files
$silFiles = getSilFiles('SIL_FILES/');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table Management</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .table {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 0.5rem;
            text-align: center;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .modal-content {
            border-radius: 0.5rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">DATA SCAN ADM ASSYST</h2>
        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add New Entry
            </button>
            <button type="button" class="btn btn-danger" id="clearButton">
                <i class="fas fa-trash"></i> Clear All Entries
            </button>
            <!-- <button class="btn btn-warning btn-sm" onclick="exportData()">
                <i class="fas fa-file-export"></i> Export
            </button>
            <input type="date" name="time" id="timePort" class="form-control w-auto"> -->
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No SIL</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display SIL files in the table
                    foreach ($silFiles as $index => $file) {
                        $noSil = substr($file, 4, -4); // Extract No SIL from file name
                        echo "<tr data-no-sil='{$noSil}'>
                                        <td>" . ($index + 1) . "</td>
                                        <td>{$noSil}</td>
                                        <td>OPEN</td>
                                        <td>
                                            <button class='btn btn-success btn-sm' onclick='continueEntry(\"{$noSil}\")'>
                                                <i class='fas fa-play'></i> Continue
                                            </button>
                                            <button class='btn btn-danger btn-sm' onclick='deleteEntry(this)'>
                                                <i class='fas fa-trash'></i> Delete
                                            </button>
                                        </td>
                                    </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New SIL Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label for="SIL" class="form-label">SIL (any length)</label>
                            <input type="text" class="form-control" id="SIL" required>
                            <div class="invalid-feedback" id="silError"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Entry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Authentication Modal -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">Authentication Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="authForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= $baseUrl; ?>JS/Auth/Auth.js"></script>
    <script>
        let noSilVar;
        let entriesVar = [];
        let partNumVar;
        let qtyVar;

        const date = new Date();
        const options = {
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        };

    
        const formattedDate = date.toLocaleString('id-ID', options);

        document.getElementById('addForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const silValue = document.getElementById('SIL').value;
            processSilInput(silValue);

            document.getElementById('SIL').value = '';
            const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
            modal.hide();
        });

        function processSilInput(silValue) {
            const noSil = silValue.substring(0, 7);
            const length = silValue.length;
            const entries = [];
            let silEntries = JSON.parse(localStorage.getItem('silEntries')) || [];
            const existingSil = silEntries.find(entry => entry.noSil === noSil);

            if (existingSil) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Duplicate SIL',
                    text: `No SIL ${noSil} already exists. Please use a different No SIL.`,
                    confirmButtonText: 'OK'
                });
                return;
            }

            for (let i = 20; i + 22 <= length; i += 22) {
                const partNumber = silValue.substring(i, i + 15).trim();
                const quantity = silValue.substring(i + 15, i + 22).replace(/^0+/, '');

                if (partNumber && quantity) {
                    entries.push({
                        partNumber: partNumber,
                        quantity: quantity
                    });
                }
            }

            if (entries.length > 0) {
                addEntryToTable(noSil, entries);
                createDynamicPHPFile(noSil, entries);

                $.ajax({
                    url: '<?= $baseUrl; ?>CONTROLLER/ADM/3P_ADM_SIL.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        noSil: noSil,
                        entries: entries,
                        timeStamp: formattedDate
                    }),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'SIL Added Successfully',
                            text: `SIL ${noSil} has been created with ${entries.length} part numbers.`,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error inserting data:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to add SIL to the database.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'No valid part number data found.',
                    confirmButtonText: 'OK'
                });
            }
        }

        function addEntryToTable(noSil, entries) {
            const tableBody = document.querySelector('#dataTable tbody');
            const rowCount = tableBody.rows.length + 1;

            const newRow = tableBody.insertRow();
            newRow.innerHTML = `
                <td>${rowCount}</td>
                <td>${noSil}</td>
                <td>OPEN (${entries.length} Part Number)</td>
                <td>
                    <button class="btn btn-success btn-sm" onclick="continueEntry('${noSil}')">
                        <i class="fas fa-play"></i> Continue
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteEntry(this)">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            `;
        }

        function createDynamicPHPFile(noSil, entries) {
            $.ajax({
                url: '<?php echo $baseUrl; ?>VIEW/OPERATIONAL/DASHBOARD/ADM/3P_ADM_GENERATOR.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    noSil: noSil,
                    entries: entries
                }),
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        console.log('File created successfully:', result.filename);
                    } else {
                        console.error('Error creating file:', result.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error creating file:', error);
                }
            });
        }

        function continueEntry(noSil) {
            window.location.href = '<?php echo $baseUrl; ?>OPERATIONAL/ADM/SIL_' + noSil; 
        }



        
        function deleteEntry(button) {
            const row = button.closest('tr');
            const numberSils = row.cells[1].innerText;
            
            swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete SIL` + numberSils,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= $baseUrl; ?>VIEW/OPERATIONAL/DASHBOARD/ADM/3P_ADM_DELETE.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            numSil: numberSils
                        },
                        success: function(response) {
                            if (response.success) {
                                row.remove();
                                swal.fire({
                                    icon: 'success',
                                    title: 'SIL Deleted',
                                    text: `SIL ${numberSils} has been deleted.`,
                                    confirmButtonText: 'OK'
                                });
                                window.location.reload();
                            } else {
                                swal.fire({
                                    icon: 'success',
                                    title: 'Error',
                                    text: response.message || 'Failed to delete SIL.',
                                    confirmButtonText: 'OK'
                                });
                                window.location.reload();
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
                                icon: 'success',
                                title: 'Success Delete Data',
                                text: errorMessage,
                                confirmButtonText: 'OK'
                            }).then(function() {
                                window.location.reload();
                            });
                        }
                    });
                }
            });

        }
        // function exportData() {
        //     var timePort = document.getElementById('timePort').value;

        //     // Validasi input tanggal
        //     if (!timePort) {
        //         Swal.fire({
        //             icon: 'warning',
        //             title: 'Invalid Input',
        //             text: 'Please select a date to export data.',
        //             confirmButtonText: 'OK'
        //         });
        //         return;
        //     }

        //     // Konversi format tanggal dari YYYY-MM-DD ke DD/MM/YYYY
        //     var dateParts = timePort.split('-');
        //     var formattedDate = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];

        //     var dataToSend = {
        //         timePort: formattedDate,
        //         customer: 'ADM ASSYST'
        //     };

        //     // Tampilkan loading
        //     Swal.fire({
        //         title: 'Exporting Data...',
        //         html: 'Please wait while preparing your export...',
        //         allowOutsideClick: false,
        //         didOpen: function() {
        //             Swal.showLoading();
        //         }
        //     });

        //     // Gunakan jQuery AJAX (sesuai dengan kode asli Anda)
        //     $.ajax({
        //         url: <?= $baseUrl; ?> + 'CONTROLLER/ADM/3P_ADM_EXPORT.php',
        //         type: 'POST',
        //         data: dataToSend,
        //         xhrFields: {
        //             responseType: 'blob'
        //         },
        //         success: function(data, status, xhr) {
        //             Swal.close();

        //             // Dapatkan nama file dari header Content-Disposition
        //             var filename = 'Report .xlsx';
        //             var disposition = xhr.getResponseHeader('Content-Disposition');
        //             if (disposition && disposition.indexOf('attachment') !== -1) {
        //                 var filenameMatch = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        //                 if (filenameMatch && filenameMatch[1]) {
        //                     filename = filenameMatch[1].replace(/['"]/g, '');
        //                 }
        //             }

        //             // Buat URL untuk download
        //             var downloadUrl = window.URL.createObjectURL(data);
        //             var a = document.createElement('a');
        //             a.style.display = 'none';
        //             a.href = downloadUrl;
        //             a.download = filename;

        //             document.body.appendChild(a);
        //             a.click();

        //             // Bersihkan URL objek
        //             window.URL.revokeObjectURL(downloadUrl);

        //             // Tampilkan pesan sukses
        //             Swal.fire({
        //                 icon: 'success',
        //                 title: 'Export Successful',
        //                 text: 'File ' + filename + ' has been downloaded',
        //                 confirmButtonText: 'OK'
        //             });
        //         },
        //         error: function(xhr, status, error) {
        //             Swal.close();

        //             var errorMessage = 'Unknown error occurred';

        //             // Coba parsing error response
        //             try {
        //                 // Jika response adalah text, parse sebagai JSON
        //                 var responseText = xhr.responseText;
        //                 if (responseText) {
        //                     var errorResponse = JSON.parse(responseText);
        //                     errorMessage = errorResponse.message || errorMessage;
        //                 }
        //             } catch (e) {
        //                 // Jika parsing gagal, gunakan pesan error default
        //                 errorMessage = xhr.statusText || 'Export failed';
        //             }

        //             Swal.fire({
        //                 icon: 'error',
        //                 title: 'Export Failed',
        //                 text: errorMessage,
        //                 confirmButtonText: 'OK'
        //             });
        //         }
        //     });

        // }
    </script>
</body>

</html>

<?php
// include "../../../GENERAL/TEMPLATE/3P_Footer.php";
?>