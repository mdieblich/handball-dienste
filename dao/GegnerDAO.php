<?php
require_once __dir__."/../handball/Gegner.php";
require_once __dir__."/../handball/MannschaftsMeldung.php";
require_once __DIR__."/DAO.php";

class GegnerDAO extends DAO{

    public function findGegner(int $id): ?Gegner{
        return $this->fetch("id=$id");
    }

    public function loadGegner(string $where = null, string $orderBy = "verein ASC, nummer ASC"): array{
        return $this->fetchAll($where, $orderBy);
    }

    private function getVereinFromName(string $name): string{
        $verein = $name;

        if(str_ends_with($name, " V")){
            $verein = substr($name, 0, strlen($name)-2);
        } else if(str_ends_with($name, " IV")){
            $verein = substr($name, 0, strlen($name)-3);
        } else if(str_ends_with($name, " III")){
            $verein = substr($name, 0, strlen($name)-4);
        } else if(str_ends_with($name, " II")){
            $verein = substr($name, 0, strlen($name)-3);
        } else if(str_ends_with($name, " I")){
            $verein = substr($name, 0, strlen($name)-2);
        }
        
        $verein = trim($verein);
        return $verein;
    }

    private function getNummerFromName(string $name): int{
        $nummer = 1;

        if(str_ends_with($name, " V")){
            $nummer = 5;
        } else if(str_ends_with($name, " IV")){
            $nummer = 4;
        } else if(str_ends_with($name, " III")){
            $nummer = 3;
        } else if(str_ends_with($name, " II")){
            $nummer = 2;
        } else if(str_ends_with($name, " I")){
            $nummer = 1;
        }
        return $nummer;
    }

    public function insert(object $gegner): int{
        if(!isset($gegner->stelltSekretaerBeiHeimspiel)){
            $gegner->stelltSekretaerBeiHeimspiel = false;
        }
        return parent::insert($gegner);
    }

    function findOrInsertGegner(string $name, MannschaftsMeldung $meldung): Gegner{

        $newGegner = new Gegner();

        $newGegner->verein = $this->getVereinFromName($name);
        $newGegner->nummer = $this->getNummerFromName($name);
        $newGegner->zugehoerigeMeldung = $meldung;

        $oldGegner = $this->findSimilar($newGegner);
        if(isset($oldGegner)){
            return $oldGegner;
        }
        // Nix gefunden - einfügen!
        $this->insert($newGegner);
        return $newGegner;
    }
}
?>