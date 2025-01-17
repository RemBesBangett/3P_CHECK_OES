function showAuthenticationModal(errorMessage = null) {
    // Pastikan dokumen sudah dimuat sepenuhnya
    if (document.readyState !== 'complete') {
        document.addEventListener('DOMContentLoaded', () => initializeAuthModal(errorMessage));
        return;
    }

    initializeAuthModal(errorMessage);
}

function initializeAuthModal(errorMessage = null) {
    // Inisialisasi modal Bootstrap
    const authModal = new bootstrap.Modal(
        document.getElementById("authenticationModal")
    );

    // Set flag autentikasi diperlukan
    localStorage.setItem("authenticationRequired", "true");

    // Tampilkan modal
    authModal.show();

    // Tampilkan pesan error jika ada
    if (errorMessage) {
        Swal.fire({
            title: "Error!",
            text: errorMessage,
            icon: "error",
            confirmButtonText: "OK",
            showCancelButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
        });
    }

    // Pastikan event listener hanya ditambahkan sekali
    const authenticateButton = document.getElementById("authenticateButton");
    authenticateButton.removeEventListener('click', handleAuthentication);
    authenticateButton.addEventListener('click', handleAuthentication);

    // Prevent modal dari dismiss
    const authenticationModal = document.getElementById("authenticationModal");
    authenticationModal.removeEventListener('hide.bs.modal', preventModalHide);
    authenticationModal.addEventListener('hide.bs.modal', preventModalHide);

    // Prevent page unload
    window.removeEventListener('beforeunload', preventPageUnload);
    window.addEventListener('beforeunload', preventPageUnload);
}

function handleAuthentication() {
    const username = document.getElementById("authUsername").value;
    const password = document.getElementById("authPassword").value;
    const authModal = bootstrap.Modal.getInstance(document.getElementById("authenticationModal"));

    // Send authentication request to server
    fetch("/PACKING%20LINE%20SYSTEM/ACTION/DASHBOARD/Authentication.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            username: username,
            password: password,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                document.getElementById("authUsername").value = "";
                document.getElementById("authPassword").value = "";
                localStorage.removeItem("authenticationRequired");
                authModal.hide();
                Swal.fire({
                    title: "Authentication Successful",
                    text: "You can now proceed with the scan.",
                    icon: "success",
                    timer: 1000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                }).then(() => {
                    // Refresh the page after the success message is shown
                    localStorage.removeItem('allScanData');
                    allScanData = {
                        seqNumber: "",
                        firstScanTimestamp: "",
                        firstScanResult: "",
                        currentPartNumber: "",
                        isFirstScanComplete: false,
                        firstSecondScanResult: "",
                        secondScanResult: "",
                        tableData: [],
                        currentStep: 0,
                        sopImageSrc: "",
                        psaImageSrc: ""
                    };
                    restoreUI();
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: "Authentication Failed",
                    text: "Invalid username or password.",
                    icon: "error",
                    confirmButtonText: "Try Again",
                    showCancelButton: false,
                });
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            Swal.fire({
                title: "Error",
                text: "An error occurred during authentication.",
                icon: "error",
                confirmButtonText: "OK",
            });
        });
}

function preventModalHide(event) {
    if (localStorage.getItem("authenticationRequired") === "true") {
        event.preventDefault();
    }
}

function preventPageUnload(e) {
    if (localStorage.getItem("authenticationRequired") === "true") {
        e.preventDefault();
        e.returnValue = "";
    }
}