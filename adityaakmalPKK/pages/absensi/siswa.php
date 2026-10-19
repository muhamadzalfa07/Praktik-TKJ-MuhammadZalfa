<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: ../../auth/login.php"); exit; }

$role    = $_SESSION['role'];
$id_user = $_SESSION['id'];

// Hanya admin & guru
if($role == 'tata_usaha'){ header("Location: ../../dashboard.php"); exit; }

if(isset($_POST['simpan'])){
    $id_siswa = (int)$_POST['siswa'];
    $id_kelas = (int)$_POST['kelas'];
    $tanggal  = date('Y-m-d');
    $status   = $_POST['status'];
    mysqli_query($conn,
        "INSERT INTO absensi_siswa (id_siswa,id_kelas,tanggal,status,created_by)
         VALUES ('$id_siswa','$id_kelas','$tanggal','$status','$id_user')"
    );
    header("Location: siswa.php"); exit;
}

if(isset($_POST['update'])){
    $id       = (int)$_POST['id'];
    $id_siswa = (int)$_POST['siswa'];
    $id_kelas = (int)$_POST['kelas'];
    $status   = $_POST['status'];
    $where    = ($role=='admin') ? "WHERE id='$id'" : "WHERE id='$id' AND created_by='$id_user'";
    mysqli_query($conn, "UPDATE absensi_siswa SET id_siswa='$id_siswa',id_kelas='$id_kelas',status='$status' $where");
    header("Location: siswa.php"); exit;
}

if(isset($_GET['hapus'])){
    $id    = (int)$_GET['hapus'];
    $where = ($role=='admin') ? "WHERE id='$id'" : "WHERE id='$id' AND created_by='$id_user'";
    mysqli_query($conn, "DELETE FROM absensi_siswa $where");
    header("Location: siswa.php"); exit;
}

$filter_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa — AKG System</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo_akg.png">
</head>
<body>
<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h1>Absensi Siswa</h1>
            <p>Catat kehadiran siswa — hari ini: <strong><?= date('d F Y') ?></strong></p>
        </div>
    </div>

    <!-- FORM -->
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-clipboard-user"></i></span> Tambah Absensi Siswa</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas" id="selectKelas" required class="input" onchange="loadSiswa(this.value)">
                            <option value="">-- Pilih Kelas --</option>
                            <?php
                            $qk = mysqli_query($conn,"SELECT * FROM kelas ORDER BY nama_kelas ASC");
                            while($k = mysqli_fetch_array($qk)){
                                echo "<option value='$k[id]'>".htmlspecialchars($k['nama_kelas'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <select name="siswa" id="selectSiswa" required class="input">
                            <option value="">-- Pilih Kelas Dulu --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="text" class="input" value="<?= date('d F Y') ?>" disabled
                               style="background:var(--bg);color:var(--gray);cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required class="input">
                            <option value="">-- Status --</option>
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
            <h2><span class="icon"><i class="fa-solid fa-table-list"></i></span> Rekap Absensi Siswa</h2>
            <form method="GET" style="display:flex;gap:10px;align-items:center;">
                <select name="kelas" class="input" style="width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php
                    $qk2 = mysqli_query($conn,"SELECT * FROM kelas ORDER BY nama_kelas ASC");
                    while($k = mysqli_fetch_array($qk2)){
                        $sel = ($filter_kelas == $k['id']) ? 'selected' : '';
                        echo "<option value='$k[id]' $sel>".htmlspecialchars($k['nama_kelas'])."</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Nama Siswa</th><th>Kelas</th>
                        <th>Tanggal</th><th>Status</th><th>Dicatat Oleh</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no     = 1;
                $wRole  = ($role=='guru') ? "AND abs.created_by='$id_user'" : "";
                $wKelas = $filter_kelas   ? "AND abs.id_kelas='$filter_kelas'" : "";
                $data   = mysqli_query($conn,
                    "SELECT abs.*, s.nama_siswa, k.nama_kelas, u.nama as nama_pencatat
                     FROM absensi_siswa abs
                     JOIN siswa s ON abs.id_siswa = s.id
                     JOIN kelas k ON abs.id_kelas = k.id
                     LEFT JOIN users u ON abs.created_by = u.id
                     WHERE 1=1 $wRole $wKelas
                     ORDER BY abs.id DESC"
                );
                while($d = mysqli_fetch_array($data)):
                    $sc = strtolower($d['status']);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($d['nama_siswa']) ?></div></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($d['nama_kelas']) ?></span></td>
                    <td><?= date('d M Y', strtotime($d['tanggal'])) ?></td>
                    <td><span class="badge badge-<?= $sc ?>"><?= $d['status'] ?></span></td>
                    <td><span style="font-size:12px;color:var(--gray);"><?= htmlspecialchars($d['nama_pencatat'] ?? '-') ?></span></td>
                    <td>
                        <?php if($role=='admin' || $d['created_by']==$id_user): ?>
                        <div class="table-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit('<?= $d['id'] ?>','<?= $d['id_siswa'] ?>','<?= $d['id_kelas'] ?>','<?= $d['status'] ?>')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus data ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                        <?php endif; ?>
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
            <h3><span class="modal-icon"><i class="fa-solid fa-pen"></i></span> Edit Absensi Siswa</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Kelas</label>
                    <select name="kelas" id="edit_kelas" class="input">
                        <?php
                        $qk3 = mysqli_query($conn,"SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while($k = mysqli_fetch_array($qk3)){
                            echo "<option value='$k[id]'>".htmlspecialchars($k['nama_kelas'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Siswa</label>
                    <select name="siswa" id="edit_siswa" class="input">
                        <?php
                        $qs = mysqli_query($conn,"SELECT * FROM siswa ORDER BY nama_siswa ASC");
                        while($s = mysqli_fetch_array($qs)){
                            echo "<option value='$s[id]'>".htmlspecialchars($s['nama_siswa'])."</option>";
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
function loadSiswa(id_kelas){
    var sel = document.getElementById('selectSiswa');
    sel.innerHTML = '<option value="">Loading...</option>';
    if(!id_kelas){ sel.innerHTML = '<option value="">-- Pilih Kelas Dulu --</option>'; return; }
    fetch('../../helpers/get_siswa.php?kelas=' + id_kelas)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">-- Pilih Siswa --</option>';
            data.forEach(s => {
                sel.innerHTML += '<option value="'+s.id+'">'+s.nama_siswa+'</option>';
            });
        });
}
function openEdit(id, id_siswa, id_kelas, status){
    document.getElementById('edit_id').value = id;
    ['edit_kelas','edit_siswa','edit_status'].forEach(function(elId, i){
        var vals = [id_kelas, id_siswa, status];
        var el = document.getElementById(elId);
        for(var j=0;j<el.options.length;j++) el.options[j].selected = (el.options[j].value == vals[i]);
    });
    document.getElementById('modalEdit').classList.add('show');
}
</script>
</body>
</html>
