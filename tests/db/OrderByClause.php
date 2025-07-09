<?php

class OrderByClause {

    public const ASC = "ASC";
    public const DESC = "DESC";
    
    private string $orderBy;

    public function __construct(string $orderBy) {
        $this->orderBy = $orderBy;
    }

    public function getOrderKeys(): array {
        return [];
    }
}