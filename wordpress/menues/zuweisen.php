<?php
require_once __DIR__."/../handball/Dienst.php";
require_once __DIR__."/../handball/Spieleliste.php";
require_once __DIR__."/../dao/MannschaftDAO.php";
require_once __DIR__."/../dao/DienstDAO.php";

require_once __DIR__."/../service/SpielService.php";

$hook_zuweisen;
function addDiensteZuweisenKonfiguration(){
    global $hook_zuweisen;
    $hook_zuweisen = add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
    
    add_action( 'admin_enqueue_scripts', 'enqueue_dienste_js' );
}
add_action( 'wp_ajax_dienst_zuweisen', 'dienst_zuweisen' );
add_action( 'wp_ajax_dienst_entfernen', 'dienst_entfernen' );

function enqueue_dienste_js($hook){
    global $hook_zuweisen;
    if ( $hook !== $hook_zuweisen ) {
        return;
    }
    wp_enqueue_script(
        'dienste-script',
        plugins_url( '/zuweisen.js', __FILE__ ),
        array( 'jquery' ),
        '1.0.0'
    );
}

function dienst_zuweisen(){
    $dienstDAO = new DienstDAO();
    
    $dienst = new Dienst();
    $dienst->spiel_id = $_POST['spiel'];
    $dienst->dienstart = $_POST['dienstart'];
    $dienst->mannschaft_id = $_POST['mannschaft'];
    $dienstDAO->insert($dienst);
    http_response_code(200);
    wp_die();
}
function dienst_entfernen(){
    $dienstDAO = new DienstDAO();
    $dienstDAO->deleteDienst($_POST['spiel'], $_POST['dienstart'], $_POST['mannschaft']);
    http_response_code(200);
    wp_die();
}

