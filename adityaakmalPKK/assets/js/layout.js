// ======================================================
// SIDEBAR TOGGLE
// ======================================================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
    } else {
        sidebar.classList.toggle('collapsed');
    }
}

// ======================================================
// DROPDOWN DATA MASTER
// ======================================================
function toggleMasterMenu() {
    const menu  = document.getElementById('masterMenu');
    const arrow = document.getElementById('masterArrow');
    menu.classList.toggle('show');
    arrow.classList.toggle('rotate');
}

// ======================================================
// MODAL LOGOUT
// ======================================================
function openLogoutModal() {
    document.getElementById('logoutModal').classList.add('show');
}

function closeLogoutModal() {
    document.getElementById('logoutModal').classList.remove('show');
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('show');
            }
        });
    });
});

// ======================================================
// GENERIC MODAL HELPERS
// ======================================================
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    if (id) {
        document.getElementById(id).classList.remove('show');
    } else {
        document.querySelectorAll('.modal-overlay.show').forEach(function (m) {
            m.classList.remove('show');
        });
    }
}
