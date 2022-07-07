<?php
require_once __dir__."/../entity/Gegner.php";
require_once __DIR__."/DAO.php";

class GegnerDAO extends DAO{
    private $alleGegner = array();

    public function findGegner(int $id): ?Gegner{
        if(!array_key_exists($id, $this->alleGegner)){
            $result = $this->fetch("id=$id");
            if(empty($result)){
                return null;
            }
            $gegner = new Gegner($result);
            $this->alleGegner[$id] = $gegner;
        }
        return $this->alleGegner[$id];
    }

    public function loadGegner($where = "1=1", $orderBy = "verein ASC, nummer ASC"): array{
        $result = $this->fetchAll($where, $orderBy);

        foreach($result as $gegner) {
            $gegnerObj = new Gegner($gegner);
            $this->alleGegner[$gegnerObj->getID()] = $gegnerObj;
        }
        return $this->alleGegner;
    }

    public function getAlleGegner(): array{
        return $this->alleGegner;
    }

    public function insertGegner(string $name, string $geschlecht, string $liga): Gegner{

        $verein = $name;
        $nummer = 1;

        if(str_ends_with($name, " V")){
            $nummer = 5;
            $verein = substr($name, 0, strlen($name)-2);
        } else if(str_ends_with($name, " IV")){
            $nummer = 4;
            $verein = substr($name, 0, strlen($name)-3);
        } else if(str_ends_with($name, " III")){
            $nummer = 3;
            $verein = substr($name, 0, strlen($name)-4);
        } else if(str_ends_with($name, " II")){
            $nummer = 2;
            $verein = substr($name, 0, strlen($name)-3);
        } else if(str_ends_with($name, " I")){
            $nummer = 1;
            $verein = substr($name, 0, strlen($name)-2);
        }
        $verein = trim($verein);

        $params = array(
            'verein' => $verein,
            'nummer' => $nummer,
            'geschlecht' => $geschlecht,
            'liga' => $liga
        );

        $params["id"] = $this->insert($params);

        $newGegner = new Gegner($params);
        $this->alleGegner[$newGegner->getID()] = $newGegner;
        return $newGegner;

  }

    function findOrInsertGegner(string $name, string $geschlecht, string $liga): Gegner{
        foreach($this->alleGegner as $gegner){
            if($gegner->getName() === $name && $gegner->getGeschlecht() === $geschlecht){
                return $gegner;
            }
        }
        // Nix gefunden - einfügen!
        return $this->insertGegner($name, $geschlecht, $liga);
    }
}
?>