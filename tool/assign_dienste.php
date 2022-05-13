<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/spiele.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/dienst.php";

$mannschaften = loadMannschaften();
$spiele = loadSpieleDeep("1=1", "date(anwurf), heimspiel desc, anwurf, mannschaft"); 

function findeGleichzeitigesSpiel(Spiel $zuVergleichendesSpiel, Mannschaft $mannschaft): ?Spiel{
    global $spiele;
    foreach($spiele as $spiel){
        if($spiel->getMannschaft() != $mannschaft->getID()){
            continue; // Kein Spiel der Mannschaft
        }
    }
    return null;
}

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
</script>
<table border="0" cellpadding="3" cellspacing="3">
    <tr style="background-color:#ddddff">
        <th>ID</th>
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

function getGleichzeitigesSpiel($zuPruefendesSpiel, $mannschaft): ?Spiel{
    global $spiele;
    // TODO das geht definitiv einfacher: Alle Spiele als Array in Mannschaft
    foreach($spiele as $spiel){
        if($spiel->getMannschaft() != $mannschaft->getID()){
            continue;
        }
        if($spiel->getZeitlicheDistanz($zuPruefendesSpiel)->ueberlappend){
            return $spiel;
        }
    }
    return null;
}

function getZeitlichNaehstesSpiel($zuPruefendesSpiel, $mannschaft): ?Spiel {
    global $spiele;

    $nahstesSpiel = null;
    $zeitlicheDistanzDesNahstenSpiels = null;
    // TODO das geht definitiv einfacher: Alle Spiele als Array in Mannschaft
    foreach($spiele as $spiel){
        if($spiel->getMannschaft() != $mannschaft->getID()){
            continue;
        }
        $zeitlicheDistanz = $spiel->getZeitlicheDistanz($zuPruefendesSpiel);
        if(!isset($nahstesSpiel) || $zeitlicheDistanz->isAbsolutKleinerAls($zeitlicheDistanzDesNahstenSpiels)){
            $nahstesSpiel = $spiel;
            $zeitlicheDistanzDesNahstenSpiels = $zeitlicheDistanz;
        }
        if($spiel->getZeitlicheDistanz($zuPruefendesSpiel)->ueberlappend){
            return $spiel;
        }
    }
    return $nahstesSpiel;
}

function isAmGleichenTag(Spiel $a, Spiel $b): bool {
    return $a->getAnwurf()->format("Y-m-d") == $b->getAnwurf()->format("Y-m-d");
}

foreach($spiele as $spiel){
    $zeitnehmerDienst = $spiel->getDienst("Zeitnehmer");
    $sekretaerDienst = $spiel->getDienst("Sekretär");
    $backgroundColor = $spiel->getAnwurf()->format("w")==6?"#eeeeee":"#eeeeff";
    echo "<tr style=\"background-color:$backgroundColor\">";
    echo "<td>".$spiel->getID()."</td>";
    echo "<td>".$spiel->getAnwurf()->format('d.m.Y H:i')."</td>";
    echo "<td>".$spiel->getHalle()."</td>";
    if($spiel->isHeimspiel()){
        echo "<td>".$mannschaften[$spiel->getMannschaft()]->getName()."</td>";
        echo "<td>".$spiel->getGegner()."</td>";
    }
    else{
        echo "<td>".$spiel->getGegner()."</td>";
        echo "<td>".$mannschaften[$spiel->getMannschaft()]->getName()."</td>";
    }
    foreach($mannschaften as $mannschaft){
        $backgroundColor = "inherit";
        $textColor = "black";
        $tooltip = "";
        $cellcontent = "";
        $zeitlichNaehstesSpiel = getZeitlichNaehstesSpiel($spiel, $mannschaft);
        $zeitlicheDistanz = $spiel->getZeitlicheDistanz($zeitlichNaehstesSpiel);
        if($spiel->getMannschaft() == $mannschaft->getID()){
            // TODO Warnung wegen eigenem Spiel bei Anklicken
            $textColor = "silver";
            $tooltip = "Eigenes Spiel";
        } else if($zeitlicheDistanz->ueberlappend) {
            // TODO Warnung wegen gleichzeitigem Spiel
            $textColor = "silver";
            $tooltip = "Gleichzeitiges Spiel, ID ".$zeitlichNaehstesSpiel->getID();
        } else {
            if(isAmGleichenTag($spiel, $zeitlichNaehstesSpiel)){
                $backgroundColor = "#ffd";
                if($spiel->getHalle() == $zeitlichNaehstesSpiel->getHalle()){
                    $tooltip = "Spiel am gleichen Tag\nSpiel in gleicher Halle";
                    $backgroundColor = "#dfd";
                }
                else{
                    $tooltip = "Spiel am gleichen Tag";
                }
            }
            else{
                $tooltip = "Zeitlich nahes Spiel: ID ".$zeitlichNaehstesSpiel->getID();
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
        $cellcontent .= "<input type=\"checkbox\" ".
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
        $cellcontent .= "<input type=\"checkbox\" ".
        "name=\"Sekretär-".$spiel->getID()."\"".
        "id=\"Sekretär-$checkBoxID\" ".
        "onclick=\"assignDienst(".$spiel->getID().",'".Dienstart::SEKRETAER."',".$mannschaft->getID().", this.checked)\"".
        " $sekretaerChecked>".
        "<label for=\"Sekretär-$checkBoxID\">S</label><br>";
        
        echo "<td style=\"background-color:$backgroundColor; color:$textColor; text-align:center\" title=\"$tooltip\">$cellcontent</td>";
    }
    echo "</tr>";
}
?>
</table>