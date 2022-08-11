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

    public static function MAX_VORHER(): ZeitlicheDistanz{
        $maximaleDistanz = new ZeitlicheDistanz();
        $maximaleDistanz->ueberlappend = false;
        $maximaleDistanz->seconds = -PHP_INT_MAX;
        return $maximaleDistanz;
    }
    public static function MAX_NACHHER(): ZeitlicheDistanz{
        $maximaleDistanz = new ZeitlicheDistanz();
        $maximaleDistanz->ueberlappend = false;
        $maximaleDistanz->seconds = PHP_INT_MAX;
        return $maximaleDistanz;
    }
}
?>