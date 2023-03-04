<?php
class BlogPostTest extends YkModelTest
{
    private $class = 'BlogPost';

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
     * @test
     *
     * @dataProvider feedGetBlogPostsUnderCategory
     * @param  int $langId
     * @param  int $categoryId
     * @return void
     */
    public function getBlogPostsUnderCategory($expected, $langId, $categoryId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getBlogPostsUnderCategory', [$langId, $categoryId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    
    /**
     * feedGetBlogPostsUnderCategory
     *
     * @return array
     */
    public function feedGetBlogPostsUnderCategory()
    {  
        return [
            [0, 'test', 1],  // Invalid langId , Valid categoryId   
            [0, 'test', 1000],  // Invalid langId , Invalid categoryId   
            [0, 1, 1000],  // Valid langId , Invalid categoryId           
            [2, 1, 1],  // Valid langId , Valid categoryId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetPostCategories
     * @param int $postId
     * @return void
     */
    public function getPostCategories($expected, $postId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getPostCategories', [$postId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }
    
    /**
     * feedGetPostCategories
     *
     * @return array
     */
    public function feedGetPostCategories()
    {  
        return [
            [0, 'test'],  // Invalid postId
            [0, 1000],  // Invalid postId  
            [2, 1],  // Valid postId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedAddUpdateCategories
     * @param mixed $postId
     * @param mixed $categories
     * @return void
     */
    public function addUpdateCategories($expected, $postId, $categories)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'addUpdateCategories', [$postId, $categories]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * feedAddUpdateCategories
     *
     * @return array
     */
    public function feedAddUpdateCategories()
    {  
        return [
            [false, 'test', 'test'],  // Invalid postId, Invalid categories 
            [false, 1, 'test'],  // Valid postId, Invalid categories 
            [false, 'test', [1,2]],  // Invalid postId, Valid categories 
            [true, 1, [1,2]],  // Valid postId, Valid categories
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedUpdateImagesOrder
     * @param  int $postId
     * @param  int $order
     * @return void
     */
    public function updateImagesOrder($expected, $postId, $order)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'updateImagesOrder', [$postId, $order]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * feedUpdateImagesOrder
     *
     * @return array
     */
    public function feedUpdateImagesOrder()
    {  
        return [
            [false, 'test', 'test'],  // Invalid postId, Invalid order 
            [false, 1, 'test'],  // Valid postId, Invalid order 
            [false, 'test', [25001,25000]],  // Invalid postId, Valid order 
            [true, 1, [25001,25000]],  // Valid postId, Valid order
        ];
    }    

    /**
     * @test
     *
     * @dataProvider feedDeleteBlogPostImage
     * @param  int $postId
     * @param  int $imageId
     * @return void
     */
    public function deleteBlogPostImage($expected, $postId, $imageId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'deleteBlogPostImage', [$postId, $imageId]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * feedDeleteBlogPostImage
     *
     * @return array
     */
    public function feedDeleteBlogPostImage()
    {  
        return [
            [false, 'test', 'test'],  // Invalid postId, Invalid imageId 
            [false, 1, 'test'],  // Valid postId, Invalid imageId 
            [false, 'test', 25001],  // Invalid postId, Valid imageId 
            [true, 1, 25001],  // Valid postId, Valid imageId
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedSetPostViewsCount
     * @param  int $postId
     * @return void
     */
    public function setPostViewsCount($expected, $postId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'setPostViewsCount', [$postId]);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * feedSetPostViewsCount
     *
     * @return array
     */
    public function feedSetPostViewsCount()
    {  
        return [
            [false, 'test'],  // Invalid postId 
            [true, 1],  // Valid postId
        ];
    } 
}
