<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/OrderByClause.php";

final class OrderByClauseTest extends TestCase {

    public function test_detectsOneASC() {
        $orderBy = new OrderByClause("id ASC");
        $this->assertEquals([
            ['id', OrderByClause::ASC]
        ], $orderBy->getOrderKeys());
    }
    public function test_detectsOneDESC() {
        $orderBy = new OrderByClause("id DESC");
        $this->assertEquals([
            ['id', OrderByClause::DESC]
        ], $orderBy->getOrderKeys());
    }
    public function test_detectsASCandDESC() {    
        $orderBy = new OrderByClause("id ASC, name DESC");
        $this->assertEquals([
            ['id', OrderByClause::ASC],
            ['name', OrderByClause::DESC]
        ], $orderBy->getOrderKeys());
    }
    public function test_detectsDESCandASC() {   
        $orderBy = new OrderByClause("name DESC, id ASC");
        $this->assertEquals([
            ['name', OrderByClause::DESC],
            ['id', OrderByClause::ASC]
        ], $orderBy->getOrderKeys());
    }
    public function test_sort_oneASC() {$this->fail("Not implemented");}
    public function test_sort_twoASC() {$this->fail("Not implemented");}
    public function test_sort_oneDESC() {$this->fail("Not implemented");}
    public function test_sort_twoDESC() {$this->fail("Not implemented");}
    public function test_sort_ASCandDESC() {$this->fail("Not implemented");}
    public function test_sort_DESCandASC() {$this->fail("Not implemented");}
    
}