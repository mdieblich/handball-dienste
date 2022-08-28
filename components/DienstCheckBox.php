<?php

require_once __DIR__."/Component.php";
require_once __DIR__."/../handball/Mannschaft.php";
require_once __DIR__."/../handball/Dienst.php";

class DienstCheckBox implements Component{

    private Dienst $dienst;
    private Mannschaft $mannschaft;

    public function __construct(Dienst $dienst, Mannschaft $mannschaft){
        $this->dienst = $dienst;
        $this->mannschaft = $mannschaft;
    }
    
    public function toHTML(): string{
        $kurzform = substr($this->dienst->dienstart, 0, 3);
        $modifier = "";
        if( isset($this->dienst->mannschaft) ) {
            if( $this->dienst->mannschaft->id == $this->mannschaft->id){
                // die Mannschaft hat den Dienst!
                $modifier = "checked";
            }
            else{
                // eine andere Mannschaft hat den Dienst
                $modifier = "disabled";
            }
        };
        $dienstart = $this->dienst->dienstart;
        $mannschaft = $this->mannschaft->id;
        $checkBoxName = "Dienst-".$this->dienst->id;
        $checkBoxID = $checkBoxName."-".$this->mannschaft->id;
        $auswaerts = $this->dienst->spiel->heimspiel ? "false" : "true";

        return "<input ".
            "type=\"checkbox\" ".
            "name=\"$checkBoxName\"".
            "id=\"$checkBoxID\" ".
            "dienstart=\"$dienstart\" ".
            "mannschaft=\"$mannschaft\" ".
            "auswaerts=\"$auswaerts\" ".
            "onclick=\"assignDienst(".$this->dienst->id.",".$this->mannschaft->id.", this.checked)\"".
            "style=\"opacity:1\"".      // Wordpress setzt opacity auf 0.7, was zu Darstellungsproblemen führt
            " $modifier>".
            "<label for=\"$checkBoxID\">$kurzform</label>";
    }
}

?>