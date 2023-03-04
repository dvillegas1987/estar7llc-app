<?php
class StatesTest extends YkModelTest
{
    private $class = 'States';

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
        $obj->insertStatesData();
        $obj->insertStatesLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".States::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".States::DB_TBL_LANG);
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
    private function insertStatesData()
    {
        $arr = [
            [
                'state_id'=>1, 'state_code'=>'TS1', 'state_country_id'=>1, 'state_identifier'=>'TS1', 'state_active'=>1
            ],
            [
                'state_id'=>2, 'state_code'=>'TS2', 'state_country_id'=>2, 'state_identifier'=>'TS2', 'state_active'=>1
            ],
            [
                'state_id'=>3, 'state_code'=>'TS3', 'state_country_id'=>3, 'state_identifier'=>'TS3', 'state_active'=>0
            ],
        ];
        $this->InsertDbData(States::DB_TBL, $arr);
       
    }
    private function insertStatesLangData()
    {
        $arr = [
            [
                'statelang_state_id'=>1, 'statelang_lang_id'=>1, 'state_name'=>'Test State1'
            ],
            [
                'statelang_state_id'=>2, 'statelang_lang_id'=>1, 'state_name'=>'Test State2'
            ],
            [
                'statelang_state_id'=>3, 'statelang_lang_id'=>1, 'state_name'=>'Test State3'
            ],
        ];
        $this->InsertDbData(States::DB_TBL_LANG, $arr);
       
    }

    // /**
    //  * @test
    //  *
    //  * @dataProvider feedGetAttributesByIdentifierAndCountry
    //  * @param  int recordId
    //  * @param  int countryId
    //  * @param  array idCol
    //  * @return void
    //  */
    // public function getAttributesByIdentifierAndCountry($expected, $recordId, $countryId, $attr)
    // {
    //     $result = $this->execute($this->class, [], 'getAttributesByIdentifierAndCountry', [$recordId, $countryId, $attr]);
    //     $this->$expected($result);
    // }    
    // /**
    //  * feedGetAttributesByIdentifierAndCountry
    //  *
    //  * @return array
    // */
    // public function feedGetAttributesByIdentifierAndCountry()
    // {  
    //     return [
    //         ['assertFalse', 'test', 0, array()],   //Invalid recordId, Invalid countryId
    //         ['assertFalse', 1, 'test', array()],   //Valid recordId, Invalid countryId
    //         ['assertFalse', 1, false, array()],   //Valid recordId, Invalid countryId
    //         ['assertIsArray', 'TS1', 1, array()],  //Valid recordId, Valid countryId
    //         ['assertIsArray', 'TS1', 1, array('state_code')],  //Valid recordId, Valid countryId, attr
    //     ];
    // } 

    /**
     * @test
     *
     * @dataProvider feedGetStatesByCountryId
     * @param  int countryId
     * @param  int langId
     * @param  int isActive
     * @param  string idCol
     * @return void
     */
    public function getStatesByCountryId($expected, $countryId, $langId, $isActive)
    {
        $result = $this->execute($this->class, [], 'getStatesByCountryId', [$countryId, $langId, $isActive]);  
        $this->$expected($result);
    }    
    /**
     * feedGetStatesByCountryId
     *
     * @return array
    */
    public function feedGetStatesByCountryId()
    {  
        return [
            ['assertIsArray', 1, 1, false],   //Valid countryId, Invalid langId
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetStateByCode
     * @param int stateCode
     * @param string attr
     * @return void
     */
    public function getStateByCode( $expected, $stateCode, $attr )
    {
        $stateObj = new States();    
        $result = $stateObj->getStateByCode($stateCode, $attr);
        $this->$expected($result);
    }
     /**
     * feedGetStateByCode
     *
     * @return array
     */ 
    public function feedGetStateByCode()
    {  
        return array(
            array('assertFalse', '', null), //Invalid stateCode
            array('assertIsInt', 'TS1', 'state_id'), //Valid stateCode
            array('assertIsArray', 'TS1', null), //Valid stateCode
        );
    }

    /**
     * @test
     *
     * @dataProvider feedGetStateByCountryAndCode
     * @param int countryId
     * @param string stateCode
     * @return void
     */
    public function getStateByCountryAndCode( $expected, $countryId, $stateCode )
    {
        $stateObj = new States();    
        $result = $stateObj->getStateByCountryAndCode($countryId, $stateCode);
        $this->$expected($result);
    }
     /**
     * feedGetStateByCountryAndCode
     *
     * @return array
     */ 
    public function feedGetStateByCountryAndCode()
    {  
        return array(
            array('assertFalse', '', null), //Invalid stateCode
            array('assertFalse', 1, ''), //Valid stateCode
            array('assertIsArray', 1, 'TS1'), //Valid stateCode
        );
    }
}