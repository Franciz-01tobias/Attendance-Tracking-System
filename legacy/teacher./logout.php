<?php
session_start();
$_SESSION['user']=' ';
$_SESSION['attendance']='';

if($_SESSION['user']==' '){
    header("location:login.php");
}

?>