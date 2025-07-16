<?php

require_once __DIR__."/../../handball/Mannschaft.php";
require_once __DIR__."/../../handball/MannschaftsListe.php";
require_once __DIR__."/DAO.php";
require_once __dir__."/MannschaftsMeldungDAO.php";

class MannschaftDAO extends DAO{

    private static ?array $mannschaftsCache = null;

    /**
     * @deprecated message Use getAllWithGlobalCache() instead.
     */
    public function loadMannschaften(): MannschaftsListe{
        $mannschaften = $this->fetchAll(null, "jugendklasse, nummer, geschlecht");
        return new MannschaftsListe($mannschaften);
    }
    
    public function getAllWithGlobalCache(): MannschaftsListe {
        if($this->mannschaftsCache === null){
            $this->mannschaftsCache = $this->fetchAll(null, "jugendklasse, nummer, geschlecht");
        }
        $deepCopy = unserialize(serialize($this->mannschaftsCache));
        return new MannschaftsListe($deepCopy);
    }
}