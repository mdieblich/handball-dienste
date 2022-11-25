<?php
require_once __dir__."/../handball/Verein.php";
require_once __DIR__."/DAO.php";

class VereinDAO extends DAO{
    
    function findOrInsertName(string $name): Verein{

        $newVerein = new Verein();
        $newVerein->name = $name;

        return $this->findOrInsert($newVerein);
    }
}
?>