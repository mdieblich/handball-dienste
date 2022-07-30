<?php

require_once __DIR__."/SpielAenderung.php";

require_once __DIR__."/../Mannschaft.php";
require_once __DIR__."/../Spiel.php";
require_once __DIR__."/../Dienst.php";

class DienstAenderungsPlan{
    private DienstDAO $dao;
    private array $mannschaften;
    private $geaenderteDienste = array();
    private $geaenderteSpiele = array();
    private $entfalleneDienste = array();

    public function __construct(array $mannschaften){
        $this->dao = new DienstDAO();
        $this->mannschaften = $mannschaften;
        
        foreach($mannschaften as $mannschaft){
            $this->geaenderteDienste[$mannschaft->id] = array();
            $this->entfalleneDienste[$mannschaft->id] = array();
        }
    }

    public function registerSpielAenderung(Spiel $alt, Spiel $neu){ 
        $this->geaenderteSpiele[$alt->id] = new SpielAenderung($alt, $neu);
        foreach($alt->dienste as $dienst){
            array_push($this->geaenderteDienste[$dienst->mannschaft->id], $dienst);
        }
    }

    public function registerEntfalleneDienste(array $dienste){
        foreach($dienste as $dienst){
            $this->registerEntfallenenDienst($dienst);
        }
    }
    

    public function registerEntfallenenDienst(Dienst $dienst){
        if(empty($dienst->mannschaft)){
            return;
        }
        $this->entfalleneDienste[$dienst->mannschaft->id][$dienst->spiel->id] = $dienst;
    }

    public function sendEmails(){
        foreach($this->mannschaften as $mannschaft){
            if($this->brauchtKeineNachricht($mannschaft)){
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

    private function brauchtKeineNachricht($mannschaft): bool{
        return $this->hatKeineGeandertenDienste($mannschaft) && $this->hatKeineEntfallenenDienste($mannschaft);
    }

    private function hatKeineGeandertenDienste(Mannschaft $mannschaft): bool{
        return count($this->geaenderteDienste[$mannschaft->id]) == 0;
    }

    private function hatKeineEntfallenenDienste(Mannschaft $mannschaft): bool{
        return count($this->entfalleneDienste[$mannschaft->id]) == 0;
    }

    private function createMessageForMannschaft(Mannschaft $mannschaft): string{
        $message = 
            "<p>Hallo ".$mannschaft->getName().",</p>"
            ."<p>es haben sich Spiele geändert, bei denen ihr Dienste übernehmt:</p>";
        
        $spieleUndDienste = $this->getGeaenderteSpieleUndDienste($mannschaft);

        foreach($spieleUndDienste as $spielID => $dienstarten){
            if(array_key_exists($spielID, $this->geaenderteSpiele)){
                $spielAenderung = $this->geaenderteSpiele[$spielID];
            }
            if(array_key_exists($spielID, $this->entfalleneDienste[$mannschaft->id])){
                $entfallenerDienst = $this->entfalleneDienste[$mannschaft->id][$spielID];
            }

            $message .= "<div style='padding-left:2em'>";
            if(isset($spielAenderung)){
                $message .= "<b>".$spielAenderung->alt->getBegegnungsbezeichnung()."</b>";
            } else {
                $message .= "<b>".$entfallenerDienst->spiel->getBegegnungsbezeichnung()."</b>";
            }
            
            $message .= "<ul>";
            if(isset($spielAenderung)){
                $message .= "<li>ÄNDERUNG: ".$spielAenderung->getAenderung()."</li>";
            }
            $message .= "<li>EURE DIENSTE: ".implode(", ", $dienstarten)."</li>";
            if(isset($entfallenerDienst)){
                $message .= "<li>ES ENTFÄLLT: ".$entfallenerDienst->dienstart."</li>";
            }
            $message .= "</ul>";
            $message .= "</div>";
        }

        $message .= 
            "<p>Viele Grüße<br>"
            ."Euer Nippesbot</p>";

        return $message;
    }

    private function getGeaenderteSpieleUndDienste(Mannschaft $mannschaft): array{
        
        $spieleUndDienste = array();

        // Geänderte Spiele
        foreach($this->geaenderteDienste[$mannschaft->id] as $dienst){
            $spielID = $dienst->spiel->id;
            if(empty($spieleUndDienste[$spielID])){
                $spieleUndDienste[$spielID] = array();
            }
            array_push($spieleUndDienste[$spielID], $dienst->dienstart);
        }

        // Entfallene Dienste
        foreach($this->entfalleneDienste[$mannschaft->id] as $entfallenerDienst){
            $spielID = $entfallenerDienst->spiel->id;
            if(empty($spieleUndDienste[$spielID])){
                $spieleUndDienste[$spielID] = array();
            }
            array_push($spieleUndDienste[$spielID], $entfallenerDienst->dienstart);

        }

        return $spieleUndDienste;
    }
}
?>