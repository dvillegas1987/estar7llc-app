<?php
class ProductTest extends YkModelTest
{   
   
    /**
     * @test
     *
     * @dataProvider feedProductData
     * @param  array $data
     * @return void
     */
    public function saveProductData( $expected, $data )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId($data['product_id']);
        $result = $prod->saveProductData($data);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedProductData
     *
     * @return array
     */
    public function feedProductData()
    {            
        $data = array('product_id' => 0, 'product_identifier' => 'test unit product', 'product_type' => 1, 'product_brand_id' => 111, 'product_min_selling_price' => 280, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'test prod', 'product_featured' => 1, 'product_cod_enabled' => 0, 'product_dimension_unit' => 2, 'product_length' => 20, 'product_width' => 30, 'product_height' => 40, 'product_weight_unit' => 2, 'product_weight' => 10); // Add new product
        
        $data1 = array('product_id' => 0, 'product_identifier' => 'test unit product', 'product_type' => 2, 'product_brand_id' => 113, 'product_min_selling_price' => 150, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'digi', 'product_featured' => 0); // Duplicate product Identifier
        
        $data2 = array('product_id' => 111, 'product_identifier' => 'fastfood', 'product_type' => 1, 'product_brand_id' => 111, 'product_min_selling_price' => 280, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'test prod', 'product_featured' => 1, 'product_cod_enabled' => 0, 'product_dimension_unit' => 2, 'product_length' => 5, 'product_width' => 6, 'product_height' => 7, 'product_weight_unit' => 2, 'product_weight' => 8); // Update existing product
        
        return [
            [true, $data],
            [false, $data1],
            [true, $data2],
        ];
    }
    
