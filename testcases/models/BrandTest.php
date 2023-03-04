<?php
class BrandTest extends YkModelTest
{
    private $class = 'Brand';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertBrandData();
        $obj->insertBrandLangData(); 
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
        FatApp::getDb()->query("TRUNCATE TABLE ".Brand::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Brand::DB_TBL_LANG);
    }
    
    /**
    * insertBrandData
    *
    * @return void
    */
    private function insertBrandData()
    {
        $arr = [
            [
                'brand_id' => 1,
                'brand_identifier' => 'Samsung',
                'brand_seller_id' => 0,
                'brand_active' => 1,  
            ],
            [
                'brand_id' => 2,
                'brand_identifier' => 'Apple',
                'brand_seller_id' => 0,
                'brand_active' => 0,  
            ]
        ];            
        $this->InsertDbData(Brand::DB_TBL, $arr);
    }
    
    /**
    * insertBrandLangData
    *
    * @return void
    */
    private function insertBrandLangData()
    {
        $arr = [
            [
                'brandlang_brand_id' => 1,
                'brandlang_lang_id' => 1,
                'brand_name' => 'Samsung',
                'brand_short_description' => 'Samsung',
                         
            ],
            [
                'brandlang_brand_id' => 2,
                'brandlang_lang_id' => 1,
                'brand_name' => 'Apple',
                'brand_short_description' => 'Apple',
            ],
        ];            
        $this->InsertDbData(Brand::DB_TBL_LANG, $arr);
    } 
    
    /**
     * @test
     *
     * @dataProvider feedGetAllIdentifierAssoc
     * @param  int $langId
     * @param  bool $isDeleted
     * @param  bool $isActive
     * @return void
     */
    public function getAllIdentifierAssoc($expected, $langId, $isDeleted, $isActive)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getAllIdentifierAssoc', [$langId, $isDeleted, $isActive]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    
    /**
     * feedGetAllIdentifierAssoc
     *
     * @return array
     */
    public function feedGetAllIdentifierAssoc()
    {  
        return [
            [0, 'test', false, false],  // Invalid langId , valid isDeleted, valid isActive  
            [2, 1, false, false],  // Valid langId , valid isDeleted, valid isActive  
            [2, 1, false, true],  // Valid langId , valid isDeleted, valid isActive   
            [2, 1, true, false],  // Valid langId , valid isDeleted, valid isActive
            [2, 1, true, true],  // Valid langId , valid isDeleted, valid isActive
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedCanRecordMarkDelete
     * @param  int $id
     * @return void
     */
    public function canRecordMarkDelete($expected, $id)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'canRecordMarkDelete', [$id]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * feedCanRecordMarkDelete
     *
     * @return array
     */
    public function feedCanRecordMarkDelete()
    {  
        return [
            [false, 'test'],  // Invalid id
            [true, 1],  // Valid id
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetBrandName
     * @param  int $brandId
     * @param  int $langId
     * @param  bool $isActive
     * @return void
     */
    public function getBrandName($expected, $brandId, $langId, $isActive)
    {
        $result = $this->execute($this->class, [], 'getBrandName', [$brandId, $langId, $isActive]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * feedGetBrandName
     *
     * @return array
     */
    public function feedGetBrandName()
    {  
       return [
            [false, 'test', 'test', true], // Invalid brandId and Invalid langId
            [false, 'test', 1, true], // Invalid brandId and Valid langId
            [false, 1, 'test', true], // Valid brandId and Invalid langId      
            ['Samsung', 1, 1, true],  // Valid brandId and Valid langId
            ['Apple', 2, 1, false],  // Valid brandId and Valid langId        
        ];
    }
    
}
