<?php
class Problem{
    public string $quelle;
    public string $beschreibung;
    public $payload;

    public function __construct(string $quelle, string $beschreibung, $payload = null){
        $this->quelle = $quelle;
        $this->beschreibung = $beschreibung;
        $this->payload = $payload;
    }
}
?>