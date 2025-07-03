<?php

require_once __dir__."/../dao/dienst/NeuerDienstDAO.php";
require_once __dir__."/../dao/DienstDAO.php";

class NeuerDienstService{
    private DienstDAO $dienstDAO;
    private NeuerDienstDAO $neuerDienstDAO;

    public function __construct($dbhandle){
        $this->dienstDAO = new DienstDAO($dbhandle);
        $this->neuerDienstDAO = new NeuerDienstDAO($dbhandle);
    }

    public function insertAll(array $neueDienste): void{
        foreach($neueDienste as $neuerDienst){
            $this->dienstDAO->insert($neuerDienst->dienst);
            $this->neuerDienstDAO->insert($neuerDienst);
        }
    }
}