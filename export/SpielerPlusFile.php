<?php

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpielerPlusFile {
    private Spreadsheet $spreadsheet;
    

    public function __construct(){
        $this->spreadsheet = new Spreadsheet();
    }

    public function demoContent(){
        $this->spreadsheet->getActiveSheet()->setCellValue('A1', 'Hello World !');
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