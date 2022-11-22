<?php
   require_once('../dbconnection.php'); 
$id=$_GET['id'];
$sql="DELETE FROM `student` WHERE Stud_id='$id'";
$qr=mysqli_query($conn,$sql);
if ($qr) {
    header('location:studentlist.php');
}
else{
    die("fail to delete student info");
}
?>