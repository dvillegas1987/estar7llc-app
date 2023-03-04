<?php
class UserFavoriteTest extends YkModelTest
{
    private $class = 'UserFavorite';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertUserData();
        $obj->insertProductData(); 
        $obj->insertSellerProductData(); 
        $obj->insertUserFavoriteData();
    }

    /**
     * tearDownAfterClass
     *
     * @return void
     */
    // public static function tearDownAfterClass() :void
    // {   
    //     self::truncateDbData();
    // }
    
    /**
     * truncateDbData
     *
     * @return void
     */
    public static function truncateDbData()
    {
        FatApp::getDb()->query("TRUNCATE TABLE ".User::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Product::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".SellerProduct::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".UserFavorite::DB_TBL);
    }    
    
    /**
    * insertUserData
    *
    * @return void
    */
    private function insertUserData()
    {
        $arr = [
            [
                'user_id' => 1,'user_name' => 'Sammy','user_zip' => 85281,'user_country_id' => 223,'user_state_id' => 2996,'user_is_buyer' => 1,'user_is_supplier' => 1,
            ],
            [
                'user_id' => 2,'user_name' => 'Samar', 'user_zip' => 85281, 'user_country_id' => 223,'user_state_id' => 2996,'user_is_buyer' => 1, 'user_is_supplier' => 1,
            ]
        ];            
        $this->InsertDbData(User::DB_TBL, $arr);
    }
    /**
    * insertProductData
    *
    * @return void
    */
    private function insertProductData()
    {
        $data = array('product_id' => 0, 'product_identifier' => 'test unit product', 'product_type' => 1, 'product_brand_id' => 111, 'product_min_selling_price' => 280, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'test prod', 'product_featured' => 1, 'product_cod_enabled' => 0, 'product_dimension_unit' => 2, 'product_length' => 20, 'product_width' => 30, 'product_height' => 40, 'product_weight_unit' => 2, 'product_weight' => 10);
        
        $data1 = array('product_id' => 0, 'product_identifier' => 'test unit product 1', 'product_type' => 2, 'product_brand_id' => 113, 'product_min_selling_price' => 150, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'digi', 'product_featured' => 0);
                
        $arr = [
            [$data],
            [$data1],
        ];            
        $this->InsertDbData(Product::DB_TBL, $arr);
    }
    /**
    * insertSellerProductData
    *
    * @return void
    */
    private function insertSellerProductData()
    {
        $data = array('selprod_id' => 0, 'selprod_user_id' => 1, 'selprod_product_id' => 1, 'selprod_code' => 111, 'selprod_price' => 280, 'selprod_cost' => 100, 'selprod_min_order_qty' => 1, 'selprod_sku' => 'TestSJKU1', 'selprod_condition' => 1, 'selprod_active' => 1, 'selprod_deleted' => 0);
        
        $data1 = array('selprod_id' => 0, 'selprod_user_id' => 1, 'selprod_product_id' => 2, 'selprod_code' => 222, 'selprod_price' => 180, 'selprod_cost' => 160, 'selprod_min_order_qty' => 1, 'selprod_sku' => 'TestSJKU2', 'selprod_condition' => 1, 'selprod_active' => 1, 'selprod_deleted' => 0);
        
                
        $arr = [
            [$data],
            [$data1],
        ];            
        $this->InsertDbData(SellerProduct::DB_TBL, $arr);
    }
    /**
    * insertUserFavoriteData
    *
    * @return void
    */
    private function insertUserFavoriteData()
    {
        $arr = [
            ['ufp_id' => 0, 'ufp_user_id' => 1, 'ufp_selprod_id' => 1]
        ];          
        $this->InsertDbData(UserFavorite::DB_TBL, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedGetUserFavouriteItemCount
     * @param  int userId
     * @return void
     */
    public function getUserFavouriteItemCount($expected, $userId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_INT);
        $result = $this->execute($this->class, [], 'getUserFavouriteItemCount', [$userId]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedGetUserFavouriteItemCount
     *
     * @return array
    */
    public function feedGetUserFavouriteItemCount()
    {  
        return [
            [0, 'test'],   //Invalid userId
            [1, 1],   //Valid userId
        ];
    }     
}
