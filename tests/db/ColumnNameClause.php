<?php

class ColumnNameClause {

    private string $columnNameClause;
    private ?array $coloumNames;
    private ?bool $isWildcard;

    public function __construct(string $columnNameClause) {
        $this->columnNameClause = $columnNameClause;
    }

    private function parse(): void {
        $this->isWildcard = false;
        $this->coloumNames = [];

        $parts = explode(",", $this->columnNameClause);
        foreach($parts as $part) {
            $name = trim($part);
            if($name === "*") {
                $this->isWildcard = true;
                $this->coloumNames = [];
                break;
            } else {
                $this->coloumNames[] = $name;
            }
        }
    }

    public function isWildcard(): bool {
        if(!isset($this->isWildcard) ) {
            $this->parse();
        }
        return $this->isWildcard;
    }

    public function getColoumNames(): array {
        if(!isset($this->coloumNames) ) {
            $this->parse();
        }
        return $this->coloumNames;
    }

    public function filterColumns(array $rows): array {
    //     if($this->isWildcard){
    //         return $rows;
    //     }
    //     return array_map(
    // fn($row) => array_intersect_key($row, array_flip($this->getColoumNames())),
    // $rows
        // );
        return [];
    }
}