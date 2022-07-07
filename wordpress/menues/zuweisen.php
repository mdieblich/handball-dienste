<?php
require_once __DIR__."/../entity/dienst.php";
require_once __DIR__."/../entity/spieleliste.php";
require_once __DIR__."/../dao/DienstDAO.php";

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
    $dienstDAO->insertDienst($_POST['spiel'], $_POST['dienstart'], $_POST['mannschaft']);
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
    require_once __DIR__."/../dao/mannschaft.php";
    require_once __DIR__."/../dao/SpielDAO.php";
    $mannschaften = loadMannschaften();
    $gegnerDAO = new GegnerDAO();
    $spielDAO = new SpielDAO();
    $gegnerDAO->loadGegner();
    $alleGegner = $gegnerDAO->getAlleGegner();
    $spieleListe = new SpieleListe( $spielDAO->loadSpieleDeep() ); 
 ?>
<div class="wrap">
    <div style="float:right; width: 200px; background-color:#ddddff; padding: 5px">
    Filter
    <?php foreach ($mannschaften as $mannschaft) { ?>
        <br>
        <input type="checkbox" name="filter-<?php echo $mannschaft->getID();?>" checked onchange="mannschaftDarstellen(<?php echo $mannschaft->getID();?>, this.checked)" id="filter-<?php echo $mannschaft->getID();?>">
        <label for="filter-<?php echo $mannschaft->getID();?>"><?php echo $mannschaft->getKurzname(); ?></label>
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
foreach($mannschaften as $mannschaft){
    $anzahlDienste = $spieleListe->zaehleDienste($mannschaft);
    echo "<td mannschaft=\"".$mannschaft->getID()."\">".$mannschaft->getName()."<br>";
    foreach($anzahlDienste as $dienstart => $anzahl){
        $dienstartKurz = substr($dienstart,0,1);
        echo $dienstartKurz.": <span id=\"$dienstartKurz-counter-".$mannschaft->getID()."\">".$anzahl."</span><br>"; 
    }
    echo "</td>";
}
?>
    </tr>
<?php

$vorherigesSpiel = null;
$zeilenFarbePrimaer = true;
foreach($spieleListe->getSpiele() as $spiel){
    $anwurf = $spiel->getAnwurf();
    $mannschaftDesSpiels = $mannschaften[$spiel->getMannschaft()];
    $gegner = $alleGegner[$spiel->getGegner()];
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
    echo "<tr style=\"background-color:$backgroundColor\" mannschaft=\"".$mannschaftDesSpiels->getID()."\">";
    echo "<td>".$spiel->getSpielNr()."</td>";
    if(isset($anwurf)){
        echo "<td id=\"spiel-".$spiel->getID()."-anwurf\">".$anwurf->format("d.m.Y ");
        $uhrzeit = $anwurf->format("H:i");
        if($uhrzeit !== "00:00"){
           echo $uhrzeit;
        }else{
           echo "<span style='color:red'>$uhrzeit</span>";
        }
        echo "</td>";
    }else {
        echo "<td id=\"spiel-".$spiel->getID()."-anwurf\">Termin offen</td>";
    }
    echo "<td id=\"spiel-".$spiel->getID()."-halle\">".$spiel->getHalle()."</td>";

    $zelleMannschaft = "<td id=\"spiel-".$spiel->getID()."-mannschaft\">".$mannschaftDesSpiels->getName()."</td>";
    $zelleGegner = "<td "
        ."id=\"spiel-".$spiel->getID()."-gegner\" "
        .($gegner->stelltSekretearBeiHeimspiel()?"title='Stellt Sekretär in deren Halle'":"")
        .">".$gegner->getName()."</td>";
    if($spiel->isHeimspiel()){
        echo $zelleMannschaft;
        echo $zelleGegner;
    }
    else{
        echo $zelleGegner;
        echo $zelleMannschaft;
    }
    foreach($mannschaften as $mannschaft){
        $backgroundColor = "inherit";
        $highlightColorVorher = "#bbf";
        $highlightColorNachher = "#bbf";
        $textColor = "black";
        $tooltip = "";
        $nahgelegeneSpiele = $spieleListe->findNahgelegeneSpiele($spiel, $mannschaft);
        if($spiel->getMannschaft() == $mannschaft->getID()){
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
                    if($spiel->getHalle() == $nahgelegeneSpiele->vorher->getHalle()){
                        $highlightColorVorher = "#dfd";
                        $hatSpielinGleicherHalle = true;
                    }
                }    
            }
            
            if(isset($nahgelegeneSpiele->nachher)){
                if($spiel->isAmGleichenTag($nahgelegeneSpiele->nachher)){
                    $highlightColorNachher = "#ffd";
                    $hatSpielAmGleichenTag = true;
                    if($spiel->getHalle() == $nahgelegeneSpiele->nachher->getHalle()){
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
        $checkBoxID = $spiel->getID()."-".$mannschaft->getID();
        $zeitnehmerChecked = "";
        if(isset($zeitnehmerDienst)){
            if( $zeitnehmerDienst->getMannschaft() == $mannschaft->getID()){
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
            "name=\"Zeitnehmer-".$spiel->getID()."\"".
            "id=\"Zeitnehmer-$checkBoxID\" ".
            "onclick=\"assignDienst(".$spiel->getID().",'".Dienstart::ZEITNEHMER."',".$mannschaft->getID().", this.checked)\"".
            " $zeitnehmerChecked>".
            "<label for=\"Zeitnehmer-$checkBoxID\">Z</label><br>";
            
        $sekretaerChecked = "";
        if(isset($sekretaerDienst)){
            if($sekretaerDienst->getMannschaft() == $mannschaft->getID()){
                // wir haben den Dienst!
                $sekretaerChecked = "checked";
            } else{
                // eine andere Mannschaft hat den Dienst
                $sekretaerChecked = "disabled";
            }
        }
        $checkboxSekretaer = "<input type=\"checkbox\" ".
        "name=\"Sekretär-".$spiel->getID()."\"".
        "id=\"Sekretär-$checkBoxID\" ".
        "onclick=\"assignDienst(".$spiel->getID().",'".Dienstart::SEKRETAER."',".$mannschaft->getID().", this.checked)\"".
        " $sekretaerChecked>".
        "<label for=\"Sekretär-$checkBoxID\">S</label><br>";
            
        $cateringChecked = "";
        if(isset($cateringDienst)){
            if($cateringDienst->getMannschaft() == $mannschaft->getID()){
                // wir haben den Dienst!
                $cateringChecked = "checked";
            } else{
                // eine andere Mannschaft hat den Dienst
                $cateringChecked = "disabled";
            }
        }
        $checkboxCatering = "<input type=\"checkbox\" ".
        "name=\"Catering-".$spiel->getID()."\"".
        "id=\"Catering-$checkBoxID\" ".
        "onclick=\"assignDienst(".$spiel->getID().",'".Dienstart::CATERING."',".$mannschaft->getID().", this.checked)\"".
        " $cateringChecked>".
        "<label for=\"Catering-$checkBoxID\">C</label><br>";

        // Zelleninhalt zusammenbauen
        $cellContent = "";
        if($spiel->isHeimspiel()){
            if($gegner->stelltSekretearBeiHeimspiel()){
                $cellContent = $checkboxZeitnehmer.$checkboxSekretaer;
            } else {
                $cellContent = $checkboxZeitnehmer;
            }
            $cellContent .= $checkboxCatering;
        } else {
            if($gegner->stelltSekretearBeiHeimspiel()){
                $cellContent = "";
            } else {
                $cellContent = $checkboxSekretaer;
            }
        }
        
        echo "<td "
            ."mannschaft=\"".$mannschaft->getID()."\""
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