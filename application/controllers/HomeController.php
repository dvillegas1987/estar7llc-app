<?php

class HomeController extends MyAppController
{
    public function index()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId(true);

        $productSrchObj = $this->getProductSearchObj($loggedUserId);
        $sponsoredShopsInCollection = $sponsoredProdsInCollection = [];
        $collections = $this->getCollections($productSrchObj, $sponsoredShopsInCollection, $sponsoredProdsInCollection);

        $sponShopLayoutCount = count($sponsoredShopsInCollection);
        $sponProdLayoutCount = count($sponsoredProdsInCollection);
        if (0 < $sponProdLayoutCount) {
            foreach ($sponsoredProdsInCollection as $indexId => $collectionId) {
                $recordId = (true === MOBILE_APP_API_CALL) ? $indexId : $collectionId;
                $sponsoredProds = $this->getSponsoredProducts($productSrchObj);

                if (empty($sponsoredProds)) {
                    unset($collections[$recordId]);
                    continue;
                }

                $collections[$recordId]['products'] = $sponsoredProds;
                $collections[$recordId]['totProducts'] = count($sponsoredProds);
            }
        }

        if (0 < $sponShopLayoutCount) {
            foreach ($sponsoredShopsInCollection as $indexId => $collectionId) {
                $recordId = (true === MOBILE_APP_API_CALL) ? $indexId : $collectionId;
                $sponsoredShops = $this->getSponsoredShops($productSrchObj);

                if (empty($sponsoredShops)) {
                    unset($collections[$recordId]);
                    continue;
                }

                $collections[$recordId]['shops'] = $sponsoredShops;
                $collections[$recordId]['totShops'] = count($sponsoredShops);
            }
        }

        /*  $this->set('sponsoredProds', $sponsoredProds);
         $this->set('sponsoredShops', $sponsoredShops); */
        /* $this->set('banners', $banners); */

