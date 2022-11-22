<?php
session_start();
 if ($_SESSION['user']==" ") {
    header('location:login.php');
  }

require_once('../dbconnection.php');
session_start();
$user=$_GET['id'];
       

if(isset($_POST["login"])){
   $pass=password_hash($_POST["teacher_password"],PASSWORD_DEFAULT);
   $sql="UPDATE `teacher` SET `Password`='$pass' WHERE teacher_id=$user";
    echo$sql;
   $query=mysqli_query($conn,$sql);

   if($query){
       header("location:login.php");

   }
   else{
       echo"problem";
   }
}
?>

<!-- --------------------------------------------------------------------------------------------------- -->

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
        <div class="card-header">Teacher Login</div>
        
        <div class="card-body">
          <form method="post" id="teacher_login_form">
            
            <div class="form-group">
              
   
            
            <div class="form-group">
            
              <!-- password -->
              <label>Enter Password</label>
              <input type="password" name="teacher_password" id="teacher_password" class="form-control" />
              <span id="error_teacher_password" class="text-danger"></span>
            </div>

            <div class="form-group">
            <label>Confirm Password</label>
              <input type="password" name="teacher_password" id="teacher_password" class="form-control" />
              <span id="error_teacher_password" class="text-danger">></span>
            </div>

            
            <!-- submit -->
            <div class="form-group">
              <input type="submit" name="login" id="teacher_login" class="btn btn-info" value="Change Password" />
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