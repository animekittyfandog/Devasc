<?php
require_once('tcpdf/tcpdf.php');
include 'db_connect.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('ParkSense System');
$pdf->SetAuthor('ParkSense Admin');
$pdf->SetTitle('Archives Report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'ParkSense - Archives Report', 0, 1, 'C');

// Section Title
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Archived Violations', 0, 1, 'L');

// Header styling
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(51, 51, 51); // Dark grey background
$pdf->SetTextColor(255, 255, 255); // White text
$pdf->SetDrawColor(200, 200, 200); // Light grey border

$w = array(40, 40, 55, 45); 

// Print table header
$pdf->Cell($w[0], 8, 'Time', 1, 0, 'C', 1);
$pdf->Cell($w[1], 8, 'License Plate', 1, 0, 'C', 1);
$pdf->Cell($w[2], 8, 'Violation', 1, 0, 'C', 1);
$pdf->Cell($w[3], 8, 'Archived', 1, 1, 'C', 1);

$sql = "SELECT * FROM archive ORDER BY violation_time ASC";

$result = $conn->query($sql);

// Set font for data rows
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0);
$fill = 0; // Flag for alternating row colors

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        $pdf->SetFillColor($fill ? 220 : 255, $fill ? 220 : 255, $fill ? 220 : 255);

        $violationTime = date('M d, Y g:i A', strtotime($row['violation_time']));
        $archiveDateTime = date('M d, Y g:i A', strtotime($row['archive_time']));

        // Print the data cells
        $pdf->Cell($w[0], 7, $violationTime, 'LR', 0, 'L', 1);
        $pdf->Cell($w[1], 7, $row['license_plate'], 'LR', 0, 'L', 1);
        $pdf->Cell($w[2], 7, $row['violation_description'], 'LR', 0, 'L', 1);
        $pdf->Cell($w[3], 7, $archiveDateTime, 'LR', 1, 'L', 1);

        $fill = !$fill; // Flip the color flag for the next row
    }
} else {
    $pdf->Cell(array_sum($w), 8, 'No archived violations found.', 1, 1, 'C');
}

$pdf->Cell(array_sum($w), 0, '', 'T');

$pdf->Output('Archives_Report.pdf', 'I');
$conn->close();
?>