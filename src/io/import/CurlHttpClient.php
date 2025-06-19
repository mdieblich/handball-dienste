<?php


require_once __DIR__."/../../log/Log.php";

    
class CurlHttpClient implements HttpClient {
    protected Log $logfile;
        
    public function __construct(Log $logfile = null) {
        if(is_null($logfile)) { 
            $this->logfile = new NoLog();
        } else {
            $this->logfile = $logfile;
        }
    }
    public function fetch(string $url): string {
        $this->logfile->log("Lade Daten von ".$url);
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
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        $data = curl_exec($ch);
        if($data === false){
            $errorMessage = "Fehler beim Laden von \"$url\": ".curl_error($ch);
            $this->logfile->log($errorMessage);
            curl_close($ch);
            throw new Exception($errorMessage);
        }
        curl_close($ch);
        return $data;
    }
}