<?php
// Include la libreria TCPDF
ob_start();

// Include l'autoloader di Dompdf
require 'dompdf/autoload.inc.php';

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
$dompdf->setBasePath($_SERVER['DOCUMENT_ROOT']);

$base_url = '/cmccopiaoriginale/public/';

require_once '../Connections/cmctrfdb.php';
require_once '../webassist/mysqli/rsobj.php';
//include 'include/headscript.php';

include('languages/' . $_SESSION['langselect'] . '/tdgen.php');
include('languages/' . $_SESSION['langselect'] . '/general.php');
$idcompany = $_SESSION["compid"];
if (isset($_GET['idtrftd'])) {
    $idtrftd = $_GET['idtrftd'];
}
if (isset($_POST['idtrftd'])) {
    $idtrftd = $_POST['idtrftd'];
}
if (isset($_POST['iddata_td'])) {
    $idtd = $_POST['iddata_td'];
}
if (isset($_GET['iddata_td'])) {
    $idtd = $_GET['iddata_td'];
}
if (isset($_GET['idtd'])) {
    $idtd = $_GET['idtd'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Preparazione di un array per contenere i valori sanificati
    $sanitizedPost = [];
    $errors = [];

    // Lista dei campi da sanificare e controllare se sono piene
    $fields = [
        'productionplace_same', 'classificationshoes', 'destinationuseppe',
        'manufacutringprocess', 'ppeageing', 'obsolescencedeadline',
        'localisationppemarking', 'manufacturerlogoid', 'sizeexamplecemark',
        'monthyearprod', 'serialbatchnumber', 'standarduse', 'symbolsaddreq',
        'proddescription', 'packaging', 'declarconformity', 'webaddress'
    ];

    foreach ($fields as $field) {
        if (!empty($_POST[$field])) {
            // Utilizzo FILTER_SANITIZE_STRING per rimuovere i tag e sanificare il testo
            $sanitizedPost[$field] = filter_input(INPUT_POST, $field, FILTER_SANITIZE_STRING);
        }
    }

    // Controllo se ci sono stati errori
    if (count($errors) === 0) {
        // Tutti i campi sono stati compilati e sanificati
        // Qui puoi procedere con l'elaborazione dei dati
        // Ad esempio, stampare i valori o salvarli in un database
        foreach ($sanitizedPost as $key => $value) {
        }
    } else {
        // Ci sono stati errori, ad esempio alcuni campi potrebbero essere vuoti
        // Puoi gestire gli errori qui, ad esempio stampandoli
        foreach ($errors as $key => $message) {
            echo "Errore nel campo $key: $message<br>";
        }
    }
}

// *: update data_td
// Assicurati che la richiesta sia di tipo POST e che l'ID sia stato fornito

$conn = mysqli_connect($servername, $username, $password, $dbname);



if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($idtrftd)) {
    // Preparazione della parte iniziale della query di aggiornamento
    $updateQuery = "UPDATE data_td SET ";
    $updateParts = [];
    $queryParams = [];

    // Iterazione sui campi sanificati per costruire la query di aggiornamento
    foreach ($sanitizedPost as $key => $value) {
        // Escludi idtrftd dalla parte di aggiornamento della query
        if ($key !== 'idtrftd') {
            $updateParts[] = "$key = ?";
            $queryParams[] = $value;
        }
    }

    // Controllo se ci sono campi da aggiornare
    if (count($updateParts) > 0) {
        $updateQuery .= join(', ', $updateParts) . " WHERE idtrf = ?";
        $queryParams[] = $idtrftd; // Aggiungi l'ID alla fine dei parametri della query

        // Preparazione della query
        $stmt = $conn->prepare($updateQuery);

        // Costruzione del tipo di parametri (stringhe, in questo caso)
        $types = str_repeat('s', count($queryParams));

        // Aggiunta dei parametri alla statement
        $stmt->bind_param($types, ...$queryParams);

        // Esecuzione della query
        if ($stmt->execute()) {
        }

        // Chiusura dello statement
        $stmt->close();
    }
}


