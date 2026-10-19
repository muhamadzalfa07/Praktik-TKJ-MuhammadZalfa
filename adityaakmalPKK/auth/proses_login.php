<?php
session_start();// mulai agar data login bisa di simpan
include '../config/koneksi.php';

//cek apakah tombol login di teken
if(isset($_POST['login'])){ 
    $email    = mysqli_real_escape_string($conn, $_POST['email']); // ngambil input dari form
    $password = md5($_POST['password']); // ngambil pasword terus di ubah jadi md5 (untuk menyembunyikan di database)

    $query = mysqli_query($conn,
        "SELECT * FROM users WHERE email='$email' AND password='$password'"
    );

    if(mysqli_num_rows($query) > 0){ // cek apakah data login/user ditemukan
        $data = mysqli_fetch_assoc($query); // ngambil data user terus di jadiin array
       // ini datanya 
        $_SESSION['login']  = true;
        $_SESSION['id']     = $data['id'];
        $_SESSION['role']   = $data['role'];
        $_SESSION['nama']   = !empty($data['nama']) ? $data['nama'] : $data['email'];

        if($data['role'] == 'guru' && !empty($data['id_guru'])){   
            $_SESSION['id_guru'] = $data['id_guru'];
        }

        header("Location: ../dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Email atau password salah.";
        header("Location: login.php");
        exit;
    }
}
?>
