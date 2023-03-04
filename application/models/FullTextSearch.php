<?php
class FullTextSearch extends FatModel
{
    private $langId;
    private $results;
    private $defaultPlugin;

    public const RECORD_LIMIT = 100;
   
    public function __construct($langId = 0)
    {
        $this->langId = FatUtility::int($langId);
        if (1 > $langId) {
            $this->langId = CommonHelper::getLangId();
        }

        $this->defaultPlugin = $this->getDefaultPlugin();
        if (false == $this->defaultPlugin) {
            trigger_error(Labels::getLabel('LBL_PLUGIN_NOT_ACTIVATED', $this->langId), E_USER_ERROR);
        }
        
        $error = '';
        if (false === PluginHelper::includePlugin($this->defaultPlugin, 'full-text-search', $error, $this->langId)) {
            trigger_error($error, E_USER_ERROR);
        }
    }

    public function createIndex() {
        $languages = Language::getAllNames();
        if (0 > count($languages)) {
            return false;
        }
        
        foreach ($languages as $langId => $language) {
            $fullTextSearch = new $this->defaultPlugin($langId);
            
            if ($fullTextSearch->isIndexExists()) {
                continue;
            }
            
            if (!$fullTextSearch->createIndex()) {
                $this->logError($fullTextSearch->getError());
                continue;
            }
        }
        return true;
    }

