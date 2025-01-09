<?php
session_start();
$baseUrl = '/3P_CHECK_OES/';
// Cek apakah user sudah login

// Variabel untuk detail 
$username = $_SESSION['nama'];
$userRole = $_SESSION['access'];
$section = $_SESSION['section'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <!-- Bootstrap CSS -->
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .access-denied-container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .access-denied-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="access-denied-container">
        <div class="access-denied-icon">
            🚫
        </div>
        <h1 class="text-danger mb-4">Akses Ditolak</h1>
        
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Perhatian!</h4>
            <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Detail Pengguna</h5>
                <p class="card-text">
                    <strong>Nama:</strong> <?php echo htmlspecialchars($username); ?><br>
                    <strong>Role:</strong> <?php echo htmlspecialchars($userRole); ?><br>
                    <strong>Section:</strong> <?php echo htmlspecialchars($section); ?>

                </p>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="<?= $baseUrl;?>Dashboard" class="btn btn-primary">
                Kembali ke Dashboard
            </a>
            <a href="<?php echo $baseUrl; ?>logout" class="btn btn-outline-danger">
                Logout
            </a>
        </div>

        <div class="mt-3 text-muted small">
            Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator.
        </div>
    </div>
</body>
</html>
