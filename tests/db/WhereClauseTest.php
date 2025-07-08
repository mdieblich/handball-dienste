<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/WhereClause.php";

final class WhereClauseTest extends TestCase {

    public function test_oneExaktCondition() {
        $where = new WhereClause("id=3");
        $this->assertEquals(['id' => '3'], $where->getExactConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_oneExaktStringCondition() {
        $where = new WhereClause("name='albert'");
        $this->assertEquals(['name' => 'albert'], $where->getExactConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_oneExaktStringCondition_doubleQuote() {
        $where = new WhereClause("name=\"albert\"");
        $this->assertEquals(['name' => 'albert'], $where->getExactConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_twoExaktConditions() {    
        $where = new WhereClause("id=3 and age=17");
        $this->assertEquals(['id' => '3', 'age' => '17'], $where->getExactConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }

    public function test_setCondition() {
        $where = new WhereClause("id in (3, 5,12)");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEquals(['id' => ['3', '5', '12']], $where->getSetConditions());
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
        $this->assertEquals(['id' => ['3', '5', '12']], $where->getSetConditions());
        $this->assertEmpty( $where->getNullChecks());
    }
    public function test_nullcheck() {
        $where = new WhereClause("name is null");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEquals(['name' => true], $where->getNullChecks());
    }
    public function test_notnullcheck() {
        $where = new WhereClause("name is not null");
        $this->assertEmpty( $where->getExactConditions());
        $this->assertEmpty( $where->getSetConditions());
        $this->assertEquals(['name' => false], $where->getNullChecks());
    }

    public function test_matches_oneCondition() {
        $where = new WhereClause("id=3");
        $this->fail("Not implemented");
    }

    public function test_matches_oneFailingCondition() {$this->fail("Not implemented");}
    public function test_matches_twoConditions() {$this->fail("Not implemented");}
    public function test_matches_twoFailingConditions() {$this->fail("Not implemented");}
    public function test_matches_setCondition() {$this->fail("Not implemented");}
    public function test_matches_failingSetCondition() {$this->fail("Not implemented");}
    public function test_matches_nullCheck() {$this->fail("Not implemented");}
    public function test_matches_failingNullCheck() {$this->fail("Not implemented");}
    public function test_matches_notNullCheck() {$this->fail("Not implemented");}
    public function test_matches_failingNotNullCheck() {$this->fail("Not implemented");}
    
}