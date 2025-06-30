<?php

require_once __DIR__."/../Spiel.php";

class DienstAenderung {
    public int $id;
    public int $dienstID;

    // TODO istNeu in extra klasse
    public bool $istNeu = false;

    public ?DateTime $anwurfVorher = null;
    public ?string $halleVorher = null;

    public static function create(int $dienstID, Spiel $spiel_vorher): DienstAenderung {
        $aenderung = new DienstAenderung();
        $aenderung->dienstID = $dienstID;
        $aenderung->anwurfVorher = $spiel_vorher->anwurf;
        $aenderung->halleVorher = $spiel_vorher->halle;
        return $aenderung;
    }

}