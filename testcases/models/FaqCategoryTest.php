<?php
class FaqCategoryTest extends YkModelTest
{
    private $class = 'FaqCategory';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertFaqCategoryData();
        $obj->insertFaqCategoryLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".FaqCategory::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".FaqCategory::DB_TBL_LANG);
    }
    /**
    * insertFaqCategoryData
    *
    * @return void
    */
    private function insertFaqCategoryData()
    {
        $arr = [
            [
            'faqcat_id'=>1, 'faqcat_identifier' => 'Test Faq Category', 'faqcat_active' => 1, 'faqcat_type' => 0,'faqcat_deleted'=>0, 'faqcat_display_order' => 1,
            'faqcat_featured' => 1
            ],
            [
                'faqcat_id'=>2, 'faqcat_identifier' => 'Test Faq Category2', 'faqcat_active' => 1, 'faqcat_type' => 1,'faqcat_deleted'=>0, 'faqcat_display_order' => 1,
                'faqcat_featured' => 1
            ]
        ];
        $this->InsertDbData(FaqCategory::DB_TBL, $arr);       
    }
    /**
    * insertFaqCategoryLangData
    *
    * @return void
    */
    private function insertFaqCategoryLangData()
    {
        $arr = [
            [
                'faqcatlang_faqcat_id' => 1, 'faqcatlang_lang_id' => 1, 'faqcat_name'=> 'Test Faq Category'
            ],
            [
                'faqcatlang_faqcat_id' => 2, 'faqcatlang_lang_id' => 1, 'faqcat_name'=> 'Test Faq Category2'
            ],
        ];            
        $this->InsertDbData(FaqCategory::DB_TBL_LANG, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedGetFaqCatTypeArr
     * @param int $langId
     * @return void
     */
    public function getFaqCatTypeArr($expected, $langId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getFaqCatTypeArr', [$langId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    /**
     * feedGetFaqCatTypeArr
     *
     * @return array
     */
    public function feedGetFaqCatTypeArr()
    {
        return [
            [2, ''],
            [2, 1]
        ];
    }     

    /**
     * @test
     *
     * @dataProvider feedGetCategoryStructure
     * @return void
     */
    public function getCategoryStructure($expected)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getCategoryStructure');
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    /**
     * feedGetCategoryStructure
     *
     * @return array
     */
    public function feedGetCategoryStructure()
    {
        return [
            [2]
        ];
    } 
    /**
     * @test
     *
     * @dataProvider feedGetFaqPageCategories
     * @return void
     */
    public function getFaqPageCategories($expected)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getFaqPageCategories');
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    /**
     * feedGetFaqPageCategories
     *
     * @return array
     */
    public function feedGetFaqPageCategories()
    {
        return [
            [1]
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetSellerPageCategories
     * @return void
     */
    public function getSellerPageCategories($expected)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getSellerPageCategories');
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    /**
     * feedGetSellerPageCategories
     *
     * @return array
     */
    public function feedGetSellerPageCategories()
    {
        return [
            [1]
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetMaxOrder
     * @return void
     */
    public function getMaxOrder($expected)
    {
        $this->expectedReturnType(YkAppTest::TYPE_INT);
        $result = $this->execute($this->class, [], 'getMaxOrder');
        $this->assertEquals($expected, $result);
    }
    /**
     * feedGetMaxOrder
     *
     * @return array
     */
    public function feedGetMaxOrder()
    {
        return [
            [2]
        ];
    } 
}