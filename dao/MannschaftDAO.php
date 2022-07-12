<?php
require_once __dir__."/../handball/Mannschaft.php";
require_once __dir__."/../handball/MannschaftsListe.php";
require_once __DIR__."/DAO.php";
require_once __dir__."/MannschaftsMeldungDAO.php";

class MannschaftDAO extends DAO{

    public function loadMannschaften(): MannschaftsListe{
        $mannschaften = $this->fetchAll(null, "jugendklasse, nummer, geschlecht");
        return new MannschaftsListe($mannschaften);
    }
}

?>