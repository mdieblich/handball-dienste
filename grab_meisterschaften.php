<?php
require_once __DIR__."/wordpress/import/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";

$ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(74851);

$nuliga_meisterschaften = $ligeneinteilung->getMeisterschaften("Turnerkreis Nippes");

foreach($nuliga_meisterschaften as $nuliga_meisterschaft){
    var_dump($nuliga_meisterschaft);
}

?>