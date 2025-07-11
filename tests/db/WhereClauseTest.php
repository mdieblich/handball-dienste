<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/WhereClause.php";

final class WhereClauseTest extends TestCase {

    public function test_oneExaktCondition() {
        $where = new WhereClause("id=3");
        $this->assertEquals(['id' => '3'], $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_oneExaktStringCondition() {
        $where = new WhereClause("name='albert'");
        $this->assertEquals(['name' => 'albert'], $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_oneExaktStringCondition_doubleQuote() {
        $where = new WhereClause("name=\"albert\"");
        $this->assertEquals(['name' => 'albert'], $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_twoExaktConditions() {    
        $where = new WhereClause("id=3 and age=17");
        $this->assertEquals(['id' => '3', 'age' => '17'], $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_rangeCondition_numbers() {   
        $where = new WhereClause("age<17 AND size>8 AND voltage <= -2 AND current >= 0");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEquals([
            'age' => [WhereClause::LT, '17'],
            'size' => [WhereClause::GT, '8'],
            'voltage' => [WhereClause::LTE, '-2'],
            'current' => [WhereClause::GTE, '0'],

        ], $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks()); 
    }
    public function test_setCondition() {
        $where = new WhereClause("id in (3, 5,12)");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEquals(['id' => ['3', '5', '12']], $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_excludingSetCondition() {
        $where = new WhereClause("id not in (3, 5,12)");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEquals(['id' => ['3', '5', '12']], $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_subselect() {
        $subselect_resolver = function(string $subselect): array {
            if($subselect == 'SELECT id FROM subtable'){
                return [['id'=>'3'], ['id' => '5'],['id'=>'12']];
            }
            return [];
        };
        $where = new WhereClause("id in (SELECT id FROM subtable)", $subselect_resolver);
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEquals(['id' => ['3', '5', '12']], $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    
    public function test_subselect_withEquals() {
        $subselect_resolver = function(string $subselect): array {
            if($subselect == 'SELECT id FROM subtable where a=b'){
                return [['id'=>'3'], ['id' => '5'],['id'=>'12']];
            }
            return [];
        };
        $where = new WhereClause("id in (SELECT id FROM subtable where a=b)", $subselect_resolver);
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEquals(['id' => ['3', '5', '12']], $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_nullcheck() {
        $where = new WhereClause("name is null");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEquals(['name' => true], $where->getNullChecks());
    }
    public function test_notnullcheck() {
        $where = new WhereClause("name is not null");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEquals(['name' => false], $where->getNullChecks());
    }
    public function test_empty() {
        $where = new WhereClause(null);
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getRangeConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getExcludingSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }

    public function test_matches_oneCondition() {
        $where = new WhereClause("id=3");
        $row = ["id" => 3];
        $this->assertTrue($where->matches($row));
    }

    public function test_matches_oneFailingCondition() {
        $where = new WhereClause("id=3");
        $row = ["id" => 4];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_twoConditions() {
        $where = new WhereClause("id=3 AND name='albert'");
        $row = ["id" => 3, 'name' => 'albert'];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_twoFailingConditions() {
        $where = new WhereClause("id=3 AND name='albert'");
        $row = ["id" => 3, 'name' => 'zwalbert'];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_range_LT() {
        $where = new WhereClause("age<18");
        $row = ['age' => 17];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_range_LT_fails() {
        $where = new WhereClause("age<18");
        $row = ['age' => 18];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_range_LTE() {
        $where = new WhereClause("age<=18");
        $row = ['age' => 18];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_range_LTE_fails() {
        $where = new WhereClause("age<=18");
        $row = ['age' => 19];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_range_GTE() {
        $where = new WhereClause("age>=18");
        $row = ['age' => 18];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_range_GTE_fails() {
        $where = new WhereClause("age>=18");
        $row = ['age' => 17];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_range_GT() {
        $where = new WhereClause("age>18");
        $row = ['age' => 19];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_range_GT_fails() {
        $where = new WhereClause("age>18");
        $row = ['age' => 18];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_range_date() {
        $where = new WhereClause("anwurf > \"2024-05-05\"");
        $row = ['anwurf' => new DateTime("2024-09-20 20:00:00")];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_range_date_fails() {
        $where = new WhereClause("anwurf > \"2024-05-05\"");
        $row = ['anwurf' => new DateTime("2023-09-20 20:00:00")];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_range_currentTimestamp() {
        $where = new WhereClause("anwurf > CURRENT_TIMESTAMP");
        $fiveMinutesLater = new DateTime("now");
        $fiveMinutesLater->add(new DateInterval('PT5M')); // 5 Minuten addieren
        $row = ['anwurf' => $fiveMinutesLater];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_range_currentTimestamp_fails() {
        $where = new WhereClause("anwurf > CURRENT_TIMESTAMP");
        $fiveMinutesEarlier = new DateTime("now");
        $fiveMinutesEarlier->sub(new DateInterval('PT5M')); // 5 Minuten abziehen
        $row = ['anwurf' => $fiveMinutesEarlier];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_setCondition() {
        $where = new WhereClause("id in (3,5,12)");
        $row = ["id" => 5];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_failingSetCondition() {
        $where = new WhereClause("id in (3,5,12)");
        $row = ["id" => 6];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_excludingSetCondition() {
        $where = new WhereClause("id not in (3,5,12)");
        $row = ["id" => 6];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_failingExcludingSetCondition() {
        $where = new WhereClause("id not in (3,5,12)");
        $row = ["id" => 5];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_nullCheck() {
        $where = new WhereClause("name is null");
        $row = ["name" => null];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_nullCheck_columnNotPresent() {
        $where = new WhereClause("name is null");
        $row = ["id" => 3];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_failingNullCheck() {        
        $where = new WhereClause("name is null");
        $row = ["name" => 'albert'];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_notNullCheck() {  
        $where = new WhereClause("name IS NOT null");
        $row = ["name" => 'albert'];
        $this->assertTrue($where->matches($row));
    }
    public function test_matches_failingNotNullCheck() {    
        $where = new WhereClause("name IS NOT null");
        $row = ["name" => null];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_failingNotNullCheck_columnNotPresent() {    
        $where = new WhereClause("name IS NOT null");
        $row = ["id" => 3];
        $this->assertFalse($where->matches($row));
    }
    public function test_matches_emptyMatchesEverything() {    
        $where = new WhereClause(null);
        $row = ["id" => 3];
        $this->assertTrue($where->matches($row));
    }
    
}