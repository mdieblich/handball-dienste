<?php
class Email {
    private $content;

    public function __construct(string $content){
        $this->content = $content;
    }


    public function getSpielNummer(): ?int{
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
      

}
?>