function initCounterValues(){
    // wichtig!
    // Das array mit den Mannschaften muss separat befüllt initialisiert werden
    mannschaften.forEach(mannschaft => setDienstCounter(mannschaft));
}

jQuery( document ).ready(function() {
    initCounterValues();
});

function assignDienst(dienstID, mannschaft, assign){
    var data = {
        'action': assign?'dienst_zuweisen':'dienst_entfernen',
        'dienst': dienstID,
        'mannschaft': mannschaft
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data);

    disableOtherCheckboxes(dienstID, mannschaft, assign);
    setDienstCounter(mannschaft);
}
function disableOtherCheckboxes(dienstID, mannschaft, assign){
    checkBoxName = "Dienst-"+dienstID;
    otherCheckBoxes = document.getElementsByName(checkBoxName);
    for(i=0; i<otherCheckBoxes.length; i++){
        otherCheckBoxes[i].disabled = assign;
    }
    // immer die aktive CheckBox aktivieren
    activeID = checkBoxName+"-"+mannschaft;
    document.getElementById(activeID).disabled = false;
}
function setDienstCounter(mannschaft){

    auf_abbau_counter = jQuery("span[counter='Auf- und Abbau'][mannschaft='"+mannschaft+"']")[0];
    auf_abbau_counter.innerText = countForMannschaft(mannschaft, "Aufbau") + countForMannschaft(mannschaft, "Abbau");
    
    heim_counter = jQuery("span[counter='Heim'][mannschaft='"+mannschaft+"']")[0];
    heim_counter.innerText = countForMannschaft(mannschaft, "Zeitnehmer") + countForMannschaft(mannschaft, "Sekretär");
    
    catering_counter = jQuery("span[counter='Catering'][mannschaft='"+mannschaft+"']")[0];
    catering_counter.innerText = countForMannschaft(mannschaft, "Catering");

    auswaerts_counter = jQuery("span[counter='Auswaerts'][mannschaft='"+mannschaft+"']")[0];
    auswaerts_counter.innerText = countForMannschaft(mannschaft, "Zeitnehmer", true) + countForMannschaft(mannschaft, "Sekretär", true);

}

function countForMannschaft(mannschaft, dienstart, auswaerts = false){
    return jQuery("input:checked[mannschaft='"+mannschaft+"'][dienstart='"+dienstart+"'][auswaerts='"+auswaerts+"']").length;
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

function mannschaftDarstellen(mannschaft, darstellen){ 

    if(darstellen){

        jQuery(function($){
            $("#tabelle-dienste-zuweisen tr, td").filter(function(){
                return $(this).attr("mannschaft") == mannschaft;
            }).show();
        });
    }else{
        jQuery(function($){
            $("#tabelle-dienste-zuweisen tr, td").filter(function(){
                return $(this).attr("mannschaft") == mannschaft;
            }).hide();
        });
    }
}
