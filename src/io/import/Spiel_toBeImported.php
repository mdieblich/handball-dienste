<?php

require_once __DIR__."/../../handball/Spiel.php";

class Spiel_toBeImported{
    
    public int $id;
    public int $spielNr;
    public int $meldung_id;
    public string $gegnerName;
    public ?int $gegner_id; // ID des Gegners in der Datenbank, nachdem er gefunden wurde
    public ?DateTime $anwurf = null;
    public string $halle;
    public bool $heimspiel;
    public DateTime $importDatum;

    public function __construct(){
        $this->importDatum = new DateTime();
    }

    // TODO Umwandlung in ein Spiel-Objekt
}