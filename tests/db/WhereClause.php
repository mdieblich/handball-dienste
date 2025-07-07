<?php

class WhereClause {
    private string $where;
    private ?Closure $subselect_resolver;

    private ?array $exactConditions;
    private ?array $setConditions;
    private ?array $nullChecks;
    public function __construct(string $where, Closure $subselect_resolver = null ) {
        $this->where = $where;
        $this->subselect_resolver = $subselect_resolver;
    }

    private function parse(): void {

    }

    public function getExactConditions(): array {
        if($this->exactConditions == null) { $this->parse(); }
        return $this->exactConditions;
    }
    public function getSetConditions(): array {
        if($this->setConditions == null) { $this->parse(); }
        return $this->setConditions;
    }
    public function getNullChecks(): array {
        if($this->nullChecks == null) { $this->parse(); }
        return $this->nullChecks;
    }
}