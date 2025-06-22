<?php

require_once __DIR__."/Mannschaft.php";

class MannschaftsListe{
    public array $mannschaften = array();

    public function __construct(array $mannschaften){
        $this->mannschaften = $mannschaften;
    }
    
    public function getIDs(): array {
        $ids = array();
        foreach($this->mannschaften as $mannschaft){
            $ids[] = $mannschaft->id;
        }
        return $ids;
    }

    public function getMeisterschaften(): array {
        $meisterschaften = array();
        foreach($this->mannschaften as $mannschaft){
            foreach($mannschaft->meldungen as $meldung){
                $meisterschaft = $meldung->meisterschaft;
                $meisterschaften[$meisterschaft->id] = $meisterschaft;
            }
        }
        return $meisterschaften;
    }
    
    public function getMannschaftFromName(string $name): ?Mannschaft{
        foreach($this->mannschaften as $mannschaft){
            if($mannschaft->getName() === $name){
                return $mannschaft;
            }
        }
        return null;
    }
    
    public function createNuLigaMannschaftsBezeichnungen(): array{
        $nuligaBezeichnungen = array();
        foreach($this->mannschaften as $mannschaft){
            $bezeichnung = $mannschaft->createNuLigaMannschaftsBezeichnung();
            $nuligaBezeichnungen[$bezeichnung] = $mannschaft;
        }
        return $nuligaBezeichnungen;
    }

    public function findMeldungByNuligaIDs(int $nuligaLigaID, int $nuligaTeamID): ?Mannschaft {
        foreach($this->mannschaften as $mannschaft){
            foreach($mannschaft->meldungen as $meldung){
                if($meldung->nuligaLigaID === $nuligaLigaID && $meldung->nuligaTeamID === $nuligaTeamID){
                    return $meldung->mannschaft;
                }
            }
        }
        return null;
    }
}