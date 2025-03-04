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

// Cek apakah session username sudah ada (sudah login)
else if (!isset($_SESSION['nama'])) {
    // Jika tidak ada session, redirect ke halaman login
    header("Location: /3P_CHECK_OES/LOGOUT");
    exit(); // Pastikan script berhenti setelah redirect
}
// Jika sudah login, ambil nama pengguna dari session
$baseUrl = '/3P_CHECK_OES/';
include '/xampp/htdocs/3P_CHECK_OES/VIEW/GENERAL/TEMPLATE/3P_Header.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3 Point Check OES - Dashboard</title>
    <!-- Bootstrap & Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $baseUrl; ?>ASSET/Animate.min.css">
    <script src="<?= $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="<?= $baseUrl; ?>ASSET/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --background-color: #ecf0f1;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }

        .card-custom:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .card-custom .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            text-align: center;
        }

        .card-custom img {
            width: 100px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
            filter: grayscale(20%) brightness(1.1);
        }

        .card-custom:hover img {
            transform: scale(1.1);
            filter: grayscale(0%) brightness(1);
        }

        .card-custom .card-title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-custom .card-text {
            color: var(--secondary-color);
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .page-header {
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            color: white;
            padding: 2.5rem 0;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-header .lead {
            font-weight: 300;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
        }

        /* Subtle Hover Effect */
        .card-custom::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: all 0.6s;
        }

        .card-custom:hover::after {
            left: 100%;
        }

        .card-custom .card-body i {
            font-size: 4rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .card-custom:hover .card-body i {
            transform: scale(1.1);
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <button type="button" class="btn btn-warning" onclick="window.location.href='<?= $baseUrl; ?>DASHBOARD'">
            <i class="fa fa-home"></i> Main Menu
                </button>
                <div class="page-header animate__animated animate__fadeInDown">
                    <h1>PC - GENBA<login user>
                    </h1>
                    <p class="lead">BEKERJA SESUAI SOP</p>
                    <p class="lead">LAKUKAN STOP, CALL & WAIT JIKA DITEMUKAN ABNORMALITY</p>
                    <p class="lead">LAKUKAN 1 CYCLE PROCESS</p>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-md-3 g-4 animate__animated animate__fadeInUp">
                    <?php
                    $dashboards = [
                        [
                            'title' => 'EXPORT',
                            'description' => 'Export Document From Operational',
                            'icon' => 'journal',  // Bootstrap Icon name
                            'link' => 'EXPORT'
                        ],
                        [
                            'title' => 'KANBAN GENERATOR',
                            'description' => 'Generate Any Kanban With Spesific Values',
                            'icon' => 'printer',  // Bootstrap Icon name
                            'link' => 'KANBAN'
                        ],
                        [
                            'title' => 'MANAGE CUSTOMER',
                            'description' => 'ADD, EDIT, DELETE CUSTOMER LIST',
                            'icon' => 'person-lines-fill',  // Bootstrap Icon name
                            'link' => 'KANBAN/DATA'
                        ]
                    ];

                    foreach ($dashboards as $dashboard): ?>
                        <div class="col">
                            <a href="<?php echo $baseUrl . 'PC-GENBA/' .  $dashboard['link']; ?>" class="text-decoration-none">
                                <div class="card card-custom">
                                    <div class="card-body">
                                        <i class="bi bi-<?php echo $dashboard['icon']; ?>"></i>
                                        <h5 class="card-title text-center"><?php echo $dashboard['title']; ?></h5>
                                        <p class="card-text"><?php echo $dashboard['description']; ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
    </div>

</body>

</html>