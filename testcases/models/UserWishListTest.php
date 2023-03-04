<?php
class UserWishListTest extends YkModelTest
{
    private $class = 'UserWishList';

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
        FatApp::getDb()->query("TRUNCATE TABLE ".User::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Product::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".SellerProduct::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".UserWishList::DB_TBL);
        //FatApp::getDb()->query("TRUNCATE TABLE ".UserWishList::DB_TBL_LIST_PRODUCTS);
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
     * @test
     *
     * @dataProvider feedAddUpdateListProducts
     * @param  int uwlpUwlistId
     * @param  int selprodId
     * @return void
     */
    public function addUpdateListProducts($expected, $uwlpUwlistId, $selprodId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'addUpdateListProducts', [$uwlpUwlistId, $selprodId]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedAddUpdateListProducts
     *
     * @return array
    */
    public function feedAddUpdateListProducts()
    {  
        return [
            [false, 'test', 1],   //Invalid uwlpUwlistId, valid selprodId
            [false, 1, 'test'],   //Valid uwlpUwlistId, Invalid selprodId
            [true, 1, 1],   //Valid uwlpUwlistId, valid selprodId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedDeleteWishList
     * @param  int uwlistId
     * @return void
     */
    public function deleteWishList($expected, $uwlistId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'deleteWishList', [$uwlistId]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedDeleteWishList
     *
     * @return array
    */
    public function feedDeleteWishList()
    {  
        return [
            [false, 'test'],   //Invalid uwlistId
            [true, 1],   //Valid uwlpUwlistId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetUserWishListsuwlp_uwlist_id
     */
    public function getUserWishLists($expected, $userId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getUserWishLists', [$userId]);
        $this->assertEquals($expected, count($result));
    }    
    /**
     * feedGetUserWishLists
     *
     * @return array
    */
    public function feedGetUserWishLists()
    {  
        return [
            [1, 1],   //Valid userId, return count 1
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetWishListId
     * @param  int userId
     * @param  int type
     * @return void
     */
    public function getWishListId($expected, $userId, $type)
    {
        $this->expectedReturnType(YkAppTest::TYPE_INT);
        $result = $this->execute($this->class, [], 'getWishListId', [$userId, $type]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedGetWishListId
     *
     * @return array
    */
    public function feedGetWishListId()
    {  
        return [
            [1, 1, 3],   //Valid userId
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetListProductsByListId
     * @param  int uwlpUwlistId
     * @param  int selprodId
     * @return void
     */
    public function getListProductsByListId($expected, $uwlpUwlistId, $selprodId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getListProductsByListId', [$uwlpUwlistId, $selprodId]);
        $this->assertEquals($expected, count($result));
    }    
    /**
     * feedGetListProductsByListId
     *
     * @return array
    */
    public function feedGetListProductsByListId()
    {  
        return [
            [0, 1000, 200],   //Inalid uwlpUwlistId, Invalid selprodId
            [0, 1, 2000],  //Valid uwlpUwlistId, Invalid selprodId
            [1, 1, 0],  //Valid uwlpUwlistId, empty selprodId
            [1, 1, 1],  //Valid uwlpUwlistId, Valid selprodId
        ];
    } 
   
     
}
