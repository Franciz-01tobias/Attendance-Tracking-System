<?php
    session_start();
     require_once('../dbconnection.php'); 
     if ($_SESSION['user']==" ") {
      header('location:login.php');
    }
    $user=$_SESSION['user'];
     $d=date('Y-m-d',time());
         $sql="SELECT attendance.Attendace_id,attendance.Status,student.student_name FROM attendance ,student 
         WHERE attendance.Stud_id=student.Stud_id AND student.teacher_id='$user' AND Attendace_date='$d'";
         $query=mysqli_query($conn,$sql);
               $n=1;
             
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
<div class="container" style="margin-top:30px">
  <div class="card">

  	<div class="card-header">
      <div class="row">
        <div class="col-md-9">
         <h2 class='h2'>Attendance List</h2> 
        </div>
  
        <div class="col-md-3 " align="right">
        <button class="btn p-2 shadow btn-primary">
          <a class="text-decoration-none text-light" href="attendancePdf.php?user=<?php echo$_SESSION['user'];?>">PRINT
          </a></button> 
        <button class="btn p-2 shadow btn-success"><a class="text-decoration-none text-light" href="addattendece.php">ADD</a></button>  
      </div>
    </div>
 

  	<div class="card-body">
  		<div class="table-responsive">
        <span id="message_operation">Attendance Date  : <?php echo date('D-m-Y',time())?></span>
    
        <table class="table table-striped table-bordered" id="attendance_table">
          <thead>
            <tr>
              <th>S/No</th>
              <th>Student Name</th>
              <th>Attendance Status</th>
              <th colspan="2">Action</th>
            </tr>
          </thead>
         <?php while($row=mysqli_fetch_array($query)){
            ?>
          <tbody>
            <td><?php echo$n;?></td>
            <td class="text-capitalize"><?php echo$row['student_name'];?></td>
            <td><?php echo$row['Status'];?></td>
            <td><a href="edit.php?id=<?php echo base64_encode($row['Attendace_id']);?>">Edit</a></td>
          </tbody>
          <?php
          $n++;
            }
          ?>
        </table>
    
<div class="footer">
         <button class="btn p-2 shadow btn-primary">
         <a class="text-decoration-none text-light" href="option.php">GO BACK</a>
      </button> 
    <button class="btn p-2 shadow btn-danger">
          <a class="text-decoration-none text-light" href="logout.php">LOG OUT</a>
          </button>
      </div>
        
    	</div>
  	</div>
  </div>
</div>

</body>
</html>




