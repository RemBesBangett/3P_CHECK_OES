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
include '../../GENERAL/TEMPLATE/3P_Header.php';
include '../../../CONTROLLER/PC_GENBA/3P_MANAGE_CONTROL.php';
$showData = showAllData();
$baseUrl = '/3P_CHECK_OES/';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            /* Warna latar belakang yang lebih lembut */
        }

        .table-container {
            margin: 20px;
            /* Margin untuk tabel */
            background-color: white;
            /* Latar belakang tabel */
            border-radius: 8px;
            /* Sudut melengkung */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* Bayangan */
            padding: 20px;
            /* Padding di dalam tabel */
        }

        table {
            width: 100%;
            /* Lebar tabel 100% */
        }

        th {
            background-color: #007bff;
            /* Warna latar belakang header */
            color: white;
            /* Warna teks header */
        }

        td {
            vertical-align: middle;
            /* Rata tengah vertikal */
        }
    </style>
</head>

<body>
    <div class="table-container">
        <a href="<?= $baseUrl; ?>/PC-GENBA" class="btn btn-warning">
            <i class="fa-duotone fa-solid fa-backward"></i> Kembali
        </a>
        <h2 class="text-center">Data Customer</h2>
        <button class="btn btn-success" type="button" style="float: right;" data-bs-target="#addDataModal" data-bs-toggle="modal">Add Customer</button>

        <table class="table table-striped table-bordered">
            <thead style="text-align: center;">
                <tr>
                    <th>No</th>
                    <th>Nama Customer</th>
                    <th>Partnumber Denso</th>
                    <th>Partnumber Customer</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($showData as $index => $duwata): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($duwata['CUSTOMER']) ?></td>
                        <td><?= htmlspecialchars($duwata['PN_DENSO']) ?></td>
                        <td><?= htmlspecialchars($duwata['PN_CUSTOMER']) ?></td>
                        <td><?= htmlspecialchars($duwata['DESCRIPTION']) ?></td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="openModalEdit(this)">Edit</button>
                            |
                            <button type="button" class="btn btn-danger" onclick="deleteData(this)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="customerName">Nama Customer</label>
                        <input type="text" class="form-control" id="customerName" name="customerName" placeholder="Nama Customer">
                    </div>
                    <div>
                        <label for="pnDenso">Partnumber Denso</label>
                        <input type="text" class="form-control" id="pnDenso" name="pnDenso" placeholder="Partnumber Denso">
                    </div>
                    <div>
                        <label for="pnCustomer">Partnumber Customer</label>
                        <input type="text" class="form-control" id="pnCustomer" name="pnCustomer" placeholder="Partnumber Customer">
                    </div>
                    <div>
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="editDataCustomer()">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addDataModalLabel">Tambah Customer</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group m-2">
                        <label for="customerName">Nama Customer</label>
                        <input type="text" class="form-control" id="addcustomerName" name="customerName" placeholder="Nama Customer">
                    </div>
                    <div class="form-group m-2">
                        <label for="pnDenso">Partnumber Denso</label>
                        <input type="text" class="form-control" id="addpnDenso" name="pnDenso" placeholder="Partnumber Denso">
                    </div>
                    <div class="form-group m-2">
                        <label for="pnCustomer">Partnumber Customer</label>
                        <input type="text" class="form-control" id="addpnCustomer" name="pnCustomer" placeholder="Partnumber Customer">
                    </div>
                    <div class="form-group m-2">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="adddescription" name="description" placeholder="Description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="addDataCustomer()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModalEdit(button) {
            const row = button.closest('tr'); // Ambil baris terdekat dari tombol yang ditekan

            const partNumberDenso = row.cells[2].textContent; // Ambil data partnumber Denso
            const partNumberCustomer = row.cells[3].textContent; // Ambil data partnumber Customer
            const description = row.cells[4].textContent; // Ambil data description
            const customerName = row.cells[1].textContent; // Ambil data customer name

            document.getElementById('pnDenso').value = partNumberDenso; // Isi input partnumber Denso
            document.getElementById('pnDenso').disabled = true; // Isi input partnumber Denso
            document.getElementById('pnCustomer').value = partNumberCustomer; // Isi input partnumber Customer
            document.getElementById('description').value = description; // Isi input description
            document.getElementById('customerName').value = customerName; // Isi input customer name
            document.getElementById('customerName').disabled = true; // Isi input partnumber Denso

        }

        function editDataCustomer() {

            let partNumberDenso = document.getElementById('pnDenso').value;
            let partNumberCustomer = document.getElementById('pnCustomer').value;
            let description = document.getElementById('description').value;
            let customerName = document.getElementById('customerName').value;
            console.log(partNumberDenso, partNumberCustomer, description, customerName);

            $.ajax({
                url: '<?= $baseUrl ?>CONTROLLER/PC_GENBA/3P_MANAGE_CONTROL.php',
                method: 'POST',
                data: {
                    partNumberEdit: partNumberDenso,
                    partCustEdit: partNumberCustomer,
                    descPartEdit: description,
                    custNameEdit: customerName
                },
                success: function(response) {
                    swal.fire({
                        title: 'Success',
                        text: 'Part Number ' + partNumberDenso + ' Berhasil diubah',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => {
                            window.location.reload();
                        }
                    })
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function addDataCustomer() {
            const partNumberDenso = document.getElementById('addpnDenso').value;
            const partNumberCustomer = document.getElementById('addpnCustomer').value;
            const description = document.getElementById('adddescription').value;
            const customerName = document.getElementById('addcustomerName').value;

            $.ajax({
                url: '<?= $baseUrl ?>CONTROLLER/PC_GENBA/3P_MANAGE_CONTROL.php',
                method: 'POST',
                data: {
                    partNumber: partNumberDenso,
                    partCust: partNumberCustomer,
                    descPart: description,
                    custName: customerName
                },
                success: function(response) {
                    swal.fire({
                        title: 'Success',
                        text: 'Data berhasil disimpan',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        willClose: () => {
                            location.reload();
                        }

                    })
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function deleteData(button) {
            const row = button.closest('tr');
            const partNumberDenso = row.cells[2].textContent;
            console.log(partNumberDenso);

            $.ajax({
                url: '<?= $baseUrl ?>CONTROLLER/PC_GENBA/3P_MANAGE_CONTROL.php',
                method: 'POST',
                data: {
                    partNumberDensoDel: partNumberDenso
                },
                success: function(response) {
                    swal.fire({
                        title: 'Success',
                        text: 'Data berhasil dihapus',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        willClose: () => {
                            location.reload();
                        }
                    })
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    </script>

</body>

</html>