$conn = new mysqli($servername, $username, $password, $dbname);
$checkQuery = "SELECT COUNT(*) as count FROM fillrisk_td WHERE iddata_td = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $idtd);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    // Non ci sono record, quindi procedi con l'inserimento dei dati da riskarea_td

    // Prendi tutti i record da riskarea_td
    $selectQuery = "SELECT * FROM riskarea_td";
    $result = $conn->query($selectQuery);



    while ($riskRow = $result->fetch_assoc()) {
        // Prepara l'insert per ogni riga trovata in riskarea_td

        $insertQuery = "INSERT INTO fillrisk_td (idriskarea_td, applicable, idcompany, iddata_td, idtrf) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);

        // Converte il valore 'Y'/'N' della colonna default in un intero (1/0)
        $applicableValue = ($riskRow['default'] == 'Y') ? 1 : 0;

        $stmt->bind_param("iiiii", $riskRow['idriskarea_td'], $applicableValue, $idcompany, $idtd, $idtrftd);
        $stmt->execute();
    }
}


// Chiudi lo statement e la connessione se non ti servono più
$stmt->close();
$conn->close();


// query data_td
$conn = new mysqli($servername, $username, $password, $dbname);
$sql = "SELECT * FROM data_td LEFT JOIN logo_td ON data_td.manufacturerlogoid=logo_td.idlogo_Td LEFT JOIN qualcheck_td ON data_td.proddescription=qualcheck_td.idqualcheck_td  WHERE iddata_td = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idtd); // "i" indica che l'id è un intero
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$prodplace = $row['productionplace_same'];
$classshoes = $row['classificationshoes'];
$tdrev  = $row['td_rev'];

if ($classshoes == "classone") {
    $classfinal = $classone;  // Assumo che il valore che vuoi assegnare sia una stringa "classone"
} elseif ($classshoes == "classtwo") {
    $classfinal = $classtwo;  // Assumo che il valore che vuoi assegnare sia una stringa "classtwo"
} else {
    $classfinal = "default";   // Opzionale: Un valore default se non corrisponde a nessuno dei casi
}

$stmt->close();
$conn->close();

$tdquery = new WA_MySQLi_RS("tdquery", $cmctrfdb, 1);
$tdquery->setQuery("SELECT * FROM `trf-details` LEFT JOIN modelarticle ON modelarticle.idmodelarticle=`trf-details`.model  WHERE `trf-details`.idtrfdetails='$idtrftd'");
$tdquery->execute();

$description = $tdquery->getColumnVal("sample_description");
$trfn = $tdquery->getColumnVal("trfnumber");
$trfrev  = $tdquery->getColumnVal("revtrf");
$trfnumb = $trfn . ' VER.' . $trfrev;
$trftdnumber = $trfn . 'TF';
$photoone  = $tdquery->getColumnVal("photoone");
$phototwo  = $tdquery->getColumnVal("phototwo");
$virusprot = $tdquery->getColumnVal("virusprotection");
$idarttype = $tdquery->getColumnVal("idarticletype");


$conn = new mysqli($servername, $username, $password, $dbname);


$kindcont = "headercertificate";
// Usa segnaposti per i parametri


$sqlcontact = "SELECT * FROM contacts LEFT JOIN countries ON countries.idcountries=contacts.country WHERE contacts.idtrf='$idtrftd' AND contacts.kindofcontacts='$kindcont'";

// Esecuzione della query
$resultcontact = $conn->query($sqlcontact);
$rowcontact = $resultcontact->fetch_assoc();
$companyname = $rowcontact["companyname"];
$address = $rowcontact["address"] . ' ' . $rowcontact["cap"] . ' ' . $rowcontact["city"];
$country = $rowcontact["namecountry"];
$phone = $rowcontact["telephone"];
$emailtd = $rowcontact["email"];
$vat = $rowcontact["piva"];
$mark = $tdquery->getColumnVal("registeredmark");

$contactperson = $rowcontact["contactname"] . ' ' . $rowcontact["contactsurname"];

// Chemical agent
$conn = new mysqli($servername, $username, $password, $dbname);
$sqlchemical = "SELECT trfchemicalagent.level, trfchemicalagent.degradationpercentage, chemicalagent.name_chemicalagent 
                FROM trfchemicalagent 
                LEFT JOIN chemicalagent ON trfchemicalagent.idchemicalagent = chemicalagent.idchemicalagent 
                WHERE trfchemicalagent.idtrf = '$idtrftd'";
$resultchemical = $conn->query($sqlchemical);
$chemicalAgents = []; // Array per memorizzare i risultati

if ($resultchemical && $resultchemical->num_rows > 0) {
    // Riempie l'array con i risultati della query
    while ($rowchemical = $resultchemical->fetch_assoc()) {
        $chemicalAgents[] = $rowchemical;
    }
}
$conn->close();



