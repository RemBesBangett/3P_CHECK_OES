// Fungsi untuk menampilkan modal autentikasi
function showAuthenticationModal() {
    // Kirim request untuk mengecek status user sebelum menampilkan modal
    $.ajax({
        type: 'POST',
        url: '/3P_CHECK_OES/CONTROLLER/INTERAKTIF/3P_INTERLOCK_CONTROL.php',
        data: JSON.stringify({
            action: 'preCheck',
            userSession: usernameLogin // Tambahkan user session yang sedang aktif
        }),
        contentType: "application/json",
        success: function(response) {
            console.log("Server Pre-Check Response:", response);

            // Jika status membutuhkan autentikasi ulang
            if (response.status === 'requireAuth') {
                // Tampilkan Sweet Alert konfirmasi
                Swal.fire({
                    title: "Verifikasi Diperlukan",
                    text: response.message || "Sistem membutuhkan verifikasi ulang.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Verifikasi",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Inisialisasi modal
                        const modal = new bootstrap.Modal(document.getElementById('authenticationModal'), {
                            backdrop: 'static',
                            keyboard: false
                        });
                        // Tampilkan modal
                        modal.show();
                    }
                });
            } 
            // Jika terjadi kesalahan
            else {
                Swal.fire({
                    title: "Kesalahan Autentikasi",
                    text: response.message || "Gagal memeriksa status pengguna",
                    icon: "error",
                    confirmButtonText: "Coba Lagi"
                });
            }
            const modal = new bootstrap.Modal(document.getElementById('authenticationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            // Tampilkan modal
            modal.show();
        },
        error: function(xhr, status, error) {
            console.error("Error pre-checking user status:", error);
            Swal.fire({
                title: "Kesalahan Sistem",
                text: "Terjadi kesalahan saat memeriksa status pengguna",
                icon: "error",
                confirmButtonText: "Tutup"
            });
        }
    });
}

// Fungsi untuk menangani proses autentikasi
function interlockArea() {
    // Ambil nilai username dan password dari input
    const usernamE = document.getElementById('authUsername').value;
    const passworD = document.getElementById('authPassword').value;

    // Validasi input
    if (!usernamE || !passworD) {
        Swal.fire({
            title: "Kesalahan Input",
            text: "Username dan password harus diisi",
            icon: "warning",
            confirmButtonText: "OK"
        });
        return;
    }

    // Kirim request autentikasi
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

            // Cek apakah autentikasi berhasil
            if (response.status === 'success') {
                // Simpan informasi user di localStorage
                localStorage.setItem('userAccess', response.data.access);
                localStorage.setItem('userName', response.data.nama);

                // Tampilkan pesan sukses
                Swal.fire({
                    title: "Autentikasi Berhasil",
                    text: "Anda dapat melanjutkan proses",
                    icon: "success",
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    // Tutup semua modal yang sedang terbuka
                    closeAllModals();
                });
            } else {
                // Autentikasi gagal
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