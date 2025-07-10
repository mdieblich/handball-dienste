<?php

class OrderByClause {

    public const ASC = +1;
    public const DESC = -1;
    
    private ?string $orderBy;
    private ?array $orderKeys;

    public function __construct(?string $orderBy) {
        $this->orderBy = $orderBy;
    }

    private function parse(): void {
        $this->orderKeys = [];
        if($this->orderBy == null) {
            return;
        }

        $orderByParts = preg_split("/,/", $this->orderBy);
        foreach($orderByParts as $orderByPart){
            $orderByPart = trim($orderByPart);
            $orderByPart_lowerCase = strtolower($orderByPart);
            if(str_ends_with($orderByPart_lowerCase, "asc")){
                $columnName = trim(substr($orderByPart, 0, -3));
                $this->orderKeys[] = [$columnName, OrderByClause::ASC];
            } else if (str_ends_with($orderByPart_lowerCase, "desc")){
                $columnName = trim(substr($orderByPart, 0, -4));
                $this->orderKeys[] = [$columnName, OrderByClause::DESC];
            } else {
                throw new Exception ("Order By-Anweisungen müssen mit ASC oder DESC enden");
            }
        }
    }

    public function getOrderKeys(): array {
        if(!isset($this->orderKeys) ) {
            $this->parse();
        }
        return $this->orderKeys;
    }

    public function sort(array &$rows): void{
        if($this->orderBy == null) {
            return;
        }
        usort($rows, function ($a, $b){
            return $this->compare($a, $b);
        });
    }

    public function compare(array $a, array $b): int {
        foreach($this->getOrderKeys() as $orderKey){
            $key = $orderKey[0];
            $direction = $orderKey[1];
            
            if($a[$key] > $b[$key]){
                return $direction;
            }
            if($a[$key] < $b[$key]){
                return -$direction;
            }
            // else continue with next order key
        }
        return 0;
    }
}