<?php
class Spieler{ 
    public string $name;
}

class Mannschaft{
    public Spieler $kapitaen;
}

$brings = new Spieler();
$brings->name = "Sven";

$nippes = new Mannschaft();
$nippes->kapitaen = $brings;
$nippes->kapitaen_id = 3;

$rc = new ReflectionClass ("Mannschaft");
var_dump($rc->hasProperty("kapitaen"));
var_dump($rc->hasProperty("kapitaen_id"));
$rp_k = $rc->getProperty("kapitaen");
var_dump($rp_k);

$rp_ki = $rc->getProperty("kapitaen_id");
var_dump($rp_ki);
// $rp = new ReflectionProperty("Mannschaft", "kapitaen_id");
// echo $rp->getType()->getName();
?>
<br>ENDE