<?php
class TaxRuleTest extends YkModelTest
{

    /**
     * @dataProvider setDeleteRule
     */
    public function testDeleteRules($taxCatId, $expected)
    {
        $taxRule = new TaxRule();
        $result = $taxRule->deleteRules($taxCatId);
        $this->assertEquals($expected, $result);
    }

    public function setDeleteRule()
    {
        return [
            [-1, true],  // Invalid taxCatId
            [0, true],  // Invalid taxCatId
            [1, true],  // Invalid taxCatId
            [8, true], // valid taxCatId
        ];
    }

    /**
     * @dataProvider setGetRules
     */
    public function testGetRules($taxCatId)
    {
        $taxRule = new TaxRule();
        $result = $taxRule->getRules($taxCatId);
        $this->assertIsArray($result);
    }

    public function setGetRules()
    {
        return [
            [-1],  // Invalid taxCatId
            [0],  // Invalid taxCatId
            [1],  // Invalid taxCatId
            [8], // valid taxCatId
        ];
    }

    /**
     * @dataProvider setGetCombinedRuleDetails
     */
    public function testGetCombinedRuleDetails($rulesIds)
    {
        $taxRule = new TaxRule();
        $result = $taxRule->getCombinedRuleDetails($rulesIds);
        $this->assertIsArray($result);
    }

    public function setGetCombinedRuleDetails()
    {
        return [
            [
                []
            ], // Empty Array
            [
                [-1, 0, null]
            ], // Invalid array
            [
                [1, 2, 3, 4, 5]
            ], // valid Rule Ids
        ];
    }

    /**
     * @dataProvider setGetLocations
     */
    public function testGetLocations($taxCatId)
    {
        $taxRule = new TaxRule();
        $result = $taxRule->getLocations($taxCatId);
        $this->assertIsArray($result);
    }

    public function setGetLocations()
    {
        return [
            [-1],  // Invalid taxCatId
            [0],  // Invalid taxCatId
            [1],  // Invalid taxCatId
            [8], // valid taxCatId
        ];
    }
}
