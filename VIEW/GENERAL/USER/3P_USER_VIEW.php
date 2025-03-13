<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
}
include "../../../MODEL/USER/3P_USER_MODEL.php";
$showAllUser = showAllUser();
include "../../GENERAL/TEMPLATE/3P_Header.php";
$baseUrl = '/3P_CHECK_OES/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Table</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }


        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);

            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }

        h1 {
            color: #007bff;
            font-weight: 600;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 500;
        }

        .btn-custom {
            border-radius: 20px;
            padding: 5px 15px;
        }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mt-4 mb-4">User Information</h1>
        <?php if ($_SESSION['access'] === 'ADMIN' && $_SESSION['access'] === 'LEADER') : ?>
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add User</button>
        <?php endif; ?>
        <div class="table-responsive">

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NPK</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Access</th>
                        <th>Status</th>
                        <th>Section</th>
                        <th>Line</th>
                        <th>Leader</th>

                        <th scope="col">Action</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($showAllUser as $index => $user) : ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($user['NPK']) ?></td>
                            <td><?= htmlspecialchars($user['NAMA']) ?></td>
                            <td><?= htmlspecialchars($user['PASSWORD']) ?></td>
                            <td><?= htmlspecialchars($user['ACCESS']) ?></td>
                            <td><?= htmlspecialchars($user['STATUS']) ?></td>
                            <td><?= htmlspecialchars($user['SECTION']) ?></td>
                            <td><?= htmlspecialchars($user['LINE']) ?></td>
                            <td><?= htmlspecialchars($user['LEADER']) ?></td>
                            <td>

                                <button class="btn btn-primary btn-custom edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-npk="<?= htmlspecialchars($user['NPK']) ?>" data-nama="<?= htmlspecialchars($user['NAMA']) ?>" data-password="<?= htmlspecialchars($user['PASSWORD']) ?>" data-access="<?= htmlspecialchars($user['ACCESS']) ?>" data-status="<?= htmlspecialchars($user['STATUS']) ?>" data-section="<?= htmlspecialchars($user['SECTION']) ?>" data-line="<?= htmlspecialchars($user['LINE']) ?>" data-leader="<?= htmlspecialchars($user['LEADER']) ?>">
                                    EDIT
                                </button>

                                <button class="btn btn-danger btn-custom delete-btn"
                                    data-npk="<?= htmlspecialchars($user['NPK']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteConfirmModal">
                                    DELETE
                                </button>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="post" action="<?php echo $baseUrl; ?>CONTROLLER/USER/3P_USER_CONTROLLER.php">
                        <div class="mb-3">
                            <label for="editNPK" class="form-label">NPK</label>
                            <input type="number" class="form-control" id="editNPK" name="editNPK" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editNama" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editNama" name="editNama">
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="text" class="form-control" id="editPassword" name="editPassword">
                        </div>
                        <div class="mb-3">
                            <label for="editAccess" class="form-label">Access</label>
                            <select class="form-control" id="editAccess" name="editAccess">
                                <?php if ($_SESSION['access'] === 'ADMIN') : ?>
                                    <option value="ADMIN">ADMIN</option>
                                <?php endif; ?>
                                <option value="LEADER">LEADER</option>
                                <option value="OPERATOR">OPERATOR</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-control" id="editStatus" name="editStatus">
                                <option value="ACTIVE">ACTIVE</option>
                                <?php if ($_SESSION['access'] === 'ADMIN') : ?>
                                    <option value="EXPIRED">EXPIRED</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editSection" class="form-label">Section</label>
                            <select name="editSection" id="editSection" class="form-control" <?php echo ($_SESSION['access'] !== 'ADMIN') ? 'disabled' : ''; ?>>
                                <option value="OPERATOR">OPERATOR</option>
                                <option value="PC - GENBA">PC GENBA</option>
                                <option value="SHIPPING">SHIPPING</option>
                            </select>
                            <!-- <input type="text" class="form-control" id="editSection" name="editSection"> -->
                        </div>
                        <div class="mb-3">
                            <label for="editLine" class="form-label">Line</label>
                            <input type="text" class="form-control" id="editLine" name="editLine">
                        </div>
                        <div class="mb-3">
                            <label for="editleader" class="form-label">Leader</label>
                            <input type="text" class="form-control" id="editleader" name="editLeader">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveChanges">Save changes</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" method="post" action="<?php echo $baseUrl; ?>CONTROLLER/USER/3P_USER_CONTROLLER.php">
                        <div class="mb-3">
                            <label for="addNPK" class="form-label">NPK</label>
                            <input type="number" class="form-control" id="addNPK" name="addNPK" required>
                        </div>
                        <div class="mb-3">
                            <label for="addNama" class="form-label">Name</label>
                            <input type="text" class="form-control" id="addNama" name="addNama" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="addPassword" name="addPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="addAccess" class="form-label">Access</label>
                            <select class="form-control" id="addAccess" name="addAccess" required>
                                <?php if ($_SESSION['access'] === 'ADMIN') : ?>
                                    <option value="ADMIN">ADMIN</option>
                                <?php endif; ?>
                                <option value="LEADER">LEADER</option>
                                <option value="OPERATOR">OPERATOR</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addStatus" class="form-label">Status</label>
                            <select class="form-control" id="addStatus" name="addStatus" required>
                                <option value="ACTIVE">ACTIVE</option>
                                <?php if ($_SESSION['access'] === 'ADMIN') : ?>
                                    <option value="EXPIRED">EXPIRED</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addSection" class="form-label">Section</label>
                            <select name="addSection" id="addSection" class="form-control">
                                <option value="OPERATOR">OPERATOR</option>
                                <option value="PC - GENBA">PC GENBA</option>
                                <option value="SHIPPING">SHIPPING</option>
                            </select>
                            <!-- <input type="text" class="form-control" id="addSection" name="addSection" required> -->
                        </div>
                        <div class="mb-3">
                            <label for="addLine" class="form-label">Line</label>
                            <input type="text" class="form-control" id="addLine" name="addLine" required>
                        </div>
                        <div class="mb-3">
                            <label for="addLeader" class="form-label">Leader</label>
                            <input type="text" class="form-control" id="addLeader" name="addLeader" required>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveNewUser ">Save New User</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Function to check user existence
            function checkUser() {
                const npk = $('#addNPK').val();
                const nama = $('#addNama').val();

                // Reset the invalid class for both inputs
                $('#addNPK').removeClass('is-invalid');
                $('#addNama').removeClass('is-invalid');
                $('#saveNewUser ').prop('disabled', false); // Enable the save button by default

                if (npk || nama) {
                    $.ajax({
                        url: '<?php echo $baseUrl; ?>CONTROLLER/USER/3P_USER_CHECK.php',
                        type: 'GET',
                        data: {
                            npk: npk,
                            nama: nama
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.exists) {
                                // Check which input is invalid and add the class accordingly
                                if (response.npkExists) {
                                    $('#addNPK').addClass('is-invalid');
                                }
                                if (response.namaExists) {
                                    $('#addNama').addClass('is-invalid');
                                }
                                $('#saveNewUser ').prop('disabled', true); // Disable the save button if any field is invalid
                            }
                        }
                    });
                }
            }

            // Event listeners for input changes
            $('#addNPK').on('input', checkUser);
            $('#addNama').on('input', checkUser);

            // Populate the edit modal with user data
            $('.edit-btn').on('click', function() {
                // Get data attributes from the clicked button
                const npk = $(this).data('npk');
                const nama = $(this).data('nama');
                const password = $(this).data('password');
                const access = $(this).data('access');
                const status = $(this).data('status');
                const section = $(this).data('section');
                const line = $(this).data('line');
                const leader = $(this).data('leader');

                // Populate the modal fields
                $('#editNPK').val(npk);
                $('#editNama').val(nama);
                $('#editPassword').val(password);
                $('#editAccess').val(access);
                $('#editStatus').val(status);
                $('#editSection').val(section);
                $('#editLine').val(line);
                $('#editleader').val(leader);
            });

            function deleteUser(npk) {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Anda yakin ingin menghapus pengguna ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?php echo $baseUrl; ?>CONTROLLER/USER/3P_USER_CONTROLLER.php',
                            type: 'GET',
                            data: {
                                deleteMbut: true, // Sesuaikan dengan kondisi di server
                                npk: npk
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Refresh atau update tabel
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan jaringan',
                                    icon: 'error'
                                });
                                console.error("Error:", error);
                            }
                        });
                    }
                });
            }

            // Event listener untuk tombol delete
            $(document).on('click', '.delete-btn', function() {
                const npk = $(this).data('npk');
                deleteUser(npk);
            });
        });
    </script>
</body>


</html>
<?php include "../../GENERAL/TEMPLATE/3P_Footer.php"; ?>