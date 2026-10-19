<?php
session_start();
// Deteksi base URL otomatis
include 'config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location:auth/login.php"); exit; }

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
$id   = $_SESSION['id'];

// Statistik
$guru   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM guru"))['t'];
$kelas  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM kelas"))['t'];
$siswa  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM siswa"))['t'];
$agenda = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM agenda"))['t'];

// Statistik khusus guru — agenda miliknya
$agenda_guru = 0;
$absensi_siswa_guru = 0;
if($role == 'guru'){
    $agenda_guru = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as t FROM agenda WHERE created_by='$id'"
    ))['t'];
    $absensi_siswa_guru = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as t FROM absensi_siswa WHERE created_by='$id'"
    ))['t'];
}

// Riwayat agenda terbaru (5 terakhir)
if($role == 'guru'){
    $riwayat = mysqli_query($conn,
        "SELECT agenda.*, guru.nama_guru
         FROM agenda
         JOIN guru ON agenda.id_guru = guru.id
         WHERE agenda.created_by='$id'
         ORDER BY agenda.id DESC LIMIT 5"
    );
} else {
    $riwayat = mysqli_query($conn,
        "SELECT agenda.*, guru.nama_guru, u.nama as pencatat
         FROM agenda
         JOIN guru ON agenda.id_guru = guru.id
         LEFT JOIN users u ON agenda.created_by = u.id
         ORDER BY agenda.id DESC LIMIT 5"
    );
}

// Label & emoji role
$roleLabel = ['admin'=>'Administrator','tata_usaha'=>'Staf Tata Usaha','guru'=>'Guru'];
$roleEmoji = ['admin'=>'🛡️','tata_usaha'=>'📋','guru'=>'👨‍🏫'];
$label = $roleLabel[$role] ?? ucfirst($role);
$emoji = $roleEmoji[$role] ?? '👤';

