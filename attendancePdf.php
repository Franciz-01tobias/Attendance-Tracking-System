<?php
require('../fpdf/fpdf.php');
require_once('../dbconnection.php'); 
 
$user=$_GET['user'];
$d=date('Y-m-d',time());
    $sql="SELECT attendance.Attendace_id,attendance.Status,student.student_name,student.gender FROM attendance ,student 
    WHERE attendance.Stud_id=student.Stud_id AND student.teacher_id='$user' AND Attendace_date='$d'";
    $query=mysqli_query($conn,$sql);
          $n=1;

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo
    // $this->Image('./images/logo.png',20,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(70);
    // Title
    $d=date('Y-m-d',time());
    $this->Cell(30,10,'ATTENDANCE',0,1,'C');
    $this->Cell(165,5,$d,0,0,'C');
    // Line break
    $this->Ln(20);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

// Instanciation of inherited class
$pdf = new PDF('p');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetDrawColor(0,0,0);
$pdf->SetFillColor(200,220,255);
$pdf->SetFont('Times','',12);
$x=150;
$y=30;
$pdf->SetX($x);
$pdf->SetY($y);
  $pdf->SetTextColor(220,50,50);
  $pdf->Cell(30);
    $pdf->Cell(12,8,'S/No',1,0,'C');
    $pdf->Cell(35,8,'Full Name',1,0,'C');
    $pdf->Cell(18,8,'Gender',1,0,'C');
    $pdf->Cell(50,8,'Status',1,1,'C');

   while($row=mysqli_fetch_array($query)){
        
         $pdf->SetTextColor(0,0,0);
         $pdf->Cell(30);
        $pdf->Cell(12,8,$n,1,0,'C');
        $pdf->Cell(35,8,$row['student_name'],1,0,'C');
        $pdf->Cell(18,8,$row['gender'],1,0,'C');
        $pdf->Cell(50,8,$row['Status'],1,1,'C');
        $n++;
    }
    $pdf->SetTitle('attendance');
$pdf->Output();

?>