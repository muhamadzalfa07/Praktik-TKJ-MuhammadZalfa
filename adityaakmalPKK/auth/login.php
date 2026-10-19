<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AKG System</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_akg.png">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="login-page">

    <div class="login-left">
        <div class="brand">
            <img src="../assets/img/logo_akg.png" alt="Logo">
            <div class="brand-text">
                <h1>AKG </h1>
                <p>Agenda Kegiatan Guru</p>
            </div>
        </div>
        <div class="hero-text">
            <h2>Kelola Agenda<br>Sekolah dengan <span>Mudah</span></h2>
            <p>Platform manajemen agenda, absensi, dan data guru yang terintegrasi untuk kemudahan administrasi sekolah.</p>
        </div>
    </div>

    <div class="login-right">
        <div class="login-card">
            <div class="lc-header">
                <h2>Selamat Datang 👋</h2>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <form action="proses_login.php" method="POST">
                <div class="input-group">
                    <i class="fa-solid fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="Alamat Email" class="input" required autocomplete="email">
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" name="password" placeholder="Password" class="input" required autocomplete="current-password">
                </div>
                <button type="submit" name="login" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem
                </button>
            </form>
        </div>
    </div>

</div>
</body>
</html>
