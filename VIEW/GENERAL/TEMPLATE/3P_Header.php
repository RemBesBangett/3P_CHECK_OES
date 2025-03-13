<?php

$page = basename($_SERVER['PHP_SELF']);
$title = '';
$baseUrl = '/3P_CHECK_OES/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3P CHECK OES</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $baseUrl; ?>ASSET/Image/Logo DE.png">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            transition: margin-left .5s;
        }

        #sidebar {
            height: 100%;
            width: 280px;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: -280px;
            background-color: rgb(106, 106, 106);
            padding-top: 60px;
            transition: 0.3s;
            overflow-x: hidden;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        #sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 16px;
            color: #ecf0f1;
            display: block;
            transition: 0.2s;
            border-left: 4px solid transparent;
        }

        #sidebar a:hover {
            background-color: rgb(132, 233, 147);
            border-left: 4px solid #3498db;
            color: black;
            font-weight: bold;
        }

        #sidebar a.active {
            background-color: #3498db;
            color: white;
            border-left: 4px solid #2980b9;
        }

        #main {
            transition: margin-left .5s;
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .menu-btn {
            color: #3498db;
            font-size: 24px;
            cursor: pointer;
            transition: 0.3s;
            margin-right: 15px;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        .navbar-title {
            flex-grow: 1;
            text-align: center;
        }

        .navbar-title h3 {
            font-weight: 600;
            color: rgb(255, 255, 255);
            margin: 0;
        }

        .user-menu {
            margin-left: auto;
        }

        .user-icon {
            font-size: 24px;
            color: #3498db;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        @media screen and (max-height: 450px) {
            #sidebar {
                padding-top: 15 px;
            }

            #sidebar a {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div id="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="<?php echo $baseUrl; ?>Dashboard" class="<?= ($page === 'data') ? 'active' : '' ?>">
            <i class="fa-duotone fa-solid fa-house"></i> MAIN MENU
        </a>
        <a href="<?php echo $baseUrl; ?>OPERATIONAL/REPORT/REGULER" class="pl-4 <?= ($page === 'report_reguler') ? 'active' : '' ?>">
            <i class="fas fa-file-alt me-2"></i> REPORT REGULER
        </a>
        <a href="<?php echo $baseUrl; ?>OPERATIONAL/REPORT/BO" class="pl-4 <?= ($page === 'report_bo') ? 'active' : '' ?>">
            <i class="fas fa-file-alt me-2"></i> REPORT BO
        </a>
        <a href="<?php echo $baseUrl; ?>UserM" class="<?= ($page === 'user') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-gear me-2"></i> USER MANAGEMENT
        </a>
        <a href="<?php echo $baseUrl; ?>PC-GENBA/EXPORT" class="<?= ($page === 'export') ? 'active' : '' ?>">
            <i class="fa-solid fa-download me-2"></i> EXPORT DATA
        </a>
        <?php if ($_SESSION['access'] === 'ADMIN' || $_SESSION['section'] === 'PC-GENBA') : ?>
            <a href="<?php echo $baseUrl; ?>PC-GENBA/MERGE" class="<?= ($page === 'merge') ? 'active' : '' ?>">
            <i class="fa-solid fa-object-group me-2"></i> MERGE .ZIP
            </a>
        <?php endif; ?>
    </div>

    <nav class="navbar">
        <div class="menu-btn" onclick="openNav()">&#9776;</div>
        <a class="navbar-brand">
            <img src="<?php echo $baseUrl; ?>ASSET/Image/DENSO.png" alt="DENSO Logo">
        </a>
        <div class="navbar-title">
            <h3><?= $title ?></h3>
        </div>
        <div class="user-menu">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle user-icon"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile (<?php echo htmlspecialchars($_SESSION['nama'] ?? 'Guest'); ?>)</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>LOGOUT"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="main">
        <!-- Konten halaman akan dimasukkan di sini -->
    </div>

    <script>
        $(document).ready(function() {
            $('#sidebar a').on('click', function(e) {
                var target = $(this).attr('href');
                if (target.startsWith('#')) {
                    e.preventDefault();
                    $(target).collapse('toggle');
                } else {
                    e.preventDefault();
                    target = target.replace(/\/+/g, '/'); // Menghapus multiple slashes  
                    target = target.replace(/ /g, '_'); // Mengubah spasi menjadi _  
                    window.location.href = target;
                }
            });
        });

        function openNav() {
            document.getElementById("sidebar").style.left = "0";
            document.getElementById("main").style.marginLeft = "280px";
        }

        function closeNav() {
            document.getElementById("sidebar").style.left = "-280px";
            document.getElementById("main").style.marginLeft = "0";
        }
    </script>
</body>

</html>