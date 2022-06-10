<?php
require_once __DIR__."/dao/mannschaft.php";
require_once __DIR__."/dao/gegner.php";

$alleGegner = loadGegner();
function findOrInsertGegner(string $name, $liga): Gegner{
    global $alleGegner;
    
    foreach($alleGegner as $gegner){
        if($gegner->getName() === $name){
            return $gegner;
        }
    }
    // Nix gefunden - einfügen!
    $gegner = insertGegner($name, $liga);
    $alleGegner[$gegner->getID()] = $gegner;
    return $gegner;
}

?>