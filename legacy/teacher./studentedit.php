<?php

session_start();
 if ($_SESSION['user']==" ") {
    header('location:login.php');
  }

require_once('../dbconnection.php'); 
$id=base64_decode($_GET['id']);
$sql="SELECT * FROM student WHERE student.Stud_id='$id'";
$query=mysqli_query($conn,$sql);
$row=mysqli_fetch_array($query);

if(isset($_POST["update"])){
    require_once('../dbconnection.php');
    $sn=$_POST["student_name"];
    $em=$_POST["student_email"];
    $gn=$_POST["gender"];
    $bd=$_POST["student_bdate"];
    $sql="UPDATE `student` SET `student_name`='$sn',`gender`='$gn',
    `email`='$em',`dateofbirth`='$bd' WHERE Stud_id=$id ";
    echo$sql;
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

        
        <div class="card-header"> Student Update Info</div>
        
        <div class="card-body">
          <form method="post" >

          <div class="form-group">
                <label>Enter Student Name:</label>
                <input type="text" value="<?php echo$row['student_name']?>" name="student_name" id="student_fname" class="form-control" />
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>

        

            <div class="form-group">
                <label>Enter Email:</label>
                <input type="text" value="<?php echo$row['email']?>"  name="student_email" id="teacher_emailid" class="form-control" />
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>
            
              <div class="form-group">
              <label>Date of Birth</label>
              <input type="date"  value="<?php echo$row['dateofbirth']?>" name="student_bdate" id="teacher_password" class="form-control" />
              <span id="error_teacher_password" class="text-danger"></span>
            </div>

           
            <label>Gender :</label>
                <Select name="gender" class="form-select">
                    <option disabled >Select gender of student</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </Select>
     
        
            <div class="form-group">
              <input type="submit" name="update" id="student_login" class="btn btn-info" value="update" />
            </div>

          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">

    </div>
  </div>
</div>
