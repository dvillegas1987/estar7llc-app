<?php
class BlogPostCategoryTest extends YkModelTest
{
    private $class = 'BlogPostCategory';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::truncateDbData();
        $obj = new self();
        $obj->insertBlogPostCategoryData();
        $obj->insertBlogPostCategoryLangData();
    }

    /**
     * truncateDbData
     *
     * @return void
     */
    public static function truncateDbData()
    {
        FatApp::getDb()->query("TRUNCATE TABLE " . BlogPostCategory::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE " . BlogPostCategory::DB_TBL_LANG);
    }
    /**
     * insertBlogPostCategoryData
     *
     * @return void
     */
    private function insertBlogPostCategoryData()
    {
        $arr = [
            [
                'bpcategory_id'             => 1,
                'bpcategory_identifier'     => 'Blog Category 1',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 1,
                'bpcategory_featured'       => 0,
                'bpcategory_active'         => 0,
                'bpcategory_deleted'        => 0,
            ],
            [
                'bpcategory_id'             => 2,
                'bpcategory_identifier'     => 'Blog Category 2',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 2,
                'bpcategory_featured'       => 0,
                'bpcategory_active'         => 1,
                'bpcategory_deleted'        => 0,
            ],
            [
                'bpcategory_id'             => 3,
                'bpcategory_identifier'     => 'Blog Category 3',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 3,
                'bpcategory_featured'       => 0,
                'bpcategory_active'         => 0,
                'bpcategory_deleted'        => 1,
            ],
            [
                'bpcategory_id'             => 4,
                'bpcategory_identifier'     => 'Blog Category 4',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 4,
                'bpcategory_featured'       => 1,
                'bpcategory_active'         => 0,
                'bpcategory_deleted'        => 0,
            ],
            [
                'bpcategory_id'             => 5,
                'bpcategory_identifier'     => 'Blog Category 5',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 5,
                'bpcategory_featured'       => 1,
                'bpcategory_active'         => 1,
                'bpcategory_deleted'        => 0,
            ],
            [
                'bpcategory_id'             => 6,
                'bpcategory_identifier'     => 'Blog Category 6',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 6,
                'bpcategory_featured'       => 1,
                'bpcategory_active'         => 0,
                'bpcategory_deleted'        => 1,
            ],
            [
                'bpcategory_id'             => 7,
                'bpcategory_identifier'     => 'Blog Category 7',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 7,
                'bpcategory_featured'       => 1,
                'bpcategory_active'         => 1,
                'bpcategory_deleted'        => 1,
            ],
            [
                'bpcategory_id'             => 8,
                'bpcategory_identifier'     => 'Blog Category 8',
                'bpcategory_parent'         => 0,
                'bpcategory_display_order'  => 8,
                'bpcategory_featured'       => 0,
                'bpcategory_active'         => 1,
                'bpcategory_deleted'        => 1,
            ],
            [
                'bpcategory_id'             => 9,
                'bpcategory_identifier'     => 'Blog Sub Category 1',
                'bpcategory_parent'         => 2,
                'bpcategory_display_order'  => 9,
                'bpcategory_featured'       => 0,
                'bpcategory_active'         => 1,
                'bpcategory_deleted'        => 0,
            ],
        ];
        $this->InsertDbData(BlogPostCategory::DB_TBL, $arr);
    }

    /**
     * insertBlogPostCategoryData
     *
     * @return void
     */
    private function insertBlogPostCategoryLangData()
    {
        $arr = [
            [
                'bpcategorylang_bpcategory_id'  => 1,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 1',
            ],
            [
                'bpcategorylang_bpcategory_id'  => 2,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 2',

            ],
            [
                'bpcategorylang_bpcategory_id'  => 3,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 3',
            ],
            [
                'bpcategorylang_bpcategory_id'  => 4,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 4',

            ],
            [
                'bpcategorylang_bpcategory_id'  => 5,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 5',
            ],
            [
                'bpcategorylang_bpcategory_id'  => 6,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 6',

            ],
            [
                'bpcategorylang_bpcategory_id'  => 7,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 7',
            ],
            [
                'bpcategorylang_bpcategory_id'  => 8,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Category 8',

            ],
            [
                'bpcategorylang_bpcategory_id'  => 9,
                'bpcategorylang_lang_id'        => 1,
                'bpcategory_name'               => 'Blog Sub Category 1',
            ]
        ];
        $this->InsertDbData(BlogPostCategory::DB_TBL_LANG, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedGetMaxOrder
     * @param  int $parentId
     * @return void
     */
    public function getMaxOrder($parentId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_INT);
        $result = $this->execute($this->class, [], 'getMaxOrder', [$parentId]);
        $this->assertIsInt($result);
    }
    /**
     * feedGetMaxOrder
     *
     * @return array
     */
    public function feedGetMaxOrder()
    {
        return [
            [100], // Invalid parentId
            [1], // Valid parentId
            [0], // Valid blank parentId 
            ['dsdsdsd'], // Invalid parentId      
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetCategoryStructure
     * @param  int $bpcategory_id
     * @param  array $category_tree_array
     * @return void
     */
    public function getCategoryStructure($expected, $bpcategory_id, $category_tree_array)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getCategoryStructure', [$bpcategory_id, $category_tree_array]);
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
            [0, 'test', array()],  // Invalid bpcategory_id  
            [2, 9, array()],  // Valid bpcategory_id
            [1, 2, array()],  // Valid bpcategory_id
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetParentTreeStructure
     * @param  int $bpcategoryId
     * @param  int $level
     * @param  mixed $nameSuffix
     * @return void
     */
    public function getParentTreeStructure($expected, $bpcategoryId, $level, $nameSuffix)
    {
        $this->expectedReturnType(YkAppTest::TYPE_STRING);
        $result = $this->execute($this->class, [], 'getParentTreeStructure', [$bpcategoryId, $level, $nameSuffix]);
        $this->assertEquals($expected, $result);
    }

    /**
     * feedGetParentTreeStructure
     *
     * @return array
     */
    public function feedGetParentTreeStructure()
    {
        return [
            ['', 'test', 0, ''],  // Invalid bpcategoryId, Valid level, Blank suffix
            ['', 'test', 0, 'xxxx'],  // Invalid bpcategoryId, Valid level, Blank suffix   
            ['', 'test', 1, ''],  // Invalid bpcategoryId, Valid level, Blank suffix   
            ['', 'test', 1, 'xxxx'],  // Invalid bpcategoryId, Valid level, Blank suffix   
            ['Blog Category 2 &nbsp;&nbsp;&raquo;&raquo;&nbsp;&nbsp;Blog Sub Category 1', 9, 0, ''],  // Valid bpcategory_id, Valid level, Blank suffix   
            ['Blog Category 2', 2, 0, ''],  // Valid bpcategoryId, Valid level, Blank suffix   
            ['Blog Category 2xxxxx', 2, 0, 'xxxxx'],  // Valid bpcategoryId, Valid level, Blank suffix   
            ['Blog Category 2 &nbsp;&nbsp;&raquo;&raquo;&nbsp;&nbsp;', 2, 1, ''],  // Valid bpcategoryId, Valid level, Blank suffix   
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetBlogPostCatParentChildWiseArr
     * @param  int $langId
     * @return void
     */
    public function getBlogPostCatParentChildWiseArr($expected, $langId, $parentId, $includeChildCat, $forSelectBox)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getBlogPostCatParentChildWiseArr', [$langId, $parentId, $includeChildCat, $forSelectBox]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }

    /**
     * feedGetBlogPostCatParentChildWiseArr
     *
     * @return array
     */
    public function feedGetBlogPostCatParentChildWiseArr()
    {
        return [
            [0, 'test', 0, false, false],  // Return blank array, Invalid langId   
            [0, 1, 0, 'test', 'test'],  // Return blank array, Valid langId, valid parentId,  Invalid includeChildCat, invalid forSelectBox
            [0, 1, 0, false, 'test'],  // Return blank array, Valid langId, valid parentId,  Invalid includeChildCat, invalid forSelectBox
            [0, 1, 0, 'test', false],  // Return array, Valid langId  
            [2, 1, 0, true, false],  // Return array, Valid langId 
            [2, 1, 0, false, true],  // Return array, Valid langId 
            [2, 1, 0, false, false],  // Return array, Valid langId   
            [2, 1, 0, true, true],  // Return array, Valid langId   
            [1, 1, 2, true, true],  // Return array, Valid langId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetRootBlogPostCatArr
     * @param int $langId
     * @return void
     */
    public function getRootBlogPostCatArr($expected, $langId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getRootBlogPostCatArr', [$langId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }

    /**
     * feedGetRootBlogPostCatArr
     *
     * @return array
     */
    public function feedGetRootBlogPostCatArr()
    {
        return [
            [0, 'test'],  // Invalid langId   
            [2, 1],  // Valid langId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetCategoriesForSelectBox
     * @param  int $langId
     * @param  int $ignoreCategoryId
     * @return void
     */
    public function getCategoriesForSelectBox($expected, $langId, $ignoreCategoryId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getCategoriesForSelectBox', [$langId, $ignoreCategoryId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }

    /**
     * feedGetCategoriesForSelectBox
     *
     * @return array
     */
    public function feedGetCategoriesForSelectBox()
    {
        return [
            [0, 'test', 0],  //Return blank array, Invalid langId 
            [3, 1, 0],  // Return array, Valid langId  
            [1, 1, 2],  // Return array, Valid langId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetFeaturedCategories
     * @param  int $langId
     * @return void
     */
    public function getFeaturedCategories($expected, $langId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getFeaturedCategories', [$langId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, count($result));
    }

    /**
     * feedGetFeaturedCategories
     *
     * @return array
     */
    public function feedGetFeaturedCategories()
    {
        return [
            [0, 'test'],  //Return blank array, Invalid langId 
            [1, 1],  // Return array, Valid langId
        ];
    }
}
