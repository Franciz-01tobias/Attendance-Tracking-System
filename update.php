<?php
 require_once('../dbconnection.php');
 session_start();
 if ($_SESSION['user']==" ") {
  header('location:login.php');
}
$user=$_SESSION['user'];
        
$sq="SELECT  * FROM teacher WHERE teacher_id='$user'";
$qr=mysqli_query($conn,$sq);
$rw=mysqli_fetch_array($qr);
if(isset($_POST["teacher_login"])){
    $fn=$_POST["teacher_fname"];
    $ln=$_POST["teacher_lname"];
    $em=$_POST["teacher_email"];
    if(isset($_FILES['image'])){
      $errors= array();
      $file_name = $_FILES['image']['name'];
      $file_size = $_FILES['image']['size'];
      $file_tmp = $_FILES['image']['tmp_name'];
      $file_type = $_FILES['image']['type'];
      $nukta='.';
      $file_ext=strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
      $extensions= array("jpeg","jpg","png");
      
      if(in_array($file_ext,$extensions)=== false){
         $errors[]="extension not allowed, please choose a JPEG or PNG file.";
      }
      
      if($file_size > 10097152) {
         $errors[]='File size must be excately 10 MB';
      }
      
      if(empty($errors)==true) {
         move_uploaded_file($file_tmp,"../images/".$file_name);
      }else{
         print_r($errors);
        }
      }

    $cs=$_POST["class"];
    $sql="UPDATE `teacher` SET `FirstName`='$fn',`LastName`='$ln',`Email`='$em',
    `Class`='$cs',`Image`='$file_name' WHERE teacher_id=$user";
     echo$sql;
    $query=mysqli_query($conn,$sql);

    if($query){
        header("location:option.php");

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

    <!-- Login page -->
    <div class="col-md-4" style="margin-top:20px;">
      <div class="card">

        <!-- heading -->
        <div class="card-header"> Teacher Registration</div>
        
        <div class="card-body">
          <form method="post" enctype="multipart/form-data" >

          <div class="form-group">
                <label>Enter First Name:</label>
                <input type="text" name="teacher_fname" id="teacher_emailid"
                value="<?php echo$rw['FirstName']; ?>" class="form-control" />
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>

            <div class="form-group">
              <label>Enter Last Name:</label>
              <input type="text" name="teacher_lname" id="teacher_emailid"
              value="<?php echo$rw['LastName']; ?>" class="form-control" />
              <span id="error_teacher_emailid" class="text-danger"></span>
            </div>

            <div class="form-group">
                <label>Enter Email:</label>
                <input type="text" name="teacher_email" id="teacher_emailid" 
                value="<?php echo$rw['Email']; ?>" class="form-control" />
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>

              <div class="form-group">
              
              <!-- username -->
                <label>Enter Class:</label>
                <Select name="class" class="form-select">
                    <option >Select your class</option>
                    <option value="Form 1">Form 1</option>
                    <option value="Form 2">Form 2</option>
                    <option value="Form 3">Form 3</option>
                    <option value="Form 4">Form 4</option>

                </Select>
                <span id="error_teacher_emailid" class="text-danger"></span>
              </div>

              <div class="mb-3">
  <label for="formFile" class="form-label">Upload Your Image</label>
  <input class="form-control" name="image" type="file" accept=".png, .jpg, .jpeg" id="formFile">
</div>
            


            
            <!-- submit -->
            <div class="form-group">
              <input type="submit" name="teacher_login" id="teacher_login" class="btn btn-info" value="Update" />
            </div>

          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">

    </div>
  </div>
</div>
