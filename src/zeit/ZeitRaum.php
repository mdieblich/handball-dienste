<?php

require_once __DIR__."/ZeitlicheDistanz.php";

class ZeitRaum {
    public DateTime $start;
    public DateTime $ende;

    public function __construct(DateTime $start, DateTime $ende) {
        $this->start = $start;
        $this->ende = $ende;
    }
}
?>