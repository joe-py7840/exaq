<?php
require_once('tcpdf.php');

if (isset($_GET['question'])) {
    $question = urldecode($_GET['question']);

    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, $question);
    $pdf->Output('question.pdf', 'I');
} else {
    // Handle the case where no question is provided
    echo "No question to generate PDF for.";
}
?>
