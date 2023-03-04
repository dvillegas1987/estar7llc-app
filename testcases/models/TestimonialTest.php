<?php
class TestimonialTest extends YkModelTest
{
    private $class = 'Testimonial';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertTestimonialData();
        $obj->insertTestimonialLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".Testimonial::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Testimonial::DB_TBL_LANG);
    }
    /**
    * insertTestimonialData
    *
    * @return void
    */
    private function insertTestimonialData()
    {
        $arr = [
            [
            'testimonial_id'=>1, 'testimonial_identifier'=>'Test', 'testimonial_active' => 1, 'testimonial_deleted' => 0,'testimonial_added_on'=>'2021-01-21', 'testimonial_user_name' => 'Vivek'
            ]
        ];
        $this->InsertDbData(Testimonial::DB_TBL, $arr);       
    }
    /**
    * insertTestimonialLangData
    *
    * @return void
    */
    private function insertTestimonialLangData()
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