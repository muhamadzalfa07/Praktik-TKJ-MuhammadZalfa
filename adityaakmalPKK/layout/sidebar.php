<?php
// Deteksi path aktif berdasarkan URL

function isActive(string $path): string {
    global $uri, $base;
    return (strpos($uri, $base . $path) !== false) ? 'active' : ''; // strpos untuk mencari posisi untama dari karakter substring, lalu untuk mengambil variabel atau perintah yang lokasinya  di header
}

function isMasterActive(): bool {
    global $uri, $base;
    return strpos($uri, $base . '/pages/master/') !== false; // cek folder apakah bener di pages/master
}

$role = $_SESSION['role'] ?? 'guru';
?>
<!-- CSS -->

<link rel="stylesheet" href="/../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <!-- LOGO -->
    <div class="sidebar-logo">
        <img src="../../assets/img/logo_akg.png" alt="Logo">
        <div class="logo-text">
            <h2>AKG </h2>
            <p>Agenda Kegiatan Guru</p>
        </div>
    </div>

    <!-- NAV -->
    <div class="sidebar-nav">

        <div class="menu-label">Menu Utama</div>

        <!-- DASHBOARD -->
        <a href="/dashboard.php"
           class="nav-item <?= isActive('/dashboard.php') ?>">
            <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>

        <?php if($role == 'admin' || $role == 'guru'): ?>
        <!-- AGENDA -->
        <a href="/pages/agenda/agenda_guru.php"
           class="nav-item <?= isActive('/pages/agenda/') ?>">
            <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
            <span class="nav-text">Agenda Kegiatan</span>
        </a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'tata_usaha'): ?>
        <!-- ABSENSI GURU -->
        <a href="/pages/absensi/guru.php"
           class="nav-item <?= isActive('/pages/absensi/guru.php') ?>">
            <span class="nav-icon"><i class="fa-solid fa-user-check"></i></span>
            <span class="nav-text">Absensi Guru</span>
        </a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'guru'): ?>
        <!-- ABSENSI SISWA -->
        <a href="/pages/absensi/siswa.php"
           class="nav-item <?= isActive('/pages/absensi/siswa.php') ?>">
            <span class="nav-icon"><i class="fa-solid fa-clipboard-user"></i></span>
            <span class="nav-text">Absensi Siswa</span>
        </a>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <!-- LAPORAN -->
        <a href="/pages/absensi/laporan_agenda.php"
           class="nav-item <?= isActive('/pages/absensi/laporan_agenda/') ?>">
            <span class="nav-icon"><i class="fa-solid fa-file-lines"></i></span>
            <span class="nav-text">Laporan Agenda</span>
        </a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'tata_usaha'): ?>
        <div class="menu-label" style="margin-top:10px;">Data Master</div>

        <button class="nav-dropdown-btn" onclick="toggleMasterMenu()">
            <span class="nav-icon"><i class="fa-solid fa-database"></i></span>
            <span class="nav-text">Data Master</span>
            <span class="arrow <?= isMasterActive() ? 'rotate' : '' ?>" id="masterArrow">
                <i class="fa-solid fa-chevron-down"></i>
            </span>
        </button>

        <div class="submenu <?= isMasterActive() ? 'show' : '' ?>" id="masterMenu">
            <a href="/pages/master/guru.php"
               class="nav-item <?= isActive('/pages/master/guru.php') ?>">
                <span class="nav-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
                <span class="nav-text">Kelola Guru</span>
            </a>
            <a href="/pages/master/siswa.php"
               class="nav-item <?= isActive('/pages/master/siswa.php') ?>">
                <span class="nav-icon"><i class="fa-solid fa-user-graduate"></i></span>
                <span class="nav-text">Kelola Siswa</span>
            </a>
            <a href="/pages/master/kelas.php"
               class="nav-item <?= isActive('/pages/master/kelas.php') ?>">
                <span class="nav-icon"><i class="fa-solid fa-school"></i></span>
                <span class="nav-text">Kelola Kelas</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <div class="menu-label" style="margin-top:10px;">Pengaturan</div>
        <a href="/pages/master/akun.php"
           class="nav-item <?= isActive('/pages/master/akun.php') ?>">
            <span class="nav-icon"><i class="fa-solid fa-users-gear"></i></span>
            <span class="nav-text">Kelola Akun</span>
        </a>
        <?php endif; ?>

    </div>

    <!-- LOGOUT -->
    <div class="sidebar-footer">
        <button class="logout-btn" onclick="openLogoutModal()">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span class="logout-text">Logout</span>
        </button>
    </div>

</div>

<!-- MODAL LOGOUT -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box" style="max-width:380px;">
        <div class="modal-logout-body">
            <div class="modal-logout-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h2>Keluar dari Sistem?</h2>
            <p>Sesi Anda akan diakhiri. Pastikan semua pekerjaan sudah tersimpan.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeLogoutModal()">
                <i class="fa-solid fa-xmark"></i> Batal
            </button>
            <a href="/auth/logout.php" class="btn btn-danger">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
</div>

<script src="<?= $assetBase ?>/js/layout.js"></script>
