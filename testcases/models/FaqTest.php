<?php
class FaqTest extends YkModelTest
{
    private $class = 'Faq';

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
        $obj->insertFaqData();
        $obj->insertFaqLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".Faq::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Faq::DB_TBL_LANG);
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
            'faqcat_id'=>1, 'faqcat_identifier' => 'Test Faq Category', 'faqcat_active' => 1, 'faqcat_type' => 1,'faqcat_deleted'=>0, 'faqcat_display_order' => 1,
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
        ];            
        $this->InsertDbData(FaqCategory::DB_TBL_LANG, $arr);
    }
    /**
    * insertFaqData
    *
    * @return void
    */
    private function insertFaqData()
    {
        $arr = [
            [
            'faq_id'=>1, 'faq_faqcat_id' => 1, 'faq_identifier' => 'TestFaq1', 'faq_active' => 1,'faq_deleted'=>0, 'faq_display_order' => 1,
            'faq_featured' => 1
            ]
        ];
        $this->InsertDbData(Faq::DB_TBL, $arr);       
    }
    /**
    * insertFaqLangData
    *
    * @return void
    */
    private function insertFaqLangData()
    {
        $arr = [
            [
                'faqlang_faq_id' => 1, 'faqlang_lang_id' => 1, 'faq_title'=> 'Test', 'faq_content'=> 'Test'
            ],
        ];            
        $this->InsertDbData(Faq::DB_TBL_LANG, $arr);
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