// Protection cat add
$conn = new mysqli($servername, $username, $password, $dbname);
$sqlprotect = "SELECT * FROM trfaddrequirements LEFT JOIN additionalrequirements ON trfaddrequirements.idadditionalrequirements = additionalrequirements.idadditionalrequirements WHERE trfaddrequirements.idtrf = '$idtrftd'";
$resultprotect = $conn->query($sqlprotect);
$protectionAdd = []; // Array per memorizzare i risultati

if ($resultprotect && $resultprotect->num_rows > 0) {;
    // Riempie l'array con i risultati della query
    while ($rowprotect = $resultprotect->fetch_assoc()) {

        $protectionAdd[] = $rowprotect["name_additionalrequirements_it"];
    }
}
$conn->close();

// Connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Preparazione della query

$tdquerystd = "SELECT * FROM trfstandards 
          LEFT JOIN standards ON trfstandards.idstandards = standards.idstandards 
          LEFT JOIN protectioncategory ON protectioncategory.idprotectioncategory = trfstandards.idprotectioncategory 
          LEFT JOIN dpicategory ON dpicategory.iddpicategory = trfstandards.iddpicategory 
          WHERE trfstandards.idtrfdetails = '$idtrftd'";

// Esecuzione della query
$resultstd = $conn->query($tdquerystd);

if (!$resultstd) {
    die("Errore nell'esecuzione della query: " . $conn->error);
}


$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
// Preparazione della query
$addprot = "SELECT * FROM trfaddrequirements 
            LEFT JOIN additionalrequirements ON trfaddrequirements.idadditionalrequirements = additionalrequirements.idadditionalrequirements 
            WHERE trfaddrequirements.idtrf = '$idtrftd'";
// Esecuzione della query
$resultaddreq = $conn->query($addprot);
if (!$resultaddreq) {
    die("Errore nell'esecuzione della query: " . $conn->error);
}


$tdquerystd = new WA_MySQLi_RS("tdquerystd", $cmctrfdb, 1);
$tdquerystd->setQuery("SELECT * FROM trfstandards LEFT JOIN standards ON trfstandards.idstandards=standards.idstandards LEFT JOIN protectioncategory ON protectioncategory.idprotectioncategory=trfstandards.idprotectioncategory LEFT JOIN dpicategory ON dpicategory.iddpicategory=trfstandards.iddpicategory WHERE trfstandards.idtrfdetails='$idtrftd'");
$tdquerystd->execute();

$archivetrflist = new WA_MySQLi_RS("archivetrflist", $cmctrfdb, 0);
$archivetrflist->setQuery("SELECT * FROM `trf-details` LEFT JOIN auth_users ON `trf-details`.iduser=auth_users.id LEFT JOIN article_type ON `trf-details`.idarticletype=article_type.idarticletype LEFT JOIN certificationtype ON certificationtype.idcertificationtype=`trf-details`.idcertification WHERE `trf-details`.idcompany='$idcompany'  AND `trf-details`.signedon <>'' ORDER BY `trf-details`.trfnumber");
$archivetrflist->execute();

// query prod place

//query location place
// Assumendo che $idt sia già definito e sanificato per prevenire SQL Injection

$conn = new mysqli($servername, $username, $password, $dbname);
$querylocation = "SELECT idcontactstd, companyName, address, city FROM contacts_td WHERE idtd = ?";
$stmt = $conn->prepare($querylocation);
$stmt->bind_param("i", $idtd); // "i" indica che il parametro è un intero
$stmt->execute();
$result = $stmt->get_result();

$rowslocation = [];
while ($rowlocation = $result->fetch_assoc()) {
    $rowslocation[] = $rowlocation;
}
$stmt->close();



