<?php

class WhereClause {
    private string $where;
    private ?Closure $subselect_resolver;
    public function __construct(string $where, Closure $subselect_resolver = null ) {
        $this->where = $where;
        $this->subselect_resolver = $subselect_resolver;
    }

    public function getExactConditions(): array {
        return [];
    }
    public function getSetConditions(): array {
        return [];
    }
    public function getNullChecks(): array {
        return [];
    }
}