// Tanggal
$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tgl   = $hari[date('w')].', '.date('d').' '.$bulan[(int)date('n')].' '.date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — AKG System</title>
<link rel="icon" href="assets/img/logo_akg.png">
<link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="content">

    <!-- ===== WELCOME BANNER ===== -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>Selamat Datang, <?= htmlspecialchars($nama) ?> 👋</h2>
            <p>Anda masuk sebagai <strong style="color:rgba(255,255,255,0.95);"><?= $label ?></strong></p>
            <div class="wdate">
                <i class="fa-regular fa-calendar"></i> <?= $tgl ?>
            </div>
        </div>
        <div class="welcome-emoji"><?= $emoji ?></div>
    </div>

    <!-- ===== STATISTIK ===== -->
    <?php if($role == 'admin' || $role == 'tata_usaha'): ?>
    <div class="stats-grid">
        <div class="stat-card guru">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-value"><?= $guru ?></div>
            <div class="stat-label">Total Guru</div>
        </div>
        <div class="stat-card kelas">
            <div class="stat-icon">🏫</div>
            <div class="stat-value"><?= $kelas ?></div>
            <div class="stat-label">Total Kelas</div>
        </div>
        <div class="stat-card siswa">
            <div class="stat-icon">🎓</div>
            <div class="stat-value"><?= $siswa ?></div>
            <div class="stat-label">Total Siswa</div>
        </div>
        <div class="stat-card agenda">
            <div class="stat-icon">📅</div>
            <div class="stat-value"><?= $agenda ?></div>
            <div class="stat-label">Total Agenda</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($role == 'guru'): ?>
    <div class="stats-grid">
        <div class="stat-card agenda">
            <div class="stat-icon">📅</div>
            <div class="stat-value"><?= $agenda_guru ?></div>
            <div class="stat-label">Agenda Saya</div>
        </div>
        <div class="stat-card siswa">
            <div class="stat-icon">📝</div>
            <div class="stat-value"><?= $absensi_siswa_guru ?></div>
            <div class="stat-label">Absensi Siswa Saya</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== AKSES CEPAT ===== -->
    <div class="role-section-title"><i class="fa-solid fa-bolt"></i> Akses Cepat</div>
    <div class="quick-access-grid">

        <?php if($role == 'admin' || $role == 'guru'): ?>
        <a href="pages/agenda/index.php" class="quick-btn qb-agenda">
            <div class="qb-icon">📅</div>
            Agenda Kegiatan
        </a>
        <a href="pages/absensi/siswa.php" class="quick-btn qb-absensi">
            <div class="qb-icon">📝</div>
            Absensi Siswa
        </a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'tata_usaha'): ?>
        <a href="pages/absensi/guru.php" class="quick-btn qb-absensi">
            <div class="qb-icon">✅</div>
            Absensi Guru
        </a>
        <a href="pages/master/guru.php" class="quick-btn qb-guru">
            <div class="qb-icon">👨‍🏫</div>
            Kelola Guru
        </a>
        <a href="pages/master/siswa.php" class="quick-btn qb-siswa">
            <div class="qb-icon">🎓</div>
            Kelola Siswa
        </a>
        <a href="pages/master/kelas.php" class="quick-btn qb-kelas">
            <div class="qb-icon">🏫</div>
            Kelola Kelas
        </a>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <a href="pages/laporan/index.php" class="quick-btn qb-laporan">
            <div class="qb-icon">📄</div>
            Laporan Agenda
        </a>
        <a href="pages/master/akun.php" class="quick-btn" style="border-color:var(--border);">
            <div class="qb-icon" style="background:#ede9fe;">⚙️</div>
            Kelola Akun
        </a>
        <?php endif; ?>

    </div>

    <!-- ===== RIWAYAT AGENDA TERBARU ===== -->
    <div class="role-section-title"><i class="fa-solid fa-clock-rotate-left"></i>
        <?= $role == 'guru' ? 'Agenda Saya Terbaru' : 'Agenda Terbaru' ?>
    </div>
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Guru</th>
                        <?php if($role != 'guru'): ?>
                        <th>Dicatat Oleh</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no    = 1;
                $count = 0;
                while($r = mysqli_fetch_array($riwayat)):
                    $count++;
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($r['kegiatan']) ?></div></td>
                    <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span>👨‍🏫</span>
                            <?= htmlspecialchars($r['nama_guru']) ?>
                        </div>
                    </td>
                    <?php if($role != 'guru'): ?>
                    <td>
                        <span style="font-size:12px;color:var(--gray);">
                            <?= htmlspecialchars($r['pencatat'] ?? '-') ?>
                        </span>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
                <?php if($count == 0): ?>
                <tr>
                    <td colspan="5">
                        <div class="no-data">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <p>Belum ada agenda tercatat</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($count > 0): ?>
        <div style="padding:12px 20px;border-top:1px solid var(--border);text-align:right;">
            <?php if($role == 'admin' || $role == 'guru'): ?>
            <a href="pages/agenda/index.php"
               style="font-size:13px;color:var(--primary);font-weight:600;text-decoration:none;">
                Lihat semua agenda <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== INFO LOGIN ===== -->
    <div class="role-section-title"><i class="fa-solid fa-circle-user"></i> Sesi Login</div>
    <div class="card">
        <div class="card-body" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                <?= $emoji ?>
            </div>
            <div style="flex:1;min-width:200px;">
                <div style="font-size:16px;font-weight:700;color:var(--dark);margin-bottom:4px;">
                    <?= htmlspecialchars($nama) ?>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span class="badge badge-<?= $role ?>"><?= ucfirst(str_replace('_',' ',$role)) ?></span>
                    <span style="font-size:12px;color:var(--gray);">
                        <i class="fa-regular fa-envelope" style="margin-right:4px;"></i>
                        <?php
                        $udata = mysqli_fetch_assoc(mysqli_query($conn,"SELECT email FROM users WHERE id='$id'"));
                        echo htmlspecialchars($udata['email'] ?? '-');
                        ?>
                    </span>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:12px;color:var(--gray);margin-bottom:6px;">
                    <i class="fa-regular fa-clock" style="margin-right:4px;"></i>
                    Login: <?= date('H:i') ?> WIB
                </div>
                <a href="auth/logout.php"
                   style="font-size:12px;color:var(--danger);font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>

</div>

<?php include 'layout/footer.php'; ?>
<script src="assets/js/layout.js"></script>
</body>
</html>
