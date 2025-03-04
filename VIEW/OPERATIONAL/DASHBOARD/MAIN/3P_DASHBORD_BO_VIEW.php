<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
} else if (!isset($_SESSION['section']) || $_SESSION['section'] != 'OPERATIONAL' && $_SESSION['access'] != 'ADMIN') {
    header('location: /3P_CHECK_OES/Error_access');
    die('Access denied: Invalid session section');
} else if (isset($_SESSION['status_user']) && $_SESSION['status_user'] == 'locked') {
    header('location: /3P_CHECK_OES/Dashboard');
    exit();
}
$baseUrl = '/3P_CHECK_OES/';
$username = $_SESSION['nama'];
$status = $_SESSION['status_user'];
include '/xampp/htdocs/3P_CHECK_OES/VIEW/GENERAL/TEMPLATE/3P_Header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3 Point Check OES - Dashboard</title>
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/Animate.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    </style>
</head>

<body>
    <div class="dashboard-container">
        <button type="button" class="btn btn-warning" onclick="window.location.href='<?= $baseUrl; ?>DASHBOARD/OPS'">
            <i class="fa fa-home"></i> <- Back
        </button>
        <div class="page-header animate__animated animate__fadeInDown">
            <h1>3 POINT CHECK OES BO</h1>
            <p class="lead">Integrated Automotive Performance Tracking System</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 animate__animated animate__fadeInUp">
            <?php
            $dashboards = [
                [
                    'title' => 'ADM',
                    'description' => 'Astra Daihatsu Motor Insights & Analytics',
                    'icon' => 'daihatsu-vector-logo-idngrafis.png',
                    'link' => 'BO/ADM'
                ],
                [
                    'title' => 'TMMIN',
                    'description' => 'Toyota Motor Manufacturing Indonesia Data',
                    'icon' => 'tmmin-logo-b1ae.png',
                    'link' => 'BO/TMMIN'
                ],
                [
                    'title' => 'TAM',
                    'description' => 'Toyota Astra Motor Comprehensive Analysis',
                    'icon' => 'pt-toyota-astra-motor-tam.png',
                    'link' => 'BO/TAM'
                ],
                [
                    'title' => 'History',
                    'description' => 'Traceability Entire Process',
                    'icon' => 'restore_10539476.png',
                    'link' => 'BO/REPORT'
                ],
                [
                    'title' => 'User Management',
                    'description' => 'Comprehensive User Registration & Tracking',
                    'icon' => 'user-icon-in-trendy-flat-style-isolated-on-grey-background-user-symbol-for-your-web-site-design-logo-app-ui-illustration-eps10-free-vector.jpg',
                    'link' => 'BO/USER'
                ],
                [
                    'title' => 'Upcoming Projects',
                    'description' => 'Work In Progress',
                    'icon' => 'OIP.jfif',
                    'link' => '#'
                ]
            ];

            foreach ($dashboards as $dashboard): ?>
                <div class="col">
                    <a href="<?php echo $baseUrl . 'OPERATIONAL/' . $dashboard['link']; ?>" class="text-decoration-none">
                        <div class="card card-custom">
                            <div class="card-body">
                                <img src="<?php echo $baseUrl; ?>ASSET/Image/<?php echo $dashboard['icon']; ?>" alt="<?php echo $dashboard['title']; ?>">
                                <h5 class="card-title text-center"><?php echo $dashboard['title']; ?></h5>
                                <p class="card-text"><?php echo $dashboard['description']; ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <script src="<?= $baseUrl; ?>/JS/3P_CHECK_INTERLOCK.js"></script>
    <script>
        const user = '<?= $username; ?>';
        const statusLogin = '<?= $status; ?>';
    </script>

</body>

</html>