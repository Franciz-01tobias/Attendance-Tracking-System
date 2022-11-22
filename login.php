<?php


require_once('../dbconnection.php');

session_start();

if(isset($_POST["login"])){
    $em=$_POST["teacher_email"];
    $pass=$_POST["teacher_password"];
    $sql="SELECT * FROM `teacher` where Email='$em'";
$perror="";
$uerror="";
    $query=mysqli_query($conn,$sql);
    $row=mysqli_fetch_array($query);
    $no=mysqli_num_rows($query);
    if($no>0){
      $_SESSION['user']=$row['teacher_id'];
      $_SESSION['attendance']="";
       $pass=password_verify($pass,$row['Password']);
       if($pass){
        header("location:option.php");
        }
        else{
          $perror="Wrong Password";
         
        }
    }
    else{
      $uerror="Email not found";
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
              
            <!-- username -->
              <label>Enter Username</label>
              <input type="text" name="teacher_email" id="teacher_emailid" class="form-control" />
              <span id="error_teacher_password" class="text-danger"><?php if(isset($_POST["login"])){
            echo $uerror;
              }?></span>
            </div>
            
            <div class="form-group">
            
              <!-- password -->
              <label>Enter Password</label>
              <input type="password" name="teacher_password" id="teacher_password" class="form-control" />
              <span id="error_teacher_password" class="text-danger"><?php if(isset($_POST["login"])){
            echo $perror;
              }?></span>
            </div>

            
            <!-- submit -->
            <div class="form-group">
              <input type="submit" name="login" id="teacher_login" class="btn btn-info" value="Login" />
            </div>

            <div class="add">
              <p>Create new account <a href="./register.php">here</a></p>
              <p><a href="./fpassword.php?msg=">Forgot Passsword </a></p>
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