<?php
$uri  = $_SERVER['REQUEST_URI']; // buat ngambil url yang di buka sama user
// lokasi halaman dan nama halaman
$pageNames = [
    //kalo url ngarahi ke url dashboard.php maka nama halaman jadi Dashbard
    '/dashboard.php'            => 'Dashboard',
    '/pages/agenda/index.php'   => 'Agenda Kegiatan',
    '/pages/absensi/guru.php'   => 'Absensi Guru',
    '/pages/absensi/siswa.php'  => 'Absensi Siswa',
    '/pages/master/guru.php'    => 'Kelola Guru',
    '/pages/master/siswa.php'   => 'Kelola Siswa',
    '/pages/master/kelas.php'   => 'Kelola Kelas',
    '/pages/laporan/index.php'  => 'Laporan Agenda',
    '/pages/master/akun.php'    => 'Kelola Akun',
];

$currentPage = 'Agenda Kegiatan Guru'; // untuk memberi nilai awal kalo gagal bakal tetep di halaman agenda kegiatan guru
foreach($pageNames as $path => $label){ // buat ngulang semua array atau data yang tersimpan di semua variabel
    if(strpos($uri, $base . $path) !== false){ // cek apakah lokasi uda ada file 
        $currentPage = $label;
        break;
    }
}

$role = $_SESSION['role'] ?? 'guru';
$nama = $_SESSION['nama'] ?? 'User';
?>
<div class="header">
    <div class="header-left">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="breadcrumb">
            <span class="sep"><i class="fa-solid fa-chevron-right" style="font-size:9px;"></i></span>
            <span class="current"><?= $currentPage ?></span>
        </div>
    </div>

        </button>
        <div class="header-divider"></div>
        <div class="header-profile">
            <img src="../../assets/img/profile.jpg" alt="Profile">
            <div class="profile-info">
                <h4><?= htmlspecialchars($nama) ?></h4>
                <small><span class="badge badge-<?= $role ?>"><?= ucfirst(str_replace('_',' ',$role)) ?></span></small>
            </div>
        </div>
    </div>
</div>
