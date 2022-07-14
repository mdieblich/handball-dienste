<?php
require_once __dir__."/../handball/Gegner.php";
require_once __DIR__."/DAO.php";

class GegnerDAO extends DAO{

    public function findGegner(int $id): ?Gegner{
        return $this->fetch("id=$id");
    }

    public function loadGegner(string $where = null, string $orderBy = "verein ASC, nummer ASC"): array{
        return $this->fetchAll($where, $orderBy);
    }

    // TODO insertGegner muss ein Gegner-Objekt erhalten
    public function insertGegner(string $name, string $geschlecht, string $liga): Gegner{

        $gegner = new Gegner();
        $gegner->verein = $this->getVereinFromName($name);
        $gegner->nummer = $this->getNummerFromName($name);
        $gegner->geschlecht = $geschlecht;
        $gegner->liga = $liga;

        $this->insert($gegner);
        return $gegner;
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

    function findOrInsertGegner(string $name, string $geschlecht, string $liga): Gegner{

        $verein = $this->getVereinFromName($name);
        $nummer = $this->getNummerFromName($name);

        $gegner = $this->fetch("verein=\"$verein\" AND nummer=$nummer AND geschlecht=\"$geschlecht\" AND liga=\"$liga\"");
        if(isset($gegner)){
            return $gegner;
            
        }
        // Nix gefunden - einfügen!
        return $this->insertGegner($name, $geschlecht, $liga);
    }
}
?>