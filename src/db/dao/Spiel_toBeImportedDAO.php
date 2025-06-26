<?php

require_once __DIR__."/../../io/import/Spiel_toBeImported.php";
require_once __DIR__."/DAO.php";

class Spiel_toBeImportedDAO extends DAO{

    public function fetchAllForUpdate(): array{
        return $this->fetchAll("gegner_id is not null and spielID_alt is not null");
    }
}