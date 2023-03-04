<?php
class ShopTest extends YkModelTest
{
    private $class = 'Shop';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertUserData();
        $obj->insertShopData();
        $obj->insertShopLangData(); 
        $obj->insertShopRewriteUrl();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".User::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Shop::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Shop::DB_TBL_LANG);
        FatApp::getDb()->query("TRUNCATE TABLE ".UrlRewrite::DB_TBL); 
        FatApp::getDb()->query("TRUNCATE TABLE ".Shop::DB_TBL_SHOP_FAVORITE); 
    }
    
    /**
    * insertUserData
    *
    * @return void
    */
    private function insertUserData()
    {
        $arr = [
            [
                'user_id' => 1,
                'user_name' => 'Sammy',
                'user_zip' => 85281,
                'user_country_id' => 223,
                'user_state_id' => 2996,
                'user_is_buyer' => 1,               
                'user_is_supplier' => 1,
            ],
            [
                'user_id' => 2,
                'user_name' => 'Samar',
                'user_zip' => 85281,
                'user_country_id' => 223,
                'user_state_id' => 2996,
                'user_is_buyer' => 1,               
                'user_is_supplier' => 1,
            ]
        ];            
        $this->InsertDbData(User::DB_TBL, $arr);
    }
    
    /**
    * insertShopData
    *
    * @return void
    */
    private function insertShopData()
    {
        $arr = [
            [
                'shop_id' => 1,
                'shop_user_id' => 1,
                'shop_identifier' => 'Sammy',
                'shop_postalcode' => 85281,
                'shop_country_id' => 223,
                'shop_state_id' => 2996,
                'shop_active' => 1,               
            ],
            [
                'shop_id' => 2,
                'shop_user_id' => 2,
                'shop_identifier' => 'Samar',
                'shop_postalcode' => 85281,
                'shop_country_id' => 223,
                'shop_state_id' => 2996,
                'shop_active' => 0,               
            ],
        ];            
        $this->InsertDbData(Shop::DB_TBL, $arr);
    }
    
    /**
    * insertShopLangData
    *
    * @return void
    */
    private function insertShopLangData()
    {
        $arr = [
            [
                'shoplang_shop_id' => 1,
                'shoplang_lang_id' => 1,
                'shop_name' => 'Sammy',              
            ],
            [
                'shoplang_shop_id' => 2,
                'shoplang_lang_id' => 1,
                'shop_name' => 'Samar',            
            ],
        ];            
        $this->InsertDbData(Shop::DB_TBL_LANG, $arr);
    }
    
    /**
    * insertShopRewriteUrl
    *
    * @return void
    */
    private function insertShopRewriteUrl()
    {
        $arr = [
            [
                'urlrewrite_id' => 1,
                'urlrewrite_original' => 'shops/view/1',
                'urlrewrite_custom' => 'sammy',
                'urlrewrite_lang_id' => 1,              
            ],
            [
                'urlrewrite_id' => 2,
                'urlrewrite_original' => 'shops/view/2',
                'urlrewrite_custom' => 'samar',
                'urlrewrite_lang_id' => 1,                  
            ],
        ];            
        $this->InsertDbData(UrlRewrite::DB_TBL, $arr);
    }

    /**
     * testIsActive
     *
     * @dataProvider providerIsActive
     * @param  bool $expected
     * @param  mixed $userId
     * @param  mixed $shopId
     * @return void
     */
    public function testIsActive(bool $expected, $shopId, $userId)
    {   
        $result = $this->execute($this->class, [$shopId, $userId, 0], 'isActive');
        $this->assertEquals($expected, $result);
    }
        
    /**
     * providerIsActive
     *
     * @return array
     */
    public function providerIsActive()
    {  
        return [
            [false, 'test', 'test'], // Invalid shopId and Invalid userId
            [false, 'test', 1], // Invalid shopId and Valid userId
            [false, 1, 'test'], // Valid shopId and Invalid userId
            [false, 2, 2],  // Valid shopId and Valid userId having shop status Inactive
            [true, 1, 1],  // Valid shopId and Valid userId having shop status Active          
        ];
    }
    
    /**
     * testGetName
     *
     * @dataProvider providerGetName
     * @param  mixed $expected
     * @param  mixed $shopId
     * @param  mixed $langId
     * @param  mixed $isActive
     * @return void
     */
    public function testGetName($expected, $shopId, $langId, $isActive)
    {
        $result = $this->execute($this->class, [], 'getName', [$shopId, $langId, $isActive]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * providerGetName
     *
     * @return array
     */
    public function providerGetName()
    {  
       return [
            [false, 'test', 'test', true], // Invalid shopId and Invalid langId
            [false, 'test', 1, true], // Invalid shopId and Valid langId
            [false, 1, 'test', true], // Valid shopId and Invalid langId            
            ['Sammy', 1, 1, true],  // Valid shopId and Valid langId
            ['Samar', 2, 1, false],  // Valid shopId and Valid langId        
        ];
    }
    
    /**
     * getRewriteCustomUrl
     *
     * @dataProvider providerGetRewriteCustomUrl
     * @param  mixed $expected
     * @param  mixed $shopId
     * @return void
     */
    public function testGetRewriteCustomUrl($expected, $shopId)
    {
        $result = $this->execute($this->class, [], 'getRewriteCustomUrl', [$shopId]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * providerGetRewriteCustomUrl
     *
     * @return void
     */
    public function providerGetRewriteCustomUrl()
    {
        return [
            [false, 'test'], // Invalid shopId 
            [false, 1000], // Invalid shopId         
            ['sammy', 1],  // Valid shopId 
            ['samar', 2],  // Valid shopId     
        ];
    }
    
    /**
     * testSetFavorite
     *
     * @dataProvider providerSetFavorite
     * @param  bool $expected
     * @param  mixed $shopId
     * @param  mixed $userId
     * @return void
     */
    public function testSetFavorite(bool $expected, $shopId, $userId)
    {
        $result = $this->execute($this->class, [$shopId], 'setFavorite', [$userId]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * providerSetFavorite
     *
     * @return void
     */
    public function providerSetFavorite()
    {
        return [
            [false, 'test', 'test'], // Invalid shopId and Invalid userId
            [false, 'test', 1], // Invalid shopId and Valid userId
            [false, 1, 'test'], // Valid shopId and Invalid userId
            [true, 1, 1],  // Valid shopId and Valid userId  
            [true, 2, 1],  // Valid shopId and Valid userId       
            [true, 1, 2],  // Valid shopId and Valid userId 
            [true, 2, 2],  // Valid shopId and Valid userId
        ];
    }
    
}
