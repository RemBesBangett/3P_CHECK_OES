<?php
$baseUrl = '/3P_CHECK_OES/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Auto redirect setelah 5 detik -->
    <meta http-equiv="refresh" content="5;url=<?php echo $baseUrl; ?>CONTROLLER/LOGIN/3P_LOGOUT_CONTROL.php"> 
    <title>Oops! Jalan Buntu</title>
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .error-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-content {
            text-align: center;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out infinite;
        }
        @keyframes shake {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(5deg); }
            75% { transform: rotate(-5deg); }
            100% { transform: rotate(0deg); }
        }
        .error-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #343a40;
        }
        .error-message {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-top: 20px;
        }
        .loading-bar {
            width: 100%;
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            margin-top: 20px;
            overflow: hidden;
        }
        .loading-progress {
            width: 100%;
            height: 100%;
            background-color: #dc3545;
            animation: loading 5s linear;
        }
        @keyframes loading {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="error-content">
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h1 class="error-title">Hayoo, Mau Kemana?</h1>
            <p class="error-message">Waduh, kamu nyasar ke jalan buntu nih! Halaman yang kamu cari nggak ada.</p>
            <p class="error-message">Error 404 - Page Not Found</p>
            <p class="error-message">Kamu akan dialihkan ke halaman login dalam</p>
            <div class="countdown" id="countdown">5</div>
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
        </div>
    </div>

   
    <script>
        // Countdown timer
        let timeLeft = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdownTimer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                window.location.href = 'C:/xampp/htdocs/3P_CHECK_OES/CONTROLLER/LOGIN/3P_LOGOUT_CONTROL.php';
            }
        }, 1000);

        // Backup redirect jika meta refresh gagal
        setTimeout(() => {
            window.location.href = 'C:/xampp/htdocs/3P_CHECK_OES/CONTROLLER/LOGIN/3P_LOGOUT_CONTROL.php';
        }, 5000);
    </script>
</body>
</html>