    /**
     * @test
     *
     * @dataProvider feedProductLangData
     * @param  array $data
     * @param int $mainTableRecordId
     * @return void
     */
    public function saveProductLangData( $expected, $data, $mainTableRecordId )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId( $mainTableRecordId );
        $result = $prod->saveProductLangData($data);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedProductLangData
     *
     * @return array
     */
    public function feedProductLangData()
    {  
        $data = array(
            'product_name' => array('1' => 'test unit product'), 
            'product_description_1' => 'test unit product decsription in english for first editor', 
            'product_youtube_video' => array('1' => 'video url in english'),                     
        );
        
        return array(
            array(false, $data, 0),     // Product id with 0
            array(true, $data, 140),    // Update existing product
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedProductCategory
     * @param int $categoryId
     * @param int $mainTableRecordId
     * @return void
     */
    public function saveProductCategory( $expected, $categoryId, $mainTableRecordId )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId( $mainTableRecordId );
        $result = $prod->saveProductCategory($categoryId);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedProductCategory
     *
     * @return array
     */
    public function feedProductCategory()
    {  
        return array(
            array(false, 0, 0), //Product id and category id is 0
            array(false, 0, 140), //Category id is 0
            array(false, 170, 0), //Product id is 0
            array(true, 170, 140), // Valid category id and product id
        );
    }    
    
     /**
     * @test
     *
     * @dataProvider feedSaveProductTax
     * @param int $taxId
     * @param int $mainTableRecordId
     * @param int $userId
     * @return void
     */
    public function saveProductTax( $expected, $taxId, $mainTableRecordId, $userId )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId( $mainTableRecordId );
        $result = $prod->saveProductTax($taxId, $userId);
        $this->assertEquals($expected, $result);
    }
     /**
     * feedSaveProductTax
     *
     * @return array
     */
    public function feedSaveProductTax()
    {  
        return array(
            array(false, 0, 0, 0), //Product id and tax id is 0
            array(false, 0, 140, 0), //Tax id is 0
            array(false, 4, 0, 0, ), //Product id is 0
            array(true, 4, 140, 0), // Valid category id and product id
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedsaveProductSellerShipping
     * @param int $mainTableRecordId
     * @param int $prodSellerId
     * @param mixed $psFree
     * @param int $psCountryId
     * @return void
     */
    public function saveProductSellerShipping( $expected, $mainTableRecordId, $prodSellerId, $psFree, $psCountryId )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId( $mainTableRecordId );
        $result = $prod->saveProductSellerShipping($prodSellerId, $psFree, $psCountryId);
        $this->assertEquals($expected, $result);
    }
     /**
     * feedsaveProductSellerShipping
     *
     * @return array
     */
    public function feedsaveProductSellerShipping()
    {  
        return array(
            array(false, 0, 0, 0, 0, 0), //Invalid Data
            array(true, 140, 0, 1, 0), //Valid Data            
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedProductSpecifications
     * @param int $mainTableRecordId
     * @param int $prodSpecId
     * @param int $langId
     * @param mixed $prodSpecName
     * @param mixed $prodSpecValue
     * @param mixed $prodSpecGroup
     * @return void
     */
    public function saveProductSpecifications( $expected, $mainTableRecordId, $prodSpecId, $langId, $prodSpecName, $prodSpecValue, $prodSpecGroup )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId( $mainTableRecordId );
        $result = $prod->saveProductSpecifications($prodSpecId, $langId, $prodSpecName, $prodSpecValue, $prodSpecGroup);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedProductSpecifications
     *
     * @return array
     */ 
    public function feedProductSpecifications()
    {  
        return array(
            array(false, 0, 0, 1, 'test name', 'test value', 'test group'), //Invalid product id
            array(false, 140, 0, 0, 'test name', 'test value', 'test group'), //Invalid lang id
            array(false, 140, 0, 1, '', 'test value', 'test group'), //invalid product specification name
            array(false, 140, 0, 1, 'test name', '', 'test group'), //invalid product specification value
            array(true, 140, 0, 1, 'test name', 'test value', 'test group'), //Add specification
            array(true, 140, 343, 1, 'test update name', 'test update value', 'test update group'), //update specification 
        );
    }
    
   /**
     * @test
     *
     * @dataProvider feedProdSpecificationsByLangId
     * @param int $mainTableRecordId
     * @param int $langId
     * @return void
     */
    public function getProdSpecificationsByLangId( $expected, $mainTableRecordId, $langId )
    {
        $prod = new Product();    
        $prod->setMainTableRecordId( $mainTableRecordId );
        $result = $prod->getProdSpecificationsByLangId($langId);
        $this->$expected($result);
    }
     /**
     * feedProdSpecificationsByLangId
     *
     * @return array
     */ 
    public function feedProdSpecificationsByLangId()
    {  
        return array(
            array('assertFalse', 0, 1), //Invalid product id
            array('assertFalse', 140, 0), //Invalid lang id
            array('assertIsArray', 140, 1), //Valid data
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedProductSpecificsDetails
     * @param int $productId
     * @return void
     */
    public function getProductSpecificsDetails( $expected, $productId )
    {
        $result = Product::getProductSpecificsDetails($productId);
        $this->$expected($result);
    }
    /**
     * feedProductSpecificsDetails
     *
     * @return array
     */ 
    public function feedProductSpecificsDetails()
    {  
        return array(
            array('assertFalse', 0), //Invalid product id
            array('assertEmpty', 140), //Valid data with no record
            array('assertIsArray', 124), //Valid data having records
        );
    }
    /**
     * @test
     *
     * @dataProvider feedAddUpdateProductOption
     * @param int $productId
     * @param int $optionId
     * @return void
     */
    public function addUpdateProductOption( $expected, $productId, $optionId )
    {
        $prod = new Product($productId);
        $result = $prod->addUpdateProductOption($optionId);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedAddUpdateProductOption
     *
     * @return array
     */ 
    public function feedAddUpdateProductOption()
    {  
        return array(
            array(false, 0, 42), //Invalid product id
            array(false, 140, 0), //Invalid option id
            array(true, 140, 42), //Valid data
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedRemoveProductOption
     * @param int $productId
     * @param int $optionId
     * @return void
     */
    public function removeProductOption( $expected, $productId, $optionId )
    {
        $prod = new Product($productId);
        $result = $prod->removeProductOption($optionId);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedRemoveProductOption
     *
     * @return array
     */ 
    public function feedRemoveProductOption()
    {  
        return array(
            array(false, 0, 42), //Invalid product id
            array(false, 140, 0), //Invalid option id
            array(true, 140, 42), //Valid data
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedAddUpdateProductTag
     * @param int $productId
     * @param int $tagId
     * @return void
     */
    public function addUpdateProductTag( $expected, $productId, $tagId )
    {
        $prod = new Product($productId);
        $result = $prod->addUpdateProductTag($tagId);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedAddUpdateProductTag
     *
     * @return array
     */ 
    public function feedAddUpdateProductTag()
    {  
        return array(
            array(false, 0, 54), //Invalid product id
            array(false, 140, 0), //Invalid tag id
            array(true, 140, 54), //Valid data
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedRemoveProductTag
     * @param int $productId
     * @param int $tagId
     * @return void
     */
    public function removeProductTag( $expected, $productId, $tagId )
    { 
        $prod = new Product($productId);
        $result = $prod->removeProductTag($tagId);
        $this->assertEquals($expected, $result);
    }
    /**
     * feedRemoveProductTag
     *
     * @return array
     */ 
    public function feedRemoveProductTag()
    {  
        return array(
            array(false, 0, 54), //Invalid product id
            array(false, 140, 0), //Invalid tag id
            array(true, 140, 54), //Valid data
        );
    }
    
    /**
     * @test
     *
     * @dataProvider feedAddUpdateProductShippingRates
     * @param int $productId
     * @param array $data
     * @param int $userId
     * @return void
     */
    public function addUpdateProductShippingRates( $expected, $productId, $data, $userId )
    {
        $result = Product::addUpdateProductShippingRates( $productId, $data, $userId );
        $this->assertEquals($expected, $result);
    }
    /**
     * feedAddUpdateProductShippingRates
     *
     * @return array
     */
    public function feedAddUpdateProductShippingRates()
    {  
        $data = array(array('country_id' =>'156', 'company_id' =>'1', 'processing_time_id' => '2', 'cost' => '250', 'additional_cost' => '25'));
        
        return array(
            array(false, 0, array(), 0), //Invalid product id with empty data
            array(false, 140, array(), 0), //Valid product id with empty data
            array(true, 140, $data, 0), //Valid product id with data
        );
    }
    
}