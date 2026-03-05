<?php
    session_start();
    require_once('../dbconnection.php'); 
    if ($_SESSION['user']==" ") {
     header('location:login.php');
   }
     require_once('../dbconnection.php'); 
 
     $user=$_SESSION['user'];
        
         $sq="SELECT  * FROM teacher WHERE teacher_id='$user'";
         $qr=mysqli_query($conn,$sq);
         $rw=mysqli_fetch_array($qr);
         $photo=$rw['Image'];
    ?>
<!DOCTYPE html>
<html lang="en">

<!-- header -->
<head>

  <title>Student Attendance System</title>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- css files -->
  <link rel="stylesheet" href="../css/bootstrap.min.css">
   <link rel="stylesheet" href="../css/profile.css">
  <script src="../js/bootstrap.min.js"></script>

</head>
<body>
    <!-- main heading -->
    <div class="jumbotron text-center" style="margin-bottom:0 " >
    <div class="profile"   style="background-image: url('../images/<?php echo $photo;?>' )">
 <a href="update.php"><img src="../images/edit.png" width="20px"  id="edit"></a> 
 <p class="" id="greeting"><?php echo ucwords($rw['FirstName'].' '.$rw['LastName']);?></p> 
</div>

        <h1  class="heading">Attendance Tracking Management System</h1>
    </div>

    <table class="table">
    <tbody>
        <tr>
            <!-- admin login -->
            <td style="text-align:center">
                <a href="studentlist.php ">STUDENT LIST</a>
               
            </td>

         

      
            <!-- teacher login -->
            <td style="text-align:center">
                <a href="attendance.php"><img src="../images/attendance3.jpeg" width="250" height="180"></a>
            </td>
            </tr>

            <tr>
            <td colspan="2" style="text-align:center">
                <a href="student.php"><img src="../images/stu.jpeg" width="250" height="180"></a>
            </td>
         
        </tr>
    </tbody>
</table>

<div class="footer"> 
    <button class="btn p-2 shadow btn-danger">
          <a class="text-decoration-none text-light" href="logout.php">LOG OUT</a>
          </button>
      </div>
        
</body>
</html>