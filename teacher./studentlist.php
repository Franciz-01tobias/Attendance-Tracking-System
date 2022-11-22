<?php
    session_start();
    if ($_SESSION['user']=="") {
      header('location:login.php');
    }
     require_once('../dbconnection.php'); 
 
     $user=$_SESSION['user'];
         $sql="SELECT * FROM student WHERE student.teacher_id='$user'";
         $query=mysqli_query($conn,$sql);
         $row=mysqli_fetch_array($query);
         $sq="SELECT Class FROM teacher WHERE teacher_id='$user'";
         $qr=mysqli_query($conn,$sq);
         $rw=mysqli_fetch_array($qr);
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
         <h2 class='h2'>Student List</h2> 
        </div>
  
        <div class="col-md-3 " align="right">
      <button class="btn p-2 shadow btn-primary">
        <a class="text-decoration-none text-light" href="listPDF.php?user=<?php echo$user;?>">PRINT</a></button> 
        <button class="btn p-2 shadow btn-success">
          <a class="text-decoration-none text-light" href="student.php">ADD</a></button>  
      </div>
    </div>
 

  	<div class="card-body">
  		<div class="table-responsive">
        <h2 class="h2">Class  : <?php echo   $rw['Class'];?></h2>
    
        <table class="table table-striped table-bordered" id="attendance_table">
          <thead>
            <tr>
              <th>S/No</th>
              <th>Student Name</th>
              <th>Gender</th>
              <th>Date Of Birth</th>
              <th>Parent Email</th>
              <th colspan="2">Action</th>
            </tr>
          </thead>
         <?php while($row=mysqli_fetch_array($query)){
            ?>
          <tbody>
            <td><?php echo$n;?></td>
            <td class="text-capitalize"><?php echo$row['student_name'];?></td>
            <td><?php echo$row['gender'];?></td>
            <td><?php echo$row['dateofbirth'];?></td>
            <td><?php echo$row['email'];?></td>
            <td><a href="studentedit.php?id=<?php echo base64_encode($row['Stud_id']); ?>">Edit</a></td>
            <td><a href="studentdelete.php?id=<?php echo base64_encode($row['Stud_id']); ?>">Delete</a></td>
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




