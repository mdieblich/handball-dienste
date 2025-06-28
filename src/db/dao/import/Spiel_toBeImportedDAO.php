<?php

require_once __DIR__."/../../../io/import/Spiel_toBeImported.php";
require_once __DIR__."/../DAO.php";

class Spiel_toBeImportedDAO extends DAO{

    public function fetchAllForDienstAenderungen(): array{
        return $this->fetchAll("gegner_id is not null AND spielID_alt is not null AND dienstAenderungenErstellt = 0");
    }

    public function fetchAllForUpdate(): array{
        return $this->fetchAll("gegner_id is not null AND spielID_alt is not null and dienstAenderungenErstellt = 1");
    }
    public function fetchAllNewOnes(): array{
        return $this->fetchAll("gegner_id is not null AND istNeuesSpiel=1");
    }
}