// Crea una nuova istanza di Dompdf
$dompdf = new Dompdf();
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<style>
    @page { 
        margin: 120px 25px 80px 25px; /* Aumenta il margine superiore e inferiore */
    }
    body { 
        font-family: 'DejaVu Sans', sans-serif; 
    }
    header { 
        position: fixed; 
        top: -120px; /* Posizionato 120px sopra il margine superiore della pagina */
        left: 0; 
        right: 0; 
        height: 100px; /* Assicurati che l'altezza sia sufficiente per il tuo logo */
        text-align: center; 
    }
    footer { 
        position: fixed; 
        bottom: -80px; /* Posizionato 80px sopra il margine inferiore della pagina */
        left: 0; 
        right: 0; 
        height: 50px; 
        text-align: center; 
    }
    .page-number:before { 
        content: "Pagina " counter(page); /* Aggiunge "Pagina" prima del numero di pagina */
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px; /* Mantieni o regola secondo necessità */
    }
    th, td {
        border: 1px solid #000000;
        padding: 4px;
        font-size: 8pt;
        background-color: #fff;
        word-wrap: break-word;
        word-break: break-all;
    }
    th {
        background-color: #909090;
        color: white;
        text-align: left;
        font-size: 9pt;
    }
    td.header-data {
        background-color: #E7F4FA;
    }
    td.firstrisk {
        background-color: #ffffff;
        color: #000000;
    }
    .boldstyle {
        font-weight: normal; /* Stile per testo in grassetto, correggi secondo necessità */
        background-color: #ffffff;
        color: #000000;
        font-size: 8pt;
    }
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
            <td class="header-data">$companyname</td>
        </tr>
        <tr>
            <td class="first-column">Indirizzo / Address</td>
            <td class="header-data">$address</td>
        </tr>
        <tr>
            <td class="first-column">Paese / Country</td>
            <td class="header-data">$country</td>
        </tr>
        <tr>
            <td class="first-column">Telefono / Phone</td>
            <td class="header-data">$phone</td>
        </tr>
        <tr>
            <td class="first-column">Email</td>
            <td class="header-data">$emailtd</td>
        </tr>
        <tr>
            <td class="first-column">Partita IVA / VAT Number</td>
            <td class="header-data">$vat</td>
        </tr>
        <tr>
            <td class="first-column">Marchio / Mark</td>
            <td class="header-data">$mark</td>
        </tr>
        <tr>
            <td class="first-column">Persona di Contatto / Contact Person</td>
            <td class="header-data">$contactperson</td>
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
            <td class="first-column">$anagraficacompany</td>
            <td class="header-data">$anagraficacompany</td>
        </tr>
        <tr>
            <td class="first-column">$anagraficaaddress</td>
            <td class="header-data">$anagraficacompany</td>
        </tr>
        
    </tbody>
</table>





HTML;
// prod place
$html .= '<table>
    <thead>
    <tr>
    <th colspan="3">LUOGO DI PRODUZIONE / PRODUCTION SITE</th>
</tr>
        <tr>
            <th class="boldstyle">CompanyName</th>
            <th class="boldstyle">Address</th>
            <th class="boldstyle">City</th>
        </tr>
    </thead>
    <tbody>';

foreach ($rowslocation as $rowlocation) {
    $html .= "<tr>
        <td class='header-data'>{$rowlocation['companyName']}</td>
        <td class='header-data'>{$rowlocation['address']}</td>
        <td class='header-data'>{$rowlocation['city']}</td>
    </tr>";
};
$html .= '</tbody></table>';

//DPI DATA

$model = $tdquery->getColumnVal("namemodelarticle");
$measuremin = $tdquery->getColumnVal("measurefrom");
$measuremax = $tdquery->getColumnVal("measureto");
$destppe = $row['destinationuseppe'];
$manprocess = $row['manufacutringprocess'];
$ppeage = $row['ppeageing'];
$obsol = $row['obsolescencedeadline'];
if ($ppeage == 'Y') {
    $ppeagetext = 'Sì';
} else {
    $ppeagetext = 'No';
}
$html .= <<<HTML

<table>
    <thead>
        <tr>
            <th colspan="2">DATI RELATIVI AL DPI / PPE DATA</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="first-column">Codice Articolo</td>
            <td class="header-data">{$description}</td>
        </tr>
        <tr>
            <td class="first-column">Modello</td>
            <td class="header-data">{$model}</td>
        </tr>
HTML;

if ($idarttype == 1) {
    $html .= <<<HTML
        <tr>
            <td class="first-column">Classificazione</td>
            <td class="header-data">{$classfinal}</td>
        </tr>
HTML;
}

$html .= <<<HTML
        <tr>
            <td class="first-column">Misura</td>
            <td class="header-data">{$measuremin} - {$measuremax}</td>
        </tr>
        <tr>
            <td class="first-column">Destinazione d'uso del DPI</td>
            <td class="header-data">{$destppe}</td>
        </tr>
        <tr>
            <td class="first-column">Processo di lavorazione</td>
            <td class="header-data">{$manprocess}</td>
        </tr>
        <tr>
            <td class="first-column">DPI soggetto ad invecchiamento</td>
            <td class="header-data">{$ppeagetext}</td>
        </tr>
    </tbody>
