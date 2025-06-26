<?php

require_once __DIR__."/../../handball/Spiel.php";

class Spiel_toBeImported{
    public DateTime $importDatum;
    
    public int $id;
    public int $spielNr;
    public int $meldung_id;
    public string $gegnerName;
    public ?DateTime $anwurf = null;
    public string $halle;
    public bool $heimspiel;

    public ?int $gegner_id; // ID des Gegners in der Datenbank, nachdem er gefunden wurde

    public ?bool $istNeuesSpiel = null; // Flag, ob es sich um ein neues Spiel handelt, das noch nicht in der Datenbank ist
    public ?int $spielID_alt = null; // ID des alten Spiels, falls es aktualisiert wird
    
    public function __construct(){
        $this->importDatum = new DateTime();
    }

    public function toUpdateArray(): array{
        $values = array();
        $values['anwurf'] = $this->anwurf->format('Y-m-d H:i:s');
        $values['halle'] = $this->halle;
        $values['heimspiel'] = $this->heimspiel;
        return $values;
    }

    // TODO Umwandlung in ein Spiel-Objekt
}