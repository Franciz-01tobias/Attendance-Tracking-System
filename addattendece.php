<?php
session_start();
if ($_SESSION['user']==" ") {
      header('location:login.php');
    }
    ?>
<!DOCTYPE html>
<html lang="en">

<!-- header -->
<head>

  <title>Student Attendance System</title>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- css files -->
  <link rel="stylesheet" href="css/main_page.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">

  <!-- js files -->
  <script src="js/bootstrap.min.js"></script>

</head>

<?php

require_once('../dbconnection.php');

$d=date('Y-m-d',time());
$user=$_SESSION['user'];
$sql="SELECT Distinct attendance.Stud_id FROM student ,attendance WHERE Attendace_date='$d' 
AND  student.Stud_id= attendance.Stud_id  AND student.teacher_id=$user ";
$query=mysqli_query($conn,$sql);
$row=mysqli_fetch_array($query);
$number=mysqli_num_rows($query);
if ($number>0) {
echo"<h1 class='h1 text-danger'>Already created today attendance<h1>";

?>

<div class="m-2 footer"> 
    <button class="btn p-2 shadow btn-primary">
          <a class="text-decoration-none text-light" href="attendance.php">GO BACK</a>
          </button>
      </div>
        
</body>
</html>

<?php
die();
}
$sql="SELECT * FROM `student` where teacher_id='$user'";
$query=mysqli_query($conn,$sql);
$row=mysqli_fetch_array($query);
$number=mysqli_num_rows($query);

$sql="SELECT * FROM `student` where teacher_id='$user' limit 1";
$query=mysqli_query($conn,$sql);
$roww=mysqli_fetch_array($query);
$n=$roww['Stud_id'];

echo$number."<br>".$n;
for($i=1; $i<=$number; $i++){
    $sql="INSERT INTO `attendance` (`Stud_id`) 
    VALUES ( '$n')";
    $qr=mysqli_query($conn,$sql);
    $n++;
}
if($qr){
    header("location:attendance.php");
    $_SESSION['attendance']="already";
}
else{
    echo$sql;
    echo"Failed to add attendence";
}






?>