</table>
HTML;


//  dpi standard

$html .= '<table>
    
    <tbody>';

while ($rowstd = $resultstd->fetch_assoc()) {
    $stdcode = $rowstd['standardcode'];
    $dpicat = $rowstd['value_dpicategory'];
    $html .= "<tr>
        <td style='font-weight: bold; width: 20%;'>Norme armonizzate di riferimento</td>
        <td class='header-data' style='width: 20%;'>{$stdcode}</td>
        <td style='font-weight: bold; width: 15%;'>Cat Protezione DPI</td>
        <td class='header-data' style='width: 15%;'>{$rowstd['name_protectioncategory']}</td>
        <td style='font-weight: bold; width: 15%;'>Categoria del DPI</td>
        <td class='header-data' style='width: 15%;'>{$rowstd['value_dpicategory']}</td>
    </tr>";
};
$html .= '</tbody></table>';

//  Add prot category

$html .= '<table>
    
    <tbody>';

while ($rowaddreq = $resultaddreq->fetch_assoc()) {

    $html .= "<tr>
        <td style='font-weight: bold; width: 15%;'>Categoria di protezione aggiuntiva</td>
        <td class='header-data' style='width: 15%;'>{$rowaddreq['name_additionalrequirements_it']}</td>

    </tr>";
};
$html .= '</tbody></table>';




//photos da fare


// virus prot
if ($virusprot == "Y") :
    $html .= '<table>
    <tbody>
    <tr>
    <td style="width: 50%;" colspan="1">Protezione da Virus</td>
    <td>{}</td>
</tr>
        
    </tbody>';



    $html .= '</tbody></table>';
endif;


// chem agent
if (!empty($chemicalAgents)) :
    $html .= '<table>
    <thead>
    <tr>
    <td>Chemical Agent</td>';

    if ($idarttype == 2) {
        $html .= '<td>Level</td>
                  <td>Degradation Percentage</td>';
    }

    $html .= '</tr>
    </thead>
    <tbody>';

    foreach ($chemicalAgents as $agent) :
        $html .= "<tr>
        <td>{$agent['name_chemicalagent']}</td>";

        if ($idarttype == 2) {
            $html .= "<td>{$agent['level']}</td>
                      <td>{$agent['degradationpercentage']}</td>";
        }

        $html .= '</tr>';
    endforeach;
    $html .= '</tbody></table>';
endif;


//table risk query

$riskquery = new WA_MySQLi_RS("riskquery", $cmctrfdb, 0);
$riskquery->setQuery("SELECT * FROM fillrisk_td LEFT JOIN riskarea_td ON riskarea_td.idriskarea_td=fillrisk_td.idriskarea_td WHERE fillrisk_td.iddata_td = '$idtd' ORDER BY fillrisk_td.idfillrisk_td");
$riskquery->execute();

