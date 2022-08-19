<?php

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpielerPlusFile {
    private Spreadsheet $spreadsheet;

    public function __construct(){
        $this->spreadsheet = new Spreadsheet();
        $this->fillContent();
    }

    private function fillContent() {
        $this->fillHeaderRow();
        $this->fillWithGames();
    }

    private function fillHeaderRow(){
        $this->fillRow(1, [
            "Spieltyp",
            "Gegner",
            "Start-Datum", "End-Datum (Optional)", 
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
            $this->spreadsheet->getActiveSheet()->setCellValue($this->cellID($row, $i+1), $value);            
        }
    }

    private function cellID(int $row, int $column): string{
        return chr($column+64).$row;
    }

    private function fillWithGames(){
        $this->fillRow(2, [
            "Spiel",                        // Spieltyp
            "SpielerPlus",                  // Gegner
            "01.09.2020", "01.09.2020",     // Start-Datum, End-Datum (Optional)
            "14:00:00", "13:00:00",         // Start-Zeit, Treffen (Optional)
            "14:30:00",                     // End-Zeit (Optional)
            "ja",                           // Heimspiel
            "In der Halle",                 // Gelände / Räumlichkeiten
            "Im Löwental 35, 45239 Essen, Deutschland", // Adresse (optional)
            "Vorsicht, es handelt sich um die beste Mannschaft der Welt. :)",   // Infos zum Spiel
            "Spieler müssen zusagen",       // Teilnahme
            "Trainer, Spieler",             // Nominierung
            "0", "6"                        // Frist Zusage, Erinnerung Zusage
        ]);
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