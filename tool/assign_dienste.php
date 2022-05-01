<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/spiele.php";

$mannschaften = loadMannschaften();
$spiele = loadSpiele("1=1", "anwurf, mannschaft"); 

?>
<table border="1" cellpadding="3" cellspacing="3">
    <tr>
        <th>Datum</th>
        <th>Gegner</th>
        <th>Heimspiel</th>
<?php
foreach($mannschaften as $mannschaft){
    echo "<td>".$mannschaft->getName()."</td>";
}
?>
    </tr>
<?php
foreach($spiele as $spiel){
    echo "<tr>";
    echo "<td>".$spiel->getAnwurf()->format('d.m.Y H:i')."</td>";
    echo "<td>".$spiel->getGegner()."</td>";
    echo "<td align=\"center\">".($spiel->isHeimspiel()?"Ja":"Nein")."</td>";
    foreach($mannschaften as $mannschaft){
        $checkBoxID = $spiel->getNuligaID()."-".$mannschaft->getID();
        echo "<td>";
        echo "<input type=\"checkbox\" id=\"Z-$checkBoxID\"><label for=\"Z-$checkBoxID\">Z</label><br>";
        echo "<input type=\"checkbox\" id=\"S-$checkBoxID\"><label for=\"S-$checkBoxID\">S</label><br>";
        echo "</td>";
    }
    echo "</tr>";
}
?>
</table>