$html .= "<table>
    <thead>
    <tr>
    <th colspan='4'> VALUTAZIONE DEI RISCHI (REQUISITI ESSENZIALI DI SALUTE E SICUREZZA IN ACCORDO ALL'ALLEGATO II DEL REGOLAMENTO (UE) 2016/425) /
    RISK ASSESSMENT (ESSENTIAL HEALTH AND SAFETY REQUIREMENT ACCORDING TO ANNEX II OF THE REGULATION (EU) 2016/425)</th>
</tr>
        <tr>
            <th class='boldstyle' style='width: 10%;'>{$requirementnumbertd}</th>
            <th class='boldstyle' style='width: 45%;'>{$requirementnametd}</th>
            <th class='boldstyle' style='width: 10%;'>{$applicabletd}</th>
            <th class='boldstyle' style='width: 35%;'>{$covertbytd}</th>
        </tr>
    </thead>
    <tbody>";

$html .= <<<HTML
    <tbody>
    HTML;

// Aggiungi qui il codice PHP fornito
while (!$riskquery->atEnd()) {
    $risknumber = $riskquery->getColumnVal("risknumber");
    $riskname_it = $riskquery->getColumnVal("riskname_it");
    $applicable = ($riskquery->getColumnVal("applicable") == "1") ? 'checked' : '';
    $customCheckId = "customCheck{$risknumber}";
    $covertext = '';
    if ($riskquery->getColumnVal("coveredby") == 'coverone') {
        $covertext = $coverone;
    } else if ($riskquery->getColumnVal("coveredby") == 'covertwo') {
        $covertext = $covertwo;
    } else if ($riskquery->getColumnVal("coveredby") == 'coverthree') {
        $covertext = $coverthree;
    }

    $html .= <<<HTML
        <tr>
            <td scope="row" >{$risknumber}</td>
            <td>{$riskname_it}</td>
            <td  class='header-data' style="text-align: center;">
                <div class="custom-control custom-checkbox">
                <input disabled type="checkbox" class="custom-control-input blue-highlight" id="{$customCheckId}" {$applicable} data-parsley-multiple="groups" data-parsley-mincheck="2">

                <label class="custom-control-label" for="{$customCheckId}"></label>
                </div>
            </td>

            <td  class='header-data'>
                <div class="col-sm-24">
                    {$covertext}
                </div>
            </td>
            <input class="form-control" type="hidden" value="{$riskquery->getColumnVal("idfillrisk_td")}" name="fillrisktd{$riskquery->getColumnVal("idfillrisk_td")}">
        </tr>
    HTML;

    $riskquery->moveNext();
}

$html .= <<<HTML
    </tbody>
    HTML;

$html .= '</tbody></table>';

//parts

$html .= "<table>
    <thead>
    <tr>
    <th colspan='7'>  COMPONENTI DEL DPI / PPE PARTS</th>
</tr>
        <tr>
            <th class='boldstyle' style='width: 5%;' >N.</th>
            <th class='boldstyle' style='width: 20%;'>{$descriptionpart}</th>
            <th class='boldstyle' style='width: 20%;'>{$articlepart}</th>
            <th class='boldstyle' style='width: 15%;'>{$colorpart}</th>
            <th class='boldstyle' style='width: 20%;'>{$descriptionpartlist}</th>
            <th class='boldstyle' style='width: 10%;'>{$reprtonumbertrdlabtitle}</th>
            <th class='boldstyle' style='width: 10%;'>{$trddatereporttitle}</th>

        </tr>
    </thead>
    <tbody>";

$partsquery = new WA_MySQLi_RS("partsquery", $cmctrfdb, 0);
$partsquery->setQuery("SELECT * FROM identificationparts WHERE identificationparts.idtrfdetails='$idtrftd'");
$partsquery->execute();

while (!$partsquery->atEnd()) {
    $html .= "<tr>
        <td  class='header-data'>{$partsquery->getColumnVal('partsidnumber')}</td>
        <td class='header-data'>{$partsquery->getColumnVal('description_identificationparts')}</td>
        <td class='header-data'>{$partsquery->getColumnVal('article_identificationparts')}</td>
        <td class='header-data'>{$partsquery->getColumnVal('color_identificationparts')}</td>
        <td class='header-data'>{$partsquery->getColumnVal('material_identificationparts')}</td>
        <td class='header-data'>{$partsquery->getColumnVal('cmcreportnumber_identificationparts')}</td>
        <td class='header-data'>{$partsquery->getColumnVal('cmcreportdate_identificationparts')}</td>
    </tr>";
    $partsquery->moveNext();
}
$html .= '</tbody></table>';

//ce mark example

$localisationppemarking = $row['localisationppemarking'];
$sizeexamplecemark = $row['sizeexamplecemark'];
$manufacturerlogoid = $row['manufacturerlogoid'];
$filenamelogo = $row['filenamelogo'];
$monthyearprod = $row['monthyearprod'];
$serialbatchnumber = $row['serialbatchnumber'];
$standarduse = $row['standarduse'];
$symbolsaddreq = $row['symbolsaddreq'];
$proddescription = $row['qualchecktext'];
$organismnumber = $row['organismnumber'];
$cemarkup = $row['cemarkupload'];
$filenamelogowithpath = "logos/" . $filenamelogo;



// Connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);
$querytdfile = "SELECT idtdfileattached, filename_fileattached, description_fileattached FROM tdfileattached WHERE iddata_td = ? AND description_fileattached = 'CE mark example'";
$stmt = $conn->prepare($querytdfile);
$stmt->bind_param("i", $idtd);
$stmt->execute();
$resulttdfile = $stmt->get_result();

$fileUploaded = $resulttdfile->num_rows > 0;
$fileDetails = $resulttdfile->fetch_assoc();

$html .= <<<HTML
<table>
    <thead>
        <tr>
            <th colspan="2">ESEMPIO DI MARCATURA CE / EXAMPLE OF CE MARKING </th>
        </tr>
    </thead>
    <tbody>
HTML;

if ($cemarkup == 'Y' && $fileUploaded) {
    $filePath = 'uploadtddocuments/' . htmlspecialchars($fileDetails['filename_fileattached']);
    $fileDescription = htmlspecialchars($fileDetails['description_fileattached']);
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    $html .= <<<HTML
        <tr>
            <td class="first-column">FILE (se diverso da immagine il file è caricato nello zip come allegato)</td>
            <td>
HTML;

    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
        $html .= "<img src='$filePath' alt='$fileDescription' style='max-height: 200px;'>";
    } else {
        $html .= "<a href='$filePath' target='_blank'>$fileDescription</a>";
    }

    $html .= <<<HTML
            </td>
        </tr>
HTML;
} else {
    $html .= <<<HTML
        <tr>
            <td class="first-column">Posizione della marcatura sul DPI</td>
            <td class="header-data">{$localisationppemarking}</td>
        </tr>
        <tr>
            <td class="first-column">Marchio del fabbricante</td>
            <td><img src="{$filenamelogowithpath}" style="max-height: 80px;"></td>
        </tr>
        <tr>
            <td class="first-column">Codice Articolo</td>
            <td class="header-data">$description</td>
        </tr>
        <tr>
            <td class="first-column">Indirizzo del fabbricante</td>
            <td class="header-data">$address - $country</td>
        </tr>
        <tr>
            <td class="first-column">Misura</td>
            <td class="header-data">{$sizeexamplecemark}</td>
        </tr>
        <tr>
            <td class="first-column">Mese ed anno di produzione</td>
            <td class="header-data">{$monthyearprod}</td>
        </tr>
        <tr>
            <td class="first-column">Numero di serie e/o di lotto</td>
            <td class="header-data">{$serialbatchnumber}</td>
        </tr>
        <tr>
            <td class="first-column">Numero ed anno della norma armonizzata utilizzata</td>
            <td class="header-data">{$standarduse}</td>
        </tr>
        <tr>
            <td class="first-column">Simbolo/i dei requisiti supplementari</td>
            <td class="header-data">{$symbolsaddreq}</td>
        </tr>
        <tr>
            <td class="first-column">Marcatura CE</td>
            <td>
            <img src="/cmccopiaoriginale/public/assets/images/ce.jpg" alt="CE Image" style="width:100px;">

            </td>
        </tr>
    </tbody>
</table>
HTML;
}



