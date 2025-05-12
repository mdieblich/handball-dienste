<?php
require_once __DIR__."/../../log/Log.php";
// you can add anoother curl options too
// see here - http://php.net/manual/en/function.curl-setopt.php
function get_dataa($url) {
    $ch = curl_init();
    $timeout = 15;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    // Chrome simulieren
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');

    // SSL bei Bedarf aktivieren
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    
    // Cookies speichern/verwalten
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . "/cookies.txt");

    // Debug
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $data = curl_exec($ch);
    if($data === false){
        throw new Exception("Fehler beim Laden von \"$url\": ".curl_error($ch));
    }
    curl_close($ch);
    return $data;
}

// Testfunktion, kann später wieder gelöscht werden
function DOMinnerHTML(DOMNode $element) 
{ 
    $innerHTML = ""; 
    $children  = $element->childNodes;

    foreach ($children as $child) 
    { 
        $innerHTML .= $element->ownerDocument->saveHTML($child);
    }

    return $innerHTML; 
} 

function getDOMFromSite(string $url, Log $logfile = null): DomDocument{
    if ($logfile === null) {
        $logfile = new NoLog();
    }
    $html = get_dataa($url);
    $logfile->log("HTML Inhalt:\n$html");
    
    $dom = new DomDocument();

    // Interne Fehlerbehandlung aktivieren und vorherige Fehler leeren
    libxml_use_internal_errors(true);

    // HTML laden
    $dom->loadHTML($html);

    // Fehler abrufen
    $errors = libxml_get_errors();

    // Fehlerbereinigung
    libxml_clear_errors();
    libxml_use_internal_errors(false);

    // Fehler anzeigen (optional)
    if(!empty($errors)){
        $logfile->log("Beim Parsen der NuLiga-Seite traten Fehler auf:");
        foreach ($errors as $error) {
            $logfile->log("\t".$error->message);
        }
    }

    return $dom;
}

function sanitizeContent(string $content): string{
    $content = preg_replace('/\s+/', ' ',$content);
    $evilSpace = hex2bin("c2a0"); // das ist ein utf-16 Zeichen. Die Ottos von nuliga geben das falsche Encoding an!
    $content = str_replace($evilSpace, " ", $content);
    $content = trim($content);
    return $content;
}


function extractTabellenZeilen($tabelle): array {
    return extractChildrenByTags($tabelle, "tr");
}

function extractTabellenZellen($zeile): array {
    return extractChildrenByTags($zeile, array("td","th"));
}

function extractChildrenByTags($domElement, $tags): array{

    if(!is_array($tags)){
        $tags = array($tags);
    }

    $children = array();
    foreach ($domElement->childNodes as $childNode){
        if(in_array($childNode->nodeName, $tags)){
            $children[] = $childNode;
        }
    }
    return $children;
}

?>