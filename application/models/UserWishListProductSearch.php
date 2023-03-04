<?php

class UserWishListProductSearch extends SearchBase
{
    private $langId;
    private $sellerProductsJoined;
    private $productsJoined;
    private $sellerUserJoined;
    private $commonLangId;
    private $joinSellerOrder;
    private $geoAddress = [];
    private $locationBasedInnerJoin = true;

    public function __construct($langId = 0)
    {
        parent::__construct(UserWishListProducts::DB_TBL, 'uwlp');
        $this->langId = FatUtility::int($langId);
        $this->sellerProductsJoined = false;
        $this->productsJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
    }

    public function joinWishLists()
    {
        $this->joinTable(UserWishList::DB_TBL, 'INNER JOIN', 'uwl.uwlist_id = uwlp.uwlp_uwlist_id', 'uwl');
    }

    public function joinFavouriteProducts($user_id)
    {
        $this->joinTable(Product::DB_TBL_PRODUCT_FAVORITE, 'LEFT OUTER JOIN', 'ufp.ufp_selprod_id = selprod_id and ufp.ufp_user_id = ' . $user_id, 'ufp');
    }

    public function joinSellerProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'uwlp.uwlp_selprod_id = sp.selprod_id', 'sp');

        if ($langId) {
            $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND sp_l.selprodlang_lang_id = ' . $langId, 'sp_l');
        }
        $this->sellerProductsJoined = true;
    }

    public function joinSellerProductSpecialPrice($forDate = '')
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('MSG_joinSellerProductSpecialPrice_can_be_joined_only_if,_joinSellerProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        if ('' == $forDate) {
            $forDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');;
        }
        $this->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            'splprice_selprod_id = sp.selprod_id AND \'' . $forDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );
    }

    public function joinProducts($langId = 0, $isProductActive = true, $isProductApproved = true, $isProductDeleted = true)
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('MSG_joinProducts_can_be_joined_only_if,_joinSellerProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');

        if ($langId) {
            $this->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $langId, 'p_l');
        }

        if ($isProductActive) {
            $this->addCondition('product_active', '=', applicationConstants::ACTIVE);
        }

        if ($isProductApproved) {
            $this->addCondition('product_approved', '=', Product::APPROVED);
        }

        if ($isProductDeleted) {
            $this->addCondition('product_deleted', '=', applicationConstants::NO);
        }

        $this->productsJoined = true;
    }

    public function joinBrands($langId = 0)
    {
        if (!$this->productsJoined) {
            trigger_error(Labels::getLabel('MSG_joinBrands_can_be_joined_only_if,_joinProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'p.product_brand_id = brand.brand_id', 'brand');
        if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $this->addCondition('brand.brand_active', '=', applicationConstants::ACTIVE);
            $this->addCondition('brand.brand_deleted', '=', '0');
        }

        if ($langId) {
            $this->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'brand.brand_id = tb_l.brandlang_brand_id AND brandlang_lang_id = ' . $langId, 'tb_l');
        }
    }

    public function joinProductToCategory($langId = 0)
    {
        if (!$this->productsJoined) {
            trigger_error(Labels::getLabel('MSG_joinBrands_can_be_joined_only_if,_joinProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = p.product_id', 'ptc');
        $this->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');

        $this->addCondition('c.prodcat_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('c.prodcat_deleted', '=', applicationConstants::NO);

        if ($langId) {
            $this->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.prodcatlang_prodcat_id = c.prodcat_id AND prodcatlang_lang_id = ' . $langId, 'c_l');
        }
    }

    public function joinSellerOrder()
    {
        $this->joinSellerOrder = true;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=shop_user_id and o.order_type=' . ORDERS::ORDER_SUBSCRIPTION, 'o');
        }
    }

    public function joinShops($langId = 0, $isActive = true, $isDisplayStatus = true)
    {
        if (!$this->sellerUserJoined) {
            trigger_error(Labels::getLabel("ERR_joinShops_cannot_be_joined,_unless_joinSellers_is_not_applied.", $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        // $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'seller_user.user_id = shop.shop_user_id', 'shop');

        $shopCondition = '';
        if ($isActive) {
            $shopCondition .= ' and shop.shop_active = ' . applicationConstants::ACTIVE;
            $this->addCondition('shop.shop_active', '=', applicationConstants::ACTIVE);
        }

        if ($isDisplayStatus) {
            $shopCondition .= ' and shop.shop_supplier_display_status = ' . applicationConstants::ON;
            $this->addCondition('shop.shop_supplier_display_status', '=', applicationConstants::ON);
        }

        $joinShopWithSubQuery = false;
        if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $prodGeoCondition = FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0);
            switch ($prodGeoCondition) {
                case applicationConstants::BASED_ON_RADIUS:
                    if (array_key_exists('ykGeoLat', $this->geoAddress) && $this->geoAddress['ykGeoLat'] != '' && array_key_exists('ykGeoLng', $this->geoAddress) && $this->geoAddress['ykGeoLng'] != '') {
                        $shopSearch = new SearchBase(Shop::DB_TBL, 'shop');
                        $shopSearch->doNotCalculateRecords();
                        $shopSearch->doNotLimitRecords();
                        $shopSearch->addCondition('shop.shop_supplier_display_status', '=', applicationConstants::ON);
                        $shopSearch->addCondition(Shop::tblFld('active'), '=', applicationConstants::ACTIVE);
                        $shopSearch->addFld('*');
                        $shopSearch->addFld('( 6371 * acos( cos( radians(' . $this->geoAddress['ykGeoLat'] . ') ) * cos( radians( shop.`shop_lat` ) ) * cos( radians( shop.`shop_lng` ) - radians(' . $this->geoAddress['ykGeoLng'] . ') ) + sin( radians(' . $this->geoAddress['ykGeoLat'] . ') ) * sin( radians( shop.`shop_lat` ) ) ) ) AS distance');
                        $shopSearch->addHaving('distance', '<=', FatApp::getConfig('CONF_RADIUS_DISTANCE_IN_MILES', FatUtility::VAR_INT, 10));
                        if (false == $this->locationBasedInnerJoin) {
                            $shopSubQuery = $shopSearch->getQuery();
                            $shopSearch = new SearchBase(Shop::DB_TBL, 'sshop');
                            $shopSearch->doNotCalculateRecords();
                            $shopSearch->doNotLimitRecords();
                            $shopSearch->addCondition('sshop.shop_supplier_display_status', '=', applicationConstants::ON);
                            $shopSearch->addCondition('sshop.' . Shop::tblFld('active'), '=', applicationConstants::ACTIVE);
                            $shopSearch->addMultipleFields(array('sshop.*', 'shop.distance'));
                            $shopSearch->joinTable('(' . $shopSubQuery . ')', 'LEFT OUTER JOIN', 'shop.shop_id = sshop.shop_id', 'shop');
                        }
                        $joinShopWithSubQuery = true;
                    }
                    break;
                case applicationConstants::BASED_ON_CURRENT_LOCATION:
                    $level = FatApp::getConfig('CONF_LOCATION_LEVEL', FatUtility::VAR_INT, 0);
                    $countryBased = $stateBased = $zipBased = false;
                    if (applicationConstants::LOCATION_COUNTRY == $level) {
                        $countryBased = true;
                    } elseif (applicationConstants::LOCATION_STATE == $level) {
                        $countryBased = $stateBased = true;
                    } elseif (applicationConstants::LOCATION_ZIP == $level) {
                        $countryBased = $stateBased = $zipBased = true;
                    }

                    $locCondition = '';
                    if ($countryBased && array_key_exists('ykGeoCountryId', $this->geoAddress) && $this->geoAddress['ykGeoCountryId'] > 0) {
                        $locCondition .= ' and shop.shop_country_id = ' . $this->geoAddress['ykGeoCountryId'];
                    }

                    if ($stateBased && array_key_exists('ykGeoStateId', $this->geoAddress) && $this->geoAddress['ykGeoStateId'] > 0) {
                        $locCondition .= ' and shop.shop_state_id = ' . $this->geoAddress['ykGeoStateId'];
                    }

                    if ($zipBased && array_key_exists('ykGeoZip', $this->geoAddress) && $this->geoAddress['ykGeoZip'] > 0) {
                        $locCondition .= ' and shop.shop_postalcode = ' . $this->geoAddress['ykGeoZip'];
                    }

                    if (true == $this->locationBasedInnerJoin) {
                        $shopCondition .= $locCondition;
                        $this->addFld('1 as availableInLocation');
                    } else {
                        if (!empty($locCondition)) {
                            $this->addFld('if ((1 ' . $locCondition . '), 1, 0) as availableInLocation');
                        } else {
                            $this->addFld('1 as availableInLocation');
                        }
                    }
                    break;
            }
        }

        $locationBasedInnerJoin = (true == $this->locationBasedInnerJoin) ? 'INNER JOIN' : 'LEFT OUTER JOIN';
        if ($joinShopWithSubQuery) {
            $this->joinTable('(' . $shopSearch->getQuery() . ')', $locationBasedInnerJoin, 'seller_user.user_id = shop.shop_user_id  ' . $shopCondition, 'shop');
        } else {

            $this->joinTable(Shop::DB_TBL, 'INNER JOIN', 'seller_user.user_id = shop.shop_user_id ' . $shopCondition, 'shop');
        }

        if ($langId) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $langId, 's_l');
        }
    }

    public function validateAndJoinDeliveryLocation($includeShipingProfileCheck = false, $addAvailableInLocation = true)
    {
        if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $prodGeoCondition = FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0);
            switch ($prodGeoCondition) {
                case applicationConstants::BASED_ON_DELIVERY_LOCATION:
                    $shippingServiceActive = Plugin::isActiveByType(Plugin::TYPE_SHIPPING_SERVICES);
                    if (!$shippingServiceActive) {
                        $this->joinDeliveryLocations();
                        if (true == $includeShipingProfileCheck) {
                            $this->addHaving('shippingProfile', 'IS NOT', 'mysql_func_null', 'and', true);
                            if ($addAvailableInLocation) {
                                $this->addFld('1 as availableInLocation');
                            }
                        } else if ($addAvailableInLocation) {
                            $this->addFld('if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', ifnull(shipprofile.shippro_product_id,0), 1) as availableInLocation');
                        }
                    }

                    break;
                case applicationConstants::BASED_ON_RADIUS:
                    if (array_key_exists('ykGeoLat', $this->geoAddress) && $this->geoAddress['ykGeoLat'] != '' && array_key_exists('ykGeoLng', $this->geoAddress) && $this->geoAddress['ykGeoLng'] != '') {
                        $distanceInMiles = FatApp::getConfig('CONF_RADIUS_DISTANCE_IN_MILES', FatUtility::VAR_INT, 10);
                        if ($addAvailableInLocation) {
                            $this->addFld('if(shop.distance <= ' . $distanceInMiles .  ', 1, 0) as availableInLocation');
                        }
                    } else if ($addAvailableInLocation) {
                        $this->addFld('0 as availableInLocation');
                    }
                    break;
                case applicationConstants::BASED_ON_CURRENT_LOCATION:

                    break;
                default:
                    if ($addAvailableInLocation) {
                        $this->addFld('1 as availableInLocation');
                    }
                    break;
            }
        } else if ($addAvailableInLocation) {
            $this->addFld('1 as availableInLocation');
        }
    }

    public function joinDeliveryLocations($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        if (empty($this->geoAddress)) {
            trigger_error(Labels::getLabel('ERR_setGoeAddress_function_not_joined.', $langId), E_USER_ERROR);
        }

        $countryId = 0;
        $stateId = 0;
        if (array_key_exists('ykGeoCountryId', $this->geoAddress) && $this->geoAddress['ykGeoCountryId'] > 0) {
            $countryId = $this->geoAddress['ykGeoCountryId'];
        }

        if (array_key_exists('ykGeoStateId', $this->geoAddress) && $this->geoAddress['ykGeoStateId'] > 0) {
            $stateId = $this->geoAddress['ykGeoStateId'];
        }

        $srch = ShippingProfileProduct::getUserSearchObject(0, true);
        $srch->joinTable(ShippingProfile::DB_TBL, 'INNER JOIN', 'sppro.shippro_shipprofile_id = spprof.shipprofile_id and spprof.shipprofile_active = ' . applicationConstants::YES, 'spprof');
        $srch->joinTable(ShippingProfileZone::DB_TBL, 'INNER JOIN', 'shippz.shipprozone_shipprofile_id = spprof.shipprofile_id', 'shippz');
        $srch->joinTable(ShippingZone::DB_TBL, 'INNER JOIN', 'shipz.shipzone_id = shippz.shipprozone_shipzone_id and shipz.shipzone_active = ' . applicationConstants::YES, 'shipz');
        $srch->joinTable(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'LEFT OUTER JOIN', 'psbs.psbs_product_id = tp.product_id', 'psbs');

        $joinCondition = 'if(tp.product_seller_id = 0, (if(psbs.psbs_user_id > 0, sppro.shippro_user_id = psbs.psbs_user_id, sppro.shippro_user_id = 0)), (sppro.shippro_user_id = psbs.psbs_user_id))';
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $joinCondition = 'sppro.shippro_user_id = 0';
        }
        $srch->addDirectCondition($joinCondition);

        $tempSrch = ShippingZone::getZoneLocationSearchObject();
        $tempSrch->addDirectCondition("(shiploc_country_id = '-1' or (shiploc_country_id = '" . $countryId . "' and (shiploc_state_id = '-1' or shiploc_state_id = '" . $stateId . "')) )");
        $tempSrch->doNotCalculateRecords();
        $tempSrch->doNotLimitRecords();

        $srch->joinTable('(' . $tempSrch->getQuery() . ')', 'INNER JOIN', 'shiploc.shiploc_shipzone_id = shippz.shipprozone_shipzone_id', 'shiploc');

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'shipprofile.shippro_product_id = p.product_id', 'shipprofile');
        $this->addFld('if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', shipprofile.shippro_product_id, -1) as shippingProfile');
        // $this->joinTable('(' . $srch->getQuery() . ')', 'INNER JOIN', '(if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', shipprofile.shippro_product_id = p.product_id, p.product_id =  p.product_id))', 'shipprofile');
        /* $this->addFld('if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', shipprofile.shippro_product_id, -1) as shippingProfile');
        $this->addHaving('shippingProfile', '!=', 'null'); */
    }

    public function joinSellers()
    {
        $this->sellerUserJoined = true;
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'selprod_user_id = seller_user.user_id', 'seller_user');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'seller_user_cred.credential_user_id = seller_user.user_id', 'seller_user_cred');
        $this->addCondition('seller_user.user_is_supplier', '=', applicationConstants::YES);
        $this->addCondition('seller_user_cred.credential_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('seller_user_cred.credential_verified', '=', applicationConstants::YES);
        $this->addCondition('seller_user.user_deleted', '=', applicationConstants::NO);
    }

    public function joinSellerOrderSubscription($langId = 0)
    {
        $langId = FatUtility::int($langId);

        if (!$this->joinSellerOrder) {
            trigger_error(Labels::getLabel('ERR_Seller_Subscription_Order_must_joined.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id=' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS'), 'oss');
            if ($langId > 0) {
                $this->joinTable(OrderSubscription::DB_TBL_LANG, 'LEFT OUTER JOIN', 'oss.ossubs_id = ossl.' . OrderSubscription::DB_TBL_LANG_PREFIX . 'ossubs_id AND ossubslang_lang_id = ' . $langId, 'ossl');
            }
        }
    }

    public function joinSellerSubscription($langId = 0, $joinSeller = false)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if ($joinSeller) {
            $this->joinSellers();
        }
        $this->joinSellerOrder();
        $this->joinSellerOrderSubscription($langId);

        //$this->addSubscriptionValidCondition();
    }

    public function addSubscriptionValidCondition($date = '')
    {
        if ($date == '') {
            $date = date("Y-m-d");
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->addCondition('oss.ossubs_till_date', '>=', $date);
            $this->addCondition('ossubs_status_id', 'IN ', Orders::getActiveSubscriptionStatusArr());
        }
    }

    public function setGeoAddress($address = [])
    {
        $this->geoAddress = Address::getYkGeoData($address);
    }
}
