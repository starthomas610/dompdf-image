<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Enable remote files to be fetched

$dompdf = new Dompdf($options);

// HTML content for the PDF
$html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .content { text-align: center; }
        .content img { width: 100%; max-width: 500px; }
    </style>
</head>
<body>
    <div class="content">
        <h1>Sample PDF with Image</h1>
        <img src="http://localhost/php/dompdf/super_badge.png" alt="Image">
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);

// (Opzionale) Imposta il formato e l'orientamento della pagina
// Puoi specificare la dimensione del documento e l'orientamento come segue:
$dompdf->setPaper('A4', 'portrait');

// Renderizza il PDF
$dompdf->render();

//rename pdf
$timestamp = time();
$filePath = 'tdpdf/' . "test_down_pdf.pdf";
// Stream il PDF al browser
// Salva il PDF su disco
$pdfOutput = $dompdf->output();
file_put_contents($filePath, $pdfOutput);

// Output the generated PDF to Browser
// $dompdf->stream("sample.pdf", array("Attachment" => false));