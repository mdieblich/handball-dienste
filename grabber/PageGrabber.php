<?php
// you can add anoother curl options too
// see here - http://php.net/manual/en/function.curl-setopt.php
function get_dataa($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
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

function getDOMFromSite(string $url): DomDocument{
    $html = get_dataa($url);
    
    $dom = new DomDocument();
    @ $dom->loadHTML($html);
    return $dom;
}


function extractTabellenZeilen($tabelle): array {
    $tabellenZeile = array();
    foreach ($tabelle->childNodes as $childNode){
        if($childNode->nodeName === "tr"){
            $tabellenZeile[] = $childNode;
        }
    }
    return $tabellenZeile;
}

?>