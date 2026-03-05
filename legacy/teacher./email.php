<?php
session_start();
if (!isset($_SESSION['password-change-token'])) {
    header("location:login.php");
}
require_once('../dbconnection.php');
$email=base64_decode($_GET['email']);
$token=$_SESSION['password-change-token'];

require "../PHPMailer-master/PHPMailerAutoload.php";
$mail = new PHPMailer;
$mail->isSMTP();                                        // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';                         // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                                 // Enable SMTP authentication
$mail->Username = 'francizthobias@gmail.com';              // SMTP username
$mail->Password = 'FRANCI$allen12';                  // SMTP password
$mail->SMTPSecure = 'ssl';                            // Enable ssl encryption, `ssl` also accepted
$mail->Port = 456;                                    // TCP port to connect to
$mail->setFrom('francizthobias@gmail.com', 'Toby Lab');
$mail->addAddress($email);                              // Name is optional
$mail->isHTML(true);                                    // Set email format to HTML
$mail->Subject = 'PASSWORD CHANGE REQUEST';
//html body
$email1=base64_encode($email);
$message='Use this link to change your password: <a href="reset.php?token='
.$token.'">LINK</a>
 <br><br>If this was not you, contact the admin'; 
$message2='Password reset link is reset.php?token='.$token.'&email='.$email1;
$mail->Body    = $message;
//plain text for non-HTML mail clients
$mail->AltBody = $message2;
if(!$mail->send()) {
    $msg="email could not be sent";
    echo "Mailer Error: " . $mail->ErrorInfo;
    // $loc=header("location:fpassword.php?msg=$msg");
    die();
} 
//-----------------------------------------------------------------------------------------------------//							
if($mail)
{
    $msg="Email was sent";
    $loc=header("location:fpassword.php?msg=$msg");
    die();
}

?>