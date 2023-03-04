<?php
class TaxRuleLocationTest extends YkModelTest
{

    /**
     * @dataProvider setUpdateLocations
     */
    public function testUpdateLocations($data, $expected)
    {
        $taxRuleLoc = new TaxRuleLocation();
        $result = $taxRuleLoc->updateLocations($data);
        $this->assertEquals($expected, $result);
    }

    public function setUpdateLocations()
    {
        return [
            [
                [
                'taxruleloc_taxcat_id' => 0,
                'taxruleloc_taxrule_id' => 9,
                'taxruleloc_country_id' => 99,
                'taxruleloc_state_id' => 1287,
                'taxruleloc_type' => 1,
                'taxruleloc_unique' => 1,
                ], false
            ], // Invalid data
            [
                [
                    'taxruleloc_taxcat_id' => 8,
                    'taxruleloc_taxrule_id' => 0,
                    'taxruleloc_country_id' => 99,
                    'taxruleloc_state_id' => 1287,
                    'taxruleloc_type' => 1,
                    'taxruleloc_unique' => 1,
                    ], false
            ], // Invalid data
            [

                [
                'taxruleloc_taxcat_id' => 8,
                'taxruleloc_taxrule_id' => 9,
                'taxruleloc_country_id' => 99,
                'taxruleloc_state_id' => 1287,
                'taxruleloc_type' => 1,
                'taxruleloc_unique' => 1,
                ], true
            ], // valid data
        ];
    }

    /**
     * @dataProvider setDeleteLocations
     */
    public function testDeleteLocations($taxCatId, $expected)
    {
        $taxRuleLoc = new TaxRuleLocation();
        $result = $taxRuleLoc->deleteLocations($taxCatId);
        $this->assertEquals($expected, $result);
    }

    public function setDeleteLocations()
    {
        return [
            [-1, true], // Invalid taxCatId
            [0, true],  // Invalid taxCatId
            [1, true], // Invalid taxCatId
            [8, true], // valid taxCatId
        ];
    }
}