$stmt->close();
$conn->close();



if (!is_null($organismnumber)) {
    $html .= " Numero Organismo: $organismnumber";
}

$html .= <<<HTML
            </td>
        </tr>

    </tbody>
</table>
HTML;;

//mezzi di controllo

$html .= <<<HTML

<table>
    <thead>
        <tr>
            <th colspan="1">MEZZI DI CONTROLLO E PROVA IN PRODUZIONE PER GARANTIRE LA CONFORMITÀ / MEANS USED DURING THE PRODUCTION TO ENSURE THE CONFORMITY</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            
            <td class="header-data">$proddescription</td>
        </tr>
       
    </tbody>
</table>
HTML;


//imballaggio

$packaging = $row['packaging'];
$declarconformity = $row['declarconformity'];
$webaddress = $row['webaddress'];
if ($declarconformity == 'declarone') {
    $declartext = $declarone;
} else {
    $declartext = $declartwo;
}


$html .= <<<HTML

<table>
    <thead>
        <tr>
            <th colspan="2">IMBALLAGGIO / PACKAGING - DICHIARAZIONE DI CONFORMITÀ UE / EU DECLARATION OF CONFORMITY</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="first-column">Imballaggio</td>
            <td class="header-data">{$packaging}</td>
        </tr>
        <tr>
            <td class="first-column">Dichiarazione di conformità UE</td>
            <td class="header-data">{$declartext}</td>
        </tr>
        <tr>
            <td class="first-column">Indirizzo del sito web</td>
            <td class="header-data">{$webaddress}</td>
        </tr>

    </tbody>
</table>
HTML;


// chiusura



$html .= '</tbody></table></body></html>';

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
$fileName = $trftdnumber . '-' . 'Rev.' . $tdrev . '-' . $timestamp . '.pdf';
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
