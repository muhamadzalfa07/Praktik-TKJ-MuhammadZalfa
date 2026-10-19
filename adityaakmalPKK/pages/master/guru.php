<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: ../../auth/login.php"); exit; }

$role = $_SESSION['role']; // ngambil role
if($role == 'guru'){ header("Location: ../../dashboard.php"); exit; } // membatasi akses guru

if(isset($_POST['simpan'])){
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $nip  = mysqli_real_escape_string($conn,$_POST['nip']); // ngambil input data
    mysqli_query($conn,"INSERT INTO guru VALUES(NULL,'$nama','$nip')"); //  untuk nyimpen data 
    header("Location: guru.php"); exit;
}
if(isset($_POST['update'])){
    $id   = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $nip  = mysqli_real_escape_string($conn,$_POST['nip']);
    mysqli_query($conn,"UPDATE guru SET nama_guru='$nama',nip='$nip' WHERE id='$id'");
    header("Location: guru.php"); exit;
}
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    mysqli_query($conn,"DELETE FROM guru WHERE id='$id'");
    header("Location: guru.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru — AKG System</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo_akg.png">
</head>
<body>
<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="page-title"><h1>Kelola Guru</h1><p>Kelola data guru sekolah</p></div>
    </div>

    <?php if($role=='admin' || $role=='tata_usaha'): ?>
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-user-plus"></i></span> Tambah Guru</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Guru</label>
                        <input type="text" name="nama" placeholder="Nama lengkap guru..." required class="input">
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" name="nip" placeholder="Nomor Induk Pegawai..." required class="input">
                    </div>
                    <div class="form-group" style="justify-content:flex-end;">
                        <button class="btn btn-success" name="simpan"><i class="fa-solid fa-plus"></i> Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-chalkboard-user"></i></span> Daftar Guru</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Nama Guru</th><th>NIP</th>
                        <?php if($role=='admin' || $role=='tata_usaha'): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $data = mysqli_query($conn,"SELECT * FROM guru ORDER BY nama_guru ASC");
                while($d = mysqli_fetch_array($data)):
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($d['nama_guru']) ?></div></td>
                    <td><span style="font-family:monospace;color:var(--gray);"><?= htmlspecialchars($d['nip']) ?></span></td>
                    <?php if($role=='admin' || $role=='tata_usaha'): ?>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit('<?= $d['id'] ?>','<?= htmlspecialchars($d['nama_guru'],ENT_QUOTES) ?>','<?= htmlspecialchars($d['nip'],ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus guru ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <h3><span class="modal-icon"><i class="fa-solid fa-pen"></i></span> Edit Guru</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Nama Guru</label>
                    <input type="text" name="nama" id="edit_nama" class="input" required>
                </div>
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" id="edit_nip" class="input" required>
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
function openEdit(id, nama, nip){
    document.getElementById('edit_id').value   = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_nip').value  = nip;
    document.getElementById('modalEdit').classList.add('show');
}
</script>
</body>
</html>
