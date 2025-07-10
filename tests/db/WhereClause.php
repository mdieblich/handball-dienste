<?php

define('ARRAY_A', 'ARRAY_A');

function str_surrounded_by(string $start, string $haystack, string $end): bool {
    return str_starts_with($haystack, $start) 
        && str_ends_with  ($haystack, $end  );
}

function remove_quotes(string $text): string {
    if(str_surrounded_by('\'', $text,'\'') || 
       str_surrounded_by('"', $text,'"')
    ){
        return substr($text,1,-1);
    }
    return $text;
}
function trim_elements(array $array): array {
    $newArray = [];
    foreach($array as $key => $value){
        $newArray[$key] = trim($value);
    }
    return $newArray;
}

class WhereClause {
    private ?string $where;
    private ?Closure $subselect_resolver;

    private ?array $exactConditions;
    private ?array $setConditions;
    private ?array $nullChecks;
    public function __construct(?string $where, Closure $subselect_resolver = null) {
        if(str_contains($where, 'OR')){
            throw new Exception("FEHLER: 'OR' wird in der WHERE-Klausel nicht unterstützt: $where");
        }
        $this->where = $where;
        $this->subselect_resolver = $subselect_resolver;
    }

    private function parse(): void {
        
        $this->exactConditions = [];      // "id=3" oder "aktiv=1" oder "spielOld is null"
        $this->setConditions = [];        // "id in (1,2,3)"
        $this->nullChecks = [];       // "anwurf is not null"
        // TODO "Ungleich"-Bedingungen "íd != 5"

        if($this->where == null) {
            return;
        }

        $whereClauseParts = preg_split('/ AND /i', $this->where);
        foreach($whereClauseParts as $whereClausePart){
            $whereClausePart_lowerCase = strtolower($whereClausePart);
            if(preg_match('/^(\w+)\s*=/', $whereClausePart)){
                $this->extractExactCondition($whereClausePart);
            } else if (str_contains($whereClausePart_lowerCase,"in")){
                $this->extractSetCondition($whereClausePart);
            } else if (preg_match('/(\w+) is( not)? null/i', $whereClausePart, $whereClausePartMatches)){
                $key = $whereClausePartMatches[1];
                $checkIsNull = !isset($whereClausePartMatches[2]);
                $this->nullChecks[$key] = $checkIsNull;
            } else {
                throw new Exception("Nicht unterstützter Teil der Where-Clause: $whereClausePart");
            }
        }
    }
    private function extractExactCondition(string $whereClausePart): void {
        $keyAndValue = explode('=', $whereClausePart, 2);
        if(count($keyAndValue) != 2){
            throw new Exception("FEHLER: Bedingung $whereClausePart fehlerhaft");
        }
        $key = trim($keyAndValue[0]);
        $value = trim($keyAndValue[1]);
        $value = remove_quotes($value);
        $this->exactConditions[$key] = $value;
    }

    private function extractSetCondition($whereClausePart): void {
        if(!preg_match('/(\w+) IN \((.*)\)/i', $whereClausePart, $keyAndValues)){
            throw new Exception("FEHLER: Bedingung $whereClausePart fehlerhaft");
        }
        $key = trim($keyAndValues[1]);
        $values = trim($keyAndValues[2]);

        if(str_starts_with(strtolower($values), 'select')){
            $valueArray = $this->resolveSubselect($values);
        } else {
            $valueArray = explode(',', $values);
        }
        $this->setConditions[$key] = trim_elements($valueArray);
    }

    private function resolveSubselect(string $subselect): array {
        $valueArray = [];
        if(!$this->subselect_resolver){
            throw new Exception("FEHLER: Subselect vorhanden ($subselect), aber kein Subselect-Resolver im Konstruktor gesetzt");
        }
        $subselect_result = ($this->subselect_resolver)($subselect, ARRAY_A);
        foreach($subselect_result as $subselect_result_row){
            foreach($subselect_result_row as $k => $v){
                $valueArray[] = $v;
            }
        }
        return $valueArray;
    }

    public function getExactConditions(): array {
        if(!isset($this->exactConditions)) { $this->parse(); }
        return $this->exactConditions;
    }
    public function getSetConditions(): array {
        if(!isset($this->setConditions)) { $this->parse(); }
        return $this->setConditions;
    }
    public function getNullChecks(): array {
        if(!isset($this->nullChecks)) { $this->parse(); }
        return $this->nullChecks;
    }

    public function matches(array $row): bool {
        return $this->matchesExactConditions($row) 
        && $this->matchesSetCondtions($row)
        && $this->matchesNullChecks($row)
        ;
    }

    private function matchesExactConditions(array $row): bool {
        foreach($this->getExactConditions() as $key => $value){
            if(!isset($row[$key])){
                return false;
            }
            if($value != $row[$key]) {
                return false;
            }
        }
        return true;
    }
    private function matchesSetCondtions(array $row): bool {
        foreach($this->getSetConditions() as $key => $valueArray){
            if(!isset($row[$key])){
                return false;
            }
            if(!in_array($row[$key], $valueArray)) {
                return false;
            }
        }
        return true;
    }
    private function matchesNullChecks(array $row): bool {
        foreach ($this->getNullChecks() as $key => $mustBeNull){
            if($mustBeNull){
                if(isset($row[$key]) && $row[$key] != null){
                    return false;
                } 
            } else { // must not be null
                if(!isset($row[$key])){
                    return false;
                } else if ($row[$key] == null){
                    return false;
                }
            }
        }
        return true;
    }
    public function filterRows(array $rows): array{
        $possibleRows = [];
        foreach($rows as $row){
            if($this->matches($row)){
                $possibleRows[] = $row;
            }
        }
        return $possibleRows;
    }
}