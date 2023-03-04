<?php
class ContentPageTest extends YkModelTest
{
    private $class = 'ContentPage';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertContentPageData();
        $obj->insertContentPageLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".ContentPage::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".ContentPage::DB_TBL_LANG);
    }
    /**
    * insertContentPageData
    *
    * @return void
    */
    private function insertContentPageData()
    {
        $arr = [
            [
                'cpage_id' => 1, 'cpage_identifier' => 'About Us', 'cpage_layout' => 1, 'cpage_deleted' => 0,
            ],
            [
                'cpage_id' => 2, 'cpage_identifier' => 'Terms & Conditions', 'cpage_layout' => 2, 'cpage_deleted' => 0,
            ],
            [
                'cpage_id' => 3, 'cpage_identifier' => 'Test', 'cpage_layout' => 2, 'cpage_deleted' => 1,
            ],
        ];
        $this->InsertDbData(ContentPage::DB_TBL, $arr);       
    }
    /**
    * insertContentPageLangData
    *
    * @return void
    */
    private function insertContentPageLangData()
    {
        $arr = [
            [
                'cpagelang_cpage_id' => 1, 'cpagelang_lang_id' => 1, 'cpage_title'=> 'About Us', 'cpage_content' => 'Test', 'cpage_image_title' => 'About Us', 'cpage_image_content' => 'Test'
            ],
            [
                'cpagelang_cpage_id' => 2, 'cpagelang_lang_id' => 1, 'cpage_title'=> 'Terms & Conditions', 'cpage_content' => 'Test', 'cpage_image_title' => 'About Us', 'cpage_image_content' => 'Test'
            ],
        ];            
        $this->InsertDbData(ContentPage::DB_TBL_LANG, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedGetPagesForSelectBox
     * @param int langId
     * @param int ignoreCpageId
     * @return void
     */
    public function getPagesForSelectBox($expected, $langId, $ignoreCpageId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getPagesForSelectBox', [$langId, $ignoreCpageId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    /**
     * feedGetPagesForSelectBox
     *
     * @return array
     */
    public function feedGetPagesForSelectBox()
    {
        return [
            [0, '', 0],
            [2, 1, 0],
            [1, 1, 1]
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
            [false, 'test'],   //Invalid id
            [false, 10000],   //Invalid id
            [true, 1],   //Valid id
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedIsNotDeleted
     * @param  int $id
     * @return void
     */
    public function isNotDeleted($expected, $id)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'isNotDeleted', [$id]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedIsNotDeleted
     *
     * @return array
    */
    public function feedIsNotDeleted()
    {  
        return [
            [false, 'test'],   //Invalid id
            [true, 1],   //Valid id, deleted 0
            [false, 3],   //Valid id, deleted 1
        ];
    } 
}