function displayDiensteZuweisen(){
    $mannschaftDAO = new MannschaftDAO();
    $spielService = new SpielService();

    $mannschaftsListe = $mannschaftDAO->loadMannschaften();
    $spieleListe = $spielService->loadSpieleMitDiensten();
 ?>
<div class="wrap">
    <div style="float:right; width: 200px; background-color:#ddddff; padding: 5px">
    Filter
    <?php foreach ($mannschaftsListe->mannschaften as $mannschaft) { ?>
        <br>
        <input type="checkbox" name="filter-<?= $mannschaft->id;?>" checked onchange="mannschaftDarstellen(<?= $mannschaft->id;?>, this.checked)" id="filter-<?= $mannschaft->id;?>">
        <label for="filter-<?= $mannschaft->id;?>"><?= $mannschaft->getKurzname(); ?></label>
    <?php } ?>
    </div>
    <h1>Dienste zuweisen</h1>
    Die Eingaben der Checkboxen werden direkt gespeichert.
    <table cellpadding="3" cellspacing="3" id="tabelle-dienste-zuweisen">
    <tr style="background-color:#ddddff; position: sticky; top: 32px">
        <th>Spiel-Nr.</th>
        <th>Datum</th>
        <th>Halle</th>
        <th>Heim</th>
        <th>Auswärts</th>
        <?php
foreach($mannschaftsListe->mannschaften as $mannschaft){
    $anzahlDienste = $spieleListe->zaehleDienste($mannschaft);
    echo "<td mannschaft=\"".$mannschaft->id."\">".$mannschaft->getKurzname()."<br>";
    foreach($anzahlDienste as $dienstart => $anzahl){
        $dienstartKurz = substr($dienstart,0,1);
        echo $dienstartKurz.": <span id=\"$dienstartKurz-counter-".$mannschaft->id."\">".$anzahl."</span><br>"; 
    }
    echo "</td>";
}
?>
    </tr>
<?php
$vorherigesSpiel = null;
$zeilenFarbePrimaer = true;
foreach($spieleListe->spiele as $spiel){
    $anwurf = $spiel->anwurf;
    $mannschaftDesSpiels = $spiel->mannschaft;
    $gegner = $spiel->gegner;
    $zeitnehmerDienst = $spiel->getDienst(Dienstart::ZEITNEHMER);
    $sekretaerDienst = $spiel->getDienst(Dienstart::SEKRETAER);
    $cateringDienst = $spiel->getDienst(Dienstart::CATERING);
    if(isset($anwurf)){
        if(!$spiel->isAmGleichenTag($vorherigesSpiel)){
            $zeilenFarbePrimaer = !$zeilenFarbePrimaer;
        }
        $backgroundColor = $zeilenFarbePrimaer?"#ddddff":"#dddddd";
    }
    else {
        $backgroundColor = "#ffffff";
    }
    echo "<tr style=\"background-color:$backgroundColor\" mannschaft=\"".$mannschaftDesSpiels->id."\">";
    echo "<td>".$spiel->spielNr."</td>";
    if(isset($anwurf)){
        echo "<td id=\"spiel-".$spiel->id."-anwurf\">".$anwurf->format("d.m.Y ");
        $uhrzeit = $anwurf->format("H:i");
        if($uhrzeit !== "00:00"){
           echo $uhrzeit;
        }else{
           echo "<span style='color:red'>$uhrzeit</span>";
        }
        echo "</td>";
    }else {
        echo "<td id=\"spiel-".$spiel->id."-anwurf\">Termin offen</td>";
    }
    echo "<td id=\"spiel-".$spiel->id."-halle\">".$spiel->halle."</td>";

    $zelleMannschaft = "<td id=\"spiel-".$spiel->id."-mannschaft\">".$mannschaftDesSpiels->getName()."</td>";
    $zelleGegner = "<td "
        ."id=\"spiel-".$spiel->id."-gegner\" "
        .($gegner->stelltSekretaerBeiHeimspiel ? "title='Stellt Sekretär in deren Halle'" : "")
        .">".$gegner->getName()."</td>";
    if($spiel->heimspiel){
        echo $zelleMannschaft;
        echo $zelleGegner;
    }
    else{
        echo $zelleGegner;
        echo $zelleMannschaft;
    }
    foreach($mannschaftsListe->mannschaften as $mannschaft){
        $backgroundColor = "inherit";
        $highlightColorVorher = "#bbf";
        $highlightColorNachher = "#bbf";
        $textColor = "black";
        $tooltip = "";
        $nahgelegeneSpiele = $spieleListe->findNahgelegeneSpiele($spiel, $mannschaft);
        if($spiel->mannschaft->id == $mannschaft->id){
            // TODO Warnung wegen eigenem Spiel bei Anklicken
            $textColor = "silver";
            $tooltip = "Eigenes Spiel";
        } else if(isset($nahgelegeneSpiele->gleichzeitig)) {
            // TODO Warnung wegen gleichzeitigem Spiel
            $textColor = "silver";
            $tooltip = "Gleichzeitiges Spiel";
        } else {
            $hatSpielAmGleichenTag = false;
            $hatSpielinGleicherHalle = false;
            
            if(isset($nahgelegeneSpiele->vorher)){
                if($spiel->isAmGleichenTag($nahgelegeneSpiele->vorher)){
                    $highlightColorVorher = "#ffd";
                    $hatSpielAmGleichenTag = true;
                    if($spiel->halle == $nahgelegeneSpiele->vorher->halle){
                        $highlightColorVorher = "#dfd";
                        $hatSpielinGleicherHalle = true;
                    }
                }    
            }
            
            if(isset($nahgelegeneSpiele->nachher)){
                if($spiel->isAmGleichenTag($nahgelegeneSpiele->nachher)){
                    $highlightColorNachher = "#ffd";
                    $hatSpielAmGleichenTag = true;
                    if($spiel->halle == $nahgelegeneSpiele->nachher->halle){
                        $highlightColorNachher = "#dfd";
                        $hatSpielinGleicherHalle = true;
                    }
                }    
            }

            if($hatSpielAmGleichenTag){
                $tooltip = "Spiel am gleichen Tag";
                $backgroundColor = "#ffd";
                if($hatSpielinGleicherHalle){
                    $tooltip .= "\nSpiel in gleicher Halle";
                    $backgroundColor = "#dfd";
                }
            }
        }
        $checkBoxID = $spiel->id."-".$mannschaft->id;
        $zeitnehmerChecked = "";
        if(isset($zeitnehmerDienst)){
            if( $zeitnehmerDienst->mannschaft->id == $mannschaft->id){
                // wir haben den Dienst!
                $zeitnehmerChecked = "checked";
            }
            else{
                // eine andere Mannschaft hat den Dienst
                $zeitnehmerChecked = "disabled";
            }
        }
        $checkboxZeitnehmer = 
            "<input type=\"checkbox\" ".
            "name=\"Zeitnehmer-".$spiel->id."\"".
            "id=\"Zeitnehmer-$checkBoxID\" ".
            "onclick=\"assignDienst(".$spiel->id.",'".Dienstart::ZEITNEHMER."',".$mannschaft->id.", this.checked)\"".
            " $zeitnehmerChecked>".
            "<label for=\"Zeitnehmer-$checkBoxID\">Z</label><br>";
            
        $sekretaerChecked = "";
        if(isset($sekretaerDienst)){
            if($sekretaerDienst->mannschaft->id == $mannschaft->id){
                // wir haben den Dienst!
                $sekretaerChecked = "checked";
            } else{
                // eine andere Mannschaft hat den Dienst
                $sekretaerChecked = "disabled";
            }
        }
        $checkboxSekretaer = "<input type=\"checkbox\" ".
        "name=\"Sekretär-".$spiel->id."\"".
        "id=\"Sekretär-$checkBoxID\" ".
        "onclick=\"assignDienst(".$spiel->id.",'".Dienstart::SEKRETAER."',".$mannschaft->id.", this.checked)\"".
        " $sekretaerChecked>".
        "<label for=\"Sekretär-$checkBoxID\">S</label><br>";
            
        $cateringChecked = "";
        if(isset($cateringDienst)){
            if($cateringDienst->mannschaft->id == $mannschaft->id){
                // wir haben den Dienst!
                $cateringChecked = "checked";
            } else{
                // eine andere Mannschaft hat den Dienst
                $cateringChecked = "disabled";
            }
        }
        $checkboxCatering = "<input type=\"checkbox\" ".
        "name=\"Catering-".$spiel->id."\"".
        "id=\"Catering-$checkBoxID\" ".
        "onclick=\"assignDienst(".$spiel->id.",'".Dienstart::CATERING."',".$mannschaft->id.", this.checked)\"".
        " $cateringChecked>".
        "<label for=\"Catering-$checkBoxID\">C</label><br>";

        // Zelleninhalt zusammenbauen
        $cellContent = "";
        if($spiel->heimspiel){
            if($gegner->stelltSekretaerBeiHeimspiel){
                $cellContent = $checkboxZeitnehmer.$checkboxSekretaer;
            } else {
                $cellContent = $checkboxZeitnehmer;
            }
            $cellContent .= $checkboxCatering;
        } else {
            if($gegner->stelltSekretaerBeiHeimspiel){
                $cellContent = "";
            } else {
                $cellContent = $checkboxSekretaer;
            }
        }
        
        echo "<td "
            ."mannschaft=\"".$mannschaft->id."\""
            ."style=\"background-color:$backgroundColor; color:$textColor; text-align:center\" "
            ."title=\"$tooltip\" "
            ."onmouseover=\"highlightGames("
                .$nahgelegeneSpiele->getVorherID().", '$highlightColorVorher', "
                .$nahgelegeneSpiele->getGleichzeitigID().", "
                .$nahgelegeneSpiele->getNachherID().", '$highlightColorNachher')\" "
            ."onmouseout=\"resetHighlight("
                .$nahgelegeneSpiele->getVorherID().","
                .$nahgelegeneSpiele->getGleichzeitigID().", "
                .$nahgelegeneSpiele->getNachherID().")\" "
            .">$cellContent</td>";
    }
    echo "</tr>";
    $vorherigesSpiel = $spiel;
}
?>
    </table>
</div>
 <?php
}
?>