<?php
class PromotionTest extends YkModelTest
{
    private $class = 'Promotion';

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
        $obj->inserPromotionData();
        $obj->inserPromotionLangData();
        $obj->inserPromotionChargeData();
        $obj->inserPromotionClickData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".Promotion::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Promotion::DB_TBL_LANG);
        FatApp::getDb()->query("TRUNCATE TABLE ".Promotion::DB_TBL_CHARGES);
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
    * inserPromotionData
    *
    * @return void
    */     
    private function inserPromotionData()
    {
        $arr = [
            [
            'promotion_id' => 1, 'promotion_identifier' => 'vivek', 'promotion_user_id' => 1, 'promotion_type' => 1, 'promotion_record_id' => 1, 'promotion_budget'=>1, 'promotion_cpc' => 1, 'promotion_duration' => 0, 'promotion_start_date' => '2021-01-25', 'promotion_end_date' => '2021-01-29', 'promotion_start_time' => '00:00:00', 'promotion_end_time' => '00:00:00', 'promotion_active' => 1, 'promotion_added_on' => '2021-01-29 00:00:00', 'promotion_approved' => 1, 'promotion_deleted' => 0
            ]
        ];
        $this->InsertDbData(Promotion::DB_TBL, $arr);
    }

    /**
    * inserPromotionLangData
    *
    * @return void
    */ 
    private function inserPromotionLangData()
    {
        $arr = [
            [
                'promotionlang_promotion_id' => 1, 'promotionlang_lang_id' => 1, 'promotion_name' => 'Test'
            ],
        ];            
        $this->InsertDbData(Promotion::DB_TBL_LANG, $arr);
    }

    /**
    * inserPromotionChargeData
    *
    * @return void
    */     
    private function inserPromotionChargeData()
    {
        $arr = [
            [
            'pcharge_user_id' => 1, 'pcharge_promotion_id' => 1, 'pcharge_charged_amount' => 1, 'pcharge_clicks' => 1, 'pcharge_date' => '2021-01-27 00:00:00', 'pcharge_start_piclick_id'=>0, 'pcharge_end_piclick_id' => 0, 'pcharge_start_date' => '2021-01-26 00:00:00', 'pcharge_end_date' => '2021-01-29 00:00:00'
            ]
        ];
        $this->InsertDbData(Promotion::DB_TBL_CHARGES, $arr);
    }

    /**
    * inserPromotionClickData
    *
    * @return void
    */     
    private function inserPromotionClickData()
    {
        $arr = [
            [
            'pclick_promotion_id' => 1, 'pclick_user_id' => 1, 'pclick_datetime' => '2021-01-28 00:00:00', 'pclick_ip' => '192.12.12.1', 'pclick_cost' => 1, 'pclick_session_id'=>0
            ]
        ];
        $this->InsertDbData(Promotion::DB_TBL_CLICKS, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedGetPromotionCostPerClick
     * @param  int $promotionType
     * @param  int $blocation_id
     * @return void
     */
    public function getPromotionCostPerClick($expected, $promotionType, $blocation_id)
    {
        $this->expectedReturnType(YkAppTest::TYPE_STRING);
        $result = $this->execute($this->class, [], 'getPromotionCostPerClick', [$promotionType, $blocation_id]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedGetPromotionCostPerClick
     *
     * @return array
    */
    public function feedGetPromotionCostPerClick()
    {  
        return [
            ['', 'test', 0],   //Invalid promotionType
            [1, 1, 0],   //Promotion type - Shop  
            [1, 2, 0],   //Promotion type - Product
            ['', 3, 0],   //Promotion type - Banner  
            [2, 4, 0],   //Promotion type - Slides   
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetPromotionLastChargedEntry
     * @param  int $promotionType
     * @return void
     */
    public function getPromotionLastChargedEntry($expected, $promotionType)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getPromotionLastChargedEntry', [$promotionType]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }    
    /**
     * feedGetPromotionLastChargedEntry
     *
     * @return array
    */
    public function feedGetPromotionLastChargedEntry()
    {  
        return [
            [0, 'test'],   //Invalid promotionType
            [10, 1],   //Promotion type - Shop  
            [0, 2],   //Promotion type - Product
            [0, 3],   //Promotion type - Banner  
            [0, 4],   //Promotion type - Slides   
        ];
    }

     /**
     * @test
     *
     * @dataProvider feedGetTotalChargedAmount
     * @param  int $userId
     * @param  bool $active
     * @return void
     */
    public function getTotalChargedAmount($expected, $userId, $active)
    {
        $result = $this->execute($this->class, [], 'getPromotionLastChargedEntry', [$userId, $active]);
        $this->assertEquals($expected, count($result));
    }    
    /**
     * feedGetTotalChargedAmount
     *
     * @return array
    */
    public function feedGetTotalChargedAmount()
    {  
        return [
            [0, 'test'],   //Invalid promotionType
            [10, 1],   //Promotion type - Shop  
            [0, 2],   //Promotion type - Product
            [0, 3],   //Promotion type - Banner  
            [0, 4],   //Promotion type - Slides   
        ];
    }
    
}