    /**
     * Push Updated/Newely Inserted records to Full Test cloud server .
     *
    */
    public function execute()
    {
        $record = UpdatedRecordLog::getQueueRecords(self::RECORD_LIMIT);
               
        if (empty($record)) {
            return false;
        }

        foreach ($record as $row) {
            $recordId = $row[UpdatedRecordLog::DB_TBL_PREFIX . 'record_id'];
            $type = $row[UpdatedRecordLog::DB_TBL_PREFIX . 'record_type'];
            $subRecordId = $row[UpdatedRecordLog::DB_TBL_PREFIX . 'subrecord_id'];

            switch ($type) {
                case UpdatedRecordLog::TYPE_SHOP:
                    if (!UpdatedRecordLog::setShopProducts($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_USER:
                    if (!UpdatedRecordLog::setUserProducts($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_CATEGORY:
                    if (!UpdatedRecordLog::setCategoryProducts($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_BRAND:
                    if (!UpdatedRecordLog::setBrandProducts($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_COUNTRY:
                    if (!UpdatedRecordLog::setCountryProducts($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_STATE:
                    if (!UpdatedRecordLog::setStateProducts($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_PRODUCT:
                    if (!$this->updateProduct($recordId)) {
                        return false;
                    }
                    break;
                case UpdatedRecordLog::TYPE_INVENTORY:
                    $productId = SellerProduct::getAttributesById($recordId, 'selprod_product_id');
                    if (!$this->updateProductInventory($productId, $recordId)) {
                        return false;
                    }
                    break;
            }
            UpdatedRecordLog::markExecuted($type, $recordId, $subRecordId);
        }
    }
    
    /*
    * Insert/Update Products data
    */
    public function updateProduct($productId = 0)
    {
        $productId = FatUtility::int($productId);
        $languages = Language::getAllNames();
        if (0 > count($languages)) {
            return false;
        }

        foreach ($languages as $langId => $language) {
            $langId = FatUtility::int($langId);
            $srch = new ProductSearch($langId);
            $srch->setDefinedCriteria(1, 0, array(), false, true);
            $srch->joinProductToCategory();
            $srch->joinSellerSubscription();
            $srch->addSubscriptionValidCondition();
            $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $srch->addMultipleFields(array('product_id','product_name', 'product_type', 'product_model', 'product_seller_id', 'product_updated_on', 'product_active', 'product_approved', 'product_upc', 'product_isbn', 'product_ship_country', 'product_ship_free', 'product_cod_enabled', 'product_short_description', 'product_description', 'product_tags_string', 'theprice', 'selprod_id','selprod_price','selprod_title','ROUND(((selprod_price - theprice)*100)/selprod_price) as discountedValue','special_price_found','if(selprod_stock > 0, 1, 0) as in_stock','prod_rating as product_rating', 'brand_id','brand_name','brand_short_description','brand_active'));
            $srch->joinProductRating();
            $srch->doNotCalculateRecords();

            if (1 > $productId) {
                //$srch->setPageSize(FatUtility::int(self::LIMIT));
                $srch->doNotLimitRecords();
            } else {
                $srch->addCondition(Product::DB_TBL_PREFIX . 'id', '=', $productId);
                $srch->setPageSize(1);
            }
            
            $rs    = $srch->getResultSet();
            $products = FatApp::getDb()->fetchAll($srch->getResultSet());
                        
            $fullTextSearch = new $this->defaultPlugin($langId);

            if (empty($products)) {
                if (!$fullTextSearch->deleteDocument($productId)) {
                    $this->logError($fullTextSearch->getError());
                }
                continue;
            }

            foreach ($products as $key => $product) {

                $convertFieldType = array('theprice', 'product_rating');

                foreach ($convertFieldType as $convertFieldKey) {
                    if (array_key_exists($convertFieldKey, $product)) {
                        $product[$convertFieldKey] = FatUtility::float($product[$convertFieldKey]);
                    }
                }

                $brands = [];
                $brandKeys = array('brand_id', 'brand_name','brand_short_description','brand_active');

                foreach ($brandKeys as $brandKey) {
                    if (array_key_exists($brandKey, $product)) {
                        $brands[$brandKey] = $product[$brandKey];
                        unset($product[$brandKey]);
                    }
                }

                $data = array(
                    'general' => $product,
                    'brand' => $brands,
                    'categories' => static::getCategories($product['product_id'], $langId),
                    'options' => static::getOptions($product['product_id'], $langId),
                    /* 'inventories' => static::insertInventory($productId, $langId) */
                );

                // Checking Document Id Exists Or Not
                if (!$fullTextSearch->isDocumentExists($product['product_id'])) {
                    if (!$fullTextSearch->createDocument($product['product_id'], $data)) {
                        $this->logError($fullTextSearch->getError());
                        continue;
                    }
                    $this->updateProductInventory($product['product_id'], 0, $langId, $fullTextSearch);
                    continue;
                }
                
                if (!$fullTextSearch->updateDocument($product['product_id'], $data)) {
                    $this->logError($fullTextSearch->getError());
                    continue;
                }

                $this->updateProductInventory($product['product_id'], 0, $langId, $fullTextSearch);
            }
        }
        return true;
    }

    /*
    * Insert Inventory
    * Param @productId
    * Param @sellerProductId required when you want to update a seller product data
    */

    public function updateProductInventory($productId, $sellerProductId = 0, $langId = 0, $fullTextSearch = null)
    {
        $langId = FatUtility::int($langId);
        if (1 > $langId) {
            $languages = Language::getAllNames();
        } else {
            $languages[$langId] = $langId;
        }
        
        $productId = FatUtility::int($productId);
        $sellerProductId = FatUtility::int($sellerProductId);
        
        if (0 > count($languages)) {
            return false;
        }
        
        if (1 > $productId) {
            return false;
        }

        foreach ($languages as $langId => $language) {
            $srch = new ProductSearch($langId);
            $srch->setDefinedCriteria(1, 0, array(), false);
            $srch->joinProductToCategory();
            $srch->joinSellerSubscription();
            $srch->addSubscriptionValidCondition();
            $srch->addCondition('msellprod.' . SellerProduct::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
            $srch->addCondition('msellprod.' . SellerProduct::DB_TBL_PREFIX . 'product_id', '=', $productId);
            $srch->addCondition(User::DB_TBL_CRED_PREFIX . 'active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('msellprod.' . SellerProduct::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
            if (0 < $sellerProductId) {
                $srch->addCondition('msellprod.' . SellerProduct::DB_TBL_PREFIX . 'id', '=', $sellerProductId);
            }
           
            $srch->addMultipleFields(array('msellprod.selprod_id','sprods_l.selprod_title','msellprod.selprod_product_id','msellprod.selprod_code','msellprod.selprod_stock', 'msellprod.selprod_condition', 'msellprod.selprod_active', 'msellprod.selprod_cod_enabled', 'msellprod.selprod_available_from','msellprod.selprod_price', 'msellprod.selprod_sold_count', 'msellprod.selprod_sku','msellprod.selprod_user_id','shop_id','shop_name', 'shop_contact_person', 'shop_description','shop_active','user_id','user_name', 'user_phone_dcode','user_phone' ));
            $rs  = $srch->getResultSet();
            $sellerProducts = FatApp::getDb()->fetchAll($rs);
            
            if (null == $fullTextSearch) { 
                $fullTextSearch = new $this->defaultPlugin($langId);
            }

            if (empty($sellerProducts)) {
                if (!$fullTextSearch->isDocumentExists($productId)) {
                    $this->logError($fullTextSearch->getError());
                    continue;
                }

                if ($sellerProductId > 0) {
                    $sellerProductKey = array('selprod_id' => $sellerProductId );
                    if (!$fullTextSearch->deleteDocumentData($productId, 'inventories', $sellerProductKey)) {
                        $this->logError($fullTextSearch->getError());
                        continue;
                    }
                }

                // Todo remove all inventories
                continue;
            }
                    
            
            $shopFields = array('shop_id','shop_name','shop_description','shop_contact_person','shop_active');
            $userFields = array('user_id','user_name', 'user_phone_dcode','user_phone');
            
            foreach ($sellerProducts as $key => $sellerProduct) {
                $sellerUserId = FatUtility::int($sellerProduct['selprod_user_id']);
                foreach ($shopFields as $shopFieldName) {
                    if (array_key_exists($shopFieldName, $sellerProduct)) {
                        $sellerProducts[$key]['shop'][$shopFieldName] = $sellerProduct[$shopFieldName];
                        unset($sellerProducts[$key][$shopFieldName]);
                    }
                }
                
                foreach ($userFields as $userFieldName) {
                    if (array_key_exists($userFieldName, $sellerProduct)) {
                        $sellerProducts[$key]['user'][$userFieldName] = $sellerProduct[$userFieldName];
                        unset($sellerProducts[$key][$userFieldName]);
                    }
                }
            }
            
            if (1 > count($sellerProducts)) {
                return false;
            }

            if (!$response = $fullTextSearch->isDocumentExists($productId)) {
                return false;
            }
            if (1 > $sellerProductId) {
                $data = array('inventories' => $sellerProducts);
                $results = $fullTextSearch->updateDocument($productId, $data);
                if (!$results) {
                    continue;
                }
                continue;
            }

            $dataIndexArray = array('selprod_id' => $sellerProductId);

            $data = array('inventories' => $sellerProducts[0]);

            $results = $fullTextSearch->updateDocumentData($productId, 'inventories', $dataIndexArray, $data);

            if (!$results) {
                continue;
            }
            $this->updateGeneralMinPrice($productId);

            continue;
        }
        return true;
    }

    /*
    * @productId -
    */
    public function updateGeneralMinPrice($productId)
    {
        $productId = FatUtility::int($productId);
        $languages = Language::getAllNames();

        if (0 > count($languages)) {
            return false;
        }

        foreach ($languages as $langId => $language) {
            $langId = FatUtility::int($langId);
                        
            $fullTextSearch = new $this->defaultPlugin($langId);
            $srch = new ProductSearch($langId);
            $srch->addMultipleFields(array('theprice', 'selprod_id'));
            $srch->joinForPrice();
            $srch->addCondition(Product::DB_TBL_PREFIX . 'id', '=', $productId);
            $srch->doNotLimitRecords(true);
            $srch->doNotCalculateRecords(true);
            $rs    = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($srch->getResultSet());

            if (0 > $data) {
                return false;
            }
            $convertFieldType = array('theprice');
            foreach ($convertFieldType as $convertFieldKey) {
                if (array_key_exists($convertFieldKey, $data)) {
                    $data[$convertFieldKey] = FatUtility::float($data[$convertFieldKey]);
                }
            }

            $updatePrice = $fullTextSearch->updateDocument($productId, $data);
            if (!$updatePrice) {
                continue;
            }
        }
    }
       
    public static function insertInventory($productId, $langId)
    {
        $langId = FatUtility::int($langId);
        $productId = FatUtility::int($productId);
        
        if (1 > $productId) {
            return false;
        }
              
        $srch  = SellerProduct::getSearchObject($langId);
        $srch->addCondition(SellerProduct::DB_TBL_PREFIX . 'product_id', '=', $productId);
        $srch->addMultipleFields(array('selprod_id','selprod_title','selprod_code','selprod_stock', 'selprod_condition', 'selprod_active', 'selprod_cod_enabled', 'selprod_available_from','selprod_price', 'selprod_sold_count', 'selprod_sku'));
        $rs    = $srch->getResultSet();
        $sellerProducts = FatApp::getDb()->fetchAll($rs);
        
        foreach ($sellerProducts as $key => $sellerProduct) {
            $sellerProducts[$key]['min_price'] = static::getSellerProductMinimumPrice($sellerProduct['selprod_id']);
        }
        return $sellerProducts;
    }
    
    public static function getCategories($productId, $langId)
    {
        $productId = FatUtility::int($productId);
        $langId = FatUtility::int($langId);
        
        $srch = new SearchBase(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'ptc');
        $srch->addCondition(Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id', '=', $productId);
        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', ProductCategory::DB_TBL_PREFIX . 'id = ptc.' . Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id', 'cat');
        $srch->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', ProductCategory::DB_TBL_LANG_PREFIX . 'prodcat_id = ' . ProductCategory::tblFld('id') . ' and ' . ProductCategory::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }
        
    public static function getOptions($productId, $langId)
    {
        $productId = FatUtility::int($productId);
        $langId = FatUtility::int($langId);
        
        $srch = new SearchBase(Product::DB_PRODUCT_TO_OPTION);
        $srch->addCondition(Product::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id', '=', $productId);
        $srch->joinTable(OptionValue::DB_TBL, 'LEFT JOIN', Product::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id = opval.' . OptionValue::DB_TBL_PREFIX .'option_id', 'opval');
        $srch->joinTable(OptionValue::DB_TBL_LANG, 'LEFT OUTER JOIN', OptionValue::DB_TBL_LANG_PREFIX . 'optionvalue_id = ' . OptionValue::tblFld('id') . ' and ' . OptionValue::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId);
        
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }
    
    /* End Elastic Search Data Insert Functions ] */
    
    
    /* Search all the categories */
    /* private function getSearchCategories($criteria, $fullTextSearch)
    {
        $categories = $fullTextSearch->search($criteria, false, array('categories.prodcat_code'));
        return $categories;
    } */
        
    
    /* private static function updateProductStatus($productId, $langId)
    {
        if (FatApp::getDb()->deleteRecords(Product::DB_PRODUCT_EXTERNAL_RELATIONS, array('smt' => Product::DB_PRODUCT_EXTERNAL_RELATIONS_PREFIX . 'product_id = ? and ' . Product::DB_PRODUCT_EXTERNAL_RELATIONS_PREFIX . 'lang_id = ? ' , 'vals' => array($productId, $langId)))) {
            return true;
        }
        
        return false;
    } */
    
    /* Collecting Seller Product Minimum Price */
    private static function getSellerProductMinimumPrice($sellerProductId)
    {
        $sellerProductId = FatUtility::int($sellerProductId);
        
        $srch = new SearchBase(Product::DB_PRODUCT_MIN_PRICE);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(Product::DB_PRODUCT_MIN_PRICE_PREFIX . 'selprod_id', '=', $sellerProductId);
        $srch->addMultipleFields(array('pmp_product_id','pmp_selprod_id','pmp_min_price as theprice','pmp_splprice_id','if(pmp_splprice_id,1,0) as special_price_found'));
        $rs = $srch->getResultSet();
        $minimumPrice = FatApp::getDb()->fetch($rs);
        if (!$minimumPrice) {
            return array();
        }
        return $minimumPrice;
    }

    private function getDefaultPlugin()
    {
        $plugin = new Plugin();
        $defaultPlugin = $plugin->getDefaultPluginData(Plugin::TYPE_FULL_TEXT_SEARCH, "plugin_code");
        if (0 > $defaultPlugin) {
            return false;
        }
        return $defaultPlugin;
    }

    private function logError()
    {
        //@toDo
    }

    public static function syncData()
    {
        if (!FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_FULL_TEXT_SEARCH, FatUtility::VAR_INT, 0)) {
            return false;
        }
    
        $fullTextSearch = new FullTextSearch();
        $fullTextSearch->createIndex();
        $fullTextSearch->execute();
    }

    public static function getListingObj($criteria, $langId, $userId = 0)
    {
        $langId = FatUtility::int($langId);
        if (1 > $langId) {
            trigger_error(Labels::getLabel('LBL_INVALID_REQUEST', $langId), E_USER_ERROR);
        }

        $defaultPlugin = (new self($langId))->getDefaultPlugin();
        if (!$defaultPlugin) {
            trigger_error(Labels::getLabel('LBL_INVALID_REQUEST', $langId), E_USER_ERROR);
        }
        
        $error = '';
        if (false === PluginHelper::includePlugin($defaultPlugin, 'full-text-search', $error, $langId)) {
            trigger_error($error, E_USER_ERROR);
        }
        
        $srch = new $defaultPlugin($langId);
            
        if (array_key_exists('keyword', $criteria)) {
            $srch->addKeywordCondition($criteria['keyword']);
        }
        
        if (array_key_exists('brand', $criteria) && !empty($criteria['brand'])) {
            if (true ===  MOBILE_APP_API_CALL) {
                $criteria['brand'] = json_decode($criteria['brand'], true);
            }
            $srch->addBrandConditions($criteria['brand']);
        }

        if (array_key_exists('category', $criteria)) {
            $srch->addCategoryCondition($criteria['category']);
        }
       
        if (array_key_exists('shop_id', $criteria)) {
            $shop_id = FatUtility::int($criteria['shop_id']);
            $srch->addShopIdCondition($shop_id);
        }
        
        if (array_key_exists('optionvalue', $criteria)) {
            if (!empty($criteria['optionvalue'])) {
                $srch->addOptionCondition($criteria['optionvalue']);
            }
        }
        
        if (array_key_exists('condition', $criteria)) {
            if (true === MOBILE_APP_API_CALL) {
                $criteria['condition'] = json_decode($criteria['condition'], true);
            }
            $condition = is_array($criteria['condition']) ? array_filter($criteria['condition']) : $criteria['condition'];
            $srch->addConditionCondition($condition);
        }

        if (array_key_exists('out_of_stock', $criteria)) {
            if (!empty($criteria['out_of_stock']) && $criteria['out_of_stock'] == 1) {
                $srch->excludeOutOfStockProducts();
            }
        }

        $minPriceRange = '';
        if (array_key_exists('price-min-range', $criteria)) {
            $minPriceRange = floor($criteria['price-min-range']);
        } elseif (array_key_exists('min_price_range', $criteria)) {
            $minPriceRange = floor($criteria['min_price_range']);
        }
                
        if (!empty($minPriceRange) && isset($criteria['currency_id'])) {
            $$minPriceRange = CommonHelper::convertExistingToOtherCurrency($criteria['currency_id'], $minPriceRange, FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1), false);
        }

        $maxPriceRange = '';
        if (array_key_exists('price-max-range', $criteria)) {
            $maxPriceRange = ceil($criteria['price-max-range']);
        } elseif (array_key_exists('max_price_range', $criteria)) {
            $maxPriceRange = ceil($criteria['max_price_range']);
        }

        if (!empty($maxPriceRange)) {
            $maxPriceRange = CommonHelper::convertExistingToOtherCurrency($criteria['currency_id'], $maxPriceRange, FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1), false);
        }
        
        $srch->addPriceFilters($minPriceRange, $maxPriceRange);

        if (array_key_exists('featured', $criteria)) {
            $featured = FatUtility::int($criteria['featured']);
            if (0 < $featured) {
                $srch->addFeaturedProdCondition();
            }
        }

        if (array_key_exists('sortBy', $criteria)) {
            $sortBy = $criteria['sortBy'];
        }

        $sortOrder = 'asc';
        if (array_key_exists('sortOrder', $criteria)) {
            $sortOrder = $criteria['sortOrder'];
        }

        $sortFields = [];

        if (!empty($sortBy)) {
            $sortByArr = explode("_", $sortBy);
            $sortBy = isset($sortByArr[0]) ? $sortByArr[0] : $sortBy;
            $sortOrder = isset($sortByArr[1]) ? $sortByArr[1] : $sortOrder;

            if (!in_array($sortOrder, array('asc', 'desc'))) {
                $sortOrder = 'asc';
            }

            if (!in_array($sortBy, array('keyword', 'price', 'popularity', 'rating', 'discounted'))) {
                $sortOrder = 'keyword_relevancy';
            }

            switch ($sortBy) {
                case 'price':
                    $sortFields = array('general.theprice' => array('order' => $sortOrder));
                    break;
                case 'popularity':
                    $sortFields = array('inventories.selprod_sold_count' => array('order' => $sortOrder));
                    break;
                case 'discounted':
                    $sortFields = array('general.discountedValue' => array('order' => $sortOrder));
                    break;
                case 'rating':
                    $sortFields = array('general.product_rating' => array('order' => $sortOrder));
                    break;
                default:
                   // $srch->addOrder('keyword_relevancy', 'DESC');
                    break;
            }
        }

        $srch->setSortFields($sortFields);

        return $srch;
    }
}
