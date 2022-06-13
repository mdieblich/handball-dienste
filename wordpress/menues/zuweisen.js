function assignDienst(spiel, dienstart, mannschaft, assign){
    jQuery(document).ready(function($) {

        var data = {
            'action': assign?'dienst_zuweisen':'dienst_entfernen',
            'spiel': spiel,
            'dienstart': dienstart,
            'mannschaft': mannschaft
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data);
    });

    disableOtherCheckboxes(spiel, dienstart, mannschaft, assign);
    setDienstCounter(dienstart, mannschaft, assign);
}
function disableOtherCheckboxes(spiel, dienstart, mannschaft, assign){
    checkBoxName = dienstart+"-"+spiel;
    otherCheckBoxes = document.getElementsByName(checkBoxName);
    for(i=0; i<otherCheckBoxes.length; i++){
        otherCheckBoxes[i].disabled = assign;
    }
    // immer die aktive CheckBox aktivieren
    activeID = checkBoxName+"-"+mannschaft;
    document.getElementById(activeID).disabled = false;
}
function setDienstCounter(dienstart, mannschaft, assign){
    id = dienstart.substring(0,1)+"-counter-"+mannschaft;
    previousValue = parseInt(document.getElementById(id).innerText);
    if(assign){
        // erhöhen
        document.getElementById(id).innerText = previousValue + 1;
    } else{
        // abziehen
        document.getElementById(id).innerText = previousValue - 1;
    }
}

function highlightGames(
    spiel_id_vorher, highlightColorVorher,
    spiel_id_gleichzeitig,
    spiel_id_nachher, highlightColorNachher) {
    enableHighlight(spiel_id_vorher, highlightColorVorher);
    enableHighlight(spiel_id_gleichzeitig, "#fdd");
    enableHighlight(spiel_id_nachher, highlightColorNachher);
}

function resetHighlight(spiel_id_vorher, spiel_id_gleichzeitig, spiel_id_nachher){
    disableHighlight(spiel_id_vorher);
    disableHighlight(spiel_id_gleichzeitig);
    disableHighlight(spiel_id_nachher);
}

function enableHighlight(spiel_id, highlightColor){
    if(spiel_id === null){
        return;
    } 
    document.getElementById("spiel-"+spiel_id+"-anwurf"    ).style.backgroundColor = highlightColor;
    document.getElementById("spiel-"+spiel_id+"-halle"     ).style.backgroundColor = highlightColor;
    document.getElementById("spiel-"+spiel_id+"-mannschaft").style.backgroundColor = highlightColor;
    document.getElementById("spiel-"+spiel_id+"-gegner"    ).style.backgroundColor = highlightColor;
}
function disableHighlight(spiel_id){
    if(spiel_id === null){
        return;
    }
    document.getElementById("spiel-"+spiel_id+"-anwurf"    ).style.backgroundColor = "inherit";
    document.getElementById("spiel-"+spiel_id+"-halle"     ).style.backgroundColor = "inherit";
    document.getElementById("spiel-"+spiel_id+"-mannschaft").style.backgroundColor = "inherit";
    document.getElementById("spiel-"+spiel_id+"-gegner"    ).style.backgroundColor = "inherit";
}