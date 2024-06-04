<?php

// Include l'autoloader di Dompdf
require 'vendor/autoload.php';

// Utilizza il namespace Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('debugPng', true); // Enable PNG debugging
$options->set('debugKeepTemp', true); // Keep temporary files
$options->set('debugCss', true);
$options->set('debugLayout', true);
$options->set('debugLayoutLines', true);
$options->set('debugLayoutBlocks', true);
$options->set('debugLayoutInline', true);
$options->set('debugLayoutPaddingBox', true);



$dompdf = new Dompdf($options);


// Crea una nuova istanza di Dompdf
$dompdf = new Dompdf();
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<style>
    
</style>



</head>
<body>
<header>
    <img src="logos/1710258082_bcklogo-light-transformed.png" alt="Logo Azienda" style="height: 80px;">
</header>
<footer>
    C.E. Soft srl via civenna 1 20151 Milano - Pagina <span class="page-number"></span>
</footer>
<table>
    <thead>
        <tr>
            <th colspan="2">DATI DEL FABBRICANTE / MANUFACTURER'S DATA</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="first-column">Nome Azienda / Company Name</td>
            <td class="header-data">a</td>
        </tr>
        <tr>
            <td class="first-column">Indirizzo / Address</td>
            <td class="header-data">b</td>
        </tr>
        <tr>
            <td class="first-column">Paese / Country</td>
            <td class="header-data">c</td>
        </tr>
        <tr>
            <td class="first-column">Telefono / Phone</td>
            <td class="header-data">d</td>
        </tr>
        <tr>
            <td class="first-column">Email</td>
            <td class="header-data">e</td>
        </tr>
        <tr>
            <td class="first-column">Partita IVA / VAT Number</td>
            <td class="header-data">f</td>
        </tr>
        <tr>
            <td class="first-column">Marchio / Mark</td>
            <td class="header-data">g</td>
        </tr>
        <tr>
            <td class="first-column">Persona di Contatto / Contact Person</td>
            <td class="header-data">h</td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="2">MANDATARIO (SE PREVISTO) / AUTHORIZED REPRESENTATIVE (IF APPLICABLE)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="first-column"></td>
            <td class="header-data"></td>
        </tr>
        <tr>
            <td class="first-column"></td>
            <td class="header-data">
            <img src="http://localhost/php/dompdf/super_badge.png" alt="Image"></td>
        </tr>
        
    </tbody>
</table>
HTML;

$html .= '</html>';

//echo $html;
// Carica il tuo HTML nel Dompdf
$dompdf->loadHtml($html);

// (Opzionale) Imposta il formato e l'orientamento della pagina
// Puoi specificare la dimensione del documento e l'orientamento come segue:
$dompdf->setPaper('A4', 'portrait');

// Renderizza il PDF
$dompdf->render();

//rename pdf
$timestamp = time();
$fileName = "1.pdf";
$filePath = 'tdpdf/' . $fileName;
// Stream il PDF al browser
// Salva il PDF su disco
$pdfOutput = $dompdf->output();
file_put_contents($filePath, $pdfOutput);
/*
$UpdateQuery = new WA_MySQLi_Query($cmctrfdb);
$UpdateQuery->Action = "update";
$UpdateQuery->Table = "data_td";
$UpdateQuery->bindColumn("pdffilenametd", "s", "$fileName", "WA_DEFAULT");
$UpdateQuery->addFilter("iddata_td", "=", "i", "" . ($idtd)  . "");
$UpdateQuery->execute();
$UpdateGoTo = "";
if (function_exists("rel2abs")) $UpdateGoTo = $UpdateGoTo ? rel2abs($UpdateGoTo, dirname(__FILE__)) : "";
$UpdateQuery->redirect($UpdateGoTo);
*/
