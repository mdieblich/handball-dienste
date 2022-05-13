<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/spiel.php";

function assertGleichzeitig(Spiel $a, Spiel $b){
    echo "<pre>";
    echo "Spiel 1: ".$a->getSpielzeitDebugOutput()."\n";
    echo "Spiel 2: ".$b->getSpielzeitDebugOutput()."\n";
    echo $a->isGleichzeitig($b)?"OK":"FEHLER";
    echo "</pre>";
}
function assertNichtGleichzeitig(Spiel $a, Spiel $b){
    echo "<pre>";
    echo "Spiel 1: ".$a->getSpielzeitDebugOutput()."\n";
    echo "Spiel 2: ".$b->getSpielzeitDebugOutput()."\n";
    echo $a->isGleichzeitig($b)?"FEHLER":"OK";
    echo "</pre>";
}

function assertTimeDiff(Spiel $a, Spiel $b, ZeitlicheDistanz $expectedTimeDiff){
    echo "<pre>";
    echo "Spiel 1: ".$a->getSpielzeitDebugOutput()."\n";
    echo "Spiel 2: ".$b->getSpielzeitDebugOutput()."\n";
    $expectedDiffFormatted = $expectedTimeDiff->abstand->format("%Y.%M.%D %H:%I");
    // echo "Erwartet: ".$expectedDiffFormatted."\n";
    echo "Erwartet: ".$expectedTimeDiff->getDebugOutput()."\n";
    $actualTimeDiff = $a->getZeitlicheDistanz($b);
    $actualDiffFormatted = $actualTimeDiff->abstand->format("%Y.%M.%D %H:%I");
    echo "Erhalten: ".$actualTimeDiff->getDebugOutput()."\n";
    echo $actualDiffFormatted == $expectedDiffFormatted?"OK":"FEHLER";
    echo "</pre>";
}

echo "Gleichzeitigkeit: ";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    assertGleichzeitig($a, $b);
}
echo "<hr>";

echo "<h1>Heimspiel-Vergleiche</h1>";

echo "Später, überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 16:20:00", "halle"=>"1000"));
    assertGleichzeitig($a, $b);
}
echo "<hr>";

echo "Später, nicht überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 19:00:00", "halle"=>"1000"));
    assertNichtGleichzeitig($a, $b);
}
echo "<hr>";

echo "Früher, überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 13:40:00", "halle"=>"1000"));
    assertGleichzeitig($a, $b);
}
echo "<hr>";

echo "Früher, nicht überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 11:00:00", "halle"=>"1000"));
    assertNichtGleichzeitig($a, $b);
}
echo "<hr>";

/// 
echo "<h1>Auswärtsspiel-Vergleiche</h1>";
echo "Später, überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"2000"));
    $b = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 20:00:00", "halle"=>"3000"));
    assertGleichzeitig($a, $b);
}
echo "<hr>";

echo "Später, nicht überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"2000"));
    $b = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 21:00:00", "halle"=>"3000"));
    assertNichtGleichzeitig($a, $b);
}
echo "<hr>";

echo "Früher, überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"2000"));
    $b = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 10:00:00", "halle"=>"3000"));
    assertGleichzeitig($a, $b);
}
echo "<hr>";

echo "Früher, nicht überlappend: ";
{
    $a = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"2000"));
    $b = new Spiel(array("heimspiel"=>"0", "anwurf"=>"2022-05-01 09:00:00", "halle"=>"3000"));
    assertNichtGleichzeitig($a, $b);
}
echo "<hr>";

echo "<h1>Zeitlicher Abstand</h1>";
echo "<h2>Heimspiele</h2>";
echo "30 Minuten später";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 17:00:00", "halle"=>"1000"));
    $expectedTimeDiff = new ZeitlicheDistanz();
    $expectedTimeDiff->abstand = new DateInterval("PT30M");
    $expectedTimeDiff->ueberlappend = false;
    $expectedTimeDiff->vorher = false;
    assertTimeDiff($a, $b, $expectedTimeDiff);
}
echo "90 Minuten früher";
{
    $a = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 15:00:00", "halle"=>"1000"));
    $b = new Spiel(array("heimspiel"=>"1", "anwurf"=>"2022-05-01 12:00:00", "halle"=>"1000"));
    
    $expectedTimeDiff = new ZeitlicheDistanz();
    $expectedTimeDiff->abstand = new DateInterval("PT1H30M");
    $expectedTimeDiff->abstand->invert = true;
    $expectedTimeDiff->ueberlappend = false;
    $expectedTimeDiff->vorher = true;
    assertTimeDiff($a, $b, $expectedTimeDiff);
}
?>