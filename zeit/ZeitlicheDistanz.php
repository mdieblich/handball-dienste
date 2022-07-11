<?php

class ZeitlicheDistanz {
    public bool $ueberlappend;
    public int $seconds;

    public function isNaeher(?ZeitlicheDistanz $other): bool{
        if(!isset($other)){
            return true;
        }
        return abs($this->seconds) < abs($other->seconds);
    }

    public function isVorher(): bool {
        return $this->seconds < 0;
    }
}
?>