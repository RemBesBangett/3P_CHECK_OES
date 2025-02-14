// Fungsi untuk menampilkan modal autentikasi
// Fungsi untuk menampilkan modal autentikasi dengan pesan kustom
function showAuthenticationModal(customMessage = null) {
    // Kirim request untuk mengecek status user sebelum menampilkan modal
    $.ajax({
        type: 'POST',
        url: '/3P_CHECK_OES/CONTROLLER/INTERAKTIF/3P_INTERLOCK_CONTROL.php',
        data: JSON.stringify({
            action: 'preCheck',
            userSession: usernameLogin // Tambahkan user session yang sedang aktif
        }),
        contentType: "application/json",
        success: function (response) {
            console.log("Server Pre-Check Response:", response);

            // Siapkan pesan default atau gunakan pesan kustom
            const defaultMessage = response.message || "Sistem membutuhkan verifikasi ulang.";
            const displayMessage = customMessage || defaultMessage;

            // Jika status membutuhkan autentikasi ulang
            if (response.status === 'requireAuth') {
                // Tampilkan Sweet Alert konfirmasi
                Swal.fire({
                    title: "Verifikasi Diperlukan",
                    text: displayMessage,
                    icon: "warning",
                    confirmButtonText: "OK"
                }).then(() => {
                    const modal = new bootstrap.Modal(document.getElementById('authenticationModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    if (customMessage) {
                        document.getElementById('authModalMessage').textContent = customMessage;
                    }
                    modal.show();
                });
            } else {
                Swal.fire({
                    title: "Cek kembali Username dan Password",
                    text: displayMessage,
                    icon: "error",
                    confirmButtonText: "Coba Lagi"
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Error pre-checking user status:", error);
            Swal.fire({
                title: "Kesalahan Sistem",
                text: customMessage || "Terjadi kesalahan saat memeriksa status pengguna",
                icon: "error",
                confirmButtonText: "Tutup"
            });
        }
    });
}

function interlockArea() {
    const usernamE = document.getElementById('authUsername').value;
    const passworD = document.getElementById('authPassword').value;
    if (!usernamE || !passworD) {
        Swal.fire({
            title: "Kesalahan Input",
            text: "Username dan password harus diisi",
            icon: "warning",
            confirmButtonText: "OK"
        });
        return;
    }
    $.ajax({
        type: 'POST',
        url: '/3P_CHECK_OES/CONTROLLER/INTERAKTIF/3P_INTERLOCK_CONTROL.php',
        data: JSON.stringify({
            action: 'authenticate',
            username: usernamE,
            password: passworD,
            userSession: usernameLogin
        }),
        contentType: "application/json",
        success: function (response) {
            console.log("Server Response:", response);
            if (response.status === 'success') {
                localStorage.setItem('userAccess', response.data.access);
                localStorage.setItem('userName', response.data.nama);
                Swal.fire({
                    title: "Autentikasi Berhasil",
                    text: "Anda dapat melanjutkan proses",
                    icon: "success",
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    closeAllModals();
                    location.reload(true);
                });
            } else {
                Swal.fire({
                    title: "Autentikasi Gagal",
                    text: response.message,
                    icon: "error",
                    confirmButtonText: "Coba Lagi"
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Error autentikasi:", error);
            Swal.fire({
                title: "Kesalahan Sistem",
                text: "Terjadi kesalahan saat autentikasi",
                icon: "error",
                confirmButtonText: "Tutup"
            });
        }
    });
}

// Fungsi untuk menutup semua modal
function closeAllModals() {
    // Dapatkan semua modal yang sedang terbuka
    const openModals = document.querySelectorAll('.modal.show');

    openModals.forEach(modalElement => {
        // Dapatkan instance modal Bootstrap
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        // Jika instance modal ditemukan, tutup modal
        if (modalInstance) {
            modalInstance.hide();
            location.reload();
        }
    });

    // Optional: Tambahkan penanganan modal kustom jika ada
    const customModals = document.querySelectorAll('.custom-modal');
    customModals.forEach(modal => {
        modal.style.display = 'none';
    });
}

// Event listener untuk tombol Authenticate
document.getElementById('authenticateButton').addEventListener('click', function () {
    interlockArea();
});