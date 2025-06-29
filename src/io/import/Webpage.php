<?php

require_once __DIR__."/../../log/Log.php";

abstract class Webpage {
    protected Log $logfile;

    public string $url;
    protected string $html;
    private DomDocument $dom;
    private DOMXPath $xpath;

    public function __construct(string $url, Log $logfile = null){
        if ($logfile === null) {
            $this->logfile = new NoLog();
        } else {
            $this->logfile = $logfile;
        }

        $this->url = $url;
    }

    protected function getElementById(string $id): DOMElement {
        return $this->getDOM()->getElementById($id);
    }
    protected function getElementsByTagName(string $id): DOMNodeList {
        return $this->getDOM()->getElementsByTagName($id);
    }
    
    
    private function getDOM(): DomDocument{
        if(isset($this->dom)){
            return $this->dom;
        }
        $this->dom = new DomDocument();

        // Interne Fehlerbehandlung aktivieren und vorherige Fehler leeren
        libxml_use_internal_errors(true);

        // HTML laden
        $this->dom->loadHTML($this->getHTML());

        // Fehler abrufen
        $errors = libxml_get_errors();

        // Fehlerbereinigung
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        // Fehler anzeigen (optional)
        if(!empty($errors)){
            $this->logfile->log("Beim Parsen der NuLiga-Seite traten Fehler auf:");
            foreach ($errors as $error) {
                $this->logfile->log("\t".$error->message);
            }
        }
        return $this->dom;
    }

    private function getHTML(): string {
        if(isset($this->html)){
            return $this->html;
        }
        $htmlFromCache = $this->getHTMLFromCache();
        if($htmlFromCache !== null){
            $this->logfile->log("Lade Daten von ".$this->url." (aus gespeicherter HTML-Datei)");
            $this->html = $htmlFromCache;
            return $this->html;
        }
        // Lade die HTML-Seite von der URL
        $this->logfile->log("Lade Daten von ".$this->url." (aus Internet)");
        $htmlFromURL = $this->getHTMLFromURL();
        $this->html = $htmlFromURL;
        
        // und speichere die HTML-Seite lokal
        $cacheFile = $this->saveLocally();
        $this->logfile->log("Daten von ".$this->url." gespeichert in ".$cacheFile);
        return $this->html;
    }

    private function getHTMLFromURL(): string {
        $this->logfile->log("Lade Daten von ".$this->url);
        $ch = curl_init();
        $timeout = 15;
        curl_setopt($ch, CURLOPT_URL, $this->url);
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
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        $data = curl_exec($ch);
        if($data === false){
            $errorMessage = "Fehler beim Laden von \"$this->url\": ".curl_error($ch);
            $this->logfile->log($errorMessage);
            curl_close($ch);
            throw new Exception($errorMessage);
        }
        curl_close($ch);
        return $data;
    }

    protected function query(string $expression, ?DOMNode $contextNode = null): DOMNodeList {
        return $this->getXPath()->query($expression, $contextNode);
    }
    private function getXPath(): DOMXPath{
        if(isset($this->xpath)){
            return $this->xpath;
        }
        
        $this->xpath = new DOMXPath($this->getDOM());
        return $this->xpath;
    }

    protected function extractTabellenZeilen(DOMElement $tabelle): array {
        return $this->extractChildrenByTags($tabelle, "tr");
    }
    
    protected function extractTabellenZellen(DOMElement $zeile): array {
        return $this->extractChildrenByTags($zeile, array("td","th"));
    }
    
    protected function extractChildrenByTags(DOMElement $domElement, $tags): array{
    
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

    protected function sanitizeContent(string $content): string{
        $content = preg_replace('/\s+/', ' ',$content);
        $evilSpace = hex2bin("c2a0"); // das ist ein utf-16 Zeichen. Die Ottos von nuliga geben das falsche Encoding an!
        $content = str_replace($evilSpace, " ", $content);
        $content = trim($content);
        return $content;
    }

    public function saveLocally(): string {
        $directory = $this->getCacheDirectory();
        $filename = $directory.date("Y.m.d - H.i.s").".html";
        file_put_contents($filename, $this->getHTML());
        return $filename;
    }

    private function getCacheDirectory(): string {
        $directory = self::CACHEFILE_BASE_DIRECTORY()."/".static::class."/".$this->getCacheFileIdentifier()."/";
        if(!is_dir($directory)){
            mkdir($directory, 0777, true);
        }
        return $directory;
    }
    public static function CACHEFILE_BASE_DIRECTORY(): string{
        return plugin_dir_path(__FILE__)."cache";
    }

    protected abstract function getCacheFileIdentifier(): string;

    private function getHTMLFromCache(): ?string {
        $directory = $this->getCacheDirectory();
        $files = glob($directory."*.html");
        if(empty($files)){
            return null;
        }
        // die letzte Datei ist die aktuellste
        return file_get_contents($files[count($files)-1]);
    }
}

?>