<?php
session_start();
include '../../config/koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: ../../auth/login.php"); exit; }

// Hanya admin
if($_SESSION['role'] != 'admin'){ header("Location: ../../dashboard.php"); exit; }

$msg = '';
$err = '';

/* =====================
   TAMBAH AKUN
===================== */
if(isset($_POST['simpan'])){
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $id_guru  = ($role == 'guru' && !empty($_POST['id_guru'])) ? (int)$_POST['id_guru'] : 'NULL';

    // Cek email sudah ada
    $cek = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT id FROM users WHERE email='$email'"
    ));
    if($cek){
        $err = "Email <strong>$email</strong> sudah terdaftar.";
    } else {
        $hash = md5($password);
        mysqli_query($conn,
            "INSERT INTO users (nama,email,password,role,id_guru)
             VALUES ('$nama','$email','$hash','$role',$id_guru)"
        );
        $msg = "Akun <strong>$nama</strong> berhasil ditambahkan.";
    }
}

/* =====================
   UPDATE AKUN
===================== */
if(isset($_POST['update'])){
    $id      = (int)$_POST['id'];
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $role    = $_POST['role'];
    $id_guru = ($role == 'guru' && !empty($_POST['id_guru'])) ? (int)$_POST['id_guru'] : 'NULL';

    // Update password hanya jika diisi
    if(!empty($_POST['password'])){
        $hash = md5($_POST['password']);
        mysqli_query($conn,
            "UPDATE users SET nama='$nama',email='$email',password='$hash',role='$role',id_guru=$id_guru
             WHERE id='$id'"
        );
    } else {
        mysqli_query($conn,
            "UPDATE users SET nama='$nama',email='$email',role='$role',id_guru=$id_guru
             WHERE id='$id'"
        );
    }
    $msg = "Akun berhasil diperbarui.";
}

