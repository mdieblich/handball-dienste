<?php

function displaySpieleImport(){
    require_once __DIR__."/dao/mannschaft.php";
    require_once __DIR__."/dao/spiel.php";
    $mannschaften = loadMannschaften();
 ?>
<div class="wrap">

    <h1>Spiele von nuLiga importieren</h1>
    <table>
        <tr>
            <th> Mannschaft </th>
            <th> Importierte Spiele </th>
        </tr>
        <?php foreach($mannschaften as $mannschaft){  ?>
        <tr>
            <td> <?php echo $mannschaft->getName(); ?> </td>
            <td style="text-align:center"> <?php echo countSpiele($mannschaft->getID()); ?> </td>
        </tr>
        <?php } ?>
    </table>
</div>
 <?php
}
?>