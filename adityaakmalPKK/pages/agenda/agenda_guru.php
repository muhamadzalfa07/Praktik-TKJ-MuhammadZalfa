<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: ../../auth/login.php"); exit; }

$role    = $_SESSION['role'];
$id_user = $_SESSION['id'];

// Hanya admin & guru
if($role == 'tata_usaha'){ header("Location: ../../dashboard.php"); exit; }

// Ambil id_guru dari session (untuk role guru)
// Jika guru belum di-link ke data guru, coba cari berdasarkan nama
$id_guru_login = null;
$nama_guru_login = null;
// code buat login dan pemelihan guru untuk link
if($role == 'guru'){ //untuk cek guru atos login atanapi belum
    if(!empty($_SESSION['id_guru'])){ // cek id guru 
        $id_guru_login = (int)$_SESSION['id_guru'];
        $rg = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT nama_guru FROM guru WHERE id='$id_guru_login'" // ngambil nama guru ti db
        ));
        $nama_guru_login = $rg['nama_guru'] ?? $_SESSION['nama']; // simpan bila bener
    } else {
        // kalau eror lari ke sini lalu cari guru berdasarkan nama yang sama dengan nama user
        $nama_esc = mysqli_real_escape_string($conn, $_SESSION['nama']);
        $rg = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id, nama_guru FROM guru WHERE nama_guru LIKE '%$nama_esc%' LIMIT 1"
        ));
        if($rg){
            $id_guru_login   = $rg['id'];
            $nama_guru_login = $rg['nama_guru'];
        } else {
            $nama_guru_login = $_SESSION['nama'];
        }
    }
}

//simpen
if(isset($_POST['simpan'])){
    $kegiatan = mysqli_real_escape_string($conn, $_POST['kegiatan']);
    $tanggal  = date('Y-m-d');

    // Guru: pakai id_guru dari session. Admin: dari form
    $id_guru = ($role == 'guru') ? $id_guru_login : (int)$_POST['guru'];

    if(!$id_guru){
        $err_simpan = "Akun Anda belum terhubung ke data guru. Hubungi admin.";
    } else {
        mysqli_query($conn,
            "INSERT INTO agenda (kegiatan,tanggal,id_guru,created_by)
             VALUES ('$kegiatan','$tanggal','$id_guru','$id_user')"
        );
        header("Location: agenda_guru.php"); exit;
    }
}

//update
if(isset($_POST['update'])){
    $id       = (int)$_POST['id'];
    $kegiatan = mysqli_real_escape_string($conn, $_POST['kegiatan']);
    $id_guru  = ($role == 'guru') ? $id_guru_login : (int)$_POST['guru'];
    $where    = ($role=='admin') ? "WHERE id='$id'" : "WHERE id='$id' AND created_by='$id_user'";
    mysqli_query($conn, "UPDATE agenda SET kegiatan='$kegiatan',id_guru='$id_guru' $where");
        header("Location: agenda_guru.php"); exit;
}