        $slides = $this->getSlides();
        $this->set('slides', $slides);
        $this->set('collections', $collections);
        $this->set('isWishlistEnable', FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1));

        $displayProductNotAvailableLable = false;
        //availableInLocation
        if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $displayProductNotAvailableLable = true;
        }

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
            die;
        }

        $this->_template->addJs('js/slick.min.js');
        $apiCall = (true === MOBILE_APP_API_CALL) ? 1 : 0;
        $geoAddress = Address::getYkGeoData();
        $cacheKey = $this->siteLangId . '-' . $this->siteCurrencyId . '-' . $apiCall . '-' . serialize($geoAddress);
        $cacheKey .= FatApp::getConfig('LAST_FAV_MARK_TIME', FatUtility::VAR_INT, 0);

        $collectionTemplates = array();
        foreach ($collections as $collection) {
            switch ($collection['collection_layout_type']) {
                case Collections::TYPE_SPONSORED_PRODUCT_LAYOUT:
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collection', $collection);
                    $tpl->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
                    $sponsoredProdsLayout = $tpl->render(false, false, '_partial/collection/sponsored-products.php', true, true);
                    $collectionTemplates[$collection['collection_id']]['html'] = $sponsoredProdsLayout;
                    break;
                case Collections::TYPE_SPONSORED_SHOP_LAYOUT:
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collection', $collection);
                    $sponsoredShopsLayout = $tpl->render(false, false, '_partial/collection/sponsored-shops.php', true, true);
                    $collectionTemplates[$collection['collection_id']]['html'] = $sponsoredShopsLayout;
                    break;
                case Collections::TYPE_BANNER_LAYOUT1:
                    if (isset($collection['banners'])) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('bannerLayout1', $collection['banners']);
                        $bannerFirstLayout = $tpl->render(false, false, '_partial/banners/home-banner-first-layout.php', true, true);
                        $collectionTemplates[$collection['collection_id']]['html'] = $bannerFirstLayout;
                    }
                    break;
                case Collections::TYPE_BANNER_LAYOUT2:
                    if (isset($collection['banners'])) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('bannerLayout1', $collection['banners']);
                        $bannersecondLayout = $tpl->render(false, false, '_partial/banners/home-banner-second-layout.php', true, true);
                        $collectionTemplates[$collection['collection_id']]['html'] = $bannersecondLayout;
                    }
                    break;
                case Collections::TYPE_PRODUCT_LAYOUT1:
                    $homePageProdLayout1 = FatCache::get('homePageProdLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageProdLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $tpl->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
                        $homePageProdLayout1 = $tpl->render(false, false, '_partial/collection/product-layout-1.php', true, true);
                        FatCache::set('homePageProdLayout1' . $collection['collection_id'] . $cacheKey, $homePageProdLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageProdLayout1;
                    break;
                case Collections::TYPE_PRODUCT_LAYOUT2:
                    $homePageProdLayout2 = FatCache::get('homePageProdLayout2' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageProdLayout2) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $tpl->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
                        $homePageProdLayout2 = $tpl->render(false, false, '_partial/collection/product-layout-2.php', true, true);
                        FatCache::set('homePageProdLayout2' . $collection['collection_id'] . $cacheKey, $homePageProdLayout2, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageProdLayout2;
                    break;
                case Collections::TYPE_PRODUCT_LAYOUT3:
                    $homePageProdLayout3 = FatCache::get('homePageProdLayout3' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageProdLayout3) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $tpl->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
                        $homePageProdLayout3 = $tpl->render(false, false, '_partial/collection/product-layout-3.php', true, true);
                        FatCache::set('homePageProdLayout3' . $collection['collection_id'] . $cacheKey, $homePageProdLayout3, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageProdLayout3;
                    break;
                case Collections::TYPE_CATEGORY_LAYOUT1:
                    $homePageCatLayout1 = FatCache::get('homePageCatLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageCatLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $tpl->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
                        $homePageCatLayout1 = $tpl->render(false, false, '_partial/collection/category-layout-1.php', true, true);
                        FatCache::set('homePageCatLayout1' . $collection['collection_id'] . $cacheKey, $homePageCatLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageCatLayout1;
                    break;
                case Collections::TYPE_CATEGORY_LAYOUT2:
                    $homePageCatLayout2 = FatCache::get('homePageCatLayout2' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageCatLayout2) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $tpl->set('`displayProductNotAvailableLable`', $displayProductNotAvailableLable);
                        $homePageCatLayout2 = $tpl->render(false, false, '_partial/collection/category-layout-2.php', true, true);
                        FatCache::set('homePageCatLayout2' . $collection['collection_id'] . $cacheKey, $homePageCatLayout2, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageCatLayout2;
                    break;
                case Collections::TYPE_SHOP_LAYOUT1:
                    $homePageShopLayout1 = FatCache::get('homePageShopLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageShopLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $homePageShopLayout1 = $tpl->render(false, false, '_partial/collection/shop-layout-1.php', true, true);
                        FatCache::set('homePageShopLayout1' . $collection['collection_id'] . $cacheKey, $homePageShopLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageShopLayout1;
                    break;
                case Collections::TYPE_BRAND_LAYOUT1:
                    $homePageBrandLayout1 = FatCache::get('homePageBrandLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageBrandLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $homePageBrandLayout1 = $tpl->render(false, false, '_partial/collection/brand-layout-1.php', true, true);
                        FatCache::set('homePageBrandLayout1' . $collection['collection_id'] . $cacheKey, $homePageBrandLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageBrandLayout1;
                    break;
                case Collections::TYPE_BLOG_LAYOUT1:
                    $homePageBlogLayout1 = FatCache::get('homePageBlogLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageBlogLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $homePageBlogLayout1 = $tpl->render(false, false, '_partial/collection/blog-layout-1.php', true, true);
                        FatCache::set('homePageBlogLayout1' . $collection['collection_id'] . $cacheKey, $homePageBlogLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageBlogLayout1;
                    break;
                case Collections::TYPE_FAQ_LAYOUT1:
                    $homePageFaqLayout1 = FatCache::get('homePageFaqLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageFaqLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $homePageFaqLayout1 = $tpl->render(false, false, '_partial/collection/faq-layout-1.php', true, true);
                        FatCache::set('homePageFaqLayout1' . $collection['collection_id'] . $cacheKey, $homePageFaqLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageFaqLayout1;
                    break;
                case Collections::TYPE_TESTIMONIAL_LAYOUT1:
                    $homePageTestimonialLayout1 = FatCache::get('homePageTestimonialLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageTestimonialLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $homePageTestimonialLayout1 = $tpl->render(false, false, '_partial/collection/testimonial-layout-1.php', true, true);
                        FatCache::set('homePageTestimonialLayout1' . $collection['collection_id'] . $cacheKey, $homePageTestimonialLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageTestimonialLayout1;
                    break;
                case Collections::TYPE_CONTENT_BLOCK_LAYOUT1:
                    $homePageContentBlockLayout1 = FatCache::get('homePageContentBlockLayout1' . $collection['collection_id'] . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                    if (!$homePageContentBlockLayout1) {
                        $tpl = new FatTemplate('', '');
                        $tpl->set('siteLangId', $this->siteLangId);
                        $tpl->set('collection', $collection);
                        $homePageContentBlockLayout1 = $tpl->render(false, false, '_partial/collection/content-block-layout-1.php', true, true);
                        FatCache::set('homePageContentBlockLayout1' . $collection['collection_id'] . $cacheKey, $homePageContentBlockLayout1, '.txt');
                    }
                    $collectionTemplates[$collection['collection_id']]['html'] = $homePageContentBlockLayout1;
                    break;
            }
        }
        $this->set('collectionTemplates', $collectionTemplates);

        $this->_template->render();
    }

    private function getProductSearchObj($loggedUserId)
    {
        $loggedUserId = FatUtility::int($loggedUserId);

        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->setLocationBasedInnerJoin(false);
        $productSrchObj->setGeoAddress();
        $productSrchObj->joinProductToCategory();
        $productSrchObj->joinProductToTax();
        /* $productSrchObj->doNotCalculateRecords();
        $productSrchObj->setPageSize( 10 ); */
        $productSrchObj->setDefinedCriteria();
        $productSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productSrchObj->addSubscriptionValidCondition();
        $productSrchObj->validateAndJoinDeliveryLocation(false);
        // $productSrchObj->joinProductRating();

        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $productSrchObj->joinFavouriteProducts($loggedUserId);
            $productSrchObj->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $productSrchObj->joinUserWishListProducts($loggedUserId);
            $productSrchObj->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $productSrchObj->addCondition('selprod_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
        $productSrchObj->addMultipleFields(array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_updated_on', 'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'theprice', 'selprod_price', 'selprod_stock', 'selprod_condition', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'selprod_sold_count', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'shop_id', 'selprod_min_order_qty'));
        return $productSrchObj;
    }

    public function languages()
    {
        $languages = Language::getAllNames(false);
        $languageArr = array();
        if (0 < count($languages)) {
            $siteDefaultLangId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
            foreach ($languages as &$language) {
                $language['isSiteDefaultLang'] = ($language['language_id'] === $siteDefaultLangId) ? 1 : 0;
                $languageArr[] = $language;
            }
        }
        $this->set('languages', $languageArr);
        $this->_template->render();
    }

    public function setLanguage($langId = 0, $pathname = '')
    {
        if (!FatUtility::isAjaxCall()) {
            die('Invalid Action.');
        }

        $pathname = FatApp::getPostedData('pathname', FatUtility::VAR_STRING, '');
        $redirectUrl = '';
        if (empty($pathname)) {
            $redirectUrl = UrlHelper::generateFullUrl();
        }

        $isDefaultLangId = false;
        if ($langId == FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1)) {
            $isDefaultLangId = true;
        }

        if (FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0) && count(LANG_CODES_ARR) > 1) {
            $langCodeArr = LANG_CODES_ARR;
            if (count($langCodeArr) > 1) {
                $langIds = array_flip($langCodeArr);

                if (!empty($pathname)) {
                    $existingUrlLangCode = strtoupper(substr(ltrim($pathname, '/'), 0, 2));
                } else {
                    $existingUrlLangCode = $langCodeArr[CommonHelper::getLangId()];
                }

                if (in_array($existingUrlLangCode, LANG_CODES_ARR)) {
                    // $existingUrlLangId = $langIds[$existingUrlLangCode];
                    $pathname = ltrim(substr(ltrim($pathname, '/'), 2), '/');
                } else {
                    // $existingUrlLangId = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
                    $pathname = ltrim($pathname, '/');
                }

                $srch = UrlRewrite::getSearchObject();
                $srch->joinTable(UrlRewrite::DB_TBL, 'LEFT OUTER JOIN', 'temp.urlrewrite_original = ur.urlrewrite_original and temp.urlrewrite_lang_id = ' . $langId, 'temp');
                $srch->doNotCalculateRecords();
                $srch->setPageSize(1);
                $srch->addMultipleFields(array('ifnull(temp.urlrewrite_custom, ur.urlrewrite_custom) customurl'));
                $srch->addCondition('ur.' . UrlRewrite::DB_TBL_PREFIX . 'custom', '=', $pathname);
                // $srch->addCondition('ur.' . UrlRewrite::DB_TBL_PREFIX . 'lang_id', '=', $existingUrlLangId);

                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                if (!empty($row)) {
                    $redirectUrl = UrlHelper::generateFullUrl('', '', [], '', null, false, false, false);

                    if (false == $isDefaultLangId) {
                        $redirectUrl .=  strtolower($langCodeArr[$langId]) . '/';
                    }
                    $redirectUrl .=  $row['customurl'];
                }
            }

            if (empty($redirectUrl)) {
                $redirectUrl = UrlHelper::generateFullUrl('', '', [], '', null, false, false, false);
                if (false == $isDefaultLangId) {
                    $redirectUrl .=  strtolower($langCodeArr[$langId]) . '/';
                }
                $redirectUrl .=  ltrim($pathname, '/');
            }
        } else {
            if (empty($redirectUrl)) {
                $redirectUrl = UrlHelper::generateFullUrl('', '', [], '', null, false, false, false) . ltrim($pathname, '/');
            }
        }


        $langId = FatUtility::int($langId);
        if (0 < $langId) {
            $languages = Language::getAllNames();
            if (array_key_exists($langId, $languages)) {
                setcookie('defaultSiteLang', $langId, time() + 3600 * 24 * 10, CONF_WEBROOT_URL);
            }
        }
        $this->set('redirectUrl', $redirectUrl);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function currencies()
    {
        $cObj = Currency::getSearchObject($this->siteLangId, true);
        $cObj->addMultipleFields(
            array(
                'currency_id', 'currency_code', 'IFNULL(curr_l.currency_name,curr.currency_code) as currency_name'
            )
        );
        $rs = $cObj->getResultSet();
        $currencies = $this->db->fetchAll($rs);
        $this->set('currencies', $currencies);
        $this->_template->render();
    }

    public function setCurrency($currencyId = 0)
    {
        if (!FatUtility::isAjaxCall()) {
            die('Invalid Action.');
        }

        $currencyId = FatUtility::int($currencyId);
        if (0 < $currencyId) {
            $currencies = Currency::getCurrencyAssoc($this->siteLangId);
            if (array_key_exists($currencyId, $currencies)) {
                setcookie('defaultSiteCurrency', $currencyId, time() + 3600 * 24 * 10, CONF_WEBROOT_URL);
            }
        }
    }

    public function languageLabels($download = 0, $langId = 0)
    {
        $langId = FatUtility::int($langId) > 0 ? $langId : $this->siteLangId;
        $download = FatUtility::int($download);
        $langCode = Language::getAttributesById($langId, 'language_code', false);

        if (0 < $download) {
            if (!Labels::updateDataToFile($langId, $langCode, Labels::TYPE_APP)) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Unable_to_update_file', $langId));
            }
            $fileName = $langCode . '.json';
            $filePath = Labels::JSON_FILE_DIR_NAME . '/' . Labels::TYPE_APP . '/AP/' . $fileName;

            if (false === file_exists(CONF_UPLOADS_PATH . $filePath)) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_FILE_NOT_FOUND._PLEASE_SYNC_FILE_FROM_ADMIN.', $langId));
            }
            AttachedFile::downloadAttachment($filePath, $fileName);
            exit;
        }

        $data = array(
            'languageCode' => $langCode,
            'downloadUrl' => UrlHelper::generateFullUrl('Home', 'languageLabels', array(1, $langId)),
            'langLabelUpdatedAt' => FatApp::getConfig('CONF_LANG_LABELS_UPDATED_AT', FatUtility::VAR_INT, time())
        );

        $this->set('data', $data);
        $this->_template->render();
    }

    public function setCurrentLocation()
    {
        $post = FatApp::getPostedData();

        $countryCode = $post['country'];
        $this->updateSettingByCurrentLocation($countryCode);

        if (!$_SESSION['geo_location']) {
            Message::addErrorMessage(Labels::getLabel('MSG_Current_Location', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Settings_with_your_current_location_setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateSettingByCurrentLocation($countryCode = '')
    {
        if (!$countryCode) {
            return;
        }

        $row = Countries::getCountryByCode($countryCode, array('country_code', 'country_id', 'country_currency_id', 'country_language_id'));
        if ($row == false) {
            return false;
        }
        $_SESSION['geo_location'] = true;
        $this->setCurrency($row['country_currency_id']);
        $this->setLanguage($row['country_language_id']);
    }

    public function affiliateReferral($referralCode)
    {
        $userSrchObj = User::getSearchObject();
        $userSrchObj->doNotCalculateRecords();
        $userSrchObj->doNotLimitRecords();
        $userSrchObj->addCondition('user_referral_code', '=', $referralCode);
        $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code'));
        $rs = $userSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row && $referralCode != '' && $row['user_referral_code'] == $referralCode) {
            $cookieExpiryDays = FatApp::getConfig("CONF_AFFILIATE_REFERRER_URL_VALIDITY", FatUtility::VAR_INT, 5);

            $cookieValue = array('data' => $row['user_referral_code'], 'creation_time' => time());
            $cookieValue = serialize($cookieValue);
            CommonHelper::setCookie('affiliate_referrer_code_signup', $cookieValue, time() + 3600 * 24 * $cookieExpiryDays);
        }
        FatApp::redirectUser(UrlHelper::generateUrl());
    }

    public function referral($userReferralCode)
    {
        $userSrchObj = User::getSearchObject();
        $userSrchObj->doNotCalculateRecords();
        $userSrchObj->doNotLimitRecords();
        $userSrchObj->addCondition('user_referral_code', '=', $userReferralCode);
        $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code'));
        $rs = $userSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row && $userReferralCode != '' && $row['user_referral_code'] == $userReferralCode) {
            $cookieExpiryDays = FatApp::getConfig("CONF_REFERRER_URL_VALIDITY", FatUtility::VAR_INT, 10);

            $cookieValue = array('data' => $row['user_referral_code'], 'creation_time' => time());
            $cookieValue = serialize($cookieValue);

            CommonHelper::setCookie('referrer_code_signup', $cookieValue, time() + 3600 * 24 * $cookieExpiryDays);
            CommonHelper::setCookie('referrer_code_checkout', $row['user_referral_code'], time() + 3600 * 24 * $cookieExpiryDays);
        }
        FatApp::redirectUser(UrlHelper::generateUrl());
    }

    private function getCollections($productSrchObj, &$sponsoredShopsInCollection, &$sponsoredProdsInCollection)
    {
        $langId = $this->siteLangId;
        $apiCall = (true === MOBILE_APP_API_CALL) ? 1 : 0;
        $geoAddress = Address::getYkGeoData();
        $cacheKey = $langId . '_' . $this->siteCurrencyId . '_' . $apiCall . '_' . serialize($geoAddress);
        $cacheKey .= FatApp::getConfig('LAST_FAV_MARK_TIME', FatUtility::VAR_INT, 0);


        $collectionCache = FatCache::get('collectionCache_' . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        /* if ($collectionCache) {
            return  unserialize($collectionCache);
        } */

        $db = FatApp::getDb();

        $collectionsArr = FatCache::get('collectionsArr' . $cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if (!$collectionsArr) {
            $srch = new CollectionSearch($langId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addOrder('collection_display_order', 'ASC');
            $srch->addMultipleFields(array('collection_id', 'IFNULL(collection_name,collection_identifier) as collection_name', 'IFNULL( collection_description, "" ) as collection_description', 'IFNULL(collection_link_caption, "") as collection_link_caption', 'collection_link_url', 'collection_layout_type', 'collection_type', 'collection_criteria', 'collection_child_records', 'collection_primary_records', 'collection_display_media_only', 'collection_for_app', 'collection_for_web', 'collection_display_order'));

            $applicableForCol = (true === MOBILE_APP_API_CALL) ? 'collection_for_app' : 'collection_for_web';
            $srch->addCondition($applicableForCol, '=', 'mysql_func_' . applicationConstants::YES, 'AND', true);

            $rs = $srch->getResultSet();
            $collectionsArr = $db->fetchAll($rs, 'collection_id');
            FatCache::set('collectionsArr' . $cacheKey, serialize($collectionsArr), '.txt');
        } else {
            $collectionsArr = unserialize($collectionsArr);
        }

        if (empty($collectionsArr)) {
            return array();
        }

        $collections = array();

        $productCatSrchObj = ProductCategory::getSearchObject(false, $langId);
        $productCatSrchObj->addOrder('m.prodcat_active', 'DESC');
        $productCatSrchObj->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_description'));

        $collectionObj = new CollectionSearch();
        $collectionObj->joinCollectionRecords();
        $collectionObj->addMultipleFields(array('ctr_record_id', 'ctr_display_order'));
        $collectionObj->addCondition('ctr_record_id', '!=', 'NULL');
        $i = 0;
        /* $sponsoredShops = $this->getSponsoredShops($productSrchObj);
        $sponsoredProds = $this->getSponsoredProducts($productSrchObj); */

        foreach ($collectionsArr as $collection_id => $collection) {
            if ($collectionCache && !in_array($collection['collection_type'], [Collections::COLLECTION_TYPE_SPONSORED_SHOPS, Collections::COLLECTION_TYPE_SPONSORED_PRODUCTS, Collections::COLLECTION_TYPE_BANNER])) {
                continue;
            }

            if (true === MOBILE_APP_API_CALL && 0 < $collection['collection_display_media_only'] && !in_array($collection['collection_type'], Collections::COLLECTION_WITHOUT_MEDIA)) {
                $imgUpdatedOn = Collections::getAttributesById($collection_id, 'collection_img_updated_on');
                $uploadedTime = AttachedFile::setTimeParam($imgUpdatedOn);

                $collection['collection_image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'collectionReal', array($collection_id, $langId,  'ORIGINAL', AttachedFile::FILETYPE_COLLECTION_IMAGE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                $collections[] = $collection;
                $i++;
                continue;
            }

            switch ($collection['collection_type']) {
                case Collections::COLLECTION_TYPE_SPONSORED_PRODUCTS:
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $sponsoredProdsInCollection[$i] = $collection['collection_id'];
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $sponsoredProdsInCollection[$collection['collection_id']] = $collection['collection_id'];
                    }
                    break;
                case Collections::COLLECTION_TYPE_SPONSORED_SHOPS:
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $sponsoredShopsInCollection[$i] = $collection['collection_id'];
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $sponsoredShopsInCollection[$collection['collection_id']] = $collection['collection_id'];
                    }

                    break;
                case Collections::COLLECTION_TYPE_BANNER:
                    /* $banners = $this->getBanners($collection_id); */
                    $banners = BannerLocation::getPromotionalBanners($collection_id, $langId);
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['banners'] = is_array($banners) && 0 < count($banners) ? $banners : (object) [];
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['banners'] = $banners;
                    }
                    break;
                case Collections::COLLECTION_TYPE_PRODUCT:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    $productSrchTempObj = clone $productSrchObj;
                    $productSrchTempObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'selprod_id = ctr.ctr_record_id', 'ctr');
                    if (true === MOBILE_APP_API_CALL) {
                        $productSrchTempObj->joinProductRating();
                        $productSrchTempObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
                    }
                    //$productSrchTempObj->addCondition('selprod_id', 'IN', array_keys($productIds));
                    $productSrchTempObj->addCondition('selprod_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
                    //$productSrchTempObj->addOrder('theprice', $orderBy);
                    $productSrchTempObj->joinSellers();
                    $productSrchTempObj->joinSellerSubscription($langId);
                    $productSrchTempObj->addGroupBy('selprod_id');
                    $productSrchTempObj->addOrder('ctr.ctr_display_order', 'ASC');
                    $productSrchTempObj->setPageSize($collection['collection_primary_records']);
                    $rs = $productSrchTempObj->getResultSet();

                    $recordCount = $productSrchTempObj->recordCount();
                    if (empty($recordCount)) {
                        continue 2;
                    }
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['products'] = $db->fetchAll($rs);
                        $collections[$i]['totProducts'] = $recordCount;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['products'] = $db->fetchAll($rs, 'selprod_id');
                        $collections[$collection['collection_id']]['totProducts'] = $recordCount;
                    }
                    /* ] */
                    unset($tempObj);
                    unset($productSrchTempObj);
                    break;

                case Collections::COLLECTION_TYPE_CATEGORY:
                    if (true === MOBILE_APP_API_CALL && Collections::TYPE_CATEGORY_LAYOUT2 == $collection['collection_layout_type']) {
                        continue 2;
                    }
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    /* fetch Categories data[ */
                    $productCatSrchTempObj = clone $productCatSrchObj;
                    $productCatSrchTempObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'prodcat_id = ctr.ctr_record_id', 'ctr');
                    //$productCatSrchTempObj->addCondition('prodcat_id', 'IN', array_keys($categoryIds));
                    $productCatSrchTempObj->addCondition('prodcat_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
                    $productCatSrchTempObj->addOrder('ctr.ctr_display_order', 'ASC');
                    $rs = $productCatSrchTempObj->getResultSet();
                    $recordCount = $productCatSrchTempObj->recordCount();
                    if (empty($recordCount)) {
                        continue 2;
                    }
                    /* ] */
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                    }
                    $counter = 0;
                    if ($collection['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT2) {
                        while ($catData = $db->fetch($rs)) {
                            if (true === MOBILE_APP_API_CALL) {
                                $collections[$i]['categories'][$counter] = $catData;
                                // $collections[$i]['categories'][$counter]['subCategories'] = $db->fetchAll($Catrs);
                            } else {
                                /* fetch Sub-Categories[ */
                                $subCategorySrch = clone $productCatSrchObj;
                                $subCategorySrch->doNotCalculateRecords();
                                //$subCategorySrch->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'prodcat_id = ctr.ctr_record_id', 'ctr');
                                //$subCategorySrch->addCondition('prodcat_id', '=', $catData['prodcat_id']);
                                $subCategorySrch->addCondition('prodcat_parent', '=', $catData['prodcat_id']);
                                $subCategorySrch->addCondition('prodcat_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
                                //$subCategorySrch->addOrder('ctr.ctr_record_id', 'ASC');
                                $subCategorySrch->setPageSize(5);
                                $Catrs = $subCategorySrch->getResultSet();

                                $collections[$collection['collection_id']]['categories'][$catData['prodcat_id']] = $catData;
                                $collections[$collection['collection_id']]['categories'][$catData['prodcat_id']]['subCategories'] = $db->fetchAll($Catrs);
                            }
                            /* ] */
                            $counter++;
                        }
                    } else {
                        while ($catData = $db->fetch($rs)) {
                            if (true === MOBILE_APP_API_CALL) {
                                $collections[$i]['categories'][$counter] = $catData;
                                // $collections[$i]['categories'][$counter]['products'] = $db->fetchAll($Prs);
                            } else {
                                /* fetch Product data[ */
                                $productShopSrchTempObj = clone $productSrchObj;
                                $productShopSrchTempObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'prodcat_id = ctr.ctr_record_id', 'ctr');
                                $productShopSrchTempObj->addCondition('prodcat_id', '=', $catData['prodcat_id']);
                                $productShopSrchTempObj->addOrder('ctr.ctr_record_id', 'ASC');
                                //$productShopSrchTempObj->addOrder('in_stock', 'DESC');
                                $productShopSrchTempObj->addGroupBy('selprod_product_id');
                                $productShopSrchTempObj->setPageSize(7);
                                $Prs = $productShopSrchTempObj->getResultSet();
                                if ($productShopSrchTempObj->recordCount() == 0) {
                                    continue;
                                }

                                $collections[$collection['collection_id']]['categories'][$catData['prodcat_id']]['catData'] = $catData;
                                $collections[$collection['collection_id']]['categories'][$catData['prodcat_id']]['products'] = $db->fetchAll($Prs);
                            }
                            /* ] */
                            $counter++;
                        }
                    }
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i]['totCategories'] = $tempObj->recordCount();
                    } else {
                        $collections[$collection['collection_id']]['totCategories'] = $tempObj->recordCount();
                    }
                    unset($tempObj);
                    break;
                case Collections::COLLECTION_TYPE_SHOP:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    $shopObj = new ShopSearch($langId);
                    $shopObj->setDefinedCriteria($langId);
                    $shopObj->joinSellerSubscription();
                    //$shopObj->addCondition('shop_id', 'IN', array_keys($shopIds));
                    $shopObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'shop_id = ctr.ctr_record_id', 'ctr');

                    if (false === MOBILE_APP_API_CALL) {
                        $shopObj->setPageSize($collection['collection_primary_records']);
                    }
                    $shopObj->addMultipleFields(array('ctr.ctr_display_order', 'shop_id', 'shop_user_id', 'IFNULL(shop_name, shop_identifier) as shop_name', 'IFNULL(country_name, country_code) as country_name', 'IFNULL(state_name, state_identifier) as state_name'));
                    $shopObj->addOrder('ctr.ctr_display_order', 'ASC');

                    $rs = $shopObj->getResultSet();
                    $recordCount = $shopObj->recordCount();
                    if (empty($recordCount)) {
                        continue 2;
                    }
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['totShops'] = $recordCount;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['totShops'] = $recordCount;
                    }
                    //CommonHelper::printArray($shopsData, true);
                    $counter = 0;
                    while ($shopsData = $db->fetch($rs)) {
                        /* fetch Shop data[ */
                        /*$productShopSrchTempObj = clone $productSrchObj;
                        $productShopSrchTempObj->addCondition('selprod_user_id', '=', $shopsData['shop_user_id']);
                        $productShopSrchTempObj->addOrder('in_stock', 'DESC');
                        $productShopSrchTempObj->addGroupBy('selprod_product_id');
                        $productShopSrchTempObj->setPageSize(3);
                        $Prs = $productShopSrchTempObj->getResultSet();*/

                        $rating = 0;
                        if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                            $rating = SelProdRating::getSellerRating($shopsData['shop_user_id']);
                        }

                        if (true === MOBILE_APP_API_CALL) {
                            $collections[$i]['shops'][$counter] = $shopsData;

                            $collections[$i]['shops'][$counter]['rating'] = $rating;
                        } else {
                            $collections[$collection['collection_id']]['shops'][$shopsData['shop_id']]['shopData'] = $shopsData;

                            $collections[$collection['collection_id']]['rating'][$shopsData['shop_id']] = $rating;
                        }


                        /*$collections[$collection['collection_layout_type']][$collection['collection_id']]['shops'][$shopsData['shop_id']]['products'] = $db->fetchAll($Prs);*/
                        /* ] */
                        $counter++;
                    }
                    unset($tempObj);
                    break;
                case Collections::COLLECTION_TYPE_BRAND:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    /* fetch Brand data[ */
                    $brandSearchObj = Brand::getSearchObject($langId, true, true);
                    $brandSearchTempObj = clone $brandSearchObj;
                    $brandSearchTempObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'brand_id = ctr.ctr_record_id', 'ctr');
                    $brandSearchTempObj->addMultipleFields(array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'));
                    //$brandSearchTempObj->addCondition('brand_id', 'IN', array_keys($brandIds));
                    $brandSearchTempObj->addOrder('ctr_display_order', 'ASC');
                    if (false === MOBILE_APP_API_CALL) {
                        $brandSearchTempObj->setPageSize($collection['collection_primary_records']);
                    }
                    $rs = $brandSearchTempObj->getResultSet();
                    $brands = $db->fetchAll($rs);
                    if (empty($brands)) {
                        continue 2;
                    }

                    /* ] */
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['totBrands'] = $brandSearchTempObj->recordCount();
                        $collections[$i]['brands'] = $brands;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['totBrands'] = $brandSearchTempObj->recordCount();
                        $collections[$collection['collection_id']]['brands'] = $brands;
                    }
                    unset($brandSearchTempObj);
                    unset($tempObj);
                    break;
                case Collections::COLLECTION_TYPE_BLOG:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    /* fetch Blog data[ */
                    $attr = [
                        'post_id',
                        'post_author_name',
                        'IFNULL(post_title, post_identifier) as post_title',
                        'post_updated_on',
                        'post_updated_on',
                        'group_concat(IFNULL(bpcategory_name, bpcategory_identifier) SEPARATOR "~") categoryNames',
                        'post_description'
                    ];
                    $blogSearchObj = BlogPost::getSearchObject($langId, true, true);
                    $blogSearchTempObj = clone $blogSearchObj;
                    $blogSearchTempObj->addMultipleFields($attr);
                    $blogSearchTempObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'post_id = ctr.ctr_record_id', 'ctr');
                    //$blogSearchTempObj->addCondition('post_id', 'IN', array_keys($blogPostIds));
                    $blogSearchTempObj->addOrder('ctr.ctr_display_order', 'ASC');
                    if (false === MOBILE_APP_API_CALL) {
                        $blogSearchTempObj->setPageSize($collection['collection_primary_records']);
                    }
                    $blogSearchTempObj->addGroupBy('post_id');
                    $rs = $blogSearchTempObj->getResultSet();
                    $blogPostsDetail = $db->fetchAll($rs);
                    if (empty($blogPostsDetail)) {
                        continue 2;
                    }
                    /* ] */
                    if (true === MOBILE_APP_API_CALL) {
                        array_walk($blogPostsDetail, function (&$value, &$key) {
                            $value['post_image'] = UrlHelper::generateFullUrl('Image', 'blogPostFront', array($value['post_id'], $this->siteLangId, ''));
                        });
                        $collections[$i] = $collection;
                        $collections[$i]['totBlogs'] = $blogSearchTempObj->recordCount();
                        $collections[$i]['blogs'] = $blogPostsDetail;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['totBlogs'] = $blogSearchTempObj->recordCount();
                        $collections[$collection['collection_id']]['blogs'] = $blogPostsDetail;
                    }

                    unset($blogSearchTempObj);
                    unset($tempObj);
                    break;
                case Collections::COLLECTION_TYPE_FAQ:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    /* fetch FAQ data[ */
                    $attr = [
                        'faq_id', 'faqcat_id', 'IFNULL(faq_title, faq_identifier) as faq_title', 'faq_content', 'IFNULL(faqcat_name, faqcat_identifier) as faqcat_name'
                    ];
                    $faqSearchObj = Faq::getSearchObject($langId);
                    $faqSearchTempObj = clone $faqSearchObj;
                    $faqSearchTempObj->joinTable(
                        FaqCategory::DB_TBL,
                        'INNER JOIN',
                        'faq_faqcat_id = faqcat_id',
                        'fc'
                    );
                    $faqSearchTempObj->joinTable(FaqCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'fc_l.' . FaqCategory::DB_TBL_LANG_PREFIX . 'faqcat_id = fc.' . FaqCategory::tblFld('id') . ' and fc_l.' . FaqCategory::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'fc_l');
                    $faqSearchTempObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'faq_id = ctr.ctr_record_id', 'ctr');
                    $faqSearchTempObj->addCondition('fc.faqcat_deleted', '=', 'mysql_func_' . applicationConstants::NO, 'AND', true);
                    $faqSearchTempObj->addCondition('fc.faqcat_active', '=', 'mysql_func_' . applicationConstants::ACTIVE, 'AND', true);
                    $faqSearchTempObj->addMultipleFields($attr);
                    $faqSearchTempObj->addOrder('ctr.ctr_display_order', 'ASC');
                    //$faqSearchTempObj->addCondition('faq_id', 'IN', array_keys($faqIds));
                    $faqSearchTempObj->addGroupBy('faq_id');
                    $res = $faqSearchTempObj->getResultSet();
                    $faqsDetail = $db->fetchAll($res);
                    if (empty($faqsDetail)) {
                        continue 2;
                    }
                    /* ] */
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['totFaqs'] = $faqSearchTempObj->recordCount();
                        $collections[$i]['faqs'] = $faqsDetail;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['totFaqs'] = $faqSearchTempObj->recordCount();
                        $collections[$collection['collection_id']]['faqs'] = $faqsDetail;
                    }

                    unset($faqSearchTempObj);
                    unset($tempObj);
                    break;

                case Collections::COLLECTION_TYPE_TESTIMONIAL:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->doNotCalculateRecords();
                    $tempObj->doNotLimitRecords();

                    /* fetch Testimonial data[ */
                    $attr = [
                        'testimonial_id', 'testimonial_user_name', 'IFNULL(testimonial_title, testimonial_identifier) as testimonial_title', 'testimonial_text'
                    ];
                    $testimonialSrchObj = Testimonial::getSearchObject($langId, true);
                    $testimonialSrchObj = clone $testimonialSrchObj;
                    $testimonialSrchObj->joinTable('(' . $tempObj->getQuery() . ')', 'INNER JOIN', 'testimonial_id = ctr.ctr_record_id', 'ctr');
                    $testimonialSrchObj->addMultipleFields($attr);
                    //$testimonialSrchObj->addCondition('testimonial_id', 'IN', array_keys($testimonialIds));
                    $testimonialSrchObj->addGroupBy('testimonial_id');
                    $testimonialSrchObj->addOrder('ctr.ctr_display_order', 'ASC');
                    $testimonialSrchObj->setPageSize(Collections::LIMIT_TESTIMONIAL_LAYOUT1);
                    $res = $testimonialSrchObj->getResultSet();
                    $testimonialsDetail = $db->fetchAll($res);
                    if (empty($testimonialsDetail)) {
                        continue 2;
                    }
                    /* ] */
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['totTestimonials'] = $testimonialSrchObj->recordCount();
                        $collections[$i]['testimonials'] = $testimonialsDetail;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['totTestimonials'] = $testimonialSrchObj->recordCount();
                        $collections[$collection['collection_id']]['testimonials'] = $testimonialsDetail;
                    }

                    unset($testimonialSrchObj);
                    unset($tempObj);
                    break;

                    /* case Collections::COLLECTION_TYPE_CONTENT_BLOCK:
                    $srch = Extrapage::getSearchObject($langId, true);
                    $srch->joinTable(
                        Collections::DB_TBL_COLLECTION_TO_RECORDS, 'INNER JOIN', 'epage_id = ctr_record_id', 'ctr'
                    );
                    $srch->addCondition(Collections::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collection_id);
                    $srch->addMultipleFields(array('epage_id', 'IFNULL(epage_label, epage_identifier) as epage_label', 'epage_content'));
                    $res = $srch->getResultSet();
                    $epageData = $db->fetch($res);
                    if (true === MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['epageContent'] = $epageData;
                    } else {
                        $collections[$collection['collection_id']] = $collection;
                        $collections[$collection['collection_id']]['epageContent'] = $epageData;
                    }

                    unset($epageData);
                    unset($tempObj);
                    break; */
                case Collections::COLLECTION_TYPE_REVIEWS:
                    $collections[$i] = $collection;
                    $collections[$i]['pendingForReviews'] = array();
                    $loggedUserId = UserAuthentication::getLoggedUserId(true);
                    if (0 < $loggedUserId && (FatApp::getConfig('CONF_ALLOW_REVIEWS', FatUtility::VAR_INT, 0))) {
                        $pendingForReviews = OrderProduct::pendingForReviews($loggedUserId, $this->siteLangId);
                        if (is_array($pendingForReviews) && 0 < count($pendingForReviews)) {
                            foreach ($pendingForReviews as $key => &$orderProduct) {
                                $canSubmitFeedback = Orders::canSubmitFeedback($orderProduct['order_user_id'], $orderProduct['order_id'], $orderProduct['op_selprod_id']);
                                if (false === $canSubmitFeedback) {
                                    continue;
                                }
                                $orderProduct['product_image_url'] = UrlHelper::generateFullUrl('image', 'product', array($orderProduct['selprod_product_id'], "THUMB", $orderProduct['op_selprod_id'], 0, $this->siteLangId));
                            }
                            $collections[$i]['pendingForReviews'] = $pendingForReviews;
                        }
                    }
                    break;
            }
            $i++;
        }

        if ($collectionCache) {
            return  unserialize($collectionCache);
        }

        FatCache::set('collectionCache_' . $cacheKey, serialize($collections), '.txt');
        return $collections;
    }

    private function getSlides()
    {
        $langId = $this->siteLangId;
        $db = FatApp::getDb();
        $srchSlide = new SlideSearch($langId);
        $srchSlide->doNotCalculateRecords();
        $srchSlide->joinPromotions($langId, true, true, true);
        $srchSlide->addPromotionTypeCondition();
        $srchSlide->joinUserWallet();
        $srchSlide->joinActiveUser();
        $srchSlide->addSkipExpiredPromotionAndSlideCondition();
        $srchSlide->joinBudget();
        $srchSlide->joinAttachedFile();
        $srchSlide->addMultipleFields(array('slide_id', 'slide_record_id', 'slide_type', 'IFNULL(promotion_name, promotion_identifier) as promotion_name,IFNULL(slide_title, slide_identifier) as slide_title', 'slide_target', 'slide_url', 'promotion_id', 'daily_cost', 'weekly_cost', 'monthly_cost', 'total_cost', 'slide_img_updated_on'));

        $totalSlidesPageSize = FatApp::getConfig('CONF_TOTAL_SLIDES_HOME_PAGE', FatUtility::VAR_INT, 4);
        $ppcSlidesPageSize = FatApp::getConfig('CONF_PPC_SLIDES_HOME_PAGE', FatUtility::VAR_INT, 4);

        $ppcSlides = array();
        $adminSlides = array();

        $slidesSrch = new SearchBase('(' . $srchSlide->getQuery() . ') as t');
        $slidesSrch->addMultipleFields(array('slide_id', 'slide_type', 'slide_record_id', 'slide_url', 'slide_target', 'slide_title', 'promotion_id', 'userBalance', 'daily_cost', 'weekly_cost', 'monthly_cost', 'total_cost', 'promotion_budget', 'promotion_duration', 'slide_img_updated_on'));
        $slidesSrch->addOrder('', 'rand()');

        if (0 < $ppcSlidesPageSize) {
            $ppcSrch = clone $slidesSrch;
            $ppcSrch->addDirectCondition(
                '((CASE
					WHEN promotion_duration=' . Promotion::DAILY . ' THEN promotion_budget > COALESCE(daily_cost,0)
					WHEN promotion_duration=' . Promotion::WEEKLY . ' THEN promotion_budget > COALESCE(weekly_cost,0)
					WHEN promotion_duration=' . Promotion::MONTHLY . ' THEN promotion_budget > COALESCE(monthly_cost,0)
					WHEN promotion_duration=' . Promotion::DURATION_NOT_AVAILABALE . ' THEN promotion_budget = -1
				  END ) )'
            );

            $ppcSrch->addCondition('slide_type', '=', 'mysql_func_' . Slides::TYPE_PPC, 'AND', true);
            $ppcSrch->setPageSize($ppcSlidesPageSize);
            $ppcRs = $ppcSrch->getResultSet();
            $ppcSlides = $db->fetchAll($ppcRs, 'slide_id');
        }

        $ppcSlidesCount = count($ppcSlides);
        if ($totalSlidesPageSize > $ppcSlidesCount) {
            $totalSlidesPageSize = $totalSlidesPageSize - $ppcSlidesCount;
            $adminSlideSrch = clone $slidesSrch;
            $adminSlideSrch->addCondition('slide_type', '=', 'mysql_func_' . Slides::TYPE_SLIDE, 'AND', true);
            $adminSlideSrch->setPageSize($totalSlidesPageSize);
            $slideRs = $adminSlideSrch->getResultSet();
            $adminSlides = $db->fetchAll($slideRs, 'slide_id');
        }

        $slides = array_merge($ppcSlides, $adminSlides);
        return $slides;
    }

    /* private function getBanners($collectionId)
    {
        $langId = $this->siteLangId;
        $top_banners = BannerLocation::getPromotionalBanners(BannerLocation::HOME_PAGE_BANNER_LAYOUT_1, $langId, $collectionId);
        $middle_banners = array();
        $pageSize = 0;
        if (true === MOBILE_APP_API_CALL) {
            $pageSize = BannerLocation::MOBILE_API_BANNER_PAGESIZE;
            $middle_banners = BannerLocation::getPromotionalBanners(BannerLocation::HOME_PAGE_MOBILE_BANNER, $langId, $collectionId, $pageSize);
        }
        $bottom_banners = BannerLocation::getPromotionalBanners(BannerLocation::HOME_PAGE_BANNER_LAYOUT_2, $langId, $collectionId, $pageSize);
        $banners = array_merge($top_banners, $middle_banners, $bottom_banners);
        return $banners;
    } */

    private function getSponsoredShops($productSrchObj)
    {
        $langId = $this->siteLangId;
        $shopPageSize = FatApp::getConfig('CONF_PPC_SHOPS_HOME_PAGE', FatUtility::VAR_INT, 2);
        if (1 > $shopPageSize) {
            return array();
        }

        $sponsoredShops = array();
        $db = FatApp::getDb();

        $shopObj = new PromotionSearch($langId);
        $shopObj->setDefinedCriteria();
        $shopObj->joinActiveUser();
        $shopObj->joinShops($langId, true, true);
        $shopObj->joinShopCountry();
        $shopObj->joinShopState();
        $shopObj->addPromotionTypeCondition(Promotion::TYPE_SHOP);
        $shopObj->addShopActiveExpiredCondition();
        $shopObj->joinUserWallet();
        $shopObj->joinBudget();
        $shopObj->addBudgetCondition();
        $shopObj->addOrder('', 'rand()');
        $shopObj->setPageSize($shopPageSize);

        $rs = $shopObj->getResultSet();
        $i = 0;
        while ($shops = $db->fetch($rs)) {
            /* fetch Shop data[ */
            $productShopSrchTempObj = clone $productSrchObj;

            if (true === MOBILE_APP_API_CALL) {
                $productShopSrchTempObj->joinProductRating();
                $productShopSrchTempObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
            }

            $productShopSrchTempObj->addCondition('selprod_user_id', '=', $shops['shop_user_id']);
            $productShopSrchTempObj->addGroupBy('selprod_product_id');
            $productShopSrchTempObj->doNotCalculateRecords();
            $productShopSrchTempObj->setPageSize(Shop::SHOP_PRODUCTS_COUNT_AT_HOMEPAGE);
            $Prs = $productShopSrchTempObj->getResultSet();

            $rating = 0;
            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                $rating = SelProdRating::getSellerRating($shops['shop_user_id']);
            }

            if (true === MOBILE_APP_API_CALL) {
                $sponsoredShops[$i]['shopData'] = $shops;
                $sponsoredShops[$i]['shopData']['promotion_id'] = $shops['promotion_id'];
                $sponsoredShops[$i]['shopData']['rating'] = $rating;
                $sponsoredShops[$i]['shopData']['shop_logo'] = UrlHelper::generateFullUrl('image', 'shopLogo', array($shops['shop_id'], $langId));
                $sponsoredShops[$i]['shopData']['shop_banner'] = UrlHelper::generateFullUrl('image', 'shopBanner', array($shops['shop_id'], $langId));
                $sponsoredShops[$i]['products'] = $db->fetchAll($Prs);
            } else {
                $sponsoredShops['shops'][$shops['shop_id']]['shopData'] = $shops;
                $sponsoredShops['shops'][$shops['shop_id']]['shopData']['promotion_id'] = $shops['promotion_id'];

                $sponsoredShops['rating'][$shops['shop_id']] = $rating;
                $sponsoredShops['shops'][$shops['shop_id']]['products'] = $db->fetchAll($Prs);
            }
            /* ] */
            $i++;
        }
        return $sponsoredShops;
    }

    private function getSponsoredProductsObj($productSrchObj)
    {
        $langId = $this->siteLangId;
        $prodObj = new PromotionSearch($langId);
        $prodObj->joinProducts();
        $prodObj->joinShops();
        $prodObj->addPromotionTypeCondition(Promotion::TYPE_PRODUCT);
        $prodObj->joinActiveUser();
        $prodObj->setDefinedCriteria();
        $prodObj->addShopActiveExpiredCondition();
        $prodObj->joinUserWallet();
        $prodObj->joinBudget();
        $prodObj->addBudgetCondition();
        $prodObj->doNotCalculateRecords();
        $prodObj->addMultipleFields(array('selprod_id as proSelProdId', 'promotion_id', 'promotion_record_id'));

        $productSrchSponObj = clone $productSrchObj;
        $productSrchSponObj->joinTable('(' . $prodObj->getQuery() . ') ', 'INNER JOIN', 'selprod_id = ppr.proSelProdId ', 'ppr');
        $productSrchSponObj->addFld(array('promotion_id', 'promotion_record_id'));
        $productSrchSponObj->joinSellers();
        $productSrchSponObj->joinSellerSubscription($langId);
        $productSrchSponObj->addGroupBy('selprod_id');
        $productSrchSponObj->addOrder('', 'rand()');
        //$productSrchSponObj->addOrder('theprice', 'ASC');
        return $productSrchSponObj;
    }

    // For Home Page
    private function getSponsoredProducts($productSrchObj)
    {
        $productPageSize = (true === MOBILE_APP_API_CALL) ? 4 : FatApp::getConfig('CONF_PPC_PRODUCTS_HOME_PAGE', FatUtility::VAR_INT, 6);

        if (1 > $productPageSize) {
            return array();
        }

        $db = FatApp::getDb();
        $productSrchSponObj = $this->getSponsoredProductsObj($productSrchObj);
        if (true === MOBILE_APP_API_CALL) {
            $productSrchSponObj->joinProductRating();
            $productSrchSponObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
        }
        $productSrchSponObj->doNotCalculateRecords();
        $productSrchSponObj->setPageSize($productPageSize);
        $rs = $productSrchSponObj->getResultSet();
        return $db->fetchAll($rs);
    }

    // Used for APP
    public function getAllSponsoredProducts()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId(true);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $page = ($page < 2) ? 1 : $page;

        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $productSrchObj = $this->getProductSearchObj($loggedUserId);

        $productSrchSponObj = $this->getSponsoredProductsObj($productSrchObj);
        $productSrchSponObj->joinProductRating();
        $productSrchSponObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
        $productSrchSponObj->setPageNumber($page);
        $productSrchSponObj->setPageSize($pagesize);

        $rs = $productSrchSponObj->getResultSet();
        $sponsoredProds = FatApp::getDb()->fetchAll($rs);

        $this->set('sponsoredProds', $sponsoredProds);
        $this->set('page', $page);
        $this->set('pageCount', $productSrchSponObj->pages());
        $this->set('recordCount', $productSrchSponObj->recordCount());
        $this->set('postedData', FatApp::getPostedData());
        $this->_template->render();
    }

    public function getImage()
    {
        $post = FatApp::getPostedData();
        if (1 > count($post)) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $type = FatApp::getPostedData('type', null, '');
        if (empty($type)) {
            $message = Labels::getLabel('MSG_Type_is_mandatory', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $image_url = "";
        switch (strtoupper($type)) {
            case 'PRODUCT_PRIMARY':
                $product_id = FatApp::getPostedData('product_id', null, 0);
                $seller_product_id = FatApp::getPostedData('seller_product_id', null, 0);
                if (1 > $product_id || 1 > $seller_product_id) {
                    $message = Labels::getLabel('MSG_Product_id_&_Seller_product_id_is_mandatory.', $this->siteLangId);
                    FatUtility::dieJsonError($message);
                }
                $image_url = UrlHelper::generateFullUrl('image', 'product', array($product_id, "MEDIUM", $seller_product_id, 0, $this->siteLangId));
                break;
            case 'SLIDE':
                $slide_id = FatApp::getPostedData('slide_id', null, 0);
                if (1 > $slide_id) {
                    $message = Labels::getLabel('MSG_Slide_id_is_mandatory.', $this->siteLangId);
                    FatUtility::dieJsonError($message);
                }
                $image_url = UrlHelper::generateFullUrl('Image', 'slide', array($slide_id, 0, $this->siteLangId));
                break;
            case 'BANNER':
                $banner_id = FatApp::getPostedData('banner_id', null, 0);
                if (1 > $banner_id) {
                    $message = Labels::getLabel('MSG_Banner_id_is_mandatory.', $this->siteLangId);
                    FatUtility::dieJsonError($message);
                }
                $image_url = UrlHelper::generateFullUrl('Banner', 'HomePageAfterFirstLayout', array($banner_id, $this->siteLangId));
                break;
        }
        $this->set('image_url', $image_url);
        $this->_template->render();
    }

    public function countries()
    {
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        $arr_country = array();
        foreach ($countriesArr as $key => $val) {
            $arr_country[] = array("id" => $key, 'name' => $val);
        }
        $this->set('countries', $arr_country);
        $this->_template->render();
    }

    public function states($countryId)
    {
        $countryId = FatUtility::int($countryId);
        if (1 > $countryId) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $statesArr = $this->getStates($countryId, 0, true);
        $states = array();
        foreach ($statesArr as $key => $val) {
            $states[] = array("id" => $key, 'name' => $val);
        }
        $this->set('states', $states);
        $this->_template->render();
    }

    public function splashScreenData()
    {
        $langCode = Language::getAttributesById($this->siteLangId, 'language_code', false);

        $data = [
            'CONF_ENABLE_GEO_LOCATION' => FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)
        ];

        $data['languageLabels'] = [
            'language_code' => $langCode,
            'language_layout_direction' => Language::getLayoutDirection($this->siteLangId),
            'downloadUrl' => UrlHelper::generateFullUrl('Home', 'languageLabels', array(1, $this->siteLangId)),
            'langLabelUpdatedAt' => FatApp::getConfig('CONF_LANG_LABELS_UPDATED_AT', FatUtility::VAR_INT, time())
        ];

        $data['appThemeSetting'] = [
            'primaryThemeColor' => FatApp::getConfig('CONF_PRIMARY_APP_THEME_COLOR', FatUtility::VAR_STRING, ''),
            'primaryInverseThemeColor' => FatApp::getConfig('CONF_PRIMARY_INVERSE_APP_THEME_COLOR', FatUtility::VAR_STRING, ''),
            'secondaryThemeColor' => FatApp::getConfig('CONF_SECONDARY_APP_THEME_COLOR', FatUtility::VAR_STRING, ''),
            'secondaryInverseThemeColor' => FatApp::getConfig('CONF_SECONDARY_INVERSE_APP_THEME_COLOR', FatUtility::VAR_STRING, ''),
        ];

        $data['isWishlistEnable'] = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $data['canSendSms'] = SmsArchive::canSendSms() ? 1 : 0;
        $data['canAddReview'] = FatApp::getConfig('CONF_ALLOW_REVIEWS', FatUtility::VAR_INT, 1);
        $data['currency_id'] = $this->siteCurrencyId;

        $defultCountryId = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
        $data['defaultCountry'] = [
            'country_id' => $defultCountryId,
            'country_code' => Countries::getAttributesById($defultCountryId, 'country_code') ?? '',
        ];

        $this->set('data', $data);
        $this->_template->render();
    }

    public function getUrlSegmentsDetail()
    {
        $url = FatApp::getPostedData('url', FatUtility::VAR_STRING, '');
        if (empty($url)) {
            LibHelper::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $detail = CommonHelper::getUrlTypeData($url);
        $this->set('data', ['urlSegmentsDetail' => (object) $detail]);
        $this->_template->render();
    }

    public function getGeoAddress()
    {
        $address = new Address();
        $lat = FatApp::getPostedData('lat', FatUtility::VAR_STRING, '');
        $lng = FatApp::getPostedData('lng', FatUtility::VAR_STRING, '');

        $response = $address->getGeoData($lat, $lng);
        if (false === $response['status']) {
            FatUtility::dieJsonError($response['msg']);
        }
        FatUtility::dieJsonSuccess($response);
    }

    public function pwaManifest()
    {
        $manifestFile = CONF_UPLOADS_PATH . '/manifest-' . $this->siteLangId . '.json';
        if (!file_exists($manifestFile)) {
            $iconsArr = [36, 48, 57, 60, 70, 72, 76, 96, 114, 120, 144, 192, 152, 180, 150, 310, 512];
            $websiteName = FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->siteLangId, FatUtility::VAR_STRING, '');

            $srch = new MetaTagSearch($this->siteLangId);
            $cond = $srch->addCondition('meta_controller', '=', 'Home');
            $cond->attachCondition('meta_controller', '=', '', 'OR');

            $cond1 = $srch->addCondition('meta_action', '=', 'index');
            $cond1->attachCondition('meta_action', '=', '', 'OR');

            $srch->addOrder('meta_default', 'asc');
            $srch->doNotCalculateRecords();
            $srch->setPageSize(1);
            $srch->addMultipleFields(array(
                'meta_title',
                'meta_keywords', 'meta_description', 'meta_other_meta_tags'
            ));

            $rs = $srch->getResultSet();
            $metas = FatApp::getDb()->fetch($rs);
            $arr = array(
                "display" => "standalone",
                "name" => $websiteName,
                "short_name" => $websiteName,
                "description" => isset($metas['meta_description']) ? $metas['meta_description'] : $websiteName,
                "lang" => $this->siteLangCode,
                "start_url" => CONF_WEBROOT_URL,
                "display" => "standalone",
                "background_color" => isset($this->themeDetail[ThemeColor::TYPE_BODY]) ? '#' . $this->themeDetail[ThemeColor::TYPE_BODY] : '',
                "theme_color" => isset($this->themeDetail[ThemeColor::TYPE_BRAND]) ? '#' . $this->themeDetail[ThemeColor::TYPE_BRAND] : '',
            );

            foreach ($iconsArr as $key => $val) {
                $iconUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'appleTouchIcon', array($this->siteLangId, $val . '-' . $val)), CONF_IMG_CACHE_TIME, '.png');
                $icons = [
                    'src' => $iconUrl,
                    'sizes' => $val . 'x' . $val,
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ];
                $arr['icons'][] = $icons;
            }
            file_put_contents($manifestFile, FatUtility::convertToJson($arr, JSON_UNESCAPED_UNICODE));
        }
        echo file_get_contents($manifestFile);
        exit;
    }
}
