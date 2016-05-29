<?php

require '../src/app/dal/dal_helper.php';

class DBHelperTest extends PHPUnit_Framework_TestCase
{
    public function testBuildValuesClauseReturnRightClauseOnPlural()
    {
        $params = [1, "value"];
        $this->assertEquals("VALUES(?,?)", __build_values_clause($params));
    }

    public function testBuildValuesClauseReturnRightClauseOnOne()
    {
        $params = [1];
        $this->assertEquals("VALUES(?)", __build_values_clause($params));
    }

    public function testBuildValuesClauseReturnRightClauseOnZero()
    {
        $params = [];
        $this->assertEquals("", __build_values_clause($params));
    }
}