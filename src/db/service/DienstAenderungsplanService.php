<?php

// require_once __dir__."/../dao/dienst/EntfallenerDienstDAO.php";
// require_once __dir__."/../dao/DienstDAO.php";

class DienstAenderungsPlanService{
    // private DienstDAO $dienstDAO;
    // private EntfallenerDienstDAO $entfallenerDienstDAO;

    public function __construct($dbhandle){
    //     $this->dienstDAO = new DienstDAO($dbhandle);
    //     $this->entfallenerDienstDAO = new EntfallenerDienstDAO($dbhandle);
    }

    public function loadFromDB(): DienstAenderungsPlan {
        return new DienstAenderungsPlan([]);
    }
}