<?php
require_once __DIR__."/Spiel.php";

class NahgelegeneSpiele {

    private Spiel $referenzSpiel;

    public ?Spiel $vorher = null;
    private ZeitlicheDistanz $distanzVorher;
    
    public ?Spiel $gleichzeitig = null;
    
    public ?Spiel $nachher = null;
    private ZeitlicheDistanz $distanzNachher;

    public function __construct(Spiel $referenzSpiel){
        $this->referenzSpiel = $referenzSpiel;
        $this->distanzVorher = ZeitlicheDistanz::MAX_VORHER();
        $this->distanzNachher = ZeitlicheDistanz::MAX_NACHHER();
    }

    public function updateWith(Spiel $spiel){
        $zeitlicheDistanz = $this->referenzSpiel->calculate_distanz_to($spiel);
        if(empty($zeitlicheDistanz)){
            return;
        }
        
        if($zeitlicheDistanz->isUeberlappend()){
            $this->gleichzeitig = $spiel;
            return;
        }
        
        if($zeitlicheDistanz->isVorher()){
            if($zeitlicheDistanz->isNaeher($this->distanzVorher)){
                $this->distanzVorher = $zeitlicheDistanz;
                $this->vorher = $spiel;
            }
        } else {
            if($zeitlicheDistanz->isNaeher($this->distanzNachher)){
                $this->distanzNachher = $zeitlicheDistanz;
                $this->nachher = $spiel;
            }
        }
    }

    public function getVorherID(): ?string{
        return $this->getOptionalID($this->vorher);
    }
    public function getGleichzeitigID(): ?string{
        return $this->getOptionalID($this->gleichzeitig);
    }
    public function getNachherID(): ?string{
        return $this->getOptionalID($this->nachher);
    }

    private function getOptionalID(?Spiel $spiel): ?string{
        if(empty($spiel)) {
            return "null";
        }
        return $spiel->id;
    }
}
?>