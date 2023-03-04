<?php
class ProductCategoryTest extends YkModelTest
{   
   
    /**
     * @dataProvider setCategoryData
     */
    public function testSaveCategoryData( $data, $mailTableRecordId, $expected )
    {
        $prodCat = new ProductCategory();
        if($mailTableRecordId > 0){
            $prodCat->setMainTableRecordId($mailTableRecordId);
        }        
        $result = $prodCat->saveCategoryData($data);
        $this->assertEquals($expected, $result);
    }
    
    public function setCategoryData()
    {     
        $data = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Men', ''), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // Existing Category
        
        $data1 = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Shoes'.rand(1, 9999), 'Shoes'.rand(1, 9999).' In Arabic'), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Root Category
        
        $data2 = array('prodcat_id' => 0, 'prodcat_parent' => 271, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Nike'.rand(1, 9999), 'Nike'.rand(1, 9999).' In Arabic'), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Sub Category
        
        $data3 = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 0, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Test'.rand(1, 9999), 'Test'.rand(1, 9999).' In Arabic'), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Root Category with Inactive status
        
        $data4 = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 1, 'prodcat_name' => array('', 'AutoUpdateLang'.rand(1, 9999), ''), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Root Category with auto update other lang data
        
        $data5 = array('prodcat_id' => 266, 'prodcat_parent' => 0, 'prodcat_active' => 0, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Unit Test'.rand(1, 9999), 'Unit Test Arabic'.rand(1, 9999)), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // Update Category name and status
        
        return array(
            array($data, 0, false),            
            array($data1, 0, true),
            array($data2, 0, true),
            //array($data3, 0, true),
            array($data4, 0, true),
            array($data5, 266, true),
        );
    }
    
    /**
     * @dataProvider setLangData
     */
    public function testSaveLangData( $prodCatId, $langId, $prodCatName, $expected )
    {
        $prodCat = new ProductCategory();
        $prodCat->setMainTableRecordId($prodCatId);     
        $result = $prodCat->saveLangData($langId, $prodCatName);
        $this->assertEquals($expected, $result);
    }
    
    public function setLangData()
    { 
        return array(
            array(0, 1, 'Unit Test3056', false), // Invalid category id
            array('test', 1, 'Unit Test3056', false), // Invalid category id
            array(266, 0, 'Unit Test3056', false), // Invalid lang id
            array(266, 'test', 'Unit Test3056', false), // Invalid lang id
            array(266, 1, 'Unit Test3056', true), // valid data
        );
    }
    
    /**
     * @dataProvider setTranslatedLangData
     */
    public function testSaveTranslatedLangData( $prodCatId, $langId, $expected )
    {
        $prodCat = new ProductCategory();
        $prodCat->setMainTableRecordId($prodCatId);     
        $result = $prodCat->saveTranslatedLangData($langId);
        $this->assertEquals($expected, $result);
    }
    
    public function setTranslatedLangData()
    { 
        return array(
            array(0, 1, false), // Invalid category id
            array('test', 1, false), // Invalid category id
            array(266, 0, false), // Invalid lang id
            array(266, 'test', false), // Invalid lang id
            array(266, 2, true), // valid data
        );
    }

    
    /**
     * @dataProvider setUpdateMedia
     */
    public function testUpdateMedia( $prodCatId, $iconImageIds, $expected )
    {
        $prodCat = new ProductCategory();
        $prodCat->setMainTableRecordId($prodCatId);     
        $result = $prodCat->updateMedia($iconImageIds);
        $this->assertEquals($expected, $result);
    }
    
    public function setUpdateMedia()
    { 
        return array(
            array(266, array(), false), // Invalid image ids            
            array(266, array(2429), true), // valid data
        );
    }
    
    /**
     * @dataProvider dataGetCategories
     */
    public function testGetCategories( $prodCatId, $includeProductCount, $includeSubCategoriesCount, $expected )
    {
        $prodCat = new ProductCategory();
        $prodCat->setMainTableRecordId($prodCatId);     
        $result = $prodCat->getCategories($includeProductCount, $includeSubCategoriesCount);
        $this->$expected($result);
    }
    
    public function dataGetCategories()
    { 
        return array(
            array(656545, true, true, 'assertEmpty'), // Invalid data 
            array(0, true, true, 'assertNotEmpty'), // Get all root categories
            array(112, true, true, 'assertNotEmpty'), // Get sub categories
        );
    }
    
    /**
     * @dataProvider dataGetSubCategoriesCount
     */
    public function testGetSubCategoriesCount( $prodCatId, $expected )
    {
        $prodCat = new ProductCategory(); 
        $result = $prodCat->getSubCategoriesCount($prodCatId);
        $this->assertEquals($expected, $result);
    }
    
    public function dataGetSubCategoriesCount()
    { 
        return array(
            array(4564564554545, 0), // Invalid data
            array(112, 3), // Valid data 
        );
    }
    
    /**
     * @dataProvider dataGetActiveInactiveCategoriesCount
     */
    public function testGetActiveInactiveCategoriesCount( $status, $expected )
    {
        $result = ProductCategory::getActiveInactiveCategoriesCount($status);
        $this->assertEquals($expected, $result['categories_count']);
    }
    
    public function dataGetActiveInactiveCategoriesCount()
    { 
        return array(
            array(545454, 0), // Invalid data
            array(1, 84), // Active categories
            array(0, 5), // Inactive categories
        );
    }
    
    /**
     * @dataProvider dataDeleteImagesWithOutCategoryId
     */
    public function testDeleteImagesWithOutCategoryId( $fileType, $expected )
    {
        $result = ProductCategory::deleteImagesWithOutCategoryId($fileType);
        $this->assertEquals($expected, $result);
    }
    
    public function dataDeleteImagesWithOutCategoryId()
    { 
        return array(
            array('', false), // Invalid data
            array('test', false), // Invalid data
            array(AttachedFile::FILETYPE_CATEGORY_ICON, true), // Removed category icon
            array(AttachedFile::FILETYPE_CATEGORY_BANNER, true), // Removed category banner
        );
    }
    
    /**
     * @dataProvider dataUpdateCatParent
     */
    public function testUpdateCatParent($prodCatId, $parentCatId, $expected )
    {
        $prodCat = new ProductCategory(); 
        $prodCat->setMainTableRecordId($prodCatId);     
        $result = $prodCat->updateCatParent($parentCatId);
        $this->assertEquals($expected, $result);
    }
    
    public function dataUpdateCatParent()
    { 
        return array(
            array(0, 0, false), // Invalid category id
            array('test', 0, false), // Invalid category id
            array(109, 0, true), // Valid data
        );
    }
    
    /**
     * @dataProvider dataGetTranslatedCategoryData
     */
    public function testGetTranslatedCategoryData( $data, $toLangId, $expected )
    {
        $prodCat = new ProductCategory();     
        $result = $prodCat->getTranslatedCategoryData($data, $toLangId);
        $this->assertEquals($expected, $result);
    }
    
    public function dataGetTranslatedCategoryData()
    { 
        return array(
            array(array(), 2, false), // Invalid Data
            array(array('prodcat_name' => 'test'), 0, false), // Invalid lang id
            array(array('prodcat_name' => 'test'), 2, true), // Valid Data
        );
    }
    
    
    
    
    
}