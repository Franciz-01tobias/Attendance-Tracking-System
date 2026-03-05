<?php
session_start();
if ($_SESSION['user']==" ") {
  header('location:login.php');
}
if ($_SESSION['user']=="") {
  header('location:studentlist.php');
}

if(isset($_POST["student_login"])){
    require_once('../dbconnection.php');
    $sn=$_POST["student_name"];
    $ti=$_POST["student_class"];
    $em=$_POST["student_email"];
    $bd=$_POST["student_bdate"];
    $sql="INSERT INTO `student`(`student_name`, `email`, `dateofbirth`, `teacher_id`)
     VALUES ('$sn','$em','$bd','$ti')";
    $query=mysqli_query($conn,$sql);
    if($query){
        header("location:studentlist.php");
    }
    else{
        echo"problem";
    }
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


    <div class="col-md-4" style="margin-top:20px;">
      <div class="card">

        
        <div class="card-header"> Student Registration</div>
        
        <div class="card-body">
          <form method="post" >

          <div class="form-group">
                <label>Enter Student Name:</label>
                <input type="text" name="student_name" id="student_fname" class="form-control" />
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>

            <div class="form-group">
              <input type="hidden" name="student_class"  value="<?php echo$_SESSION['user']; ?>" id="student_classid" class="form-control" />
              <span id="error_teacher_emailid" class="text-danger"></span>
            </div>

            <div class="form-group">
                <label>Enter Email:</label>
                <input type="text" name="student_email" id="teacher_emailid" class="form-control" />
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>
            
              
              <label>Date of Birth</label>
              <input type="date" name="student_bdate" id="teacher_password" class="form-control" />
              <span id="error_teacher_password" class="text-danger"></span>
            </div>
            
        
            <div class="form-group">
              <input type="submit" name="student_login" id="student_login" class="btn btn-info" value="Register" />
            </div>

          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">

    </div>
  </div>
</div>
