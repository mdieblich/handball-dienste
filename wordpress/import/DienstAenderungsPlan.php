<?php

require_once __DIR__."/SpielAenderung.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../entity/mannschaft.php";
require_once __DIR__."/../entity/spiel.php";
require_once __DIR__."/../entity/dienst.php";
require_once __DIR__."/../dao/gegner.php";

class DienstAenderungsPlan{
    private DienstDAO $dao;
    private array $mannschaften;
    private GegnerDAO $gegnerDAO;
    private $geanderteDienste = array();
    private $geanderteSpiele = array();

    public function __construct(array $mannschaften, GegnerDAO $gegnerDAO){
        $this->dao = new DienstDAO();
        $this->mannschaften = $mannschaften;
        $this->gegnerDAO = $gegnerDAO;
        
        foreach($mannschaften as $mannschaft){
            $this->geanderteDienste[$mannschaft->getID()] = array();
        }
    }

    public function registerSpielAenderung(Spiel $alt, NuLigaSpiel $neu){ 
        $this->geanderteSpiele[$alt->getID()] = new SpielAenderung($alt, $neu);
        $bisherigeDienste = $this->dao->loadAllDienste("spiel=".$alt->getID());
        foreach($bisherigeDienste as $dienst){
            array_push($this->geanderteDienste[$dienst->getMannschaft()], $dienst);
        }
    }

    public function simulateEmails(): string{
        $messages = array();
        foreach($this->mannschaften as $mannschaft){
            if($this->hatKeineGeandertenDienste($mannschaft)){
                continue;
            }
            $messages[$mannschaft->getID()] = $this->createMessageForMannschaft($mannschaft);
        }
        return "<pre>".implode("\n\n--------------\n\n", $messages)."</pre>";
    }

    private function hatKeineGeandertenDienste(Mannschaft $mannschaft): bool{
        return count($this->geanderteDienste[$mannschaft->getID()]) == 0;
    }

    private function createMessageForMannschaft(Mannschaft $mannschaft): string{
        $message = 
            "Hallo ".$mannschaft->getName().",\n"
            ."\n"
            ."es haben sich Spiele geändert, bei denen ihr Dienste übernehmt:\n"
            ."\n";
        
        $spieleUndDienste = $this->getGeanderteSpieleUndDienste($mannschaft);

        foreach($spieleUndDienste as $spielID => $dienstarten){
            $spielAenderung = $this->geanderteSpiele[$spielID];
            $message .= "  ".$spielAenderung->getBegegnungsbezeichnung($this->mannschaften, $this->gegnerDAO)."\n";
            $message .= "    ÄNDERUNG: ".$spielAenderung->getAenderung()."\n";
            $message .= "    EURE DIENSTE: ".implode(", ", $dienstarten)."\n";
        }

        $message .= "\n"
            ."Viele Grüße,\n"
            ."Euer Nippesbot";

        return $message;
    }

    private function getGeanderteSpieleUndDienste(Mannschaft $mannschaft): array{
        
        $spieleUndDienste = array();
        foreach($this->geanderteDienste[$mannschaft->getID()] as $dienst){
            $spielID = $dienst->getSpiel();
            if(empty($spieleUndDienste[$spielID])){
                $spieleUndDienste[$spielID] = array();
            }
            array_push($spieleUndDienste[$spielID], $dienst->getDienstart());
        }
        return $spieleUndDienste;
    }
}
?>