<?php
include '../config/koneksi.php';
$id_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$result = [];
if($id_kelas){
    $q = mysqli_query($conn,
        "SELECT id, nama_siswa FROM siswa WHERE id_kelas='$id_kelas' ORDER BY nama_siswa ASC"
    );
    while($r = mysqli_fetch_assoc($q)){
        $result[] = $r;
    }
}
header('Content-Type: application/json');
echo json_encode($result);
?>
