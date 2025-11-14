<?php
require_once('tcpdf/tcpdf.php');
include 'db_connect.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('ParkSense System');
$pdf->SetAuthor('ParkSense Admin');
$pdf->SetTitle('Unregistered Vehicle Report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'ParkSense - Unregistered Vehicles', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 11); // Bold font for header
$pdf->SetFillColor(51, 51, 51); // Dark grey background (#333)
$pdf->SetTextColor(255, 255, 255); // White text
$pdf->SetDrawColor(200, 200, 200); // Light grey border

$w = array(50, 60, 70); // Widths for Time, License Plate, Violation

$pdf->Cell($w[0], 8, 'Time', 1, 0, 'C', 1);
$pdf->Cell($w[1], 8, 'License Plate', 1, 0, 'C', 1);
$pdf->Cell($w[2], 8, 'Violation', 1, 1, 'C', 1); // The '1' at the end moves to the next line

$sql = "SELECT * FROM violations WHERE vehicle_status = 'unregistered' ORDER BY violation_time ASC";
$result = $conn->query($sql);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0); // Black text for data
$fill = 0; // Used to alternate row background color

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Date format 
        $time = date('M d, Y g:i A', strtotime($row['violation_time']));

        // Set alternating row background
        $pdf->SetFillColor($fill ? 220 : 255, $fill ? 220 : 255, $fill ? 220 : 255);

        $pdf->Cell($w[0], 7, $time, 'LR', 0, 'L', 1);
        $pdf->Cell($w[1], 7, $row['license_plate'], 'LR', 0, 'L', 1);
        $pdf->Cell($w[2], 7, $row['violation_description'], 'LR', 1, 'L', 1);
        
        $fill = !$fill;
    }
} else {
    $pdf->Cell(array_sum($w), 10, 'No unregistered violations found.', 1, 1, 'C');
}
// Draw the bottom border of the table
$pdf->Cell(array_sum($w), 0, '', 'T');

$pdf->Output('Unregistered_Vehicles.pdf', 'I');

$conn->close();
?>