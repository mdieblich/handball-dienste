<?php

require_once __DIR__."/SpielAenderung.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../handball/Mannschaft.php";
require_once __DIR__."/../handball/Spiel.php";
require_once __DIR__."/../handball/Dienst.php";

class DienstAenderungsPlan{
    private DienstDAO $dao;
    private array $mannschaften;
    private $geanderteDienste = array();
    private $geanderteSpiele = array();

    public function __construct(array $mannschaften){
        $this->dao = new DienstDAO();
        $this->mannschaften = $mannschaften;
        
        foreach($mannschaften as $mannschaft){
            $this->geanderteDienste[$mannschaft->id] = array();
        }
    }

    public function registerSpielAenderung(Spiel $alt, NuLigaSpiel $neu){ 
        $this->geanderteSpiele[$alt->id] = new SpielAenderung($alt, $neu);
        $bisherigeDienste = $this->dao->loadAllDienste("spiel_id=".$alt->id);
        foreach($bisherigeDienste as $dienst){
            array_push($this->geanderteDienste[$dienst->mannschaft_id], $dienst);
        }
    }

    public function sendEmails(){
        foreach($this->mannschaften as $mannschaft){
            if($this->hatKeineGeandertenDienste($mannschaft)){
                continue;
            }
            
            $mail = init_nippes_mailer();
            $mail->addAddress($mannschaft->email);
            $mail->Subject = "Spielplanänderungen, bei denen ihr Dienste habt";
            $mail->Body = $this->createMessageForMannschaft($mannschaft);
            $mail->isHTML(true);
            $mail->send();
        }
    }

    private function hatKeineGeandertenDienste(Mannschaft $mannschaft): bool{
        return count($this->geanderteDienste[$mannschaft->id]) == 0;
    }

    private function createMessageForMannschaft(Mannschaft $mannschaft): string{
        $message = 
            "<p>Hallo ".$mannschaft->getName().",</p>"
            ."<p>es haben sich Spiele geändert, bei denen ihr Dienste übernehmt:</p>";
        
        $spieleUndDienste = $this->getGeanderteSpieleUndDienste($mannschaft);

        foreach($spieleUndDienste as $spielID => $dienstarten){
            $spielAenderung = $this->geanderteSpiele[$spielID];
            $message .= "<div style='padding-left:2em'>";
            $message .= "<b>".$spielAenderung->getBegegnungsbezeichnung()."</b>";
            $message .= "<ul>";
            $message .= "<li>ÄNDERUNG: ".$spielAenderung->getAenderung()."</li>";
            $message .= "<li>EURE DIENSTE: ".implode(", ", $dienstarten)."</li>";
            $message .= "</ul>";
            $message .= "</div>";
        }

        $message .= 
            "<p>Viele Grüße<br>"
            ."Euer Nippesbot</p>";

        return $message;
    }

    private function getGeanderteSpieleUndDienste(Mannschaft $mannschaft): array{
        
        $spieleUndDienste = array();
        foreach($this->geanderteDienste[$mannschaft->id] as $dienst){
            $spielID = $dienst->spiel->id;
            if(empty($spieleUndDienste[$spielID])){
                $spieleUndDienste[$spielID] = array();
            }
            array_push($spieleUndDienste[$spielID], $dienst->dienstart);
        }
        return $spieleUndDienste;
    }
}
?>