<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: ../../auth/login.php"); exit; }

$role    = $_SESSION['role'];
$id_user = $_SESSION['id'];

// Hanya admin & tata_usaha
if($role == 'guru'){ header("Location: ../../dashboard.php"); exit; }

if(isset($_POST['simpan'])){
    $id_guru = (int)$_POST['guru'];
    $tanggal = date('Y-m-d');
    $status  = $_POST['status'];
    mysqli_query($conn,
        "INSERT INTO absensi (id_guru,tanggal,status,created_by)
         VALUES ('$id_guru','$tanggal','$status','$id_user')"
    );
    header("Location: guru.php"); exit;
}

if(isset($_POST['update'])){
    $id      = (int)$_POST['id'];
    $id_guru = (int)$_POST['guru'];
    $status  = $_POST['status'];
    mysqli_query($conn, "UPDATE absensi SET id_guru='$id_guru',status='$status' WHERE id='$id'");
    header("Location: guru.php"); exit;
}

if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM absensi WHERE id='$id'");
    header("Location: guru.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Guru — AKG System</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo_akg.png">
</head>
<body>
<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h1>Absensi Guru</h1>
            <p>Catat kehadiran guru — hari ini: <strong><?= date('d F Y') ?></strong></p>
        </div>
    </div>

    <!-- FORM -->
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-user-check"></i></span> Tambah Absensi Guru</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Guru</label>
                        <select name="guru" required class="input">
                            <option value="">-- Pilih Guru --</option>
                            <?php
                            $qg = mysqli_query($conn,"SELECT * FROM guru ORDER BY nama_guru ASC");
                            while($g = mysqli_fetch_array($qg)){
                                echo "<option value='$g[id]'>".htmlspecialchars($g['nama_guru'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="text" class="input" value="<?= date('d F Y') ?>" disabled
                               style="background:var(--bg);color:var(--gray);cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Status Kehadiran</label>
                        <select name="status" required class="input">
                            <option value="">-- Pilih Status --</option>
                            <option value="Hadir">✅ Hadir</option>
                            <option value="Izin">📋 Izin</option>
                            <option value="Sakit">🤒 Sakit</option>
                            <option value="Alpa">❌ Alpa</option>
                        </select>
                    </div>
                    <div class="form-group" style="justify-content:flex-end;">
                        <button class="btn btn-success" name="simpan">
                            <i class="fa-solid fa-plus"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-table-list"></i></span> Rekap Absensi Guru</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Nama Guru</th><th>Tanggal</th>
                        <th>Status</th><th>Dicatat Oleh</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $data = mysqli_query($conn,
                    "SELECT absensi.*, guru.nama_guru, u.nama as nama_pencatat
                     FROM absensi
                     JOIN guru ON absensi.id_guru = guru.id
                     LEFT JOIN users u ON absensi.created_by = u.id
                     ORDER BY absensi.id DESC"
                );
                while($d = mysqli_fetch_array($data)):
                    $sc = strtolower($d['status']);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($d['nama_guru']) ?></div></td>
                    <td><?= date('d M Y', strtotime($d['tanggal'])) ?></td>
                    <td><span class="badge badge-<?= $sc ?>"><?= $d['status'] ?></span></td>
                    <td><span style="font-size:12px;color:var(--gray);"><?= htmlspecialchars($d['nama_pencatat'] ?? '-') ?></span></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit('<?= $d['id'] ?>','<?= $d['id_guru'] ?>','<?= $d['status'] ?>')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus data ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <h3><span class="modal-icon"><i class="fa-solid fa-pen"></i></span> Edit Absensi Guru</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Nama Guru</label>
                    <select name="guru" id="edit_guru" class="input">
                        <?php
                        $qg2 = mysqli_query($conn,"SELECT * FROM guru ORDER BY nama_guru ASC");
                        while($g = mysqli_fetch_array($qg2)){
                            echo "<option value='$g[id]'>".htmlspecialchars($g['nama_guru'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" class="input">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Batal</button>
                <button class="btn btn-primary" name="update"><i class="fa-solid fa-floppy-disk"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>
<script src="../../assets/js/layout.js"></script>
<script>
function openEdit(id, id_guru, status){
    document.getElementById('edit_id').value = id;
    var gs = document.getElementById('edit_guru');
    for(var i=0;i<gs.options.length;i++) gs.options[i].selected = (gs.options[i].value == id_guru);
    var ss = document.getElementById('edit_status');
    for(var i=0;i<ss.options.length;i++) ss.options[i].selected = (ss.options[i].value == status);
    document.getElementById('modalEdit').classList.add('show');
}
</script>
</body>
</html>
