<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/ColumnNameClause.php";

final class ColumnNameClauseTest extends TestCase {

    public function test_detects_asterisk() {
        $coloumnClause = new ColumnNameClause("*");
        $this->assertTrue($coloumnClause->isWildcard());
        $this->assertEmpty($coloumnClause->getColoumNames());
    }
    public function test_detects_oneColoumn() {
        $coloumnClause = new ColumnNameClause("id");
        $this->assertFalse($coloumnClause->isWildcard());
        $this->assertEquals(['id'], $coloumnClause->getColoumNames());
    }
    public function test_detects_twoColoumns() {
        $coloumnClause = new ColumnNameClause("id, name");
        $this->assertFalse($coloumnClause->isWildcard());
        $this->assertEquals(['id', 'name'], $coloumnClause->getColoumNames());
    }
    public function test_detects_twoColoumns_wrongOrder() {
        $coloumnClause = new ColumnNameClause("name, id");
        $this->assertFalse(condition: $coloumnClause->isWildcard());
        $this->assertEquals(['name', 'id'], $coloumnClause->getColoumNames());
    }
    public function test_filterColumns_asterisk() {
        // arrange
        $coloumnClause = new ColumnNameClause("*");
        $rows = [
            ['id' => 3, 'name'=> 'Niklas',  'alter' => 17.3],
            ['id' => 8, 'name'=> 'William', 'alter' => 22.0],
            ['id' => 9, 'name'=> 'Charon',  'alter' => 12.5],
        ];

        // act
        $reducedRows = $coloumnClause->filterColumns($rows);

        // assert
        $this->assertEquals([
            ['id' => 3, 'name'=> 'Niklas',  'alter' => 17.3],
            ['id' => 8, 'name'=> 'William', 'alter' => 22.0],
            ['id' => 9, 'name'=> 'Charon',  'alter' => 12.5],
        ], $reducedRows);
    }
    public function test_filterColumns_oneColoumn() {
        // arrange
        $coloumnClause = new ColumnNameClause("id");
        $rows = [
            ['id' => 3, 'name'=> 'Niklas',  'alter' => 17.3],
            ['id' => 8, 'name'=> 'William', 'alter' => 22.0],
            ['id' => 9, 'name'=> 'Charon',  'alter' => 12.5],
        ];

        // act
        $reducedRows = $coloumnClause->filterColumns($rows);

        // assert
        $this->assertEquals([
            ['id' => 3],
            ['id' => 8],
            ['id' => 9],
        ], $reducedRows);
    }
    public function test_filterColumns_twoColoumns() {
        // arrange
        $coloumnClause = new ColumnNameClause("id, name");
        $rows = [
            ['id' => 3, 'name'=> 'Niklas',  'alter' => 17.3],
            ['id' => 8, 'name'=> 'William', 'alter' => 22.0],
            ['id' => 9, 'name'=> 'Charon',  'alter' => 12.5],
        ];

        // act
        $reducedRows = $coloumnClause->filterColumns($rows);

        // assert
        $this->assertEquals([
            ['id' => 3, 'name'=> 'Niklas'],
            ['id' => 8, 'name'=> 'William'],
            ['id' => 9, 'name'=> 'Charon'],
        ], $reducedRows);
    }
    public function test_filterColumns_twoColoumns_wrongOrder() {
        // arrange
        $coloumnClause = new ColumnNameClause("name, id");
        $rows = [
            ['id' => 3, 'name'=> 'Niklas',  'alter' => 17.3],
            ['id' => 8, 'name'=> 'William', 'alter' => 22.0],
            ['id' => 9, 'name'=> 'Charon',  'alter' => 12.5],
        ];

        // act
        $reducedRows = $coloumnClause->filterColumns($rows);

        // assert
        $this->assertEquals([
            [ 'name'=> 'Niklas', 'id' => 3],
            [ 'name'=> 'William','id' => 8],
            [ 'name'=> 'Charon', 'id' => 9],
        ], $reducedRows);
    }
}