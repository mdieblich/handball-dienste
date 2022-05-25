<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/gegner.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/spiele.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/dienst.php";

$mannschaften = loadMannschaften();
$alleGegner = loadGegner();
$spiele = loadSpieleDeep("1=1", "date(anwurf), heimspiel desc, anwurf, mannschaft"); 

function zaehleDienste(Mannschaft $mannschaft): array{
    global $spiele;
    $anzahl = array();
    foreach(Dienstart::values as $dienstart){
        $anzahl[$dienstart] = 0;
    }
    foreach($spiele as $spiel){
        foreach($spiel->getDienste() as $dienst){
            if($dienst->getMannschaft() == $mannschaft->getID()){
                $anzahl[$dienst->getDienstart()]++;
            }
        }
    }
    return $anzahl;
}

?>
<script>
    function assignDienst(spiel, dienstart, mannschaft, assign){
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState != 4) return;

            if (this.status == 200) {
                // alles gut!
            }
        };
        xhr.open(assign?"PUT":"DELETE", "../api/dienst.php", true);
        var dienst = new Object();
        dienst.spiel = spiel;
        dienst.dienstart = dienstart;
        dienst.mannschaft = mannschaft;
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(dienst));
        disableOtherCheckboxes(spiel, dienstart, mannschaft, assign);
        setDienstCounter(dienstart, mannschaft, assign);
    }
    function disableOtherCheckboxes(spiel, dienstart, mannschaft, assign){
        checkBoxName = dienstart+"-"+spiel;
        otherCheckBoxes = document.getElementsByName(checkBoxName);
        for(i=0; i<otherCheckBoxes.length; i++){
            otherCheckBoxes[i].disabled = assign;
        }
        // immer die aktive CheckBox aktivieren
        activeID = checkBoxName+"-"+mannschaft;
        document.getElementById(activeID).disabled = false;
    }
    function setDienstCounter(dienstart, mannschaft, assign){
        id = dienstart.substring(0,1)+"-counter-"+mannschaft;
        previousValue = parseInt(document.getElementById(id).innerText);
        if(assign){
            // erhöhen
            document.getElementById(id).innerText = previousValue + 1;
        } else{
            // abziehen
            document.getElementById(id).innerText = previousValue - 1;
        }
    }

    function highlightGames(
        spiel_id_vorher, highlightColorVorher,
        spiel_id_gleichzeitig,
        spiel_id_nachher, highlightColorNachher) {
        enableHighlight(spiel_id_vorher, highlightColorVorher);
        enableHighlight(spiel_id_gleichzeitig, "#fdd");
        enableHighlight(spiel_id_nachher, highlightColorNachher);
    }

    function resetHighlight(spiel_id_vorher, spiel_id_gleichzeitig, spiel_id_nachher){
        disableHighlight(spiel_id_vorher);
        disableHighlight(spiel_id_gleichzeitig);
        disableHighlight(spiel_id_nachher);
    }
    
    function enableHighlight(spiel_id, highlightColor){
        if(spiel_id === null){
            return;
        } 
        document.getElementById("spiel-"+spiel_id+"-anwurf").style.backgroundColor = highlightColor;
        document.getElementById("spiel-"+spiel_id+"-halle").style.backgroundColor = highlightColor;
        document.getElementById("spiel-"+spiel_id+"-mannschaft").style.backgroundColor = highlightColor;
        document.getElementById("spiel-"+spiel_id+"-gegner").style.backgroundColor = highlightColor;
    }
    function disableHighlight(spiel_id){
        if(spiel_id === null){
            return;
        }
        document.getElementById("spiel-"+spiel_id+"-anwurf").style.backgroundColor = "inherit";
        document.getElementById("spiel-"+spiel_id+"-halle").style.backgroundColor = "inherit";
        document.getElementById("spiel-"+spiel_id+"-mannschaft").style.backgroundColor = "inherit";
        document.getElementById("spiel-"+spiel_id+"-gegner").style.backgroundColor = "inherit";
    }
</script>
<table border="0" cellpadding="3" cellspacing="3">
    <tr style="background-color:#ddddff; position: sticky; top: 0">
        <th>Spiel-Nr.</th>
        <th>Datum</th>
        <th>Halle</th>
        <th>Heim</th>
        <th>Auswärts</th>
<?php
foreach($mannschaften as $mannschaft){
    $anzahlDienste = zaehleDienste($mannschaft);
    echo "<td>".$mannschaft->getName()."<br>";
    foreach($anzahlDienste as $dienstart => $anzahl){
        $dienstartKurz = substr($dienstart,0,1);
        echo $dienstartKurz.": <span id=\"$dienstartKurz-counter-".$mannschaft->getID()."\">".$anzahl."</span><br>"; 
    }
    echo "</td>";
}
?>
    </tr>
