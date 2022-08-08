<?php
require_once __DIR__."/../handball/Dienst.php";
require_once __DIR__."/../handball/SpieleListe.php";
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
    $dienstDAO->assign( $_POST['dienst'], $_POST['mannschaft']);
    http_response_code(200);
    wp_die();
}
function dienst_entfernen(){
    $dienstDAO = new DienstDAO();
    $dienstDAO->unassign( $_POST['dienst']);
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
    <div style="float:right; width: 200px; background-color:#ddd; padding: 5px">
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
    <tr style="background-color:#ddd; position: sticky; top: 32px">
        <th>Spiel-Nr.</th>
        <th>Datum</th>
        <th>Halle</th>
        <th>Heim</th>
        <th>Auswärts</th>
        <?php
foreach($mannschaftsListe->mannschaften as $mannschaft){
    $anzahlDienste = $spieleListe->zaehleDienste($mannschaft);
    echo "<td mannschaft=\"".$mannschaft->id."\">".$mannschaft->getKurzname()."<br>";
    echo "# <span id=\"counter-".$mannschaft->id."\">".$anzahlDienste."</span><br>"; 
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
    if(isset($anwurf)){
        if(!$spiel->isAmGleichenTag($vorherigesSpiel)){
            $zeilenFarbePrimaer = !$zeilenFarbePrimaer;
        }
        $backgroundColor = $zeilenFarbePrimaer?"#ddd":"#eee";
    }
    else {
        $backgroundColor = "#fff";
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

        $cellContent = "";
        foreach(Dienstart::values as $dienstart){
            $dienst = $spiel->getDienst($dienstart);
            if(isset($dienst)){
                $kurzform = substr($dienstart, 0, 3);
                $checked = "";
                if( isset($dienst->mannschaft) ) {
                    if( $dienst->mannschaft->id == $mannschaft->id){
                        // wir haben den Dienst!
                        $checked = "checked";
                    }
                    else{
                        // eine andere Mannschaft hat den Dienst
                        $checked = "disabled";
                    }
                }
                $checkBoxName = "Dienst-".$dienst->id;
                $checkBoxID = $checkBoxName."-".$mannschaft->id;
                $cellContent .= 
                    "<input type=\"checkbox\" ".
                    "name=\"$checkBoxName\"".
                    "id=\"$checkBoxID\" ".
                    "onclick=\"assignDienst(".$dienst->id.",".$mannschaft->id.", this.checked)\"".
                    " $checked>".
                    "<label for=\"$checkBoxID\">$kurzform</label><br>";
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