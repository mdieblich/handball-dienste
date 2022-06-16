<?php

function dienst_tabelle_einblenden($content){
    return preg_replace_callback(
        "/<dienste ?(.*)>(.*)<\/dienste>/", 
        'dienste_tabellen_ersetzen', 
        $content
    );
}

function dienste_tabellen_ersetzen(array $matches){
    $kompletterTreffer = $matches[0];
    $attribute = $matches[1];
    $innerHTML = $matches[2];
    return "<pre>".var_export($matches, true)."</pre>";
}
?>