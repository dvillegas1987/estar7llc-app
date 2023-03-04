<?php
class SellerProductTest extends YkModelTest
{
    private $class = 'SellerProduct';

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
        $obj->insertBrandData();
        $obj->insertBrandLangData();
        $obj->insertProductCategoryData();
        $obj->insertProductCategoryLangData();        
        $obj->insertProductData();
        $obj->insertProductLangData();
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
        FatApp::getDb()->query("TRUNCATE TABLE ".Brand::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Brand::DB_TBL_LANG);
        FatApp::getDb()->query("TRUNCATE TABLE ".ProductCategory::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".Product::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".SellerProduct::DB_TBL);
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
            'credential_user_id'=>1, 'credential_username'=>'vivek', 'credential_email'=>'vivek.kumar@fatbit.in', 'credential_password'=>'Welcome@123', 'credential_active'=>1, 'credential_verified'=>1
            ]
        ];
        $this->InsertDbData(User::DB_TBL_CRED, $arr);

        $arr = [
            [
            'user_id'=>1, 'user_name'=>'vivek_seller', 'user_dial_code'=>'+91', 'user_phone'=>'9501496955', 'user_deleted'=>0,'user_id_seller'=>1
            ]
        ];
        $this->InsertDbData(User::DB_TBL, $arr);
    }

    /**
    * insertBrandData
    *
    * @return void
    */
    private function insertBrandData()
    {
        $arr = [
            [
                'brand_id' => 1, 'brand_identifier' => 'Samsung','brand_seller_id' => 0, 'brand_active' => 1,  
            ]
        ];            
        $this->InsertDbData(Brand::DB_TBL, $arr);
    }
    
    /**
    * insertBrandLangData
    *
    * @return void
    */
    private function insertBrandLangData()
    {
        $arr = [
            [
                'brandlang_brand_id' => 1, 'brandlang_lang_id' => 1, 'brand_name' => 'Samsung', 'brand_short_description' => 'Samsung',                         
            ],            
        ];            
        $this->InsertDbData(Brand::DB_TBL_LANG, $arr);
    } 

    /**
    * insertProductCategoryData
    *
    * @return void
    */
    private function insertProductCategoryData()
    {
        $data = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Men', ''), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // Existing Category
        
        $data1 = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Shoes'.rand(1, 9999), 'Shoes'.rand(1, 9999).' In Arabic'), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Root Category
        
        $data2 = array('prodcat_id' => 0, 'prodcat_parent' => 271, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Nike'.rand(1, 9999), 'Nike'.rand(1, 9999).' In Arabic'), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Sub Category
        
        $data3 = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 0, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Test'.rand(1, 9999), 'Test'.rand(1, 9999).' In Arabic'), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Root Category with Inactive status
        
        $data4 = array('prodcat_id' => 0, 'prodcat_parent' => 0, 'prodcat_active' => 1, 'auto_update_other_langs_data' => 1, 'prodcat_name' => array('', 'AutoUpdateLang'.rand(1, 9999), ''), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // New Root Category with auto update other lang data
        
        $data5 = array('prodcat_id' => 266, 'prodcat_parent' => 0, 'prodcat_active' => 0, 'auto_update_other_langs_data' => 0, 'prodcat_name' => array('', 'Unit Test'.rand(1, 9999), 'Unit Test Arabic'.rand(1, 9999)), 'cat_icon_image_id' => array(), 'cat_banner_image_id' => array()); // Update Category name and status
        $arr = [
           $data,
           $data1,
           $data2,
           $data3,
           $data4,
           $data5
        ];            
        $this->InsertDbData(ProductCategory::DB_TBL, $arr);
    }
    /**
    * insertProductCategoryLangData
    *
    * @return void
    */
    private function insertProductCategoryLangData()
    {
        $arr = array(
            array(0, 1, 'Unit Test3056'),
            array('test', 1, 'Unit Test3056'),
            array(266, 0, 'Unit Test3056'),
            array(266, 'test', 'Unit Test3056'),
            array(266, 1, 'Unit Test3056'),
        );
        $this->InsertDbData(ProductCategory::DB_TBL_LANG, $arr);
    }

    

    /**
    * insertProductData
    *
    * @return void
    */
    private function insertProductData()
    {
        $data = array('product_id' => 0, 'product_identifier' => 'test unit product', 'product_type' => 1, 'product_brand_id' => 111, 'product_min_selling_price' => 280, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'test prod', 'product_featured' => 1, 'product_cod_enabled' => 0, 'product_dimension_unit' => 2, 'product_length' => 20, 'product_width' => 30, 'product_height' => 40, 'product_weight_unit' => 2, 'product_weight' => 10); // Add new product
        
        $data1 = array('product_id' => 0, 'product_identifier' => 'test unit product', 'product_type' => 2, 'product_brand_id' => 113, 'product_min_selling_price' => 150, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'digi', 'product_featured' => 0); // Duplicate product Identifier
        
        $data2 = array('product_id' => 111, 'product_identifier' => 'fastfood', 'product_type' => 1, 'product_brand_id' => 111, 'product_min_selling_price' => 280, 'product_approved' => 1, 'product_active' => 1, 'product_added_by_admin_id' => 0, 'product_model' => 'test prod', 'product_featured' => 1, 'product_cod_enabled' => 0, 'product_dimension_unit' => 2, 'product_length' => 5, 'product_width' => 6, 'product_height' => 7, 'product_weight_unit' => 2, 'product_weight' => 8); // Update existing product
        
        $arr =  [
            $data,
            $data1,
            $data2,
        ];  
        $this->InsertDbData(Product::DB_TBL, $arr);        
    }
    /**
    * insertProductLangData
    *
    * @return void
    */
    private function insertProductLangData()
    {
        $arr = [
            [
                'productlang_product_id' => 1, 'productlang_lang_id' => 1, 'product_name' => 'test unit product', 'product_short_description' => 'test unit product', 'product_description' => 'test unit product', 'product_tags_string'=> '', 'product_youtube_video' => ''
            ] ,
            [
                'productlang_product_id' => 1, 'productlang_lang_id' => 1, 'product_name' => 'test unit product', 'product_short_description' => 'test unit product', 'product_description' => 'test unit product', 'product_tags_string'=> '', 'product_youtube_video' => ''
            ],
            [
                'productlang_product_id' => 1, 'productlang_lang_id' => 1, 'product_name' => 'test unit product', 'product_short_description' => 'test unit product', 'product_description' => 'test unit product', 'product_tags_string'=> '', 'product_youtube_video' => ''
            ]                                
            ];
        $this->InsertDbData(SellerProduct::DB_TBL, $arr);        
    }
    /**
    * insertSellerProductData
    *
    * @return void
    */
    private function insertSellerProductData()
    {
        $arr = [
            [
                'selprod_user_id' => 1, 'selprod_product_id' => 1, 'selprod_code' => '1', 'selprod_price' => 280, 'selprod_cost' => 100, 'selprod_stock' => 280, 'selprod_min_order_qty' => 1, 'selprod_subtract_stock' => 1, 'selprod_track_inventory' => 1, 'selprod_threshold_stock_level' => 1, 'selprod_available_from' => '2021-01-21 00:00:00', 'selprod_sku' => 'TestSellProd1', 'selprod_condition' => Product::CONDITION_NEW, 'selprod_active' => 1, 'selprod_deleted' => 0, 'selprod_added_on' => '2021-01-21 00:00:00','selprod_updated_on' => '2021-01-21 00:00:00', 'selprod_cod_enabled' => 0, 'selprod_sold_count' => 0, 'selprod_max_download_times' => 100, 'selprod_download_validity_in_days' => 1, 'selprod_urlrewrite_id' => 1, 'selprod_fulfillment_type' => 1
            ]
        ];    
        $this->InsertDbData(SellerProduct::DB_TBL, $arr);        
    }
}