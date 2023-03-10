<?php

class ProductsController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        //$this->productsData(__FUNCTION__);
        $this->featured();
    }

    public function search()
    {
        $post = (MOBILE_APP_API_CALL) ? FatApp::getPostedData() : [];
        $this->productsData(__FUNCTION__, true, $post);
    }

    public function featured()
    {
        $this->productsData(__FUNCTION__);
    }

    private function productsData($method, $validateBrand = false, $post = [])
    {
        $get = (!empty($post)) ? $post : Product::convertArrToSrchFiltersAssocArr(FatApp::getParameters());

        $postBrands = [];
        if (MOBILE_APP_API_CALL) {
            $postBrands = FatApp::getPostedData('brand', FatUtility::VAR_STRING, '[]');
            $postBrands = json_decode($postBrands, true);
        }

        $includeKeywordRelevancy = false;
        $keyword = '';
        if (array_key_exists('keyword', $get)) {
            $includeKeywordRelevancy = true;
            $keyword = trim($get['keyword']);
        }

        if ($validateBrand && array_key_exists('keyword', $get)) {
            $prodSrchObj = new ProductSearch(0);
            $prodSrchObj->addMultipleFields(array('brand_id', 'COALESCE(tb_l.brand_name, brand.brand_identifier) as brand_name'));
            $prodSrchObj->joinSellerProducts(0, '', ['doNotJoinSpecialPrice' => true], true);
            $prodSrchObj->joinSellers();
            $prodSrchObj->setGeoAddress();
            $prodSrchObj->joinShops();
            $prodSrchObj->validateAndJoinDeliveryLocation();
            $prodSrchObj->joinBrands();
            $prodSrchObj->joinBrandsLang($this->siteLangId, $keyword);
            $prodSrchObj->joinProductToCategory();
            $prodSrchObj->joinProductToTax();
            $prodSrchObj->joinSellerSubscription(0, false, true);
            $prodSrchObj->addSubscriptionValidCondition();
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->setPageSize(1);
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->addHaving('brand_name', 'like', $keyword);
            $brandRs = $prodSrchObj->getResultSet();
            $brandArr = FatApp::getDb()->fetchAllAssoc($brandRs);
            if (!empty($brandArr)) {
                $brands = array_keys($brandArr);
                $get['brand'] = !empty($postBrands) ? array_merge($brands, $postBrands) : $brands;
            }
        }
        $frm = $this->getProductSearchForm($includeKeywordRelevancy);

        $get['join_price'] = 1;

        $arr = array();

        switch ($method) {
            case 'index':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_All_PRODUCTS', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'index'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_PRODUCT_INDEX,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'allProducts'),
                );
                break;
            case 'search':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_Search_results_for', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'search'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_PRODUCT,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'searchListing'),
                    'keyword' => $keyword,
                );
                break;
            case 'featured':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_FEATURED_PRODUCTS', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'featured'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_FEATURED_PRODUCT,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'searchListing'),
                );
                $get['featured'] = 1;
                break;
        }

        $frm->fill($get);
        $data = $this->getListingData($get);

        if (array_key_exists('keyword', $get) && count($data['products'])) {
            $searchItemObj = new SearchItem();
            $searchData = array('keyword' => $get['keyword']);
            $searchItemObj->addSearchResult($searchData);
        }

        $common = [];
        if (false === MOBILE_APP_API_CALL) {
            $common = array(
                'frmProductSearch' => $frm,
                'recordId' => 0,
                'showBreadcrumb' => false
            );
        }

        $data = array_merge($data, $common, $arr);

        if (FatUtility::isAjaxCall()) {
            $this->set('products', $data['products']);
            $this->set('page', $data['page']);
            $this->set('pageCount', $data['pageCount']);
            $this->set('postedData', $get);
            $this->set('recordCount', $data['recordCount']);
            $this->set('siteLangId', $this->siteLangId);
            echo $this->_template->render(false, false, 'products/products-list.php', true);
            exit;
        }
        $this->set('data', $data);

        $this->includeProductPageJsCss();
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true, 'products/index.php');
    }

    private function getFilterSearchObj($langId, $headerFormParamsAssocArr)
    {
        return FilterHelper::getSearchObj($langId, $headerFormParamsAssocArr);
    }

    public function brandFilters()
    {
        $db = FatApp::getDb();
        $post = FilterHelper::getParamsAssocArr();

        $categoryId = 0;
        if (array_key_exists('category', $post)) {
            $categoryId = FatUtility::int($post['category']);
        }

        $keyword = '';
        $langIdForKeywordSeach = 0;
        if (array_key_exists('keyword', $post) && !empty($post['keyword'])) {
            $keyword = $post['keyword'];
            $langIdForKeywordSeach = $this->siteLangId;
        }

        $post['doNotJoinSpecialPrice'] = true;
        $prodSrchObj = $this->getFilterSearchObj($langIdForKeywordSeach, $post);
        $prodSrchObj->doNotCalculateRecords();

        $brandsCheckedArr = FilterHelper::selectedBrands($post);
        //$prodSrchObj->addFld('count(selprod_id) as totalProducts');
        $cacheKey = FilterHelper::getCacheKey($this->siteLangId, $post);

        $brandFilter = FatCache::get('brandFilter' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$brandFilter) {
            $brandsArr = FilterHelper::brands($prodSrchObj, $this->siteLangId, $post, true);
            FatCache::set('brandFilter' . $cacheKey, serialize($brandsArr), '.txt');
        } else {
            $brandsArr = unserialize($brandFilter);
        }

        $this->set('brandsArr', $brandsArr);
        $this->set('brandsCheckedArr', $brandsCheckedArr);

        echo $this->_template->render(false, false, 'products/brand-filters.php', true);
        exit;
    }

    public function filters()
    {
        $db = FatApp::getDb();
        $headerFormParamsAssocArr = FilterHelper::getParamsAssocArr();

        $categoryId = 0;
        if (array_key_exists('category', $headerFormParamsAssocArr)) {
            $categoryId = FatUtility::int($headerFormParamsAssocArr['category']);
        }

        $keyword = '';
        $langIdForKeywordSeach = 0;
        if (array_key_exists('keyword', $headerFormParamsAssocArr) && !empty($headerFormParamsAssocArr['keyword'])) {
            $keyword = $headerFormParamsAssocArr['keyword'];
            $langIdForKeywordSeach = $this->siteLangId;
        }

        $cacheKey = FilterHelper::getCacheKey($this->siteLangId, $headerFormParamsAssocArr);

        $headerFormParamsAssocArr['doNotJoinSpecialPrice'] = true;

        /* Categories Data[ ToDO need to update logic fetch from prodsrch obj or catid only*/
        $categoriesArr = array();
        if (empty($keyword)) {
            $catCriteria = $headerFormParamsAssocArr;
            $catCriteria['addFld'] = 'DISTINCT(prodcat_id) as prodcatid';

            $catProdSrchObj = $this->getFilterSearchObj($langIdForKeywordSeach, $catCriteria);
            $catProdSrchObj->doNotCalculateRecords();
            $catProdSrchObj->removeFld('1 as availableInLocation');
            $categoriesArr = FilterHelper::getCategories($this->siteLangId, $categoryId, $catProdSrchObj, $cacheKey);
        }
        /* ] */

        $prodSrchObj = $this->getFilterSearchObj($langIdForKeywordSeach, $headerFormParamsAssocArr);
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->removeFld('1 as availableInLocation');

        /* Brand Filters Data[ */
        $brandsCheckedArr = FilterHelper::selectedBrands($headerFormParamsAssocArr);
        $brandsArr = FilterHelper::brands($prodSrchObj, $this->siteLangId, $headerFormParamsAssocArr, false, true);
        /* ] */

        /* {Can modify the logic fetch data directly from query . will implement later}
        Option Filters Data[ */
        $options = FilterHelper::getOptions($this->siteLangId, $categoryId, $prodSrchObj);
        /* $optionSrch->joinSellerProductOptionsWithSelProdCode();
        $optionSrch->addGroupBy('optionvalue_id'); */
        /*]*/


        /* Condition filters data[ */
        $conditionsArr = array();
        $conditions = FatCache::get('conditions' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$conditions) {
            $conditionArr = Product::getConditionArr($this->siteLangId);
            $conditions = array();
            foreach ($conditionArr as $key => $val) {
                $conditionSrch = clone $prodSrchObj;
                $conditionSrch->setPageSize(1);
                $conditionSrch->addMultipleFields(array('selprod_condition'));
                $conditionSrch->addCondition('selprod_condition', '=', $key);
                /* if needs to show product counts under any condition[ */
                //$conditionSrch->addFld('count(selprod_condition) as totalProducts');
                /* ] */
                // echo $conditionSrch->getQuery().'<br>';
                $conditionRs = $conditionSrch->getResultSet();
                $row = $db->fetch($conditionRs);
                if (!empty($row)) {
                    $conditionsArr[] = $row;
                }
            }
            FatCache::set('conditions' . $cacheKey, serialize($conditionsArr), '.txt');
        } else {
            $conditionsArr = unserialize($conditions);
        }
        $conditionsArr = array_filter($conditionsArr);
        /* ] */

        /* Price Filters[ */
        $priceArr = FilterHelper::getPrice($headerFormParamsAssocArr, $this->siteLangId);

        $priceInFilter = false;
        $filterDefaultMinValue = $priceArr['minPrice'];
        $filterDefaultMaxValue = $priceArr['maxPrice'];

        if ($this->siteCurrencyId != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1) || (array_key_exists('currency_id', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['currency_id'] != $this->siteCurrencyId)) {
            $filterDefaultMinValue = CommonHelper::displayMoneyFormat($priceArr['minPrice'], false, false, false);
            $filterDefaultMaxValue = CommonHelper::displayMoneyFormat($priceArr['maxPrice'], false, false, false);
            $priceArr['minPrice'] = $filterDefaultMinValue;
            $priceArr['maxPrice'] = $filterDefaultMaxValue;
        }

        if (array_key_exists('price-min-range', $headerFormParamsAssocArr) && array_key_exists('price-max-range', $headerFormParamsAssocArr)) {
            $priceArr['minPrice'] = $headerFormParamsAssocArr['price-min-range'];
            $priceArr['maxPrice'] = $headerFormParamsAssocArr['price-max-range'];
            $priceInFilter = true;
        }

        if (array_key_exists('currency_id', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['currency_id'] != $this->siteCurrencyId && array_key_exists('price-min-range', $headerFormParamsAssocArr) && array_key_exists('price-max-range', $headerFormParamsAssocArr)) {
            $filterDefaultMinValue = CommonHelper::convertExistingToOtherCurrency($headerFormParamsAssocArr['currency_id'], $headerFormParamsAssocArr['price-min-range'], $this->siteCurrencyId, false);
            $filterDefaultMaxValue = CommonHelper::convertExistingToOtherCurrency($headerFormParamsAssocArr['currency_id'], $headerFormParamsAssocArr['price-max-range'], $this->siteCurrencyId, false);
            $priceArr['minPrice'] = $filterDefaultMinValue;
            $priceArr['maxPrice'] = $filterDefaultMaxValue;
        }

        /* ] */

        /* Availability Filters[ */
        $availabilities = FatCache::get('availabilities' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$availabilities) {
            $availabilitySrch = clone $prodSrchObj;
            $availabilitySrch->setPageSize(1);
            //$availabilitySrch->addGroupBy('in_stock');
            $availabilitySrch->addHaving('in_stock', '>', 0);
            $availabilitySrch->addMultipleFields(array('if(selprod_stock > 0,1,0) as in_stock'));
            $availabilityRs = $availabilitySrch->getResultSet();
            $availabilityArr = $db->fetchAll($availabilityRs, 'in_stock');
            FatCache::set('availabilities' . $cacheKey, serialize($availabilityArr), '.txt');
        } else {
            $availabilityArr = unserialize($availabilities);
        }
        /*] */

        $optionValueCheckedArr = array();
        if (array_key_exists('optionvalue', $headerFormParamsAssocArr)) {
            $optionValueCheckedArr = $headerFormParamsAssocArr['optionvalue'];
        }

        $conditionsCheckedArr = array();
        if (array_key_exists('condition', $headerFormParamsAssocArr)) {
            $conditionsCheckedArr = $headerFormParamsAssocArr['condition'];
        }

        $availability = 0;
        if (array_key_exists('out_of_stock', $headerFormParamsAssocArr)) {
            $availability = $headerFormParamsAssocArr['out_of_stock'];
        }

        $productFiltersArr = array('count_for_view_more' => FatApp::getConfig('CONF_COUNT_FOR_VIEW_MORE', FatUtility::VAR_INT, 5));

        $prodcatArr = array();
        //$productCategories = array();
        if (array_key_exists('prodcat', $headerFormParamsAssocArr)) {
            $prodcatArr = $headerFormParamsAssocArr['prodcat'];
            // $productCatObj = new ProductCategory;
            // $productCategories =  $productCatObj->getCategoriesForSelectBox($this->siteLangId);
        }

        $shopCatFilters = false;
        if (array_key_exists('shop_id', $headerFormParamsAssocArr)) {
            $shop_id = FatUtility::int($headerFormParamsAssocArr['shop_id']);
            $searchFrm = Shop::getFilterSearchForm();
            $searchFrm->fill($headerFormParamsAssocArr);
            $this->set('searchFrm', $searchFrm);
            if (0 < $shop_id) {
                $shopCatFilters = true;
            }
        }

        $this->set('productFiltersArr', $productFiltersArr);
        $this->set('headerFormParamsAssocArr', $headerFormParamsAssocArr);
        $this->set('categoriesArr', $categoriesArr);
        $this->set('shopCatFilters', $shopCatFilters);
        $this->set('prodcatArr', $prodcatArr);
        // $this->set('productCategories',$productCategories);
        $this->set('brandsArr', $brandsArr);
        $this->set('brandsCheckedArr', $brandsCheckedArr);
        $this->set('optionValueCheckedArr', $optionValueCheckedArr);
        $this->set('conditionsArr', $conditionsArr);
        $this->set('conditionsCheckedArr', $conditionsCheckedArr);
        $this->set('options', $options);
        $this->set('priceArr', $priceArr);
        $this->set('priceInFilter', $priceInFilter);
        $this->set('filterDefaultMinValue', $filterDefaultMinValue);
        $this->set('filterDefaultMaxValue', $filterDefaultMaxValue);
        $this->set('availability', $availability);
        $availabilityArr = (true === MOBILE_APP_API_CALL) ? array_values($availabilityArr) : $availabilityArr;
        $this->set('availabilityArr', $availabilityArr);
        $this->set('layoutDirection', CommonHelper::getLayoutDirection());

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $templateName = 'filters.php';
        /*
        if (FatApp::getConfig('CONF_FILTERS_LAYOUT', FatUtility::VAR_INT, 1) == FilterHelper::LAYOUT_TOP) {
            $templateName = 'filters-top.php';
        }
        */
        echo $this->_template->render(false, false, 'products/' . $templateName, true);
        exit;
    }

    public function view($selprod_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (true === MOBILE_APP_API_CALL && 1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $prodSrchObj = new ProductSearch($this->siteLangId);
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id');
        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setLocationBasedInnerJoin(false);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria(0, 0, array('product_id' => $productId), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinShopSpecifics();
        $prodSrch->joinProductSpecifics();
        $prodSrch->joinSellerProductSpecifics();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation(false);
        $prodSrch->joinProductToTax();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', 'mysql_func_' . $selprod_id, 'AND', true);
        $prodSrch->addCondition('selprod_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
        $prodSrch->doNotLimitRecords();

        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $prodSrch->joinUserWishListProducts($loggedUserId);
            $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinProducts($this->siteLangId);
        $selProdReviewObj->joinSellerProducts($this->siteLangId);
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->joinUser();
        // $selProdReviewObj->joinSelProdReviewHelpful();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', 'mysql_func_' . SelProdRating::TYPE_PRODUCT, 'AND', true);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        // $selProdReviewObj->addGroupBy('sprh_spreview_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', 'mysql_func_' . SelProdReview::STATUS_APPROVED, 'AND', true);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id', 'spr.spreview_product_id', "ROUND(AVG(sprating_rating),2) as prod_rating", "count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $prodSrch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'selprod_sku', 'product_identifier', 'COALESCE(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description',
                'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'special_price_found', 'splprice_start_date', 'splprice_end_date', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'selprod_warranty', 'selprod_return_policy', 'selprodComments',
                'theprice', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name',
                'shop_id', 'COALESCE(shop_name, shop_identifier) as shop_name', 'COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'COALESCE(sq_sprating.totReviews,0) totReviews',
                'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price', 'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled', 'selprod_available_from', 'selprod_min_order_qty', 'product_updated_on', 'product_warranty', 'selprod_return_age', 'selprod_cancellation_age', 'shop_return_age', 'shop_cancellation_age', 'selprod_fulfillment_type', 'shop_fulfillment_type', 'product_fulfillment_type'
            )
        );
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        if (!$product) {
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }
            FatUtility::exitWithErrorCode(404);
        }

        /* over all catalog product reviews */
        $selProdReviewObj->addCondition('spreview_product_id', '=', $product['product_id']);
        $selProdReviewObj->addMultipleFields(array('count(spreview_postedby_user_id) totReviews', 'sum(if(sprating_rating=1,1,0)) rated_1', 'sum(if(sprating_rating=2,1,0)) rated_2', 'sum(if(sprating_rating=3,1,0)) rated_3', 'sum(if(sprating_rating=4,1,0)) rated_4', 'sum(if(sprating_rating=5,1,0)) rated_5'));

        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        /* CommonHelper::printArray($reviews); die; */
        $this->set('reviews', $reviews);
        $subscription = false;
        $allowed_images = -1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $product['selprod_user_id'], array('ossubs_images_allowed'));
            $allowed_images = $currentPlanData['ossubs_images_allowed'];
            $subscription = true;
        }

        /* Product::recordProductWeightage($product['selprod_code'],SmartWeightageSettings::PRODUCT_VIEW);
        Product::addUpdateProductBrowsingHistory($product['selprod_code'],SmartWeightageSettings::PRODUCT_VIEW); */

        /* Current Product option Values[ */
        $options = SellerProduct::getSellerProductOptions($selprod_id, false);
        $productSelectedOptionValues = array();
        $productGroupImages = array();
        $productImagesArr = array();

        if (count($options) > 0) {
            foreach ($options as $op) {
                /* Product UPC code [ */
                $product['product_upc'] = UpcCode::getUpcCode($product['product_id'], $op['selprodoption_optionvalue_id']);
                /* ] */
                $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], $op['selprodoption_optionvalue_id'], $this->siteLangId, true, '', $allowed_images);
                if ($images) {
                    $productImagesArr += $images;
                }
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        if (count($productImagesArr) > 0) {
            foreach ($productImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        $product['selectedOptionValues'] = $productSelectedOptionValues;
        /* ] */

        if (isset($allowed_images) && $allowed_images > 0) {
            $universal_allowed_images_count = $allowed_images - count($productImagesArr);
        }

        $productUniversalImagesArr = array();
        if (empty($productGroupImages) || !$subscription || isset($universal_allowed_images_count)) {
            $universalImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], -1, $this->siteLangId, true, '');
            /* CommonHelper::printArray($universalImages); die; */
            if ($universalImages) {
                if (isset($universal_allowed_images_count)) {
                    $images = array_slice($universalImages, 0, $universal_allowed_images_count);
                    $productUniversalImagesArr = $images;
                    $universal_allowed_images_count = $universal_allowed_images_count - count($productUniversalImagesArr);
                } elseif (!$subscription) {
                    $productUniversalImagesArr = $universalImages;
                }
            }
        }

        if ($productUniversalImagesArr) {
            foreach ($productUniversalImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        //abled and Get Shipping Rates [*/
        $codEnabled = false;
        $isProductShippedBySeller = Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id']);
        if ($isProductShippedBySeller) {
            $walletBalance = User::getUserBalance($product['selprod_user_id']);
            if ($product['selprod_cod_enabled']) {
                $codEnabled = true;
            }
            $codMinWalletBalance = -1;
            $shop_cod_min_wallet_balance = Shop::getAttributesByUserId($product['selprod_user_id'], 'shop_cod_min_wallet_balance');
            if ($shop_cod_min_wallet_balance > -1) {
                $codMinWalletBalance = $shop_cod_min_wallet_balance;
            } elseif (FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1) > -1) {
                $codMinWalletBalance = FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1);
            }
            if ($codMinWalletBalance > -1 && $codMinWalletBalance > $walletBalance) {
                $codEnabled = false;
            }
            $shippingRates = Product::getProductShippingRates($product['product_id'], $this->siteLangId, 0, $product['selprod_user_id']);
            $shippingDetails = Product::getProductShippingDetails($product['product_id'], $this->siteLangId, $product['selprod_user_id']);
        } else {
            if ($product['product_cod_enabled']) {
                $codEnabled = true;
            }
            $shippingRates = Product::getProductShippingRates($product['product_id'], $this->siteLangId, 0, 0);
            $shippingDetails = Product::getProductShippingDetails($product['product_id'], $this->siteLangId, 0);
        }

        if ($product['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $shippingRates = array();
            $shippingDetails = array();
        }
        // $isProductShippedBySeller = Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id']);
        $fulfillmentType = $product['selprod_fulfillment_type'];
        if (true == $isProductShippedBySeller) {
            if ($product['shop_fulfillment_type'] != Shipping::FULFILMENT_ALL) {
                $fulfillmentType = $product['shop_fulfillment_type'];
                $product['selprod_fulfillment_type'] = $fulfillmentType;
            }
        } else {
            $fulfillmentType = isset($product['product_fulfillment_type']) ? $product['product_fulfillment_type'] : Shipping::FULFILMENT_SHIP;
            $product['selprod_fulfillment_type'] = $fulfillmentType;
            if (FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1) != Shipping::FULFILMENT_ALL) {
                $fulfillmentType = FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1);
                $product['selprod_fulfillment_type'] = $fulfillmentType;
            }
        }

        if ($product['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $fulfillmentType = Shipping::FULFILMENT_ALL;
        }

        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('codEnabled', $codEnabled);
        $this->set('shippingRates', $shippingRates);
        $this->set('shippingDetails', $shippingDetails);
        /*]*/

        /*[ Product shipping cost */
        $shippingCost = 0;
        /*]*/
        $sellerId = (false === MOBILE_APP_API_CALL) ? $product['selprod_user_id'] : 0;
        $product['moreSellersArr'] = $this->getMoreSeller($product['selprod_code'], $this->siteLangId, $sellerId);
        $product['selprod_return_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_RETURN, $this->siteLangId);
        $product['selprod_warranty_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_WARRANTY, $this->siteLangId);
        /* Form buy product[ */
        $frm = $this->getCartForm($this->siteLangId);
        $frm->fill(array('selprod_id' => $selprod_id));
        $this->set('frmBuyProduct', $frm);
        /* ] */

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->doNotCalculateRecords();
        $optionSrchObj->doNotLimitRecords();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $validDateCondition = " and oss.ossubs_till_date >= '" . date('Y-m-d') . "'";
            $optionSrchObj->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=seller_user.user_id AND o.order_type=' . ORDERS::ORDER_SUBSCRIPTION . ' AND o.order_payment_status =1', 'o');
            $optionSrchObj->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id=' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') . $validDateCondition, 'oss');
        }
        $optionSrchObj->addCondition('product_id', '=', $product['product_id']);

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'COALESCE(option_name,option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', '=', $selprod_id);
        $optionSrch->addGroupBy('option_id');

        $optionRs = $optionSrch->getResultSet();
        if (true === MOBILE_APP_API_CALL) {
            $optionRows = FatApp::getDb()->fetchAll($optionRs);
        } else {
            $optionRows = FatApp::getDb()->fetchAll($optionRs, 'option_id');
        }

        if (count($optionRows) > 0) {
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $product['product_id']);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array('COALESCE(product_name, product_identifier) as product_name', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'COALESCE(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
                $optionValueSrch->addGroupBy('optionvalue_id');
                $optionValueRs = $optionValueSrch->getResultSet();
                if (true === MOBILE_APP_API_CALL) {
                    $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs);
                } else {
                    $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs, 'optionvalue_id');
                }
                $option['values'] = $optionValueRows;
            }
        }
        $this->set('optionRows', $optionRows);



        /* Product group attributes[ */
        /* $attributes = array();
        $infoAttributes = array();
        $numericAttributes = array();
        $textAttributes  = array();
        if( $product['product_attrgrp_id'] ){
        $srch = AttrGroupAttribute::getSearchObject();
        $srch->joinTable( AttrGroupAttribute::DB_TBL.'_lang', 'LEFT JOIN', 'lang.attrlang_attr_id = '. AttrGroupAttribute::DB_TBL_PREFIX.'id AND attrlang_lang_id = '.$this->siteLangId, 'lang');
        $srch->addCondition( AttrGroupAttribute::DB_TBL_PREFIX.'attrgrp_id', '=', $product['product_attrgrp_id'] );
        //$srch->addCondition( AttrGroupAttribute::DB_TBL_PREFIX.'type', '!=', AttrGroupAttribute::ATTRTYPE_TEXT );
        $srch->addOrder( AttrGroupAttribute::DB_TBL_PREFIX.'display_order');
        $srch->addMultipleFields( array('COALESCE( attr_name, attr_identifier ) as attr_name', 'attr_type', 'attr_fld_name','attr_options','attr_prefix','attr_postfix') );
        $rs = $srch->getResultSet();
        $attributes = FatApp::getDb()->fetchAll($rs);
        $numericAttributes = Product::getProductNumericAttributes( $product['product_id'] );
        $textAttributes = Product::getProductTextualAttributes( $this->siteLangId, $product['product_id'] );
        }
        $this->set( 'attributes', $attributes );
        $this->set( 'infoAttributes', $numericAttributes + $textAttributes ); */
        /* ] */



        /* product combo/batch[ */
        /*
        $productGroups = $sellerProduct->getGroupsToProduct($this->siteLangId);
        if( $productGroups ){
        foreach( $productGroups as $key => &$pg ){
        $srch = new ProductSearch( $this->siteLangId, ProductGroup::DB_PRODUCT_TO_GROUP, ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'product_id' );
        $srch->setBatchProductsCriteria();
        $srch->addCondition( ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id', '=', $pg['ptg_prodgroup_id'] );
        $srch->addMultipleFields( array( 'selprod_id', 'product_id', 'selprod_stock', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'COALESCE(product_name, product_identifier) as product_name', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'COALESCE(splprice_price, selprod_price) AS theprice', 'CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found' ) );
        $rs = $srch->getResultSet();
        $pg_products = FatApp::getDb()->fetchAll($rs);
        //$pg_products = $sellerProduct->getProductsToGroup( $pg['ptg_prodgroup_id'], $this->siteLangId );
        if( $pg_products ){
        foreach( $pg_products as $pg_product){
         if( !$pg_product['in_stock'] ){
          unset($productGroups[$key]);
          continue 2;
         }
        }
        }
        $pg['products'] = $pg_products;
        }
        } */
        $sellerProduct = new SellerProduct($selprod_id);
        $criteria = array('selprod_id');

        $upsellProducts = $sellerProduct->getUpsellProducts($product['selprod_id'], $this->siteLangId, $loggedUserId);
        $relatedProducts = $sellerProduct->getRelatedProducts($this->siteLangId, $product['selprod_id'], $criteria);
        $relatedProductsRs = $this->relatedProductsById(array_keys($relatedProducts));

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array('shop_id', 'shop_user_id', 'shop_ltemplate_id', 'shop_created_on', 'COALESCE(shop_name, shop_identifier) as shop_name', 'shop_description', 'shop_payment_policy', 'shop_delivery_policy', 'shop_refund_policy',  'COALESCE(shop_country_l.country_name,shop_country.country_code) as shop_country_name', 'COALESCE(shop_state_l.state_name,state_identifier) as shop_state_name', 'shop_city'/* , 'shop_free_ship_upto' */)
        );
        $srch->addCondition('shop_id', '=', $product['shop_id']);
        $shopRs = $srch->getResultSet();
        $shop = FatApp::getDb()->fetch($shopRs);

        $shop_rating = 0;
        if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
            $shop_rating = SelProdRating::getSellerRating($shop['shop_user_id']);
        }

        /*   [ Promotional Banner   */
        $banners = BannerLocation::getPromotionalBanners(0, $this->siteLangId);
        /* End of Prmotional Banner  ]*/

        /* Get Product Specifications */
        $this->set('productSpecifications', $this->getProductSpecifications($product['product_id'], $this->siteLangId));
        /* End of Product Specifications */

        $canSubmitFeedback = true;
        if ($loggedUserId) {
            $orderProduct = SelProdReview::getProductOrderId($product['product_id'], $loggedUserId);
            if (empty($orderProduct) || (isset($orderProduct['op_order_id']) && !Orders::canSubmitFeedback($loggedUserId, $orderProduct['op_order_id'], $selprod_id))) {
                $canSubmitFeedback = false;
            }
        }

        $displayProductNotAvailableLable = false;
        if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $displayProductNotAvailableLable = true;
        }

        $this->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
        $this->set('canSubmitFeedback', $canSubmitFeedback);
        $this->set('upsellProducts', !empty($upsellProducts) ? $upsellProducts : array());
        $this->set('relatedProductsRs', !empty($relatedProductsRs) ? $relatedProductsRs : array());
        $this->set('banners', $banners);
        $this->set('product', $product);
        $this->set('shop_rating', $shop_rating);
        $this->set('shop', $shop);
        $this->set('shopTotalReviews', SelProdReview::getSellerTotalReviews($shop['shop_user_id']));
        $this->set('productImagesArr', $productGroupImages);
        //    $this->set( 'productGroups', $productGroups );
        $frmReviewSearch = $this->getReviewSearchForm(5);
        $frmReviewSearch->fill(array('selprod_id' => $selprod_id));
        $this->set('frmReviewSearch', $frmReviewSearch);
        $this->set('currentStock', $product['selprod_stock'] - Product::tempHoldStockCount($selprod_id));
        /* Get product Polls [ */
        /*$pollQuest = Polling::getProductPoll($product['product_id'], $this->siteLangId);
        $this->set('pollQuest', $pollQuest);*/
        /* ] */

        /* Get Product Volume Discount (if any)[ */
        $this->set('volumeDiscountRows', $sellerProduct->getVolumeDiscounts());
        /* ] */

        if (!empty($product)) {
            $afile_id = !empty($productGroupImages) ? array_keys($productGroupImages)[0] : 0;
            $this->set('socialShareContent', $this->getOgTags($product, $afile_id));
        }

        /* Recommnended Products [ */
        $loggedUserId = UserAuthentication::getLoggedUserId(true);
        $recommendedProducts = $this->getRecommendedProducts($selprod_id, $this->siteLangId, $loggedUserId);
        $this->set('recommendedProducts', $recommendedProducts);
        /* ]  */

        $this->setRecentlyViewedItem($selprod_id);

        if (false === MOBILE_APP_API_CALL) {
            $this->_template->addJs(array('js/slick.js', 'js/modaal.js', 'js/product-detail.js', 'js/xzoom.js', 'js/magnific-popup.js'));
        } else {
            $recentlyViewed = FatApp::getPostedData('recentlyViewed');
            $recentlyViewed = is_array($recentlyViewed) && 0 < count($recentlyViewed) ? FatUtility::int($recentlyViewed) : array();
            if (in_array($selprod_id, $recentlyViewed)) {
                unset($recentlyViewed[$selprod_id]);
            }
            $recentlyViewed = $this->getRecentlyViewedProductsDetail($recentlyViewed);
            $this->set('recentlyViewed', $recentlyViewed);
        }

        $this->_template->render();
    }

    private function getProductSpecifications($product_id, $langId)
    {
        $product_id = FatUtility::int($product_id);
        $langId = FatUtility::int($langId);
        if (1 > $product_id) {
            return array();
        }
        $specSrchObj = new ProductSearch($langId);
        $specSrchObj->setDefinedCriteria();
        $specSrchObj->doNotCalculateRecords();
        $specSrchObj->doNotLimitRecords();
        $specSrchObj->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'LEFT OUTER JOIN', 'product_id = tcps.prodspec_product_id', 'tcps');
        $specSrchObj->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'INNER JOIN', 'tcps.prodspec_id = tcpsl.prodspeclang_prodspec_id and   prodspeclang_lang_id  = ' . $langId, 'tcpsl');
        $specSrchObj->addMultipleFields(array('prodspec_id', 'prodspec_name', 'prodspec_value', 'prodspec_group'));
        $specSrchObj->addGroupBy('prodspec_id');
        $specSrchObj->addOrder('prodspec_group');
        $specSrchObj->addOrder('prodspec_name');
        $specSrchObj->addCondition('prodspec_product_id', '=', $product_id);
        $specSrchObjRs = $specSrchObj->getResultSet();
        return FatApp::getDb()->fetchAll($specSrchObjRs);
    }

    private function getMoreSeller($selprodCode, $langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);

        $moreSellerSrch = new ProductSearch($langId);
        $moreSellerSrch->setGeoAddress();
        $moreSellerSrch->addMoreSellerCriteria($selprodCode, $userId);
        $moreSellerSrch->validateAndJoinDeliveryLocation();
        /*$moreSellerSrch->addMultipleFields(array( 'selprod_id', 'selprod_user_id', 'selprod_price', 'special_price_found', 'theprice', 'shop_id', 'shop_name' ,'IF(selprod_stock > 0, 1, 0) AS in_stock'));*/
        $moreSellerSrch->addMultipleFields(
            array('selprod_id', 'selprod_user_id', 'selprod_price', 'special_price_found', 'theprice', 'shop_id', 'shop_name', 'product_seller_id', 'product_id', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city', 'selprod_cod_enabled', 'product_cod_enabled', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_min_order_qty', 'selprod_available_from')
        );
        $moreSellerSrch->addHaving('in_stock', '>', 0);
        $moreSellerSrch->addOrder('theprice');
        $moreSellerSrch->addGroupBy('selprod_id');
        // echo $moreSellerSrch->getQuery(); exit;
        $moreSellerRs = $moreSellerSrch->getResultSet();
        return FatApp::getDb()->fetchAll($moreSellerRs);
    }

    private function setRecentlyViewedItem($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (1 > $selprod_id) {
            return;
        }

        $recentProductsArr = array();
        if (!isset($_COOKIE['recentViewedProducts'])) {
            setcookie('recentViewedProducts', $selprod_id . '_', time() + 60 * 60 * 72, CONF_WEBROOT_URL);
        } else {
            $recentProducts = $_COOKIE['recentViewedProducts'];
            $recentProductsArr = explode('_', $recentProducts);
        }

        $products = array();

        if (is_array($recentProductsArr) && !in_array($selprod_id, $recentProductsArr)) {
            if (count($recentProductsArr) >= 10) {
                $recentProductsArr = array_reverse($recentProductsArr);
                array_pop($recentProductsArr);
                $recentProductsArr = array_reverse($recentProductsArr);
            }

            foreach ($recentProductsArr as $val) {
                if ($val == '') {
                    continue;
                }
                array_push($products, $val);
            }
            array_push($products, $selprod_id);
            setcookie('recentViewedProducts', implode('_', $products), time() + 60 * 60 * 72, CONF_WEBROOT_URL);
        }
    }

    private function getRecommendedProducts($selprod_id, $langId, $userId = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (1 > $selprod_id) {
            return;
        }

        /*if($recommendedProducts =  FatCache::get('recommProds'.$selprod_id.'-'.$langId.'-'.$userId, CONF_HOME_PAGE_CACHE_TIME, '.txt')){
            return  unserialize($recommendedProducts);
        }*/

        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);

        $srch = new ProductSearch($langId);
        $join_price = 1;
        $srch->setGeoAddress();
        $srch->setDefinedCriteria($join_price);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription();
        $srch->addSubscriptionValidCondition();
        $srch->validateAndJoinDeliveryLocation();
        $srch->addCondition('selprod_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
        $srch->addMultipleFields(
            array(
                'product_id', 'prodcat_id', 'substring_index(group_concat(COALESCE(prodcat_name, prodcat_identifier) ORDER BY COALESCE(prodcat_name, prodcat_identifier) ASC SEPARATOR "," ) , ",", 1) as prodcat_name', 'COALESCE(product_name, product_identifier) as product_name', 'product_model', 'product_short_description', 'product_updated_on',
                'selprod_id', 'selprod_user_id',  'selprod_code', 'selprod_stock', 'selprod_condition', 'selprod_price', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
                'theprice', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name',
                'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sold_count', 'selprod_return_policy'
            )
        );

        $dateToEquate = date('Y-m-d');

        $subSrch1 = new SearchBase('tbl_product_product_recommendation', 'ppr');
        $subSrch1->addMultipleFields(array('ppr_recommended_product_id as rec_product_id', 'ppr_weightage as weightage'));
        $subSrch1->addCondition('ppr_viewing_product_id', '=', $productId);
        $subSrch1->addOrder('weightage', 'desc');
        $subSrch1->doNotCalculateRecords();
        $subSrch1->setPageSize(5);

        $subSrch2 = new SearchBase(Product::DB_PRODUCT_TO_TAG, 'ptt');
        $subSrch2->joinTable('tbl_tag_product_recommendation', 'INNER JOIN', 'tpr.tpr_tag_id = ptt.ptt_tag_id', 'tpr');
        $subSrch2->addMultipleFields(array('tpr_product_id  as rec_product_id', 'if(tpr_custom_weightage_valid_till <= ' . $dateToEquate . ', tpr_custom_weightage+tpr_weightage , tpr_weightage) as weightage'));
        $subSrch2->addOrder('weightage', 'desc');
        $subSrch2->doNotCalculateRecords();
        $subSrch2->setPageSize(5);

        $recommendedProductsQuery = '(' . $subSrch1->getQuery() . ') union (' . $subSrch2->getQuery() . ')';
        if (0 < $userId) {
            $subSrch3 = new SearchBase('tbl_user_product_recommendation', 'upr');
            $subSrch3->addMultipleFields(array('upr_product_id as rec_product_id', 'upr_weightage as weightage'));
            $subSrch3->addOrder('weightage', 'desc');
            $subSrch3->addCondition('upr_user_id', '=', $userId);
            $subSrch3->doNotCalculateRecords();
            $subSrch3->setPageSize(5);
            $recommendedProductsQuery .= ' union (' . $subSrch3->getQuery() . ')';
        }

        $rs = FatApp::getDb()->query('select rec_product_id , weightage from (' . $recommendedProductsQuery . ') as temp order by weightage desc');
        $recommendedProds = FatApp::getDb()->fetchAllAssoc($rs);
        if (empty($recommendedProds)) {
            return array();
        }

        $srch->addGroupBy('product_id');

        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $srch->joinFavouriteProducts($userId);
            $srch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $srch->joinUserWishListProducts($userId);
            $srch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        /*$selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating');
        $srch->addFld('COALESCE(prod_rating,0) prod_rating');*/
        $srch->addCondition('selprod_id', '!=', $selprod_id);
        $srch->addCondition('product_id', 'in', array_keys($recommendedProds));
        $srch->setPageSize(5);
        $srch->doNotCalculateRecords();

        $recommendedProducts = FatApp::getDb()->fetchAll($srch->getResultSet());
        //FatCache::set('recommProds'.$selprod_id.'-'.$langId.'-'.$userId, serialize($recommendedProducts), '.txt');
        return $recommendedProducts;
    }

    private function getOgTags($product = array(), $afile_id = 0)
    {
        if (empty($product)) {
            return array();
        }
        $afile_id = FatUtility::int($afile_id);
        $title = $product['product_name'];

        if ($product['selprod_title']) {
            $title = $product['selprod_title'];
        }

        $product_description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($product["product_description"], true)), 500));
        $product_description .= ' - ' . Labels::getLabel('LBL_See_more_at', $this->siteLangId) . ": " . UrlHelper::getCurrUrl();

        $productImageUrl = '';
        /* $productImageUrl = UrlHelper::generateFullUrl('Image','product', array($product['product_id'],'', $product['selprod_id'],0,$this->siteLangId )); */
        if (0 < $afile_id) {
            $productImageUrl = UrlHelper::generateFullUrl('Image', 'product', array($product['product_id'], 'FB_RECOMMEND', 0, $afile_id));
        }
        $socialShareContent = array(
            'type' => 'Product',
            'title' => $title,
            'description' => $product_description,
            'image' => $productImageUrl,
        );
        return $socialShareContent;
    }

    public function getProductShippingRates()
    {
        $post = FatApp::getPostedData();
        $productId = $post['productId'];
        $sellerId = $post['sellerId'];
    }

    private function getRecentlyViewedProductsDetail($cookiesProductsArr = array())
    {
        if (1 > count($cookiesProductsArr)) {
            return $cookiesProductsArr;
        }

        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $prodSrch->joinUserWishListProducts($loggedUserId);
            $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }
        $prodSrch->addCondition('selprod_id', 'IN', $cookiesProductsArr);
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'COALESCE(product_name, product_identifier) as product_name', 'prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'product_updated_on',
                'selprod_id', 'selprod_condition', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'theprice',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'selprod_sold_count', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'selprod_price'
            )
        );
        // echo $prodSrch->getQuery(); die;

        $productRs = $prodSrch->getResultSet();
        $recentViewedProducts = FatApp::getDb()->fetchAll($productRs, 'selprod_id');
        uksort(
            $recentViewedProducts,
            function ($key1, $key2) use ($cookiesProductsArr) {
                return (array_search($key1, $cookiesProductsArr) > array_search($key2, $cookiesProductsArr));
            }
        );
        return $recentViewedProducts;
    }
    public function recentlyViewedProducts($productId = 0)
    {
        $productId = FatUtility::int($productId);
        $recentViewedProducts = array();
        $cookieProducts = isset($_COOKIE['recentViewedProducts']) ? $_COOKIE['recentViewedProducts'] : false;
        if ($cookieProducts != false) {
            $cookiesProductsArr = explode("_", $cookieProducts);
            if (!isset($cookiesProductsArr) || !is_array($cookiesProductsArr) || count($cookiesProductsArr) <= 0) {
                return '';
            }
            if ($productId && in_array($productId, $cookiesProductsArr)) {
                $pos = array_search($productId, $cookiesProductsArr);
                unset($cookiesProductsArr[$pos]);
            }

            if (isset($cookiesProductsArr) && is_array($cookiesProductsArr) && count($cookiesProductsArr)) {
                $cookiesProductsArr = array_map('intval', $cookiesProductsArr);
                $cookiesProductsArr = array_reverse($cookiesProductsArr);

                $recentViewedProducts = $this->getRecentlyViewedProductsDetail($cookiesProductsArr);
            }
        }

        $this->set('recentViewedProducts', $recentViewedProducts);
        $this->_template->render(false, false);
    }

    public function relatedProductsById($ids = array())
    {
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        if (isset($ids) && is_array($ids) && count($ids)) {
            $prodSrch = new ProductSearch($this->siteLangId);
            $prodSrch->setDefinedCriteria();
            $prodSrch->joinProductToCategory();
            $prodSrch->doNotCalculateRecords();

            if (true === MOBILE_APP_API_CALL) {
                $prodSrch->joinTable(SelProdReview::DB_TBL, 'LEFT OUTER JOIN', 'spr.spreview_selprod_id = selprod_id AND spr.spreview_product_id = product_id', 'spr');
                $prodSrch->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', 'sprating.sprating_spreview_id = spr.spreview_id', 'sprating');
                $prodSrch->addFld(array('COALESCE(ROUND(AVG(sprating_rating),2),0) as prod_rating'));
                $prodSrch->addGroupBy('selprod_id');
            }

            $prodSrch->doNotLimitRecords();
            if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
                $prodSrch->joinFavouriteProducts($loggedUserId);
                $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
            } else {
                $prodSrch->joinUserWishListProducts($loggedUserId);
                $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
            }
            // $prodSrch->joinProductRating();
            $prodSrch->addCondition('selprod_id', 'IN', $ids);
            $prodSrch->addMultipleFields(
                array(
                    'product_id', 'COALESCE(product_name, product_identifier) as product_name', 'prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'product_updated_on', 'COALESCE(selprod_title,product_name, product_identifier) as selprod_title',
                    'selprod_id', 'selprod_condition', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'theprice',
                    'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'selprod_sold_count', 'selprod_price', 'selprod_stock', 'selprod_min_order_qty'
                )
            );

            $productRs = $prodSrch->getResultSet();
            $products = FatApp::getDb()->fetchAll($productRs, 'selprod_id');

            uksort(
                $products,
                function ($key1, $key2) use ($ids) {
                    return (array_search($key1, $ids) > array_search($key2, $ids));
                }
            );
            return $products;
        }
    }

    public function clearSearchKeywords()
    {
        $keyword = FatApp::getPostedData("keyword");
        if (!empty($keyword)) {
            $recentSearchArr = [];
            if (isset($_COOKIE['recentSearchKeywords_' . $this->siteLangId])) {
                $recentSearchArr = unserialize($_COOKIE['recentSearchKeywords_' . $this->siteLangId]);
            }

            if (($key = array_search($keyword, $recentSearchArr)) !== false) {
                unset($recentSearchArr[$key]);
                setcookie('recentSearchKeywords_' . $this->siteLangId, serialize($recentSearchArr), time() + 60 * 60 * 72, CONF_WEBROOT_URL);
            }
        } else {
            setcookie('recentSearchKeywords_' . $this->siteLangId, '', time() + 60 * 60 * 72, CONF_WEBROOT_URL);
        }
    }

    public function searchProducttagsAutocomplete()
    {
        $keyword = FatApp::getPostedData("keyword");

        $recentSearchArr = [];
        if (isset($_COOKIE['recentSearchKeywords_' . $this->siteLangId])) {
            $recentSearchArr = unserialize($_COOKIE['recentSearchKeywords_' . $this->siteLangId]);
        }

        if (empty($keyword) || mb_strlen($keyword) < 3) {
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_PLEASE_ENTER_ATLEAST_3_CHARACTERS', $this->siteLangId));
            }

            $this->set('keyword', $keyword);
            $this->set('recentSearchArr', $recentSearchArr);
            $html = '';
            if (!empty($recentSearchArr)) {
                $html = $this->_template->render(false, false, 'products/search-producttags-autocomplete.php', true, false);
            }
            $this->set('html', $html);
            $this->_template->render(false, false, 'json-success.php', false, false);
        }
        $cacheKey = $this->siteLangId . '-' .  urlencode($keyword);


        $autoSuggetionsCache = FatCache::get('autoSuggetionsCache' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$autoSuggetionsCache) {
            $criteria = [
                'keyword' => $keyword,
                'doNotJoinSpecialPrice' => true
            ];
            $prodSrchObj = new ProductSearch($this->siteLangId);
            $prodSrchObj->joinSellerProducts(0, '', $criteria, true);
            $prodSrchObj->unsetDefaultLangForJoins();
            $prodSrchObj->joinSellers();
            $prodSrchObj->setGeoAddress();
            $prodSrchObj->joinShops();
            /*$prodSrchObj->validateAndJoinDeliveryLocation(false, false);*/
            $prodSrchObj->joinBrands($this->siteLangId);

            $prodSrchObj->joinSellerSubscription(0, false, true);
            $prodSrchObj->addSubscriptionValidCondition();
            $prodSrchObj->doNotCalculateRecords();

            $brandSrch = clone $prodSrchObj;
            $brandSrch->validateAndJoinDeliveryLocation(false, false);
            $brandSrch->joinProductToCategory($this->siteLangId);
            $brandSrch->addMultipleFields(array('brand_id', 'COALESCE(tb_l.brand_name, brand.brand_identifier) as brand_name', 'if(LOCATE("' . $keyword . '", COALESCE(tb_l.brand_name, brand.brand_identifier)) > 0, LOCATE("' . $keyword . '", COALESCE(tb_l.brand_name, brand.brand_identifier)), 99) as level'));
            //$brandSrch->addKeywordSearch($keyword, false, false);
            $brandSrch->addCondition('brand_name', 'LIKE', '%' . $keyword . '%');
            $brandSrch->addOrder('level');
            $brandSrch->addGroupBy('brand_id');
            $brandSrch->setPageSize(5);
            $brandRs = $brandSrch->getResultSet();
            $brandArr = [];
            while ($row = FatApp::getDb()->fetch($brandRs)) {
                $brandArr[$row['brand_id']] = $row['brand_name'];
            }

            $catListingCount = 10 - count($brandArr);
            $srch = new SearchBase(ProductCategory::DB_TBL_PROD_CAT_RELATIONS, 'cr');

            $catSrch = clone $prodSrchObj;

            $catSrch->joinProductToCategory();
            $catSrch->joinCategoryRelationWithChild();
            $catSrch->addMultipleFields(array('DISTINCT(prodcat_code)', 'cr.pcr_parent_id as qryProducts_prodcat_id'));
            $catSrch->removeFld('1 as availableInLocation');
            $catSrch->validateAndJoinDeliveryLocation(false, false);
            $catSrch->doNotCalculateRecords();
            $catSrch->doNotLimitRecords();
            // echo $catSrch->getQuery();
            $srch->joinTable('(' . $catSrch->getQuery() . ')', 'INNER JOIN', 'qryProducts.qryProducts_prodcat_id = cr.pcr_prodcat_id', 'qryProducts');
            $srch->addMultipleFields(array('prodcat_id', 'COALESCE(c_l.prodcat_name, c.prodcat_identifier) as prodcat_name', 'if(LOCATE("' . $keyword . '", COALESCE(c_l.prodcat_name, c.prodcat_identifier)) > 0, LOCATE("' . $keyword . '", COALESCE(c_l.prodcat_name, c.prodcat_identifier)), 99) as level'));
            $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', 'c.prodcat_id = cr.pcr_prodcat_id', 'c');
            $srch->joinTable(
                ProductCategory::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'prodcatlang_prodcat_id = c.prodcat_id
                AND prodcatlang_lang_id = ' . $this->siteLangId,
                'c_l'
            );
            $srch->setPageSize($catListingCount);
            $srch->addOrder('level');
            $srch->addGroupBy('prodcat_id');
            $catRs = $srch->getResultSet();
            // $catArr = FatApp::getDb()->fetchAll($catRs);
            $catArr = [];
            while ($row = FatApp::getDb()->fetch($catRs)) {
                $catArr[$row['prodcat_id']] = $row['prodcat_name'];
            }

            $srch = Tag::getSearchObject($this->siteLangId);
            $srch->doNotCalculateRecords();
            $srch->setPageSize(10);
            $srch->addMultipleFields(array('tag_id', 'COALESCE(tag_name, tag_identifier) as tag_name', 'if(LOCATE("' . $keyword . '", COALESCE(tag_name, tag_identifier)) > 0 , LOCATE("' . $keyword . '", COALESCE(tag_name, tag_identifier)), 99) as level'));
            $srch->addOrder('level');
            $srch->addGroupby('tag_id');
            $srch->addHaving('tag_name', 'LIKE', '%' . urldecode($keyword) . '%');
            $rs = $srch->getResultSet();
            $tags = FatApp::getDb()->fetchAll($rs);
            $prodArr = [];
            if (empty($tags)) {
                $prodSrchObj->validateAndJoinDeliveryLocation(false, false);
                $prodSrchObj->setPageSize(10);
                $prodSrchObj->joinProductToCategory($this->siteLangId);
                $prodSrchObj->addMultipleFields(array('selprod_id', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'COALESCE(c_l.prodcat_name, c.prodcat_identifier) as prodcat_name', 'if(LOCATE("' . $keyword . '", COALESCE(selprod_title, product_name, product_identifier)) > 0, LOCATE("' . $keyword . '", COALESCE(selprod_title, product_name, product_identifier)), 99) as level'));
                $prodSrchObj->addKeywordSearch($keyword, false, false);
                $prodSrchObj->addOrder('level');
                $prodSrchObj->addGroupBy('selprod_title');
                $prodRs = $prodSrchObj->getResultSet();
                $prodArr = FatApp::getDb()->fetchAll($prodRs);
            }

            $suggestions = [
                'tags' => $tags,
                'brands' => $brandArr,
                'categories' => $catArr,
                'products' => $prodArr
            ];
            if (!empty($tags) || !empty($brandArr) || !empty($catArr) || !empty($prodArr)) {
                array_unshift($recentSearchArr, $keyword);
            }
            $recentSearchArr = array_unique($recentSearchArr);
            $recentSearchArr = array_slice($recentSearchArr, 0, 5);
            setcookie('recentSearchKeywords_' . $this->siteLangId, serialize($recentSearchArr), time() + 60 * 60 * 72, CONF_WEBROOT_URL);
            FatCache::set('autoSuggetionsCache' . $cacheKey, serialize($suggestions), '.txt');
        } else {
            $suggestions = unserialize($autoSuggetionsCache);
        }

        $this->set('suggestions', $suggestions);
        $this->set('recentSearchArr', $recentSearchArr);
        $this->set('keyword', $keyword);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $html = $this->_template->render(false, false, 'products/search-producttags-autocomplete.php', true, false);
        $this->set('html', $html);
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
            case 'view':
                if (isset($parameters[0]) && FatUtility::int($parameters[0]) > 0) {
                    $selprod_id = FatUtility::int($parameters[0]);
                    if ($selprod_id) {
                        $srch = new ProductSearch($this->siteLangId);
                        $srch->joinSellerProducts();
                        $srch->joinProductToCategory();
                        $srch->doNotCalculateRecords();
                        $srch->doNotLimitRecords();
                        $srch->addMultipleFields(array('COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'COALESCE(product_name, product_identifier)as product_name', 'prodcat_code'));
                        $srch->addCondition('selprod_id', '=', $selprod_id);
                        $rs = $srch->getResultSet();
                        $row = FatApp::getDb()->fetch($rs);
                        if ($row) {
                            $productCatCode = $row['prodcat_code'];
                            $productCatCode = explode("_", $productCatCode);
                            $productCatCode = array_filter($productCatCode, 'strlen');
                            $productCatObj = new ProductCategory();
                            $prodCategories = $productCatObj->getCategoriesForSelectBox($this->siteLangId, '', $productCatCode);

                            foreach ($productCatCode as $code) {
                                $code = FatUtility::int($code);
                                if (isset($prodCategories[$code]['prodcat_name'])) {
                                    $prodCategories[$code]['prodcat_name'];
                                    $nodes[] = array('title' => $prodCategories[$code]['prodcat_name'], 'href' => UrlHelper::generateUrl('category', 'view', array($code)));
                                }
                            }
                            $nodes[] = array('title' => ($row['selprod_title']) ? $row['selprod_title'] : $row['product_name']);
                        }
                    }
                }
                break;
            default:
                $nodes[] = array('title' => Labels::getLabel('LBL_' . FatUtility::camel2dashed($action), $this->siteLangId));
                break;
        }
        return $nodes;
    }

    public function logWeightage()
    {
        $post = FatApp::getPostedData();
        $selprod_code = (isset($post['selprod_code']) && $post['selprod_code'] != '') ? $post['selprod_code'] : '';

        if ($selprod_code == '') {
            return false;
        }

        $weightageKey = SmartWeightageSettings::PRODUCT_VIEW;
        if (isset($post['timeSpend']) && $post['timeSpend'] == true) {
            $weightageKey = SmartWeightageSettings::PRODUCT_TIME_SPENT;
        }

        $weightageSettings = SmartWeightageSettings::getWeightageAssoc();
        Product::recordProductWeightage($selprod_code, $weightageKey, $weightageSettings[$weightageKey]);
    }

    private function getCartForm($formLangId)
    {
        $frm = new Form('frmBuyProduct', array('id' => 'frmBuyProduct'));
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Quantity', $formLangId), 'quantity', 1, array('maxlength' => '3'));
        $fld->requirements()->setIntPositive();
        // $frm->addSubmitButton(null, 'btnProductBuy', Labels::getLabel('LBL_Buy_Now', $formLangId ), array( 'id' => 'btnProductBuy' ) );
        //$frm->addSubmitButton(null, 'btnAddToCart', Labels::getLabel('LBL_Add_to_Cart', $formLangId), array( 'id' => 'btnAddToCart' ));
        // $frm->addHTML(null, 'btnProductBuy', '<button name="btnProductBuy" type="submit" id="btnProductBuy" class="btn btn-brand block-on-mobile add-to-cart--js btnBuyNow"> ' . Labels::getLabel('LBL_Buy_Now', $formLangId) . '</button>');
        $frm->addHTML('', 'btnAddToCart', '<button name="btnAddToCart" type="submit" id="btnAddToCart" class="btn btn-brand btn-block quickView add-to-cart add-to-cart--js "> ' . Labels::getLabel('LBL_Add_to_Cart', $formLangId) . '</button>');
        $frm->addHiddenField('', 'selprod_id');
        return $frm;
    }

    private function getReviewSearchForm($pageSize = 10)
    {
        $frm = new Form('frmReviewSearch');
        $frm->addHiddenField('', 'selprod_id');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'pageSize', $pageSize);
        $frm->addHiddenField('', 'orderBy', 'most_helpful');
        return $frm;
    }

    private function getReviewAbuseForm($reviewId)
    {
        $frm = new Form('frmReviewAbuse');
        $frm->addHiddenField('', 'spra_spreview_id', $reviewId);
        $frm->addTextarea(Labels::getLabel('Lbl_Comments', $this->siteLangId), 'spra_comments');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('Lbl_Report_Abuse', $this->siteLangId));
        return $frm;
    }

    private function getPollForm($pollId, $langId)
    {
        $frm = new Form('frmPoll');
        $frm->addHiddenField('', 'polling_id', $pollId);
        $frm->addRadioButtons('', 'polling_feedback', Polling::getPollingResponseTypeArr($langId), '', array('class' => 'listing--vertical listing--vertical-chcek'), array());
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('Lbl_Vote', $this->siteLangId), array('class' => 'btn btn-brand poll--link-js'));
        return $frm;
    }

    public function fatActionCatchAll($action)
    {
        FatUtility::exitWithErrorCode(404);
    }

    public function track($productId = 0)
    {
        $bannerId = FatUtility::int($productId);
        if (1 > $productId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl(''));
        }
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        /* Track Click */
        $prodObj = new PromotionSearch($this->siteLangId, true);
        $prodObj->joinProducts();
        $prodObj->joinActiveUser();
        $prodObj->joinShops();
        $prodObj->addPromotionTypeCondition(Promotion::TYPE_PRODUCT);
        $prodObj->addShopActiveExpiredCondition();
        $prodObj->joinUserWallet();
        $prodObj->joinBudget();
        $prodObj->addBudgetCondition();
        $prodObj->doNotCalculateRecords();
        $prodObj->addMultipleFields(array('selprod_id as proSelProdId', 'promotion_id'));
        $prodObj->addCondition('promotion_record_id', '=', $productId);
        $prodObj->addCondition('promotion_deleted', '=', applicationConstants::NO);
        $sponsoredProducts = array();
        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory($this->siteLangId);
        $productSrchObj->doNotCalculateRecords();
        $productSrchObj->setPageSize(10);
        $productSrchObj->setDefinedCriteria();

        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $productSrchObj->joinFavouriteProducts($loggedUserId);
            $productSrchObj->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $productSrchObj->joinUserWishListProducts($loggedUserId);
            $productSrchObj->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }
        $productSrchObj->joinProductRating();
        $productSrchObj->addMultipleFields(
            array(
                'product_id', 'selprod_id', 'COALESCE(product_name, product_identifier) as product_name', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
                'theprice', 'selprod_price', 'selprod_stock', 'selprod_condition', 'prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'selprod_sold_count'
            )
        );

        $productCatSrchObj = ProductCategory::getSearchObject(false, $this->siteLangId);
        $productCatSrchObj->addOrder('m.prodcat_active', 'DESC');
        $productCatSrchObj->doNotCalculateRecords();
        /* $productCatSrchObj->setPageSize(4); */
        $productCatSrchObj->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_description'));

        $productSrchObj->joinTable('(' . $prodObj->getQuery() . ') ', 'INNER JOIN', 'selprod_id = ppr.proSelProdId ', 'ppr');
        $productSrchObj->addFld(array('promotion_id'));
        $productSrchObj->joinSellerSubscription();
        $productSrchObj->addSubscriptionValidCondition();
        $productSrchObj->addGroupBy('selprod_id');

        $rs = $productSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $url = UrlHelper::generateFullUrl('products', 'view', array($productId));
        if ($row == false) {
            if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
                FatApp::redirectUser($url);
            }
        }

        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (Promotion::isUserClickCountable($userId, $row['promotion_id'], $_SERVER['REMOTE_ADDR'], session_id())) {
            $promotionClickData = array(
                'pclick_promotion_id' => $row['promotion_id'],
                'pclick_user_id' => $userId,
                'pclick_datetime' => date('Y-m-d H:i:s'),
                'pclick_ip' => $_SERVER['REMOTE_ADDR'],
                'pclick_cost' => Promotion::getPromotionCostPerClick(Promotion::TYPE_PRODUCT),
                'pclick_session_id' => session_id(),
            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_CLICKS, $promotionClickData, false, [], $promotionClickData);
            $clickId = FatApp::getDb()->getInsertId();

            $promotionClickChargesData = array(
                'picharge_pclick_id' => $clickId,
                'picharge_datetime' => date('Y-m-d H:i:s'),
                'picharge_cost' => Promotion::getPromotionCostPerClick(Promotion::TYPE_PRODUCT),
            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_ITEM_CHARGES, $promotionClickChargesData, false);

            $promotionLogData = array(
                'plog_promotion_id' => $row['promotion_id'],
                'plog_date' => date('Y-m-d'),
                'plog_clicks' => 1,
            );

            $onDuplicatePromotionLogData = array_merge($promotionLogData, array('plog_clicks' => 'mysql_func_plog_clicks+1'));
            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_LOGS, $promotionLogData, true, array(), $onDuplicatePromotionLogData);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            FatApp::redirectUser($url);
        }

        FatApp::redirectUser(UrlHelper::generateUrl(''));
    }

    public function setUrlString()
    {
        $urlString = FatApp::getPostedData('urlString', FatUtility::VAR_STRING, '');
        if ($urlString != '') {
            $_SESSION['referer_page_url'] = rtrim($urlString, '/') . '/';
        }
    }

    public function sellers($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrchObj = new ProductSearch($this->siteLangId);

        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setLocationBasedInnerJoin(false);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria(0, 0, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation(false);
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->doNotLimitRecords();

        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id', 'spr.spreview_product_id', "ROUND(AVG(sprating_rating),2) as prod_rating", "count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $prodSrch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');

        $prodSrch->addMultipleFields(
            array(
                'product_id', 'COALESCE(product_name,product_identifier ) as product_name', 'product_seller_id', 'product_model', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description', 'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'special_price_found', 'splprice_start_date', 'splprice_end_date', 'COALESCE(selprod_title,product_name,product_identifier) as selprod_title', 'selprod_warranty', 'selprod_return_policy', 'selprodComments', 'theprice', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'shop_id', 'shop_name', 'COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'COALESCE(sq_sprating.totReviews,0) totReviews', 'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price', 'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled'
            )
        );

        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }
        /* more sellers[ */
        $product['moreSellersArr'] = $this->getMoreSeller($product['selprod_code'], $this->siteLangId);

        foreach ($product['moreSellersArr'] as $seller) {
            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                $product['rating'][$seller['selprod_user_id']] = SelProdRating::getSellerRating($seller['selprod_user_id']);
            } else {
                $product['rating'][$seller['selprod_user_id']] = 0;
            }

            /*[ Check COD enabled*/
            $codEnabled = false;
            if (Product::isProductShippedBySeller($seller['product_id'], $seller['product_seller_id'], $seller['selprod_user_id'])) {
                if ($product['selprod_cod_enabled']) {
                    $codEnabled = true;
                }
            } else {
                if ($product['product_cod_enabled']) {
                    $codEnabled = true;
                }
            }
            $product['cod'][$seller['selprod_user_id']] = $codEnabled;
            /*]*/
        }
        /* ] */

        $displayProductNotAvailableLable = false;
        if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $displayProductNotAvailableLable = true;
        }
        $this->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
        $this->set('product', $product);
        $this->_template->render();
    }

    public function productQuickDetail($selprod_id = 0)
    {
        $productImagesArr = array();
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrchObj = new ProductSearch($this->siteLangId);

        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setLocationBasedInnerJoin(false);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria(false, false, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation(false);
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->doNotLimitRecords();

        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $prodSrch->joinUserWishListProducts($loggedUserId);
            $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $prodSrch->addMultipleFields(array('product_id', 'product_identifier', 'COALESCE(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description', 'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'special_price_found', 'splprice_start_date', 'splprice_end_date', 'COALESCE(selprod_title,product_name, product_identifier) as selprod_title', 'selprod_warranty', 'selprod_return_policy', 'selprodComments', 'theprice', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'shop_id', 'shop_name', 'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price', 'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled', 'selprod_available_from', 'selprod_min_order_qty'));
        /* echo $selprod_id; die; */
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }

        $subscription = false;
        $allowed_images = -1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $product['selprod_user_id'], array('ossubs_images_allowed'));
            $allowed_images = $currentPlanData['ossubs_images_allowed'];
            $subscription = true;
        }

        /* Current Product option Values[ */
        $options = SellerProduct::getSellerProductOptions($selprod_id, false);
        /* CommonHelper::printArray($options);die(); */
        $productSelectedOptionValues = array();
        $productGroupImages = array();
        if ($options) {
            foreach ($options as $op) {
                /* Product UPC code [ */
                /* $product['product_upc'] = UpcCode::getUpcCode( $product['product_id'] , $op['selprodoption_optionvalue_id'] ); */
                /* ] */
                $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], $op['selprodoption_optionvalue_id'], $this->siteLangId, true, '', $allowed_images);
                if ($images) {
                    $productImagesArr += $images;
                }
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        if ($productImagesArr) {
            foreach ($productImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }
        $product['selectedOptionValues'] = $productSelectedOptionValues;
        /* ] */

        if (isset($allowed_images) && $allowed_images > 0) {
            $universal_allowed_images_count = $allowed_images - count($productImagesArr);
        }

        $productUniversalImagesArr = array();
        if (empty($productGroupImages) || !$subscription || isset($universal_allowed_images_count)) {
            $universalImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], -1, $this->siteLangId, true, '');
            /* CommonHelper::printArray($universalImages); die; */
            if ($universalImages) {
                if (isset($universal_allowed_images_count)) {
                    $images = array_slice($universalImages, 0, $universal_allowed_images_count);
                    $productUniversalImagesArr = $images;
                    $universal_allowed_images_count = $universal_allowed_images_count - count($productUniversalImagesArr);
                } elseif (!$subscription) {
                    $productUniversalImagesArr = $universalImages;
                }
            }
        }

        if ($productUniversalImagesArr) {
            foreach ($productUniversalImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        /*[ Product shipping cost */
        $shippingCost = 0;
        /*]*/

        /* more sellers[ */
        $product['moreSellersArr'] = $this->getMoreSeller($product['selprod_code'], $this->siteLangId, $product['selprod_user_id']);
        /* ] */

        $product['selprod_return_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_RETURN, $this->siteLangId);
        $product['selprod_warranty_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_WARRANTY, $this->siteLangId);

        /* Form buy product[ */
        $frm = $this->getCartForm($this->siteLangId);
        $frm->fill(array('selprod_id' => $selprod_id));
        $this->set('frmBuyProduct', $frm);
        /* ] */

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->doNotCalculateRecords();
        $optionSrchObj->doNotLimitRecords();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $validDateCondition = " and oss.ossubs_till_date >= '" . date('Y-m-d') . "'";
            $optionSrchObj->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=seller_user.user_id AND o.order_type=' . ORDERS::ORDER_SUBSCRIPTION . ' AND o.order_payment_status =1', 'o');
            $optionSrchObj->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id=' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') . $validDateCondition, 'oss');
        }
        $optionSrchObj->addCondition('product_id', '=', $product['product_id']);

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'COALESCE(option_name,option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', '=', $selprod_id);
        $optionSrch->addGroupBy('option_id');
        /* echo $optionSrch->getQuery();die; */
        $optionRs = $optionSrch->getResultSet();
        $optionRows = FatApp::getDb()->fetchAll($optionRs, 'option_id');
        /* CommonHelper::printArray($optionRows);die; */
        if ($optionRows) {
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $product['product_id']);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array('COALESCE(product_name, product_identifier) as product_name', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'COALESCE(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
                $optionValueSrch->addGroupBy('optionvalue_id');
                $optionValueRs = $optionValueSrch->getResultSet();
                $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs, 'optionvalue_id');
                $option['values'] = $optionValueRows;
            }
        }
        $this->set('optionRows', $optionRows);

        /* Get Product Specifications */
        $this->set('productSpecifications', $this->getProductSpecifications($product['product_id'], $this->siteLangId));
        /* End of Product Specifications */

        if ($product) {
            $title = $product['product_name'];

            if ($product['selprod_title']) {
                $title = $product['selprod_title'];
            }

            $product_description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($product["product_description"], true)), 500));
            $product_description .= ' - ' . Labels::getLabel('LBL_See_more_at', $this->siteLangId) . ": " . UrlHelper::getCurrUrl();

            $productImageUrl = '';
            /* $productImageUrl = UrlHelper::generateFullUrl('Image','product', array($product['product_id'],'', $product['selprod_id'],0,$this->siteLangId )); */
            if ($productImagesArr) {
                $afile_id = array_keys($productImagesArr)[0];
                $productImageUrl = UrlHelper::generateFullUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $afile_id));
            }
        }

        $displayProductNotAvailableLable = false;
        //availableInLocation
        if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $displayProductNotAvailableLable = true;
        }
        $this->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
        $this->set('product', $product);
        $this->set('productImagesArr', $productGroupImages);
        $this->_template->render(false, false);
    }

    public function linksAutocomplete()
    {
        $prodCatObj = new ProductCategory();
        $post = FatApp::getPostedData();
        $arr_options = $prodCatObj->getAutoCompleteProdCatTreeStructure(0, $this->siteLangId, $post['keyword']);
        $json = array();
        foreach ($arr_options as $key => $product) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($product, ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getListingData($get)
    {
        $db = FatApp::getDb();

        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $page = 1;
        if (array_key_exists('page', $get)) {
            $page = FatUtility::int($get['page']);
            if ($page < 2) {
                $page = 1;
            }
        }

        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        if (array_key_exists('pageSize', $get)) {
            $pageSize = FatUtility::int($get['pageSize']);
            $pageSizeArr = FilterHelper::getPageSizeArr($this->siteLangId);
            if (0 >= $pageSize || !array_key_exists($pageSize, $pageSizeArr)) {
                $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
            }
        }

        if (FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_FULL_TEXT_SEARCH, FatUtility::VAR_INT, 0)) {
            $srch = FullTextSearch::getListingObj($get, $this->siteLangId, $userId);
            $page = ($page - 1) * $pageSize;
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $srch->setFields(array('brand', 'categories', 'general'));
            $records = $srch->fetch();
            $products = [];

            if (isset($records['hits']) && count($records['hits']) > 0) {
                foreach ($records['hits'] as $record) {
                    if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
                        $arr = array('ufp_id' => 0);
                        $favSrch = new UserFavoriteProductSearch();
                        $favSrch->addCondition('ufp_user_id', '=', $userId);
                        $favSrch->addCondition('ufp_selprod_id', '=', $record['_source']['general']['selprod_id']);
                        $favSrch->doNotCalculateRecords();
                        $favSrch->setPageSize(1);
                        $favSrch->addGroupBy('selprod_id');
                        $rs = $favSrch->getResultSet();
                        $wishListProd = $db->fetch($rs);
                        if (!empty($wishListProd) && $wishListProd['ufp_id']) {
                            $arr = array('ufp_id' => $wishListProd['ufp_id']);
                        }
                    } else {
                        $arr = array('is_in_any_wishlist' => 0);
                        $wislistPSrchObj = new UserWishListProductSearch();
                        $wislistPSrchObj->joinWishLists();
                        $wislistPSrchObj->doNotCalculateRecords();
                        $wislistPSrchObj->setPageSize(1);
                        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $userId);
                        $wislistPSrchObj->addMultipleFields(array('uwlp_selprod_id', 'uwlp_uwlist_id'));
                        $wislistPSrchObj->addCondition('uwlp_selprod_id', '=', $record['_source']['general']['selprod_id']);
                        $rs = $wislistPSrchObj->getResultSet();
                        $wishListProd = $db->fetch($rs);
                        if (!empty($wishListProd) && $wishListProd['uwlp_uwlist_id']) {
                            $arr = array('is_in_any_wishlist' => 1);
                        }
                    }

                    $products[] = array_merge($record['_source']['general'], $record['_source']['brand'], current($record['_source']['categories']), $arr);
                }
            }
        } else {
            $srch = Product::getListingObj($get, $this->siteLangId, $userId);
            $srch->setPageNumber($page);
            if ($pageSize) {
                $srch->setPageSize($pageSize);
            }

            $rs = $srch->getResultSet();
            $db = FatApp::getDb();
            $products = $db->fetchAll($rs);
        }

        /* to show searched category data[ */
        $categoryId = null;
        $category = array();
        if (array_key_exists('category', $get)) {
            $categoryId = FatUtility::int($get['category']);
            if ($categoryId) {
                $productCategorySearch = new ProductCategorySearch($this->siteLangId);
                $productCategorySearch->addCondition('prodcat_id', '=', $categoryId);
                $productCategorySearch->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_description', 'prodcat_code'));
                $productCategorySearchRs = $productCategorySearch->getResultSet();
                $category = $db->fetch($productCategorySearchRs);
                $category['banner'] = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER, $categoryId);
            }
        }
        /* ] */

        $data = array(
            'products' => $products,
            'category' => $category,
            'categoryId' => $categoryId,
            'postedData' => $get,
            'page' => $page,
            'pageCount' => $srch->pages(),
            'pageSize' => $pageSize,
            'recordCount' => $srch->recordCount(),
            'siteLangId' => $this->siteLangId
        );
        return $data;
    }

    public function getFilteredProducts()
    {
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId(true);
        $post['join_price'] = 1;
        $page = 1;
        if (array_key_exists('page', $post)) {
            $page = FatUtility::int($post['page']);
            if ($page < 2) {
                $page = 1;
            }
        }

        $pageSize = !empty($post['pageSize']) ? FatUtility::int($post['pageSize']) : FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);

        $srch = Product::getListingObj($post, $this->siteLangId, $userId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs);

        $data = array(
            'products' => $products,
            'page' => $page,
            'pageCount' => $srch->pages(),
            'pageSize' => $pageSize,
            'recordCount' => $srch->recordCount()
        );
        $this->set('data', $data);
        $this->_template->render();
    }

    public function getOptions($selProdId)
    {
        $selProdId = FatUtility::int($selProdId);
        if (1 > $selProdId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $productId = SellerProduct::getAttributesById($selProdId, 'selprod_product_id', false);

        $options = SellerProduct::getSellerProductOptions($selProdId, false);
        $productSelectedOptionValues = array();
        if (is_array($options) && 0 < count($options)) {
            foreach ($options as $op) {
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        $prodSrchObj = new ProductSearch($this->siteLangId);

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'COALESCE(option_name,option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', '=', $selProdId);
        $optionSrch->addGroupBy('option_id');

        $optionRs = $optionSrch->getResultSet();
        $optionRows = FatApp::getDb()->fetchAll($optionRs);

        if ($optionRows) {
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $productId);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array('COALESCE(product_name, product_identifier) as product_name', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'COALESCE(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
                $optionValueSrch->addGroupBy('optionvalue_id');
                $optionValueRs = $optionValueSrch->getResultSet();
                $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs);

                foreach ($optionValueRows as $index => $opVal) {
                    $optionValueRows[$index]['isAvailable'] = 1;
                    if (is_array($productSelectedOptionValues) && !in_array($opVal['optionvalue_id'], $productSelectedOptionValues)) {
                        $optionUrl = Product::generateProductOptionsUrl($selProdId, $productSelectedOptionValues, $option['option_id'], $opVal['optionvalue_id'], $productId);
                        $optionUrlArr = explode("::", $optionUrl);
                        if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                            $optionValueRows[$index]['isAvailable'] = 0;
                        }
                    }
                }

                $option['values'] = $optionValueRows;
            }
        }
        $this->set('options', $optionRows);
        $this->_template->render();
    }

    public function autoCompleteTaxCategories()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $srch = Tax::getSearchObject($this->siteLangId, true);
        $srch->addCondition('taxcat_deleted', '=', 0);
        $activatedTaxServiceId = Tax::getActivatedServiceId();

        $srch->addFld('taxcat_id');
        if ($activatedTaxServiceId) {
            $srch->addFld('concat(IFNULL(taxcat_name,taxcat_identifier), " (",taxcat_code,")")as taxcat_name');
        } else {
            $srch->addFld('IFNULL(taxcat_name,taxcat_identifier)as taxcat_name');
        }
        $srch->addCondition('taxcat_plugin_id', '=', $activatedTaxServiceId);

        if (!empty($post['keyword'])) {
            $srch->addCondition('taxcat_name', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('taxcat_identifier', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('taxcat_code', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $taxCategories = $db->fetchAll($rs, 'taxcat_id');
        $json = array();
        $defaultStringLength = applicationConstants::DEFAULT_STRING_LENGTH;
        foreach ($taxCategories as $key => $taxCategory) {
            $taxCatName = strip_tags(html_entity_decode($taxCategory['taxcat_name'], ENT_QUOTES, 'UTF-8'));
            $taxCatName1 = substr($taxCatName, 0, $defaultStringLength);
            if ($defaultStringLength < strlen($taxCatName)) {
                $taxCatName1 .= '...';
            }
            $json[] = array(
                'id' => $key,
                'name' => $taxCatName1
            );
        }
        die(json_encode($json));
    }
}
