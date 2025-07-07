<?php

class WhereClause {
    private string $where;
    private ?callable $subselect_resolver;
    public function __construct(string $where, callable $subselect_resolver = null ) {
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