<?php

class ZeitlicheDistanz {
    public bool $ueberlappend;
    public bool $vorher;
    public DateInterval $abstand;

    public function getDebugOutput(): string {
        $debugOutput = ($this->vorher?"Vorher":"Nachher").", ";
        if($this->ueberlappend){
            $debugOutput .= "(überlappend) ";
        }
        $debugOutput .= $this->abstand->format("%r%Y.%M.%D %H:%I");
        return $debugOutput;
    }
}
?>