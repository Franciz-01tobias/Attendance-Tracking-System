<?php
session_start();
 if ($_SESSION['user']==" ") {
  header('location:login.php');
}

$id=base64_decode($_GET['id']);
if(isset($_POST["update"])){
    require_once('../dbconnection.php');

    $cs=$_POST["class"];
    $sql = "UPDATE `attendance` SET `Status` = '$cs' WHERE Attendace_id='$id' ";
    $query=mysqli_query($conn,$sql);
    if($query){
        header("location:attendance.php");
    }
    else{
      echo$sql;
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
<body class="text-center">

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
        <div class="card-header "> Student Attendance Status</div>
        
        <div class="card-body">
          <form method="post" >

           
                   <div class="form-group">
                <label>Status :</label>
                <Select name="class" class="form-select">
                    <option disabled >Select status of student</option>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                </Select>
</div>
            
           
            
    
            <div class="form-group ">
            <button name="update" class="btn p-2 shadow btn-primary">Submit</button>
            </div>

          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">

    </div>
  </div>
</div>