//hapus
if(isset($_GET['hapus'])){
    $id    = (int)$_GET['hapus'];
    $where = ($role=='admin') ? "WHERE id='$id'" : "WHERE id='$id' AND created_by='$id_user'";
    mysqli_query($conn, "DELETE FROM agenda $where");
        header("Location: agenda_guru.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Kegiatan — AKG System</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo_akg.png">
    <link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body>
<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="sage-title">
            <h1>Agenda Kegiatan</h1>
            <p>Hari ini: <strong><?= date('d F Y') ?></strong></p>
        </div>
    </div>

    <?php if(isset($err_simpan)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> <?= $err_simpan ?>
    </div>
    <?php endif; ?>


//tambah
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-calendar-plus"></i></span> Tambah Agenda</h2>

            <?php if($role == 'guru'): ?>
            <!-- Info guru yang sedang login -->
            <div style="display:flex;align-items:center;gap:8px;background:#f5f3ff;padding:7px 14px;border-radius:8px;border:1px solid #ede9fe;">
                <i class="fa-solid fa-chalkboard-user" style="color:var(--primary);"></i>
                <span style="font-size:13px;font-weight:600;color:var(--dark);">
                    <?= htmlspecialchars($nama_guru_login) ?>
                </span>
                <span class="badge badge-guru" style="margin-left:4px;">Guru</span>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group" style="<?= $role=='guru' ? 'grid-column:span 2;' : '' ?>">
                        <label>Nama Kegiatan</label>
                        <input type="text" name="kegiatan"
                               placeholder="Masukkan nama kegiatan..."
                               required class="input">
                    </div>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="text" class="input" value="<?= date('d F Y') ?>" disabled
                               style="background:var(--bg);color:var(--gray);cursor:not-allowed;">
                    </div>

                    <?php if($role == 'admin'): ?>
                    <!-- Admin: bisa pilih guru -->
                    <div class="form-group">
                        <label>Guru Penanggung Jawab</label>
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
                    <?php endif; ?>
                    <!-- Guru: id_guru dikirim hidden, tidak perlu pilih -->

                </div>
                <button class="btn btn-success" name="simpan">
                    <i class="fa-solid fa-plus"></i> Simpan Agenda
                </button>
            </form>
        </div>
    </div>

//tabel
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-calendar-days"></i></span> Daftar Agenda</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Guru</th>
                        <th>Dicatat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no   = 1;
                $w    = ($role=='guru') ? "WHERE agenda.created_by='$id_user'" : "";
                $data = mysqli_query($conn,
                    "SELECT agenda.*, guru.nama_guru, u.nama as nama_pencatat
                     FROM agenda
                     JOIN guru ON agenda.id_guru = guru.id
                     LEFT JOIN users u ON agenda.created_by = u.id
                     $w ORDER BY agenda.tanggal DESC, agenda.id DESC"
                );
                $count = 0;
                while($d = mysqli_fetch_array($data)):
                    $count++;
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($d['kegiatan']) ?></div></td>
                    <td><?= date('d M Y', strtotime($d['tanggal'])) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:14px;">👨‍🏫</span>
                            <?= htmlspecialchars($d['nama_guru']) ?>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:12px;color:var(--gray);">
                            <?= htmlspecialchars($d['nama_pencatat'] ?? '-') ?>
                        </span>
                    </td>
                    <td>
                        <?php if($role=='admin' || $d['created_by']==$id_user): ?>
                        <div class="table-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit('<?= $d['id'] ?>','<?= htmlspecialchars($d['kegiatan'],ENT_QUOTES) ?>','<?= $d['id_guru'] ?>')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus agenda ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($count == 0): ?>
                <tr>
                    <td colspan="6">
                        <div class="no-data">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <p>Belum ada agenda yang ditambahkan</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

//modal Edit
<div class="modal-overlay" id="modalAgenda">
    <div class="modal-box">
        <div class="modal-header">
            <h3><span class="modal-icon"><i class="fa-solid fa-pen"></i></span> Edit Agenda</h3>
            <button class="modal-close" onclick="closeModal('modalAgenda')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group" style="margin-bottom:14px;">
                    <label>Nama Kegiatan</label>
                    <input type="text" name="kegiatan" id="edit_kegiatan" class="input" required>
                </div>

                <?php if($role == 'admin'): ?>
                <div class="form-group">
                    <label>Guru</label>
                    <select name="guru" id="edit_guru" class="input">
                        <?php
                        $qg2 = mysqli_query($conn,"SELECT * FROM guru ORDER BY nama_guru ASC");
                        while($g = mysqli_fetch_array($qg2)){
                            echo "<option value='$g[id]'>".htmlspecialchars($g['nama_guru'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php else: ?>
                <!-- Guru: tampilkan nama saja, tidak bisa diubah -->
                <div class="form-group">
                    <label>Guru</label>
                    <input type="text" class="input" value="<?= htmlspecialchars($nama_guru_login) ?>" disabled
                           style="background:var(--bg);color:var(--gray);cursor:not-allowed;">
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalAgenda')">Batal</button>
                <button class="btn btn-primary" name="update">
                    <i class="fa-solid fa-floppy-disk"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>
<script src="../../assets/js/layout.js"></script>
<script>
function openEdit(id, kegiatan, id_guru){
    document.getElementById('edit_id').value       = id;
    document.getElementById('edit_kegiatan').value = kegiatan;

    // Hanya ada di role admin
    var gs = document.getElementById('edit_guru');
    if(gs){
        for(var i=0;i<gs.options.length;i++){
            gs.options[i].selected = (gs.options[i].value == id_guru);
        }
    }

    document.getElementById('modalAgenda').classList.add('show');
}
</script>
</body>
</html>
