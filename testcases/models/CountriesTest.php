<?php
class CountriesTest extends YkModelTest
{
    private $class = 'Countries';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertCountriesData();
        $obj->insertCountriesLangData();
    }  

    /**
     * tearDownAfterClass
     *
     * @return void
     */
    public static function tearDownAfterClass() :void
    {   
        self::truncateDbData();
    }    
    /**
     * truncateDbData
     *
     * @return void
     */
    public static function truncateDbData()
    {
        FatApp::getDb()->query("TRUNCATE TABLE ".Countries::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Countries::DB_TBL_LANG);
    }
    
    private function insertCountriesData()
    {
        $arr = [
            [
                'country_id'=>1, 'country_code'=>'AL', 'country_code_alpha3'=>'ALB', 'country_active'=>1, 'country_zone_id'=>2, 'country_currency_id'=>0, 'country_language_id'=>0
            ],
            [
                'country_id'=>2, 'country_code'=>'AF', 'country_code_alpha3'=>'AFG', 'country_active'=>1, 'country_zone_id'=>4, 'country_currency_id'=>0, 'country_language_id'=>0
            ],
            [
                'country_id'=>3, 'country_code'=>'DZ', 'country_code_alpha3'=>'DZA', 'country_active'=>0, 'country_zone_id'=>1, 'country_currency_id'=>0, 'country_language_id'=>0
            ]
        ];
        $this->InsertDbData(Countries::DB_TBL, $arr);
       
    }
    private function insertCountriesLangData()
    {
        $arr = [
            [
                'countrylang_country_id'=>1, 'countrylang_lang_id'=>'AL', 'country_name'=>'ALB'
            ],
            [
                'countrylang_country_id'=>2, 'countrylang_lang_id'=>'AF', 'country_name'=>'AFG'
            ],
            [
                'countrylang_country_id'=>3, 'countrylang_lang_id'=>'DZ', 'country_name'=>'DZA'
            ],
        ];
        $this->InsertDbData(Countries::DB_TBL_LANG, $arr);
       
    }

    /**
     * @test
     *
     * @dataProvider feedGetCountriesArr
     * @param  int $langId
     * @param  int $isActive
     * @param  string $idCol
     * @return void
     */
    public function getCountriesArr($expected, $langId, $isActive, $idCol = 'country_id')
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getCountriesArr', [$langId, $isActive, $idCol]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }    
    /**
     * feedGetCountriesArr
     *
     * @return array
    */
    public function feedGetCountriesArr()
    {  
        return [
            [2, 1, 'test'],   //Valid langId, Invalid isActive
            [2, 1, true],   //Valid langId, Valid isActive
            [3, 1, false],  //Valid langId, Valid isActive
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetCountryByCode
     * @param int countryCode
     * @param string attr
     * @return void
     */
    public function getCountryByCode( $expected, $countryCode, $attr )
    {
        $countryObj = new Countries();    
        $result = $countryObj->getCountryByCode($countryCode, $attr);
        $this->$expected($result);
    }
     /**
     * feedgetCountryByCode
     *
     * @return array
     */ 
    public function feedGetCountryByCode()
    {  
        return array(
            array('assertFalse', '', null), //Invalid countryCode
            array('assertIsInt', 'AL', 'country_id'), //Valid countryCode
            array('assertIsArray', 'AL', null), //Valid countryCode
        );
    }

    /**
     * @test
     *
     * @dataProvider feedGetCountryById
     * @param int countryId
     * @param int langId
     * @param string attr
     * @return void
     */
    public function getCountryById( $expected, $countryId, $langId, $attr )
    {
        $countryObj = new Countries();    
        $result = $countryObj->getCountryById($countryId, $langId, $attr);
        $this->$expected($result);
    }
     /**
     * feedGetCountryById
     *
     * @return array
     */ 
    public function feedGetCountryById()
    {  
        return array(
            array('assertFalse', '', '', null), //Invalid countryId
            array('assertIsInt', 1, 1, 'country_id'), //Valid countryId
            array('assertIsArray', 1, 1, null), //Valid countryId
        );
    }

}