<?php

class NahgelegeneSpiele {
    public ?Spiel $vorher = null;
    public ?Spiel $gleichzeitig = null;
    public ?Spiel $nachher = null;

    public function getVorherID(): ?string{
        return $this->getOptionalID($this->vorher);
    }
    public function getGleichzeitigID(): ?string{
        return $this->getOptionalID($this->gleichzeitig);
    }
    public function getNachherID(): ?string{
        return $this->getOptionalID($this->nachher);
    }

    private function getOptionalID(?Spiel $spiel): ?string{
        if(!isset($spiel)) {
            return "null";
        }
        return $spiel->getID();
    }
}

function findNahgelegeneSpiele($zuPruefendesSpiel, $mannschaft): NahgelegeneSpiele {
    global $spiele;

    $nahgelegeneSpiele = new NahgelegeneSpiele();
    $distanzVorher = null;
    $distanzNachher = null;
    foreach($spiele as $spiel){
        // TODO das geht definitiv einfacher: Alle Spiele als Array in Mannschaft
        if($spiel->getMannschaft() != $mannschaft->getID()){
            continue;
        }
        $zeitlicheDistanz = $spiel->getZeitlicheDistanz($zuPruefendesSpiel);
        if($zeitlicheDistanz->ueberlappend){
            $nahgelegeneSpiele->gleichzeitig = $spiel;
        } else {
            if($zeitlicheDistanz->isVorher()){
                if($zeitlicheDistanz->isNaeher($distanzVorher)){
                    $distanzVorher = $zeitlicheDistanz;
                    $nahgelegeneSpiele->vorher = $spiel;
                }
            } else {
                if($zeitlicheDistanz->isNaeher($distanzNachher)){
                    $distanzNachher = $zeitlicheDistanz;
                    $nahgelegeneSpiele->nachher = $spiel;
                }
            }
        }
    }
    return $nahgelegeneSpiele;
}

function isAmGleichenTag(Spiel $a, Spiel $b): bool {
    if(!isset($a) || !isset($b)){
        return false;
    }
    return $a->getAnwurf()->format("Y-m-d") == $b->getAnwurf()->format("Y-m-d");
}

foreach($spiele as $spiel){
    $gegner = $alleGegner[$spiel->getGegner()];
    $zeitnehmerDienst = $spiel->getDienst("Zeitnehmer");
    $sekretaerDienst = $spiel->getDienst("Sekretär");
    $backgroundColor = $spiel->getAnwurf()->format("w")==6?"#eeeeee":"#eeeeff";
    echo "<tr style=\"background-color:$backgroundColor\">";
    echo "<td>".$spiel->getSpielNr()."</td>";
    echo "<td id=\"spiel-".$spiel->getID()."-anwurf\">".$spiel->getAnwurf()->format('d.m.Y H:i')."</td>";
    echo "<td id=\"spiel-".$spiel->getID()."-halle\">".$spiel->getHalle()."</td>";

    $zelleMannschaft = "<td id=\"spiel-".$spiel->getID()."-mannschaft\">".$mannschaften[$spiel->getMannschaft()]->getName()."</td>";
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
        $nahgelegeneSpiele = findNahgelegeneSpiele($spiel, $mannschaft);
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
                if(isAmGleichenTag($spiel, $nahgelegeneSpiele->vorher)){
                    $highlightColorVorher = "#ffd";
                    $hatSpielAmGleichenTag = true;
                    if($spiel->getHalle() == $nahgelegeneSpiele->vorher->getHalle()){
                        $highlightColorVorher = "#dfd";
                        $hatSpielinGleicherHalle = true;
                    }
                }    
            }
            
            if(isset($nahgelegeneSpiele->nachher)){
                if(isAmGleichenTag($spiel, $nahgelegeneSpiele->nachher)){
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

        $cellContent = "";
        if($spiel->isHeimspiel()){
            if($gegner->stelltSekretearBeiHeimspiel()){
                $cellContent = $checkboxZeitnehmer.$checkboxSekretaer;
            } else {
                $cellContent = $checkboxZeitnehmer;
            }
        } else {
            if($gegner->stelltSekretearBeiHeimspiel()){
                $cellContent = "";
            } else {
                $cellContent = $checkboxSekretaer;
            }
        }
        
        echo "<td "
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
}
?>
</table>