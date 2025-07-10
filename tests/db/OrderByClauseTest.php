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
    public function test_canHandleNull() {   
        $orderBy = new OrderByClause(null);
        $this->assertEmpty($orderBy->getOrderKeys());
    }
    public function test_sort_oneASC() {
        $orderBy = new OrderByClause("id ASC");
        $original = [
            ['id' =>  5, 'name' => 'Albert'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
        ];
        $sorted = unserialize(serialize($original));

        $orderBy->sort($sorted);
        
        $this->assertNotEquals($original, $sorted);
        $this->assertEquals([
            ['id' =>  5, 'name' => 'Albert'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' => 12, 'name' => 'Talulah'],
        ], $sorted);
    }
    public function test_sort_twoASC() {
        $orderBy = new OrderByClause("id ASC, name ASC");
        $original = [
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  5, 'name' => 'Albert'],
            ['id' =>  9, 'name' => 'Noob'],
        ];
        $sorted = unserialize(serialize($original));
        
        $orderBy->sort($sorted);
        
        $this->assertNotEquals($original, $sorted);
        $this->assertEquals([
            ['id' =>  5, 'name' => 'Albert'],
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' => 12, 'name' => 'Talulah'],
        ], $sorted);
    }
    public function test_sort_oneDESC() {
        $orderBy = new OrderByClause("id DESC");
        $original = [
            ['id' =>  5, 'name' => 'Albert'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
        ];
        $sorted = unserialize(serialize($original));

        $orderBy->sort($sorted);
        
        $this->assertNotEquals($original, $sorted);
        $this->assertEquals([
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' =>  5, 'name' => 'Albert'],
        ], $sorted);
    }
    public function test_sort_twoDESC() {
        $orderBy = new OrderByClause("id DESC, name DESC");
        $original = [
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  5, 'name' => 'Albert'],
            ['id' =>  9, 'name' => 'Noob'],
        ];
        $sorted = unserialize(serialize($original));
        
        $orderBy->sort($sorted);
        
        $this->assertNotEquals($original, $sorted);
        $this->assertEquals([
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' =>  5, 'name' => 'Albert'],
        ], $sorted);
    }
    public function test_sort_ASCandDESC() {
        $orderBy = new OrderByClause("id ASC, name DESC");
        $original = [
            ['id' =>  5, 'name' => 'Albert'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' =>  9, 'name' => 'Noob'],
        ];
        $sorted = unserialize(serialize($original));
        
        $orderBy->sort($sorted);
        
        $this->assertNotEquals($original, $sorted);
        $this->assertEquals([
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' =>  5, 'name' => 'Albert'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' => 12, 'name' => 'Talulah'],
        ], $sorted);
    }
    public function test_sort_DESCandASC() {
        $orderBy = new OrderByClause("id DESC, name ASC");
        $original = [
            ['id' =>  5, 'name' => 'Bertiane'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' =>  5, 'name' => 'Albert'],
        ];
        $sorted = unserialize(serialize($original));
        
        $orderBy->sort($sorted);
        
        $this->assertNotEquals($original, $sorted);
        $this->assertEquals([
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
            ['id' =>  5, 'name' => 'Albert'],
            ['id' =>  5, 'name' => 'Bertiane'],
        ], $sorted);
    }
    
    public function test_sort_nothing() {
        $orderBy = new OrderByClause(null);
        $original = [
            ['id' =>  5, 'name' => 'Albert'],
            ['id' => 12, 'name' => 'Talulah'],
            ['id' =>  9, 'name' => 'Noob'],
        ];
        $sorted = unserialize(serialize($original));

        $orderBy->sort($sorted);
        
        $this->assertEquals($original, $sorted);
    }
}