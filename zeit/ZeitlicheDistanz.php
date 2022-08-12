<?php

require_once __DIR__."/ZeitRaum.php";

class ZeitlicheDistanz {
    private int $seconds;

    public function __construct(int $seconds) {
        $this->seconds = $seconds;
    }

    public function isVorher(): bool {
        return $this->seconds < 0;
    }
    
    public function isUeberlappend(): bool{
        return $this->seconds === 0;
    }
    
    public function isNachher(): bool {
        return $this->seconds > 0;
    }
    

    public function isNaeher(?ZeitlicheDistanz $other): bool{
        if(!isset($other)){
            return true;
        }
        return abs($this->seconds) < abs($other->seconds);
    }

    public static function from_a_to_b(ZeitRaum $a, ZeitRaum $b): ZeitlicheDistanz {
        $ueberlappend = $a->ende > $b->start  && $b->ende > $a->start;
        if($a->ende > $b->start  && $b->ende > $a->start){
            return new ZeitlicheDistanz(0);
        }
        
        $a_vor_b = $a->start < $b->start;
        if($a_vor_b){
            return new ZeitlicheDistanz( $b->start->getTimestamp() - $a->ende->getTimestamp());
        } else { // b vor a
            return new ZeitlicheDistanz( $b->ende->getTimestamp() - $a->start->getTimestamp());
        }
    }

    public static function MAX_VORHER(): ZeitlicheDistanz{
        return new ZeitlicheDistanz(-PHP_INT_MAX);
    }
    public static function MAX_NACHHER(): ZeitlicheDistanz{
        return new ZeitlicheDistanz(PHP_INT_MAX);
    }
}
?>