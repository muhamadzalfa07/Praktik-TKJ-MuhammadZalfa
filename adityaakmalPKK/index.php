<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location:auth/login.php");
} else {
    header("Location:dashboard.php");
}
exit;
?>
