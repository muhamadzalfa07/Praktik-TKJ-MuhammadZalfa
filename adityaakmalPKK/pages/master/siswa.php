<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: ../../auth/login.php"); exit; }

$role = $_SESSION['role'];
if($role == 'guru'){ header("Location: ../../dashboard.php"); exit; }

if(isset($_POST['simpan'])){
    $nama  = mysqli_real_escape_string($conn,$_POST['nama']);
    $nis   = mysqli_real_escape_string($conn,$_POST['nis']);
    $kelas = (int)$_POST['kelas'];
    mysqli_query($conn,"INSERT INTO siswa VALUES(NULL,'$nama','$nis','$kelas')");
    header("Location: siswa.php"); exit;
}
if(isset($_POST['update'])){
    $id    = (int)$_POST['id'];
    $nama  = mysqli_real_escape_string($conn,$_POST['nama']);
    $nis   = mysqli_real_escape_string($conn,$_POST['nis']);
    $kelas = (int)$_POST['kelas'];
    mysqli_query($conn,"UPDATE siswa SET nama_siswa='$nama',nis='$nis',id_kelas='$kelas' WHERE id='$id'");
    header("Location: siswa.php"); exit;
}
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    mysqli_query($conn,"DELETE FROM siswa WHERE id='$id'");
    header("Location: siswa.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa — AKG System</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo_akg.png">
</head>
<body>
<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="page-title"><h1>Kelola Siswa</h1><p>Kelola data siswa sekolah</p></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-user-plus"></i></span> Tambah Siswa</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama" placeholder="Nama lengkap siswa..." required class="input">
                    </div>
                    <div class="form-group">
                        <label>NIS</label>
                        <input type="text" name="nis" placeholder="Nomor Induk Siswa..." required class="input">
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas" required class="input">
                            <option value="">-- Pilih Kelas --</option>
                            <?php
                            $qk = mysqli_query($conn,"SELECT * FROM kelas ORDER BY nama_kelas ASC");
                            while($k = mysqli_fetch_array($qk)){
                                echo "<option value='$k[id]'>".htmlspecialchars($k['nama_kelas'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" style="justify-content:flex-end;">
                        <button class="btn btn-success" name="simpan"><i class="fa-solid fa-plus"></i> Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-user-graduate"></i></span> Daftar Siswa</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>#</th><th>Nama Siswa</th><th>NIS</th><th>Kelas</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $data = mysqli_query($conn,"SELECT siswa.*,kelas.nama_kelas FROM siswa JOIN kelas ON siswa.id_kelas=kelas.id ORDER BY siswa.nama_siswa ASC");
                while($d = mysqli_fetch_array($data)):
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="td-name"><?= htmlspecialchars($d['nama_siswa']) ?></div></td>
                    <td><span style="font-family:monospace;color:var(--gray);"><?= htmlspecialchars($d['nis']) ?></span></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($d['nama_kelas']) ?></span></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit('<?= $d['id'] ?>','<?= htmlspecialchars($d['nama_siswa'],ENT_QUOTES) ?>','<?= htmlspecialchars($d['nis'],ENT_QUOTES) ?>','<?= $d['id_kelas'] ?>')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus siswa ini?')">
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

<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <h3><span class="modal-icon"><i class="fa-solid fa-pen"></i></span> Edit Siswa</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Nama Siswa</label>
                    <input type="text" name="nama" id="edit_nama" class="input" required>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label>NIS</label>
                    <input type="text" name="nis" id="edit_nis" class="input" required>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas" id="edit_kelas" class="input">
                        <?php
                        $qk2 = mysqli_query($conn,"SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while($k = mysqli_fetch_array($qk2)){
                            echo "<option value='$k[id]'>".htmlspecialchars($k['nama_kelas'])."</option>";
                        }
                        ?>
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
function openEdit(id, nama, nis, id_kelas){
    document.getElementById('edit_id').value   = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_nis').value  = nis;
    var ks = document.getElementById('edit_kelas');
    for(var i=0;i<ks.options.length;i++) ks.options[i].selected = (ks.options[i].value == id_kelas);
    document.getElementById('modalEdit').classList.add('show');
}
</script>
</body>
</html>
