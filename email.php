<?php
class Email {

    const SPIELNUMMER_SUCHE = "/BISHER: Nr\. (\d*),.*Turnerkreis Nippes.*/";

    private $content;

    public function __construct(string $content){
        $this->content = $content;
    }

    public function getSpielNummer(): ?int {
        $spielnummer_gefunden = preg_match(self::SPIELNUMMER_SUCHE, $this->content, $matches);
        if($spielnummer_gefunden){
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

    public function getDebugOutput(): string {
        return 
            "<div>".
            "<b>Spielnummer:</b> ".$this->getSpielNummer().
            "<pre style='padding-left:1em; font-style:italic'>".$this->getBisherZeile()."</pre>\n".
            "</div>";
    }

}
?>