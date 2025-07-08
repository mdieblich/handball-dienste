<?php

define('ARRAY_A', 'ARRAY_A');

class WhereClause {
    private string $where;
    private ?Closure $subselect_resolver;

    private ?array $exactConditions;
    private ?array $setConditions;
    private ?array $nullChecks;
    public function __construct(string $where, Closure $subselect_resolver = null) {
        $this->where = $where;
        $this->subselect_resolver = $subselect_resolver;
    }

    private function parse(): void {
        $whereClauseParts = preg_split('/ AND /i', $this->where);
        $this->exactConditions = [];      // "id=3" oder "aktiv=1" oder "spielOld is null"
        $this->setConditions = [];        // "id in (1,2,3)"
        $this->nullChecks = [];       // "anwurf is not null"
        // TODO "Ungleich"-Bedingungen "íd != 5"
        foreach($whereClauseParts as $whereClausePart){
            $whereClausePart_lowerCase = strtolower($whereClausePart);
            if(str_contains($whereClausePart,'=')){
                $keyAndValue = explode('=', $whereClausePart, 2);
                if(count($keyAndValue) != 2){
                    throw new Exception("FEHLER: Bedingung $whereClausePart fehlerhaft");
                }
                $key = trim($keyAndValue[0]);
                $value = trim($keyAndValue[1]);
                if((str_starts_with($value,'\'') && str_ends_with($value,'\''))
                 ||(str_starts_with($value,'"') && str_ends_with($value,'"'))
                ){
                    $value = substr($value,1,-1);
                }
                $this->exactConditions[$key] = $value;
            } else if (str_contains($whereClausePart_lowerCase,"in")){
                $keyAndValues = explode('in', $whereClausePart, 2);
                if(count($keyAndValues) != 2){
                    throw new Exception("FEHLER: Bedingung $whereClausePart fehlerhaft");
                }
                $key = trim($keyAndValues[0]);
                $values = trim($keyAndValues[1]);
                if(!str_starts_with($values, '(') || !str_ends_with($values,')')){
                    throw new Exception("FEHLER: rechter Teil der Bedingung von $whereClausePart muss in runden Klammern sein");
                }
                $values = trim(substr($values,1, -1));
                // subselects erst abfrühstücken
                if(str_starts_with(strtolower($values), 'select')){
                    $subselect = $values;
                    if(!$this->subselect_resolver){
                        throw new Exception("FEHLER: Subselect vorhanden ($subselect), aber kein Subselect-Resolver im Konstruktor gesetzt");
                    }
                    $subselect_result = $this->subselect_resolver->call($this, $subselect, ARRAY_A);
                    foreach($subselect_result as $subselect_result_row){
                        foreach($subselect_result_row as $k => $v){
                            $valueArray[] = $v;
                        }
                    }
                } else {
                    $valueArray = explode(',', $values);
                }
                foreach($valueArray as $valueIndex => $value){
                    $valueArray[$valueIndex] = trim($value);
                }
                $this->setConditions[$key] = $valueArray;
            } else if (preg_match('/(\w*) is( not)? null/i', $whereClausePart, $whereClausePartMatches)){
                $key = $whereClausePartMatches[1];
                $checkIsNull = !isset($whereClausePartMatches[2]);
                $this->nullChecks[$key] = $checkIsNull;
            } else {
                throw new Exception("Nicht unterstützter Teil der Where-Clause: $whereClausePart");
            }
        }


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
}