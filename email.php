<?php
class Email {
    private $content;

    public function __construct(string $content){
        $this->content = $content;
    }


    public function getSpielNummer(): ?int {
        $spielnummer_suche = "/BISHER: Nr\. (\d*),.*Turnerkreis Nippes.*/";
        $spielnummer_gefunden = preg_match(
          $spielnummer_suche,
          $this->content, 
        $matches);
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

}
?>