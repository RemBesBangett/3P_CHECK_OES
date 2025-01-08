<?php
// Start the session if it hasn't been started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to securely clear session data
function secureSessionClear() { 
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 3600,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    session_write_close();
}

// Clear any output that might have been sent
ob_clean();

// Clear the session
secureSessionClear();

// Unset all cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time() - 3600, '/');
    }
}

// Clear any stored data in PHP output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Prevent caching and browser history
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Prevent page from being cached or stored in browser history
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-cache, no-store, must-revalidate, private');

// Define base URL and login path
$baseURL = '/3P%20CHECK%20OES'; 
$loginPath = '/VIEW/LOGIN/3P_LOGIN_VIEW.php'; 

// Redirect to login page with random parameter
$random = bin2hex(random_bytes(8));
$redirectUrl = $baseURL . "/login" . "?logout=" . $random;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out</title>
    
    <!-- Prevent caching and browser history -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Prevent browser from storing page in history -->
    <script>
        // Disable browser back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };

        // Prevent page from being cached
        window.onload = function() {
            if (window.history.forward(1) != null) {
                window.history.forward(1);
            }
        }

        // Redirect if user tries to go back
        window.addEventListener('popstate', function(event) {
            window.location.href = '<?php echo $redirectUrl; ?>';
        });

        // Redirect immediately
        window.location.replace('<?php echo $redirectUrl; ?>');
    </script>
</head>
<body>
    <noscript>
        <meta http-equiv="refresh" content="0;url=<?php echo $redirectUrl; ?>">
        <p>Redirecting to login page. If not redirected, <a href="<?php echo $redirectUrl; ?>">click here</a>.</p>
    </noscript>
</body>
</html>
<?php
exit();
?>