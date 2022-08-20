<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../handball/Spiel.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpielerPlusFile {
    private Spreadsheet $spreadsheet;

    public function __construct(array $spiele){
        $this->spreadsheet = new Spreadsheet();
        $this->fillContent($spiele);
    }

    private function fillContent(array $spiele) {
        $this->fillHeaderRow();
        $this->fillWithGames($spiele);
    }

    private function fillHeaderRow(){
        $this->fillRow(1, [
            "Spieltyp",
            "Gegner",
            "Start-Datum", 
            "End-Datum", 
            "Start-Zeit", "Treffen (Optional)",
            "End-Zeit (Optional)",
            "Heimspiel",
            "Gelände / Räumlichkeiten",
            "Adresse (optional)",
            "Infos zum Spiel",
            "Teilname",
            "Nominierung",
            "Zu-/Absagen bis (Stunden vor dem Termin)", "Erinnerung zum Zu-/Absagen (Stunden vor dem Termin)"
        ]);
    }
    
    private function fillRow(int $row, array $values){
        foreach($values as $i=>$value){
            $cell_id = $this->cellID($row, $i+1);
            $this->spreadsheet->getActiveSheet()->setCellValue($cell_id, $value);            
        }
    }

    private function cellID(int $row, int $column): string{
        return chr($column+64).$row;
    }

    private function fillWithGames(array $spiele){
        $row = 2;
        foreach($spiele as $spiel){
            $this->fillRow($row++, [
                "Spiel",                        // Spieltyp
                $spiel->gegner->getName(),                  // Gegner
                $spiel->anwurf->format("Y-m-d"), // Start-Datum
                $spiel->anwurf->format("Y-m-d"), // End-Datum
                $spiel->anwurf->format("H:i:s"),                             // Start-Zeit
                $spiel->getAbfahrt()->format("H:i:s"),                             // Treffen (Optional)
                $spiel->getRueckkehr()->format("H:i:s"),                     // End-Zeit (Optional)
                $spiel->heimspiel?"ja":"nein",                           // Heimspiel
                "In der Halle",                 // Gelände / Räumlichkeiten
                "", // Adresse (optional), TODO: Hallenadresses auslesen
                "Nuliga-Halle: ".$spiel->halle,   // Infos zum Spiel
                "Spieler müssen zusagen",       // Teilnahme
                "",             // Nominierung
                24*7,           // Frist Zusage
                24*8            // Erinnerung Zusage
            ]);
        }
    }

    public function provideDownload() {
        $writer = new Xlsx($this->spreadsheet);
        $file = 'SpielerPlusExport.xlsx';
        $writer->save($file);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        readfile($file);

        unlink($file);
    }
} 

?>