/* =====================
   HAPUS AKUN
===================== */
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    // Jangan hapus akun sendiri
    if($id == $_SESSION['id']){
        $err = "Tidak bisa menghapus akun yang sedang aktif.";
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
        $msg = "Akun berhasil dihapus.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun — AKG System</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo_akg.png">
</head>
<body>
<?php include '../../layout/sidebar.php'; ?>
<?php include '../../layout/header.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h1>Kelola Akun</h1>
            <p>Tambah dan atur akun pengguna sistem</p>
        </div>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $msg ?></div>
    <?php endif; ?>
    <?php if($err): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $err ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-user-plus"></i></span> Tambah Akun Baru</h2>
        </div>
        <div class="card-body">
            <form method="POST" id="formTambah">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Nama lengkap..." required class="input">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@contoh.com" required class="input">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="pwdTambah" placeholder="Min. 6 karakter" required class="input" style="padding-right:42px;">
                            <button type="button" onclick="togglePwd('pwdTambah',this)"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray);font-size:14px;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required class="input" onchange="toggleGuruSelect(this,'guruSelectTambah')">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">🛡️ Admin</option>
                            <option value="tata_usaha">📋 Staf Tata Usaha</option>
                            <option value="guru">👨‍🏫 Guru</option>
                        </select>
                    </div>
                    <div class="form-group" id="guruSelectTambah" style="display:none;">
                        <label>Link ke Data Guru</label>
                        <select name="id_guru" class="input">
                            <option value="">-- Pilih Guru --</option>
                            <?php
                            $qg = mysqli_query($conn,"SELECT * FROM guru ORDER BY nama_guru ASC");
                            while($g = mysqli_fetch_array($qg)){
                                echo "<option value='$g[id]'>".htmlspecialchars($g['nama_guru'])."</option>";
                            }
                            ?>
                        </select>
                        <small style="color:var(--gray);font-size:11px;margin-top:4px;">
                            Hubungkan akun ini ke data guru agar agenda otomatis terisi
                        </small>
                    </div>
                </div>
                <button class="btn btn-success" name="simpan">
                    <i class="fa-solid fa-plus"></i> Tambah Akun
                </button>
            </form>
        </div>
    </div>

    <!-- TABEL AKUN -->
    <div class="card">
        <div class="card-header">
            <h2><span class="icon"><i class="fa-solid fa-users"></i></span> Daftar Akun</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Link Guru</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no   = 1;
                $data = mysqli_query($conn,
                    "SELECT u.*, g.nama_guru
                     FROM users u
                     LEFT JOIN guru g ON u.id_guru = g.id
                     ORDER BY u.id ASC"
                );
                $roleLabel = [
                    'admin'      => '<span class="badge badge-admin">Admin</span>',
                    'tata_usaha' => '<span class="badge badge-tata_usaha">Tata Usaha</span>',
                    'guru'       => '<span class="badge badge-guru">Guru</span>',
                ];
                while($d = mysqli_fetch_array($data)):
                    $isSelf = ($d['id'] == $_SESSION['id']);
                ?>
                <tr <?= $isSelf ? 'style="background:#f5f3ff;"' : '' ?>>
                    <td><?= $no++ ?></td>
                    <td>
                        <div class="td-name"><?= htmlspecialchars($d['nama']) ?></div>
                        <?php if($isSelf): ?>
                        <div class="td-sub" style="color:var(--primary);">
                            <i class="fa-solid fa-circle-dot" style="font-size:9px;"></i> Akun Anda
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--gray);font-size:13px;"><?= htmlspecialchars($d['email']) ?></td>
                    <td><?= $roleLabel[$d['role']] ?? $d['role'] ?></td>
                    <td>
                        <?php if($d['nama_guru']): ?>
                        <span style="font-size:12px;color:var(--dark3);">
                            <i class="fa-solid fa-link" style="color:var(--primary);margin-right:4px;"></i>
                            <?= htmlspecialchars($d['nama_guru']) ?>
                        </span>
                        <?php else: ?>
                        <span style="font-size:12px;color:var(--gray-light);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit(
                                    '<?= $d['id'] ?>',
                                    '<?= htmlspecialchars($d['nama'],ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($d['email'],ENT_QUOTES) ?>',
                                    '<?= $d['role'] ?>',
                                    '<?= $d['id_guru'] ?>'
                                )">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <?php if(!$isSelf): ?>
                            <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus akun <?= htmlspecialchars($d['nama'],ENT_QUOTES) ?>?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <h3><span class="modal-icon"><i class="fa-solid fa-pen"></i></span> Edit Akun</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" class="input" required>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="input" required>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Password Baru <small style="color:var(--gray);font-weight:400;">(kosongkan jika tidak diubah)</small></label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="pwdEdit" placeholder="Isi untuk ganti password..." class="input" style="padding-right:42px;">
                        <button type="button" onclick="togglePwd('pwdEdit',this)"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray);font-size:14px;">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="input" onchange="toggleGuruSelect(this,'guruSelectEdit')">
                        <option value="admin">🛡️ Admin</option>
                        <option value="tata_usaha">📋 Staf Tata Usaha</option>
                        <option value="guru">👨‍🏫 Guru</option>
                    </select>
                </div>
                <div class="form-group" id="guruSelectEdit" style="display:none;">
                    <label>Link ke Data Guru</label>
                    <select name="id_guru" id="edit_id_guru" class="input">
                        <option value="">-- Pilih Guru --</option>
                        <?php
                        $qg2 = mysqli_query($conn,"SELECT * FROM guru ORDER BY nama_guru ASC");
                        while($g = mysqli_fetch_array($qg2)){
                            echo "<option value='$g[id]'>".htmlspecialchars($g['nama_guru'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Batal</button>
                <button class="btn btn-primary" name="update">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>
<script src="../../assets/js/layout.js"></script>
<script>
function openEdit(id, nama, email, role, id_guru){
    document.getElementById('edit_id').value    = id;
    document.getElementById('edit_nama').value  = nama;
    document.getElementById('edit_email').value = email;

    var rs = document.getElementById('edit_role');
    for(var i=0;i<rs.options.length;i++) rs.options[i].selected = (rs.options[i].value == role);

    toggleGuruSelect(rs, 'guruSelectEdit');

    var gs = document.getElementById('edit_id_guru');
    for(var i=0;i<gs.options.length;i++) gs.options[i].selected = (gs.options[i].value == id_guru);

    document.getElementById('modalEdit').classList.add('show');
}

function toggleGuruSelect(sel, targetId){
    var el = document.getElementById(targetId);
    el.style.display = (sel.value == 'guru') ? 'flex' : 'none';
}

function togglePwd(inputId, btn){
    var inp = document.getElementById(inputId);
    var icon = btn.querySelector('i');
    if(inp.type === 'password'){
        inp.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}
</script>
</body>
</html>
