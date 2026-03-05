<?php
require('../fpdf/fpdf.php');
require_once('../dbconnection.php'); 
 
$user=$_GET['user'];
    $sql="SELECT * FROM student WHERE student.teacher_id='$user'";
    $query=mysqli_query($conn,$sql);
    $row=mysqli_fetch_array($query);
    $sq="SELECT Class FROM teacher WHERE teacher_id='$user'";
    $qr=mysqli_query($conn,$sq);
    $rw=mysqli_fetch_array($qr);
          $n=1;
$d=$rw['Class'];

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
   
    $this->Cell(30,10,'CLASS LIST',0,1,'C');
  
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
$pdf->Cell(165,5,$d,0,1,'C');
  $pdf->SetTextColor(220,50,50);
  $pdf->Cell(3);
    $pdf->Cell(12,8,'S/No',1,0,'C');
    $pdf->Cell(35,8,'Full Name',1,0,'C');
    $pdf->Cell(18,8,'Gender',1,0,'C');
    $pdf->Cell(50,8,'Date Of Birth',1,0,'C');
    $pdf->Cell(50,8,'Parent Email',1,1,'C');

   while($row=mysqli_fetch_array($query)){
        
         $pdf->SetTextColor(0,0,0);
         $pdf->Cell(3);
        $pdf->Cell(12,8,$n,1,0,'C');
        $pdf->Cell(35,8,$row['student_name'],1,0,'C');
        $pdf->Cell(18,8,$row['gender'],1,0,'C');
        $pdf->Cell(50,8,$row['dateofbirth'],1,0,'C');
        $pdf->Cell(50,8,$row['email'],1,1,'C');
        $n++;
    }
    $pdf->SetTitle('attendance');
$pdf->Output();

?>