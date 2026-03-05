<?php


require_once('../dbconnection.php');

session_start();

if(isset($_POST["login"])){
    require_once('../dbconnection.php');
    $em=$_POST["admin_email"];
    $pass=$_POST["admin_password"];
    $sql="SELECT * FROM `admin` where Email='$em'";


    $query=mysqli_query($conn,$sql);
    $row=mysqli_fetch_array($query);
    if($query){
       $pass=password_verify($pass,$row['Password']);
       if($pass){
        header("location:option.php");
        }
        else{
            echo"wrong password";
            echo$sql;
        }
    }
    else{
        echo"Email not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Student Attendance System</title>
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
    <div class="col-md-4" style="margin-top:20px;">
      <div class="card">
        <div class="card-header">Admin Login</div>

         <!-- LOGIN FORM -->
        <div class="card-body">
           <form method="post" id="admin_login_form">

            <div class="form-group">
              <label>Enter Username</label>
              <input type="text" name="admin_email" id="admin_user_name" class="form-control" />
              <span id="error_admin_user_name" class="text-danger"></span>
            </div>

            <div class="form-group">
              <label>Enter Password</label>
              <input type="password" name="admin_password" id="admin_password" class="form-control" />
              <span id="error_admin_password" class="text-danger"></span>
            </div>

            <div class="form-group">
              <input type="submit" name="admin_login" id="admin_login" class="btn btn-info" value="Login" />
            </div>

          </form>
        </div>

      </div>
    </div>
    <div class="col-md-4">

    </div>
  </div>
</div>

</body>
</html>

<script>

</script>