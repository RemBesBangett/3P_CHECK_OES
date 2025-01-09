<?php
$baseUrl = '/3P_CHECK_OES/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENSO Login</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $baseUrl; ?>ASSET/Image/DENSO.png">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <style>
        #comValue {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .admin-settings-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .admin-settings-btn:hover {
            background-color: darken(var(--secondary-color), 10%);
            transform: scale(1.1);
        }

        /* CONFIGURABLE: You can change these variables to customize the look */
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --background-color: #f8f9fa;
            --text-color: #333333;
            --card-background: #ffffff;
            --input-background: #e9ecef;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: url("<?php echo $baseUrl; ?>ASSET/Image/BG\ QR.jpeg");
            color: var(--text-color);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background-color: var(--card-background);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* CONFIGURABLE: You can change the time box style here */
        .time-box {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 30px;
            text-align: center;
        }

        h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid var(--input-background);
            border-radius: 50px;
            padding: 12px 20px;
            background-color: var(--input-background);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            background-color: var(--card-background);
            border-color: var(--primary-color);
        }

        /* CONFIGURABLE: You can change the button style here */
        .btn-login {
            background-color: var(--primary-color);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: darken(var(--primary-color), 10%);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-card">
            <!-- CONFIGURABLE: You can change the content of the time box here -->
            <div class="time-box" id="time-box">TIME</div>
            <!-- CONFIGURABLE: You can change the title here -->
            <h2>PRE - DELIVERY CHECK SYSTEM</h2>
            <form id="login-form">
                <div class="form-group">
                    <label for="nama">NAME</label>
                    <input type="text" class="form-control" id="nama" name="nama" required autocomplete="off" placeholder="Enter your Name">
                </div>
                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="off" placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-login">LOGIN</button>
            </form>
        </div>
      
      
      
      
      
        <div id="comValue"></div>
        <button class="admin-settings-btn" data-bs-toggle="modal" data-bs-target="#adminLoginModal">⚙️</button>
        
        
        
        
        
        <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminLoginModalLabel">Admin Login</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="admin-login-form">
                            <div class="mb-3">
                                <label for="adminUsername" class="form-label">Username</label>
                                <input type="text" class="form-control" id="adminUsername" required>
                            </div>
                            <div class="mb-3">
                                <label for="adminPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="adminPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- COM Port Selection Modal -->
        <div class="modal fade" id="comPortModal" tabindex="-1" aria-labelledby="comPortModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="comPortModalLabel">Select COM Port</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <select id="comPortSelect" class="form-select">
                            <option value="COM1">COM 1</option>
                            <option value="COM2">COM 2</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveComPort">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CONFIGURABLE: You can modify the time format here
        function updateTime() {
            const timeBox = document.getElementById('time-box');
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            timeBox.textContent = now.toLocaleDateString('en-US', options);
        }

        setInterval(updateTime, 1000);
        updateTime();
        $(document).ready(function() {
            // Regular user login form submission
            $('#login-form').on('submit', function(event) {
                event.preventDefault();
                let nama = $('#nama').val().toUpperCase();
                let password = $('#password').val();

                performLogin(nama, password, false);
            });

            // Admin login form submission
            $('#admin-login-form').on('submit', function(event) {
                event.preventDefault();
                let username = $('#adminUsername').val();
                let password = $('#adminPassword').val();
 
                performLogin(username, password, true);
            });

            function performLogin(username, password, isAdmin) {
                username = username.toUpperCase();
                $.ajax({
                    url: '<?php echo $baseUrl; ?>CONTROLLER/LOGIN/3P_LOGIN_CONTROL.php',
                    method: 'POST',
                    data: {
                        nama: username,
                        password: password,
                        admin_auth: isAdmin
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            if (isAdmin) {
                                $('#adminLoginModal').modal('hide');
                                $('#comPortModal').modal('show');
                            } else {
                                Swal.fire({
                                    title: 'Login Successful!',
                                    text: 'Welcome, ' + response.nama + '!',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    // Ganti URL di sini
                                    window.location.href = '<?php echo $baseUrl; ?>DASHBOARD'; // Menggunakan URL bersih
                                });
                            }
                        } else if (response.status === 'fail' && response.message === 'Account expired') {
                            Swal.fire({
                                title: 'Account Expired',
                                text: 'Please contact support.',
                                icon: 'warning'
                            });
                        } else {
                            Swal.fire({
                                title: 'Login Failed',
                                text: 'Please check your credentials.',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error:', textStatus, errorThrown);
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred during the login process. Please try again.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>
</body>

</html>