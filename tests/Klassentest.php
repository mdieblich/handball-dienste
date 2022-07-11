<?php
class Spieler{ 
    public string $name;
}

class Mannschaft{
    public Spieler $kapitaen;
    public DateTime $naechstesSpiel;
    public int $anzahl;
    public string $liga;
    public bool $frauen;
}

$brings = new Spieler();
$brings->name = "Sven";

$nippes = new Mannschaft();
$nippes->kapitaen = $brings;
$nippes->kapitaen_id = 3;

$rc = new ReflectionClass ("Mannschaft");
$rp = $rc->getProperty("frauen");
$typ = $rp->getType();
$builtin =  $typ->isBuiltin();
var_dump($builtin);

?>
<br>ENDE