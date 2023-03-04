<?php
class BlogCommentTest extends YkModelTest
{
    private $class = 'BlogComment';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertBlogPostData();
        $obj->insertBlogPostLangData(); 
        $obj->insertBlogPostImageData(); 
        $obj->insertBlogPostToCategoryData(); 
        $obj->insertBlogPostCommentData(); 
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
        FatApp::getDb()->query("TRUNCATE TABLE ".BlogPost::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".BlogPost::DB_TBL_LANG);
        FatApp::getDb()->query("TRUNCATE TABLE ".BlogPost::DB_POST_TO_CAT_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".BlogComment::DB_TBL);
        FatApp::getDb()->query("DELETE FROM ".AttachedFile::DB_TBL." WHERE afile_type = ".AttachedFile::FILETYPE_BLOG_POST_IMAGE);
    }    
    
    /**
    * insertBlogPostData
    *
    * @return void
    */
    private function insertBlogPostData()
    {
        $arr = [
            [
                'post_id' => 1,
                'post_identifier' => 'Test Blog 1',
                'post_published' => 1,
                'post_comment_opened' => 1,  
                'post_featured' => 0, 
                'post_view_count' => 0, 
                'post_deleted' => 0, 
            ],
            [
                'post_id' => 2,
                'post_identifier' => 'Test Blog 2',
                'post_published' => 1,
                'post_comment_opened' => 1,  
                'post_featured' => 1, 
                'post_view_count' => 0, 
                'post_deleted' => 0, 
                
            ],
            [
                'post_id' => 3,
                'post_identifier' => 'Test Blog 3',
                'post_published' => 1,
                'post_comment_opened' => 1,  
                'post_featured' => 1, 
                'post_view_count' => 0, 
                'post_deleted' => 1, 
                
            ]
        ];            
        $this->InsertDbData(BlogPost::DB_TBL, $arr);
    }
    
    /**
    * insertBlogPostLangData
    *
    * @return void
    */
    private function insertBlogPostLangData()
    {
        $arr = [
            [
                'postlang_post_id' => 1,
                'postlang_lang_id' => 1,
                'post_author_name' => 'Test User 1',
                'post_title' => 'Test Blog 1',
                'post_short_description' => 'Test Blog 1 Short Description',
                'post_description' => 'Test Blog 1 Long Description',                         
            ],
            [
                'postlang_post_id' => 2,
                'postlang_lang_id' => 1,
                'post_author_name' => 'Test User 2',
                'post_title' => 'Test Blog 2',
                'post_short_description' => 'Test Blog 2 Short Description',
                'post_description' => 'Test Blog 2 Long Description', 
            ],
        ];            
        $this->InsertDbData(BlogPost::DB_TBL_LANG, $arr);
    } 
    /**
    * insertBlogPostImageData
    *
    * @return void
    */
    private function insertBlogPostImageData()
    {
        $arr = [
            [
                'afile_id' => 25000,
                'afile_type' => AttachedFile::FILETYPE_BLOG_POST_IMAGE,
                'afile_record_id' => 1,
                'afile_record_subid' => 0,
                'afile_lang_id' => 1,
                'afile_screen' => 1,   
                'afile_physical_path' => '2017/07/1500283738-1jpg',
                'afile_name' => '1.jpg',
                'afile_aspect_ratio' => 0,
                'afile_display_order' => 1, 
            ],
            [
                'afile_id' => 25001,
                'afile_type' => AttachedFile::FILETYPE_BLOG_POST_IMAGE,
                'afile_record_id' => 1,
                'afile_record_subid' => 0,
                'afile_lang_id' => 1,
                'afile_screen' => 1,   
                'afile_physical_path' => '2017/07/1500295354-1jpg',
                'afile_name' => '1.jpg',
                'afile_aspect_ratio' => 0,
                'afile_display_order' => 2, 
            ],
            [
                'afile_id' => 25002,
                'afile_type' => AttachedFile::FILETYPE_BLOG_POST_IMAGE,
                'afile_record_id' => 2,
                'afile_record_subid' => 0,
                'afile_lang_id' => 1,
                'afile_screen' => 1,   
                'afile_physical_path' => '2017/07/1500295354-1jpg',
                'afile_name' => '1.jpg',
                'afile_aspect_ratio' => 0,
                'afile_display_order' => 1, 
            ],
        ];            
        $this->InsertDbData(AttachedFile::DB_TBL, $arr);
    } 

    /**
    * insertBlogPostToCategoryData
    *
    * @return void
    */
    private function insertBlogPostToCategoryData()
    {
        $arr = [
            [
                'ptc_bpcategory_id' => 1,
                'ptc_post_id' => 1                
            ],
            [
                'ptc_bpcategory_id' => 1,
                'ptc_post_id' => 2                
            ],
            [
                'ptc_bpcategory_id' => 2,
                'ptc_post_id' => 2                
            ],
            [
                'ptc_bpcategory_id' => 2,
                'ptc_post_id' => 1               
            ],
        ];            
        $this->InsertDbData(BlogPost::DB_POST_TO_CAT_TBL, $arr);
    } 
    /**
    * insertBlogPostCommentData
    *
    * @return void
    */
    private function insertBlogPostCommentData()
    {
        $arr = [
            [
                'bpcomment_post_id' => 1,
                'bpcomment_user_id' => 1,
                'bpcomment_author_name' => 'Test User 2',
                'bpcomment_author_email' => 'testuser2@gmail.com',
                'bpcomment_content' => 'Test comment',
                'bpcomment_approved' => 1,                         
                'bpcomment_deleted' => 0, 
                'bpcomment_added_on' => '2021-01-27 00:00:00', 
                'bpcomment_user_ip' => '192.22.22.1',
                'bpcomment_user_agent' => '',  
            ],
        ];            
        $this->InsertDbData(BlogComment::DB_TBL, $arr);
    } 
    
    /**
     * @test
     *
     * @dataProvider feedCanMarkRecordDelete
     * @param  int $bpcommentId
     * @return void
     */
    public function canMarkRecordDelete($expected, $bpcommentId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'canMarkRecordDelete', [$bpcommentId]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedCanMarkRecordDelete
     *
     * @return array
    */
    public function feedCanMarkRecordDelete()
    {  
        return [
            [false, 'test'],   //Invalid bpcommentId
            [false, 10000],   //Invalid bpcommentId
            [true, 1],   //Valid bpcommentId
        ];
    } 
}
