<?php
class Email {

    const SPIELNUMMER_SUCHE = "/BISHER: Nr\. (\d*),.*Turnerkreis Nippes.*/";
    const HVM_LIGA_SUCHE = "/Mittelrhein.* - (.*)/";
    const HKKR_LIGA_SUCHE = "/Köln\/Rheinberg.* - (.*)/";

    private $content;

    public function __construct(string $content){
        $this->content = $content;
    }

    public function getSpielNummer(): ?int {
        $liga_gefunden = preg_match(self::SPIELNUMMER_SUCHE, $this->content, $matches);
        if($liga_gefunden){
          return $matches[1];
        }
        return null;
    }

    public function getBisherZeile(): ?string {
        $lines = preg_split("/\r\n|\n|\r/", $this->content);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, "BISHER") !== false) {
                return $line;
            }
        }
        return null;
    }

    public function getLigaZeile(): ?string {
        $hvm_liga_gefunden = preg_match(self::HVM_LIGA_SUCHE, $this->content, $matches);
        if($hvm_liga_gefunden){
          return $matches[1];
        }

        $hkkr_liga_gefunden = preg_match(self::HKKR_LIGA_SUCHE, $this->content, $matches);
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