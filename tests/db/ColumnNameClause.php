<?php

class ColumnNameClause {

    private string $columnNameClause;
    private ?array $coloumNames;

    public function __construct(string $columnNameClause) {
        $this->columnNameClause = $columnNameClause;
    }

    private function parse(): void {
    }


    public function getColoumNames(): array {
        if(!isset($this->coloumNames) ) {
            $this->parse();
        }
        return $this->coloumNames;
    }
}