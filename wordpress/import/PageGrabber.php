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