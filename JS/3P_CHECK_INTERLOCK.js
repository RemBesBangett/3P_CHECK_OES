$(document).ready(function() {
   
    $.ajax({
        type: 'GET',
        url: '/3P_CHECK_OES/CONTROLLER/INTERAKTIF/3P_CHECKER.php',
        data: {
            userLogin: user
        },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'error') {
                // Jika status pengguna adalah 'OPEN', tidak perlu autentikasi
                if (response[0].STATUS_USER === 'OPEN') {
                    console.log('NIHAO');
                } else {
                    // Tampilkan modal autentikasi jika status bukan 'OPEN'
                    showAuthenticationModal();
                }
            } else {
                console.log('Error:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
});