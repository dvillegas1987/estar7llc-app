<?php
class BannerTest extends YkModelTest
{
    private $class = 'Banner';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertBannerData();
        $obj->insertBannerLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".Banner::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Banner::DB_TBL_LANG);
    }
    /**
    * insertBannerData
    *
    * @return void
    */
    private function insertBannerData()
    {
        $arr = [
            [
            'banner_id'=>1, 'banner_blocation_id'=>'Test', 'banner_type' => 1, 'banner_record_id' => 0,'banner_url'=>'2021-01-21', 'banner_target' => 'https://www.google.com/', 'banner_added_on' => '2021-01-21 00:00:00', 'banner_start_date' => '2021-01-21', 'banner_end_date' => '2021-01-21', 'banner_start_time' => '00:00:00', 'banner_end_time' => '00:00:00', 'banner_active' => 1, 'banner_deleted' => 0, 'banner_display_order' => 1, 'banner_updated_on' => '2021-01-01 00:00:00'
            ]
        ];
        $this->InsertDbData(Testimonial::DB_TBL, $arr);       
    }
    /**
    * insertBannerLangData
    *
    * @return void
    */
    private function insertBannerLangData()
    {
        $arr = [
            [
                'testimoniallang_testimonial_id'=> 1, 'testimoniallang_lang_id'=>1, 'testimonial_title'=> 'Test', 'testimonial_text'=> 'Test'
            ],
        ];            
        $this->InsertDbData(Testimonial::DB_TBL_LANG, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedCanRecordMarkDelete
     * @param  int $testimonialId
     * @return void
     */
    public function canRecordMarkDelete($expected, $testimonialId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'canRecordMarkDelete', [$testimonialId]);
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
            [false, 'test'],   //Invalid testimonialId
            [false, 10000],   //Invalid testimonialId
            [true, 1],   //Valid testimonialId
        ];
    } 
}