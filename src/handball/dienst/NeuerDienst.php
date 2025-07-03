<?php
require_once __DIR__."/../Dienst.php";

class NeuerDienst {
    public int $id;
    public Dienst $dienst; public int $dienst_id;
    public string $grund;

    public function __construct(Dienst $dienst, string $grund) {
        $this->dienst = $dienst;
        $this->grund = $grund;
    }
}