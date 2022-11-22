<?php
$n=20;
 function getName($n)
{
    $char="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randStr='';
    for ($i=0;$i <$n ; $i++) { 
        $index=rand(0,strlen($char)-1);
        $randStr.=$char[$index];
    }
    return $randStr;
}


require_once('../dbconnection.php');
session_start();

if(isset($_POST["login"])){
    $em=$_POST["teacher_email"];
    $sql="SELECT * FROM `teacher` where Email='$em'";

$uerror="";
    $query=mysqli_query($conn,$sql);
    $row=mysqli_fetch_array($query);
    $no=mysqli_num_rows($query);
    if($no>0){
      
        $_SESSION['password-change-token']=getName($n);
     $id=base64_encode($row['Email']);
        header("location:email.php?email=$id");
        
    }
    else{
      $uerror="Email not found";
    }
}
$msg="";
if ($_GET['msg']) {
    $msg=$_GET['msg'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <title>Attendance Tracking Management System</title>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/bootstrap.min.js"></script>

</head>
<body>

<div class="jumbotron text-center" style="margin-bottom:0">
  <h1>Attendance Tracking Management System</h1>
</div>

<div class="container">
  <div class="row">
    <div class="col-md-4">
    </div>

    <!-- Login page -->
    <div class="col-md-4" style="margin-top:20px;">
      <div class="card">

        <!-- heading -->
        <div class="card-header">Password Change Request</div>
        
        <div class="card-body">
          <form method="post" id="teacher_login_form">
          <span id="error_teacher_password" class="text-danger"><?php  echo $msg; ?></span>
            <div class="form-group">
              
            <!-- username -->
              <label>Enter Email</label>
              <input type="text" name="teacher_email" id="teacher_emailid" class="form-control" />
              <span id="error_teacher_password" class="text-danger"><?php if(isset($_POST["login"])){
            echo $uerror;
              }?></span>
            </div>

            <!-- submit -->
            <div class="form-group">
              <input type="submit" name="login" id="teacher_login" class="btn btn-info" value="Check" />
            </div>

          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">

    </div>
  </div>
</div>


<script>

</script>