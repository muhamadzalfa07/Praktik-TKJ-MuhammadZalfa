<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }//cek user udah login apa blum

$id_user = $_SESSION['id']; //ngambil data dari user
$role    = $_SESSION['role'];

$dari   = $_GET['dari']   ?? '';
$sampai = $_GET['sampai'] ?? '';

$where = []; //buat array kosong
if($role != 'admin') $where[] = "agenda.created_by='$id_user'"; // kalo admin kondisi ini ga kepake soalnya admin jadi bisa liat semuanya
if($dari != '' && $sampai != '') $where[] = "agenda.tanggal BETWEEN '$dari' AND '$sampai'"; // pakai beetwen biar rentan tanggaknya bisa di tentukan sesuai tanggal agenda yang di tambahkan

$whereStr = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : ""; // buat menghitung isi array


//ngambil data dari tabel agenda kegiatan guru dan kolom dari tabel guru
$data = mysqli_query($conn,"SELECT agenda.*,guru.nama_guru FROM agenda JOIN guru ON agenda.id_guru=guru.id $whereStr ORDER BY agenda.tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Agenda — AKG System</title>
    <link rel="icon" type="image/png" href="assets/img/logo_akg.png">
</head>
<body>

<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">

    <div class="page-header">
        <div class="page-title">
            <h1>Laporan Agenda</h1>
            <p>Filter dan cetak laporan agenda kegiatan guru</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </button>
    </div>

    <!-- FILTER CARD -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-filter"></i></span> Filter Laporan</h2>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="form-row" style="max-width:600px;">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="input">
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="input">
                    </div>
                    <div class="form-group" style="justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-file-lines"></i></span> Hasil Laporan</h2>
            <?php if($dari && $sampai): ?>
            <span class="badge badge-admin">
                <?= date('d M Y', strtotime($dari)) ?> — <?= date('d M Y', strtotime($sampai)) ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Guru</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $count = 0;
                while($d = mysqli_fetch_array($data)):
                    $count++;
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($d['kegiatan']) ?></div></td>
                    <td><?= date('d M Y', strtotime($d['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($d['nama_guru']) ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if($count == 0): ?>
                <tr>
                    <td colspan="4" class="no-data">
                        <i class="fa-regular fa-folder-open"></i>
                        <p>Tidak ada data agenda ditemukan</p>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../../layout/footer.php'; ?>
<script src="../../assets/js/layout.js"></script>
</body>
</html>
