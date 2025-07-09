<?php

class OrderByClause {
    
    private string $orderBy;

    public function __construct(string $orderBy) {
        $this->orderBy = $orderBy;
    }
}