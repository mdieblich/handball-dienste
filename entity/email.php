<?php
class Email {

    const SPIELNUMMER_SUCHE = "/BISHER: Nr\. (\d*),.*Turnerkreis Nippes.*/";
    const HVM_LIGA_SUCHE = "/Mittelrhein.* - (.*)/";
    const HKKR_LIGA_SUCHE = "/Köln\/Rheinberg.* - (.*)/";

    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(){
        return $this->assoc_array["id"];
    }

    public function getInhalt(){
        return $this->assoc_array["inhalt"];
    }

    public function getSpielNummer(): ?int {
        $liga_gefunden = preg_match(self::SPIELNUMMER_SUCHE, $this->getInhalt(), $matches);
        if($liga_gefunden){
          return $matches[1];
        }
        return null;
    }

    public function getBisherZeile(): ?string {
        $lines = preg_split("/\r\n|\n|\r/", $this->getInhalt());
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, "BISHER") !== false) {
                return $line;
            }
        }
        return null;
    }

    public function getLigaZeile(): ?string {
        $hvm_liga_gefunden = preg_match(self::HVM_LIGA_SUCHE, $this->getInhalt(), $matches);
        if($hvm_liga_gefunden){
          return $matches[1];
        }

        $hkkr_liga_gefunden = preg_match(self::HKKR_LIGA_SUCHE, $this->getInhalt(), $matches);
        if($hkkr_liga_gefunden){
          return $matches[1];
        }

        return null;
    }

    public function getDebugOutput(): string {
        return 
            "<div>".
            "<b>".$this->getLigaZeile()."</b> Nr. ".$this->getSpielNummer().
            "<pre style='padding-left:1em; font-style:italic'>".$this->getBisherZeile()."</pre>\n".
            "</div>";
    }

}
?>