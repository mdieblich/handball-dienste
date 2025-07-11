<?php

require_once __DIR__."/../Spiel.php";

class DienstAenderung {
    public int $id;
    public int $dienst_id;

    public ?DateTime $anwurfVorher = null;
    public ?string $halleVorher = null;

    public static function create(int $dienst_id, Spiel $spiel_vorher): DienstAenderung {
        $aenderung = new DienstAenderung();
        $aenderung->dienst_id = $dienst_id;
        $aenderung->anwurfVorher = $spiel_vorher->anwurf;
        $aenderung->halleVorher = $spiel_vorher->halle;
        return $aenderung;
    }

}