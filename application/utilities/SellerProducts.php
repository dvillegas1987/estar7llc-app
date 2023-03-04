<?php

trait SellerProducts
{
    protected function getSellerProductSearchForm($product_id = 0)
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox('', 'keyword', '', array('id' => 'keyword'));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'product_id', $product_id);
        $frm->addHiddenField('', 'page', 1);
        return $frm;
    }

    public function products($product_id = 0)
    {
        $this->userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId());
        $this->includeDateTimeFiles();
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }

        $product_id = FatUtility::int($product_id);
        $this->set('canEdit', $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId(), true));
        $this->set('frmSearch', $this->getSellerProductSearchForm($product_id));
        $this->set('product_id', $product_id);

        $srch = new ProductSearch($this->siteLangId, null, null, false, false);
        $srch->joinProductShippedBySeller($this->userParentId);
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'product_attrgrp_id = attrgrp_id', 'attrgrp');
        $srch->joinTable(UpcCode::DB_TBL, 'LEFT OUTER JOIN', 'upc_product_id = product_id', 'upc');
        $srch->addDirectCondition(
            '((CASE
                    WHEN product_seller_id = 0 THEN product_active = 1
                    WHEN product_seller_id > 0 THEN product_active IN (1, 0)
                    END ) )'
        );
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        $cnd = $srch->addCondition('product_seller_id', '=', 0);
        $cnd->attachCondition('product_added_by_admin_id', '=', applicationConstants::YES, 'AND');
        $srch->addGroupBy('product_id');
        $rs = $srch->getResultSet();
        $adminCatalogs = $srch->recordCount();
        $this->set('adminCatalogs', $adminCatalogs);
        $this->_template->render(true, true);
    }

    public function sellerProducts($product_id = 0)
    {
        $this->userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId());
        $product_id = FatUtility::int($product_id);
        if (0 < $product_id) {
            $row = Product::getAttributesById($product_id, array('product_id'));
            if (!$row) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
        }
        $keyword = FatApp::getPostedData('keyword');
        $userId = $this->userParentId;

        $srch = SellerProduct::searchSellerProducts($this->siteLangId, $userId, $keyword);
        $srch->addMultipleFields(
            array(
                'selprod_id', 'selprod_user_id', 'selprod_price', 'selprod_stock', 'selprod_track_inventory', 'selprod_threshold_stock_level', 'selprod_product_id', 'selprod_active', 'selprod_available_from', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title'
            )
        );
        if ($product_id) {
            $srch->addCondition('selprod_product_id', '=', $product_id);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        } else {
            $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
            $post = FatApp::getPostedData();
            $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : $post['page'];
            $page = (empty($page) || $page <= 0) ? 1 : $page;
            $page = FatUtility::int($page);

            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
        }
        $srch->addOrder('selprod_id', 'DESC');
        $db = FatApp::getDb();

        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);
        if (count($arrListing)) {
            foreach ($arrListing as &$arr) {
                $arr['options'] = SellerProduct::getSellerProductOptions($arr['selprod_id'], true, $this->siteLangId);
            }
        }

        $this->set("arrListing", $arrListing);
        $this->set('product_id', $product_id);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->siteLangId));
        $this->set('canEdit', $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId(), true));
        if (!$product_id) {
            $this->set('page', $page);
            $this->set('pageCount', $srch->pages());
            $this->set('pageSize', $pageSize);
            $this->set('postedData', $post);
            $this->set('recordCount', $srch->recordCount());
        }
        $this->_template->render(false, false);
    }

    public function sellerProductForm($product_id, $selprod_id = 0)
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());

        if (0 == $selprod_id && !Product::availableForAddToStore($product_id, $this->userParentId)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'catalog'));
        }

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        if (0 == $selprod_id && FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && SellerProduct::getActiveCount($this->userParentId) >= SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_inventory_allowed')) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        $selprod_id = FatUtility::int($selprod_id);
        $product_id = FatUtility::int($product_id);

        if (!$product_id) {
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $userId = $this->userParentId;
        $userObj = new User($userId);
        $vendorReturnAddress = $userObj->getUserReturnAddress($this->siteLangId);

        if (!$vendorReturnAddress) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_add_return_address', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('seller', 'shop', array(User::RETURN_ADDRESS_ACCOUNT_TAB)));
        }
        $languages = Language::getAllNames();
        $userObj = new User($userId);

        foreach ($languages as $langId => $langName) {
            $srch = new SearchBase(User::DB_TBL_USR_RETURN_ADDR_LANG);
            $srch->addCondition('uralang_user_id', '=', $userId);
            $srch->addCondition('uralang_lang_id', '=', $langId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $vendorReturnAddress = FatApp::getDb()->fetch($rs);
            if (!$vendorReturnAddress) {
                Message::addErrorMessage(Labels::getLabel('MSG_Please_add_return_address_before_adding/updating_product', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('seller', 'shop', array(User::RETURN_ADDRESS_ACCOUNT_TAB, $langId)));
            }
        }

        $productRow = Product::getProductDataById($this->siteLangId, $product_id, array('product_type'));

        if (!$productRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        if (!UserPrivilege::canSellerAddProductInCatalog($product_id, $userId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Products'));
        }

        /* $this->_template->addJs(array('js/jquery.datetimepicker.js'), false); */
        $this->set('product_type', $productRow['product_type']);
        $this->set('product_id', $product_id);
        $this->set('selprod_id', $selprod_id);
        $this->set('language', Language::getAllNames());
        $this->_template->render(true, true);
    }

    public function sellerProductGeneralForm($product_id, $selprod_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            LibHelper::exitWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (0 == $selprod_id && FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && SellerProduct::getActiveCount($this->userParentId) >= SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_inventory_allowed')) {
            LibHelper::exitWithError(Labels::getLabel('MSG_You_have_crossed_your_package_limit', $this->siteLangId), false, true);
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            LibHelper::exitWithError(Labels::getLabel('MSG_Please_buy_subscription', $this->siteLangId), false, true);
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        if ($selprod_id == 0 && !UserPrivilege::canSellerAddProductInCatalog($product_id, $this->userParentId)) {
            LibHelper::exitWithError(Labels::getLabel('LBL_Please_Upgrade_your_package_to_add_new_products', $this->siteLangId), false);
        }

        $productRow = Product::getProductDataById($this->siteLangId, $product_id, array('IFNULL(product_name, product_identifier) as product_name', 'product_active', 'product_seller_id', 'product_added_by_admin_id', 'product_cod_enabled', 'product_type', 'product_approved', 'product_min_selling_price'));

        if (!$productRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($productRow['product_active'] != applicationConstants::ACTIVE) {
            LibHelper::exitWithError(Labels::getLabel('MSG_Catalog_is_no_more_active', $this->siteLangId), false);
        }

        if ($productRow['product_approved'] != applicationConstants::YES) {
            LibHelper::exitWithError(Labels::getLabel('MSG_Catalog_is_not_yet_approved', $this->siteLangId), false);
        }

        if (($productRow['product_seller_id'] != $this->userParentId) && $productRow['product_added_by_admin_id'] == 0) {
            LibHelper::exitWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId), false);
        }
        $productLangRow = Product::getProductDataById(CommonHelper::getLangId(), $product_id, array('product_identifier'));

        $frmSellerProduct = $this->getSellerProductForm($product_id, $selprod_id);

        $sellerProductRow = [];
        if ($selprod_id) {
            $sellerProductRow = SellerProduct::getAttributesById($selprod_id, null, true, true);
            if (!$sellerProductRow) {
                LibHelper::exitWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId), false);
            }

            if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
                LibHelper::exitWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId), false);
            }
            $urlRewriteData = UrlRewrite::getAttributesById($sellerProductRow['selprod_urlrewrite_id']);
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', 'products/view/' . $selprod_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);

            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }

            $customUrl = explode("/", $urlRow['urlrewrite_custom']);
            $sellerProductRow['selprod_url_keyword'] = $customUrl[0];
        } else {
            $sellerProductRow['selprod_available_from'] = date('Y-m-d');
            $sellerProductRow['selprod_cod_enabled'] = $productRow['product_cod_enabled'];
            $sellerProductRow['selprod_url_keyword'] = strtolower(CommonHelper::createSlug($productLangRow['product_identifier']));
        }

        $productWarranty = Product::getAttributesById($product_id, 'product_warranty', true);
        $sellerProductRow['product_warranty'] = FatUtility::int($productWarranty);

        $returnAge = isset($sellerProductRow['selprod_return_age']) ? FatUtility::int($sellerProductRow['selprod_return_age']) : '';
        $cancellationAge = isset($sellerProductRow['selprod_cancellation_age']) ? FatUtility::int($sellerProductRow['selprod_cancellation_age']) : '';

        if ('' === $returnAge || '' === $cancellationAge) {
            $sellerProductRow['use_shop_policy'] = 1;
        }

        if ($selprod_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                $langData = SellerProduct::getAttributesByLangId($langId, $selprod_id);
                $sellerProductRow['selprod_title' . $langId] = '';
                $sellerProductRow['selprod_comments' . $langId] = '';
                if (!empty($langData)) {
                    $sellerProductRow['selprod_title' . $langId] = $langData['selprod_title'];
                    $sellerProductRow['selprod_comments' . $langId] = $langData['selprod_comments'];
                }
            }
        } else {
            $sellerProductRow['selprod_title' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)] = $productRow['product_name'];
        }

        $frmSellerProduct->fill($sellerProductRow);
        $shippedBySeller = 0;
        if (Product::isProductShippedBySeller($product_id, $productRow['product_seller_id'], $this->userParentId)) {
            $shippedBySeller = 1;
        }
        $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        $availableOptions = array();
        foreach ($optionCombinations as $optionKey => $optionValue) {
            /* Check if product already added for this option [ */
            $selProdCode = $product_id . '_' . $optionKey;
            $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId);
            if (!empty($selProdAvailable) && !$selProdAvailable['selprod_deleted']) {
                continue;
            }
            $availableOptions[$optionKey] = $optionValue;
            /* ] */
        }
        $optionValues = array();
        if (isset($sellerProductRow['selprodoption_optionvalue_id'])) {
            foreach ($sellerProductRow['selprodoption_optionvalue_id'] as $opId => $op) {
                $optionValue = new OptionValue($op[$opId]);
                $option = $optionValue->getOptionValue($opId);
                $optionValues[] = $option['optionvalue_name' . $this->siteLangId];
            }
        }

        //$shipBySeller = SellerProduct::prodShipByseller($product_id);
        $shipBySeller = Product::isProductShippedBySeller($product_id, $productRow['product_seller_id'], UserAuthentication::getLoggedUserId());

        $this->set('shipBySeller', $shipBySeller);
        $this->set('optionValues', $optionValues);
        $this->set('availableOptions', $availableOptions);
        $this->set('productOptions', $productOptions);
        /* $this->_template->addJs(array('js/jquery.datetimepicker.js'), false); */
        $this->set('customActiveTab', 'GENERAL');
        $this->set('frmSellerProduct', $frmSellerProduct);
        $this->set('product_id', $product_id);
        $this->set('selprod_id', $selprod_id);
        $this->set('product_type', $productRow['product_type']);
        $this->set('shippedBySeller', $shippedBySeller);
        $this->set('productMinSellingPrice', $productRow['product_min_selling_price']);
        $this->set('language', Language::getAllNames());
        $this->set('activeTab', 'GENERAL');
        $this->_template->render(false, false);
    }

    public function validatePostedData($post)
    {
        $selprod_id = FatUtility::int($post['selprod_id']);
        $selprod_product_id = FatUtility::int($post['selprod_product_id']);

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!$selprod_product_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productRow = Product::getAttributesById($selprod_product_id, array('product_id', 'product_active', 'product_seller_id', 'product_added_by_admin_id'));
        if (!$productRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (($productRow['product_seller_id'] != $this->userParentId) && $productRow['product_added_by_admin_id'] == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $selProdCode = $productRow['product_id'] . '_';
        $post['selprod_code'] = $selProdCode;

        /* Validate product belongs to current logged seller[ */
        if ($selprod_id) {
            $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id'));
            if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */
        $post['selprod_url_keyword'] = strtolower(CommonHelper::createSlug($post['selprod_url_keyword']));

        if (isset($post['selprod_track_inventory']) && $post['selprod_track_inventory'] == Product::INVENTORY_NOT_TRACK) {
            $post['selprod_threshold_stock_level'] = 0;
        }

        if (!$selprod_id) {
            $post['selprod_user_id'] = $this->userParentId;
            $post['selprod_added_on'] = date("Y-m-d H:i:s");
        }
        return $post;
    }

    public function setUpSellerProduct()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $post = $this->validatePostedData(FatApp::getPostedData());
        $selprod_id = FatUtility::int($post['selprod_id']);

        if ($selprod_id > 0) {
            unset($post['selprod_code']);
        }

        $srch = new SearchBase(SellerProductSpecialPrice::DB_TBL);
        $srch->addCondition('splprice_selprod_id', '=', $selprod_id);
        $srch->addCondition('splprice_price', '>=', $post['selprod_price']);
        $srch->addCondition('splprice_end_date', '>=', date('Y-m-d H:i:s'));
        $srch->addFld('splprice_price');
        $srch->addOrder('splprice_price', 'DESC');
        $srch->doNotCalculateRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $result = $db->fetch($rs);
        if (is_array($result) && !empty($result)) {
            $price =  CommonHelper::displayMoneyFormat($result['splprice_price']);
            $msg = Labels::getLabel('MSG_SELLING_PRICE_MUST_BE_GREATER_THAN_SPECIAL_PRICE_{SPECIAL-PRICE}', $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{SPECIAL-PRICE}' => $price]);
            Message::addErrorMessage($msg);
            FatUtility::dieWithError(Message::getHtml());
        }

        $post['selprod_subtract_stock'] = FatApp::getPostedData('selprod_subtract_stock', FatUtility::VAR_INT, 0);
        $post['selprod_track_inventory'] = FatApp::getPostedData('selprod_track_inventory', FatUtility::VAR_INT, 0);
        // CommonHelper::printArray($post, true);
        $data_to_be_save = $post;
        $sellerProdObj = new SellerProduct($selprod_id);
        $sellerProdObj->assignValues($data_to_be_save);
        if (!$sellerProdObj->save()) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $selprod_id = $sellerProdObj->getMainTableRecordId();

        $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
        if (0 < $useShopPolicy) {
            if (!$selProdSpecificsObj->deleteRecord()) {
                FatUtility::dieJsonError($selProdSpecificsObj->getError());
            }
        } else {
            $post['sps_selprod_id'] = $selprod_id;
            $selProdSpecificsObj->assignValues($post);
            $data = $selProdSpecificsObj->getFlds();
            if (!$selProdSpecificsObj->addNew(array(), $data)) {
                FatUtility::dieJsonError($selProdSpecificsObj->getError());
            }
        }

        $sellerProdObj->rewriteUrlProduct($post['selprod_url_keyword']);
        $sellerProdObj->rewriteUrlReviews($post['selprod_url_keyword']);
        $sellerProdObj->rewriteUrlMoreSellers($post['selprod_url_keyword']);

        /* Add Meta data automatically[ */
        if (0 == FatApp::getPostedData('selprod_id', Fatutility::VAR_INT, 0)) {
            if (!$sellerProdObj->saveMetaData()) {
                Message::addErrorMessage($sellerProdObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ]*/

        /* Update seller product language data[ */
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!empty($post['selprod_title' . $langId])) {
                $selProdData = array(
                    'selprodlang_selprod_id' => $selprod_id,
                    'selprodlang_lang_id' => $langId,
                    'selprod_title' => $post['selprod_title' . $langId],
                    'selprod_comments' => $post['selprod_comments' . $langId],
                );

                if (!$sellerProdObj->updateLangData($langId, $selProdData)) {
                    Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }
        /* ] */

        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);
        Product::updateMinPrices($productId);
        $this->set('selprod_id', $selprod_id);
        $this->set('product_id', $productId);
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function deleteSelProdWithoutOptions($productId, $userId)
    {
        $productId = FatUtility::int($productId);
        $userId = FatUtility::int($userId);
        if (!$productId || !$userId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', CommonHelper::getLangId()));
        }
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(array('selprod_id', 'selprodoption_optionvalue_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (empty($row)) {
            return true;
        }
        if (empty($row['selprodoption_optionvalue_id'])) {
            $this->deleteSellerProduct($row['selprod_id']);
        }
    }

    public function setUpMultipleSellerProducts()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $post = $this->validatePostedData(FatApp::getPostedData());
        $productOptions = Product::getProductOptions($post['selprod_product_id'], $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        if (empty($productOptions) || empty($optionCombinations)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $productId = FatApp::getPostedData('selprod_product_id', FatUtility::VAR_INT, 0);
        unset($post['selprod_id']);
        $data_to_be_save = $post;
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
        $this->deleteSelProdWithoutOptions($productId, $this->userParentId);
        $error = false;
        $selprod_id = 0;

        $prodAllowedLimit = -1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $prodAllowedLimit = SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_inventory_allowed') - SellerProduct::getActiveCount($this->userParentId);
            if (0 > $prodAllowedLimit) {
                $prodAllowedLimit = 0;
            }
        }
        $minSellingPrice = Product::getAttributesById($productId, 'product_min_selling_price');
        $productCount = 0;
        foreach ($optionCombinations as $optionKey => $optionValue) {
            /* Check if product already added for this option [ */
            $selProdCode = $post['selprod_code'] . $optionKey;
            $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId);
            if (!empty($selProdAvailable)) {
                if (!$selProdAvailable['selprod_deleted']) {
                    /*$error = true;
                    Message::addErrorMessage($optionValue . ' ' . Labels::getLabel('MSG_ALREADY_ADDED', $this->siteLangId));*/
                    continue;
                }
                $data_to_be_save['selprod_deleted'] = applicationConstants::NO;
            }
            if (!isset($post['selprod_cost' . $optionKey]) || !isset($post['selprod_price' . $optionKey]) || !isset($post['selprod_stock' . $optionKey])) {
                continue;
            }
            $sellingPrice = $post['selprod_price' . $optionKey];
            if ($minSellingPrice > $sellingPrice) {
                $error = true;
                $msg = Labels::getLabel('MSG_FOR_{VARIANT}_VARIANT_YOU_CANNOT_ADD_LESS_THEN_MINIMUM_SELLING_PRICE_{MINIMUM-SELLING-PRICE}', $this->siteLangId);
                $msg = CommonHelper::replaceStringData($msg, ['{VARIANT}' => $optionValue, '{MINIMUM-SELLING-PRICE}' => $minSellingPrice]);
                Message::addErrorMessage($msg);
                continue;
            }

            $data_to_be_save['selprod_code'] = $selProdCode;
            $data_to_be_save['selprod_cost'] = $post['selprod_cost' . $optionKey];
            $data_to_be_save['selprod_price'] = $sellingPrice;
            $data_to_be_save['selprod_stock'] = $post['selprod_stock' . $optionKey];
            $data_to_be_save['selprod_sku'] = $post['selprod_sku' . $optionKey] ?? '';

            if (-1 < $prodAllowedLimit && $prodAllowedLimit <= $productCount) {
                $data_to_be_save['selprod_active'] = applicationConstants::INACTIVE;
            }

            $sellerProdObj = new SellerProduct();
            $sellerProdObj->assignValues($data_to_be_save);
            if (!$sellerProdObj->save()) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $selprod_id = $sellerProdObj->getMainTableRecordId();

            $productCount++;

            /* save options data, if any [ */
            $options = array();
            $optionValues = explode("_", $optionKey);
            foreach ($optionValues as $optionValueId) {
                $optionId = OptionValue::getAttributesById($optionValueId, 'optionvalue_option_id', false);
                $options[$optionId] = $optionValueId;
            }
            asort($options);
            if (!$sellerProdObj->addUpdateSellerProductOptions($selprod_id, $options)) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ] */

            $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
            if (0 < $useShopPolicy) {
                if (!$selProdSpecificsObj->deleteRecord()) {
                    Message::addErrorMessage(Labels::getLabel($selProdSpecificsObj->getError(), $this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
            } else {
                $post['sps_selprod_id'] = $selprod_id;
                $selProdSpecificsObj->assignValues($post);
                $data = $selProdSpecificsObj->getFlds();
                if (!$selProdSpecificsObj->addNew(array(), $data)) {
                    Message::addErrorMessage(Labels::getLabel($selProdSpecificsObj->getError(), $this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
            }

            $sellerProdObj->rewriteUrlProduct($post['selprod_url_keyword']);
            $sellerProdObj->rewriteUrlReviews($post['selprod_url_keyword']);
            $sellerProdObj->rewriteUrlMoreSellers($post['selprod_url_keyword']);

            /* Add Meta data automatically[ */
            if (!$sellerProdObj->saveMetaData()) {
                Message::addErrorMessage($sellerProdObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ] */

            /* Update seller product language data[ */
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (empty($post['selprod_title' . $langId])) {
                    continue;
                }
                $selProdData = array(
                    'selprodlang_selprod_id' => $selprod_id,
                    'selprodlang_lang_id' => $langId,
                    'selprod_title' => $post['selprod_title' . $langId],
                    'selprod_comments' => isset($post['selprod_comments' . $langId]) ? $post['selprod_comments' . $langId] : '',
                );

                if (!$sellerProdObj->updateLangData($langId, $selProdData)) {
                    Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
            /* ] */

            // $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);
        }
        Product::updateMinPrices($productId);

        if ($error) {
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('product_id', $productId);
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function checkSellProdAvailableForUser()
    {
        $post = FatApp::getPostedData();
        $selprod_id = Fatutility::int($post['selprod_id']);
        $selprod_product_id = Fatutility::int($post['selprod_product_id']);

        $productRow = Product::getAttributesById($selprod_product_id, array('product_id', 'product_active', 'product_seller_id', 'product_added_by_admin_id'));
        if (!$productRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (($productRow['product_seller_id'] != $this->userParentId) && $productRow['product_added_by_admin_id'] == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $options = array();
        if (isset($post['selprodoption_optionvalue_id']) && count($post['selprodoption_optionvalue_id'])) {
            $options = $post['selprodoption_optionvalue_id'];
            unset($post['selprodoption_optionvalue_id']);
        }
        asort($options);
        $selProdCode = $productRow['product_id'] . '_' . implode('_', $options);

        $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId, $selprod_id);

        if (!empty($selProdAvailable) && !$selProdAvailable['selprod_deleted']) {
            Message::addErrorMessage(Labels::getLabel("LBL_Inventory_for_this_option_have_been_added", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        } else {
            FatUtility::dieJsonSuccess(Message::getHtml());
        }
    }

    private function getSellerProductLangForm($formLangId, $selprod_id = 0)
    {
        $formLangId = FatUtility::int($formLangId);

        $frm = new Form('frmSellerProductLang');
        /* $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $formLangId), 'lang_id', Language::getAllNames(), $formLangId, array(), ''); */
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Display_Title', $formLangId), 'selprod_title');
        $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $formLangId), 'selprod_comments');
        $frm->addHiddenField('', 'lang_id', $formLangId);
        $frm->addHiddenField('', 'selprod_product_id');
        $frm->addHiddenField('', 'selprod_id', $selprod_id);

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $formLangId == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $formLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $formLangId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $formLangId), array('onClick' => 'cancelForm(this)'));
        $fld1->attachField($fld2);
        return $frm;
    }

    public function sellerProductLangForm($langId, $selprod_id, $autoFillLangData = 0)
    {
        $langId = FatUtility::int($langId);
        $selprod_id = FatUtility::int($selprod_id);

        if ($langId == 0 || $selprod_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        if (!$sellerProductRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $frmSellerProdLangFrm = $this->getSellerProductLangForm($langId, $selprod_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(SellerProduct::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($selprod_id, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = SellerProduct::getAttributesByLangId($langId, $selprod_id);
        }
        $langData['selprod_product_id'] = $sellerProductRow['selprod_product_id'];

        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));
        /* $langData['selprod_title'] = array_key_exists('selprod_title', $langData) ? $langData['selprod_title'] : SellerProduct::getProductDisplayTitle($selprod_id, $langId); */
        if ($langData) {
            $frmSellerProdLangFrm->fill($langData);
        }
        $this->set('customActiveTab', '');
        $this->set('frmSellerProdLangFrm', $frmSellerProdLangFrm);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('selprod_id', $selprod_id);
        $this->set('formLangId', $langId);
        $this->set('product_type', $productRow['product_type']);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('language', Language::getAllNames());
        $this->set('activeTab', 'GENERAL');
        $this->_template->render(false, false);
    }

    public function setUpSellerProductLang()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $selprod_id = Fatutility::int($post['selprod_id']);
        $lang_id = Fatutility::int($post['lang_id']);
        $selprod_product_id = Fatutility::int($post['selprod_product_id']);

        if ($selprod_id == 0 || $selprod_product_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $frm = $this->getSellerProductLangForm($lang_id, $selprod_id);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id'));
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $data = array(
            'selprodlang_selprod_id' => $selprod_id,
            'selprodlang_lang_id' => $lang_id,
            'selprod_title' => $post['selprod_title'],
            /* 'selprod_warranty' => $post['selprod_warranty'],
        'selprod_return_policy' => $post['selprod_return_policy'], */
            'selprod_comments' => $post['selprod_comments'],
        );

        $obj = new SellerProduct($selprod_id);
        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(SellerProduct::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($selprod_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        if ($selprod_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if ($langId > $lang_id) {
                    $newTabLangId = $langId;
                    break;
                }
                /*if (!$row = SellerProduct::getAttributesByLangId($langId, $selprod_id)) {
                    $newTabLangId = $langId;
                    break;
                }*/
            }
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('product_id', $selprod_product_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productTaxRates($selprod_id)
    {
        $selprod_id = Fatutility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $taxRates[] = $this->getTaxRates($sellerProductRow['selprod_product_id'], $this->userParentId);

        $this->set('arrListing', $taxRates);
        $this->set('activeTab', 'TAX');
        $this->set('userId', $this->userParentId);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);

        $this->_template->render(false, false);
    }

    private function getTaxRates($productId, $userId)
    {
        $productId = Fatutility::int($productId);
        $userId = Fatutility::int($userId);

        $taxRates = array();
        $taxObj = Tax::getTaxCatObjByProductId($productId, $this->siteLangId);
        $taxObj->addMultipleFields(array('IFNULL(taxcat_name,taxcat_identifier) as taxcat_name', 'ptt_seller_user_id', 'ptt_taxcat_id', 'ptt_product_id'));
        $taxObj->doNotCalculateRecords();

        $cnd = $taxObj->addCondition('ptt_seller_user_id', '=', 0);
        $cnd->attachCondition('ptt_seller_user_id', '=', $userId, 'OR');

        $taxObj->setPageSize(1);
        $taxObj->addOrder('ptt_seller_user_id', 'DESC');

        $rs = $taxObj->getResultSet();
        $taxRates = FatApp::getDb()->fetch($rs);

        return $taxRates ? $taxRates : array();
    }

    private function changeTaxCategoryForm($langId)
    {
        $frm = new Form('frmTaxRate');
        $frm->addHiddenField('', 'selprod_id');
        $taxCatArr = Tax::getSaleTaxCatArr($langId);

        $frm->addSelectBox(Labels::getLabel('LBL_Tax_Category', $langId), 'ptt_taxcat_id', $taxCatArr, '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired(true);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function changeTaxCategory($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        /* $srch = Tax::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array('taxcat_id','IFNULL(taxcat_name,taxcat_identifier) as taxcat_name'));
        $rs =  $srch->getResultSet();
        if($rs){
        $records = FatApp::getDb()->fetchAll($rs,'taxcat_id');
        }
        var_dump($records); */
        $taxRates = $this->getTaxRates($sellerProductRow['selprod_product_id'], $this->userParentId);
        $frm = $this->changeTaxCategoryForm($this->siteLangId);

        $frm->fill($taxRates + array('selprod_id' => $sellerProductRow['selprod_id']));

        $this->set('frm', $frm);
        $this->set('userId', $this->userParentId);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->_template->render(false, false);
    }

    public function setUpTaxCategory()
    {
        $this->userPrivilege->canEditTaxCategory(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!$selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $data = array(
            'ptt_product_id' => $sellerProductRow['selprod_product_id'],
            'ptt_taxcat_id' => $post['ptt_taxcat_id'],
            'ptt_seller_user_id' => $this->userParentId
        );
        /* CommonHelper::printArray($data); die; */
        $obj = new Tax();
        if (!$obj->addUpdateProductTaxCat($data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('MSG_SETUP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function resetTaxRates($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        if (!FatApp::getDb()->deleteRecords(Tax::DB_TBL_PRODUCT_TO_TAX, array('smt' => 'ptt_product_id = ? and ptt_seller_user_id = ?', 'vals' => array($sellerProductRow['selprod_product_id'], $this->userParentId)))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('MSG_Reset_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSellerProductSpecialPriceForm()
    {
        $frm = new Form('frmSellerProductSpecialPrice');
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Special_Price', $this->siteLangId) . CommonHelper::concatCurrencySymbolWithAmtLbl(), 'splprice_price');
        $fld->requirements()->setPositive();
        $fld = $frm->addDateField(Labels::getLabel('LBL_Price_Start_Date', $this->siteLangId), 'splprice_start_date', '', array('readonly' => 'readonly'));
        $fld->requirements()->setRequired();

        $fld = $frm->addDateField(Labels::getLabel('LBL_Price_End_Date', $this->siteLangId), 'splprice_end_date', '', array('readonly' => 'readonly'));
        $fld->requirements()->setRequired();
        $fld->requirements()->setCompareWith('splprice_start_date', 'ge', Labels::getLabel('LBL_Price_Start_Date', $this->siteLangId));

        $frm->addHiddenField('', 'splprice_selprod_id');
        $frm->addHiddenField('', 'splprice_id');

        /* $str = "<span id='special-price-discounted-string'>".Labels::getLabel("LBL_[Save_nn_(XX%_Off)]", $this->siteLangId)."</span>";
        $frm->addHtml( '', 'discountHtmlHeading', Labels::getLabel('LBL_Optional_Discount_Fields', $this->siteLangId)." ". Labels::getLabel("LBL_Below_String_will_appear_as:", $this->siteLangId) .'<br/>'.$str );
        $fld = $frm->addTextBox( Labels::getLabel( 'LBL_Save' ,$this->siteLangId), 'splprice_display_list_price' );
        $fld->requirements()->setFloat();
        $fld->addFieldTagAttribute( 'onChange', 'updateDiscountString()');
        $fld = $frm->addTextBox( Labels::getLabel( 'LBL_Amount' ,$this->siteLangId), 'splprice_display_dis_val' );
        $fld->requirements()->setFloat();
        $fld->addFieldTagAttribute( 'onChange', 'updateDiscountString()');
        $fld = $frm->addSelectBox( Labels::getLabel('LBL_Discount_Type', $this->siteLangId), 'splprice_display_dis_type', applicationConstants::getPercentageFlatArr($this->siteLangId), '', array() );
        $fld->addFieldTagAttribute( 'onChange', 'updateDiscountString()');
        */
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->siteLangId), array('onClick' => 'javascript:$("#sellerProductsForm").html(\'\')'));
        $fld1->attachField($fld2);
        return $frm;
    }

    public function sellerProductSpecialPrices($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }


        $arrListing = SellerProduct::getSellerProductSpecialPrices($selprod_id);
        $this->set('arrListing', $arrListing);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('product_type', $productRow['product_type']);
        $this->set('activeTab', 'SPECIAL_PRICE');
        $this->_template->render(false, false);
    }

    public function sellerProductSpecialPriceForm($selprod_id, $splprice_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $splprice_id = FatUtility::int($splprice_id);
        if (!$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $frmSellerProductSpecialPrice = $this->getSellerProductSpecialPriceForm();
        $specialPriceRow = array();
        if ($splprice_id) {
            $tblRecord = new TableRecord(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE);
            if (!$tblRecord->loadFromDb(array('smt' => 'splprice_id = ?', 'vals' => array($splprice_id)))) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
                FatApp::redirectUser($_SESSION['referer_page_url']);
            }
            $specialPriceRow = $tblRecord->getFlds();
        }

        $specialPriceRow['splprice_selprod_id'] = $selprod_id;
        $frmSellerProductSpecialPrice->fill($specialPriceRow);

        $this->set('frmSellerProductSpecialPrice', $frmSellerProductSpecialPrice);
        $this->set('selprod_id', $selprod_id);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('activeTab', 'SPECIAL_PRICE');
        $this->_template->render(false, false);
    }

    public function setUpSellerProductSpecialPrice()
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['splprice_selprod_id']);
        $splprice_id = FatUtility::int($post['splprice_id']);

        if (!$selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->joinSellerProducts();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addMultipleFields(array('product_min_selling_price', 'selprod_price', 'selprod_user_id'));
        $prodSrch->setPageSize(1);
        $rs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($rs);

        if ($post['splprice_price'] < $product['product_min_selling_price'] || $post['splprice_price'] >= $product['selprod_price']) {
            $str = Labels::getLabel('MSG_Price_must_between_min_selling_price_{minsellingprice}_and_selling_price_{sellingprice}', $this->siteLangId);
            $minSellingPrice = CommonHelper::displayMoneyFormat($product['product_min_selling_price'], false, true, true);
            $sellingPrice = CommonHelper::displayMoneyFormat($product['selprod_price'], false, true, true);

            $message = CommonHelper::replaceStringData($str, array('{minsellingprice}' => $minSellingPrice, '{sellingprice}' => $sellingPrice));
            FatUtility::dieJsonError($message);
        }

        if ($product['selprod_user_id'] != $this->userParentId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $frm = $this->getSellerProductSpecialPriceForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }

        /* Check if same date already exists [ */
        $tblRecord = new TableRecord(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE);
        if ($tblRecord->loadFromDb(array('smt' => '(splprice_selprod_id = ?) AND ((splprice_start_date between ? AND ?) OR (splprice_end_date between ? AND ?) )', 'vals' => array($selprod_id, $post['splprice_start_date'], $post['splprice_end_date'], $post['splprice_start_date'], $post['splprice_end_date'])))) {
            $specialPriceRow = $tblRecord->getFlds();
            if ($specialPriceRow['splprice_id'] != $post['splprice_id']) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Special_price_for_this_date_already_added', $this->siteLangId));
            }
        }
        /* ] */

        $data_to_save = array(
            'splprice_id' => $splprice_id,
            'splprice_selprod_id' => $selprod_id,
            'splprice_start_date' => $post['splprice_start_date'],
            'splprice_end_date' => $post['splprice_end_date'],
            'splprice_price' => $post['splprice_price'],
            /* 'splprice_display_dis_type' =>    $post['splprice_display_dis_type'],
        'splprice_display_dis_val' =>    $post['splprice_display_dis_val'],
        'splprice_display_list_price' =>$post['splprice_display_list_price'], */
        );
        $sellerProdObj = new SellerProduct();
        if (!$sellerProdObj->addUpdateSellerProductSpecialPrice($data_to_save)) {
            FatUtility::dieJsonError(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
        }
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);
        Product::updateMinPrices($productId);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSellerProductSpecialPrice()
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
        $this->removeSpecialPrice($splPriceId, $specialPriceRow);
        $productId = SellerProduct::getAttributesById($specialPriceRow['selprod_id'], 'selprod_product_id', false);
        Product::updateMinPrices($productId);
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeSpecialPriceArr()
    {
        $splpriceIdArr = FatApp::getPostedData('selprod_ids');
        $splpriceIds = FatUtility::int($splpriceIdArr);
        foreach ($splpriceIds as $splPriceId => $selProdId) {
            $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
            $this->removeSpecialPrice($splPriceId, $specialPriceRow);
        }
        Product::updateMinPrices();
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function removeSpecialPrice($splPriceId, $specialPriceRow)
    {
        if ($specialPriceRow['selprod_user_id'] != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $sellerProdObj = new SellerProduct($specialPriceRow['selprod_id']);
        if (!$sellerProdObj->deleteSellerProductSpecialPrice($splPriceId, $specialPriceRow['selprod_id'])) {
            FatUtility::dieWithError(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
        }
        return true;
    }

    /* Seller Product Volume Discount [ */

    public function sellerProductVolumeDiscounts($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id', 'selprod_id', 'selprod_product_id'));

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        $srch = new SellerProductVolumeDiscountSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('voldiscount_selprod_id', '=', $selprod_id);
        $rs = $srch->getResultSet();

        $arrListing = FatApp::getDb()->fetchAll($rs);
        $this->set('arrListing', $arrListing);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('product_type', $productRow['product_type']);
        $this->set('activeTab', 'VOLUME_DISCOUNT');


        $productLangRow = Product::getAttributesByLangId($this->siteLangId, $sellerProductRow['selprod_product_id'], array('product_name'));
        $this->set('productCatalogName', $productLangRow['product_name']);

        $this->_template->render(false, false);
    }

    public function sellerProductVolumeDiscountForm($selprod_id, $voldiscount_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $voldiscount_id = FatUtility::int($voldiscount_id);
        if ($selprod_id <= 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_id', 'selprod_user_id', 'selprod_product_id'));
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId || $selprod_id != $sellerProductRow['selprod_id']) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $frmSellerProductVolDiscount = $this->getSellerProductVolumeDiscountForm($this->siteLangId);
        $volumeDiscountRow = array();
        if ($voldiscount_id) {
            $volumeDiscountRow = SellerProductVolumeDiscount::getAttributesById($voldiscount_id);
            if (!$volumeDiscountRow) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
        }
        $volumeDiscountRow['voldiscount_selprod_id'] = $sellerProductRow['selprod_id'];
        $frmSellerProductVolDiscount->fill($volumeDiscountRow);
        $this->set('frmSellerProductVolDiscount', $frmSellerProductVolDiscount);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('activeTab', 'VOLUME_DISCOUNT');
        $this->_template->render(false, false);
    }

    public function setUpSellerProductVolumeDiscount()
    {
        $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId());
        $selprod_id = FatApp::getPostedData('voldiscount_selprod_id', FatUtility::VAR_INT, 0);
        if (!$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $voldiscount_id = FatApp::getPostedData('voldiscount_id', FatUtility::VAR_INT, 0);

        $frm = $this->getSellerProductVolumeDiscountForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()), $this->siteLangId);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->updateSelProdVolDiscount($selprod_id, $voldiscount_id, $post['voldiscount_min_qty'], $post['voldiscount_percentage']);

        $this->set('msg', Labels::getLabel('LBL_Volume_Discount_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSelProdVolDiscount($selprod_id, $voldiscount_id, $minQty, $perc)
    {
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id', 'selprod_stock', 'selprod_min_order_qty'), false);

        if ($minQty > $sellerProductRow['selprod_stock']) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Quantity_cannot_be_more_than_the_Stock_of_the_Product', $this->siteLangId));
        }

        if ($minQty < $sellerProductRow['selprod_min_order_qty']) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Quantity_cannot_be_less_than_the_Minimum_Order_Quantity', $this->siteLangId) . ': ' . $sellerProductRow['selprod_min_order_qty']);
        }

        if ($perc > 100 || 1 > $perc) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Percentage', $this->siteLangId));
        }

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        /* Check if volume discount for same quantity already exists [ */
        $tblRecord = new TableRecord(SellerProductVolumeDiscount::DB_TBL);
        if ($tblRecord->loadFromDb(array('smt' => 'voldiscount_selprod_id = ? AND voldiscount_min_qty = ?', 'vals' => array($selprod_id, $minQty)))) {
            $volDiscountRow = $tblRecord->getFlds();
            if ($volDiscountRow['voldiscount_id'] != $voldiscount_id) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Volume_discount_for_this_quantity_already_added', $this->siteLangId));
            }
        }
        /* ] */

        $data_to_save = array(
            'voldiscount_selprod_id' => $selprod_id,
            'voldiscount_min_qty' => $minQty,
            'voldiscount_percentage' => $perc
        );

        if (0 < $voldiscount_id) {
            $data_to_save['voldiscount_id'] = $voldiscount_id;
        }

        // Return Volume Discount ID if $return(Second Param) is true else it will return bool value.
        $voldiscount_id = SellerProductVolumeDiscount::updateData($data_to_save, true);
        if (1 > $voldiscount_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_UNABLE_TO_SAVE_THIS_RECORD', $this->siteLangId));
        }
        return $voldiscount_id;
    }

    public function deleteSellerProductVolumeDiscount()
    {
        $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $voldiscount_id = FatApp::getPostedData('voldiscount_id', FatUtility::VAR_INT, 0);
        if (!$voldiscount_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $volumeDiscountRow = SellerProductVolumeDiscount::getAttributesById($voldiscount_id);
        $sellerProductRow = SellerProduct::getAttributesById($volumeDiscountRow['voldiscount_selprod_id'], array('selprod_user_id'), false);
        if (!$volumeDiscountRow || !$sellerProductRow || $sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->deleteVolumeDiscount($voldiscount_id, $volumeDiscountRow['voldiscount_selprod_id']);

        $this->set('selprod_id', $volumeDiscountRow['voldiscount_selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Volume_Discount_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteVolumeDiscountArr()
    {
        $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId());
        $splpriceIdArr = FatApp::getPostedData('selprod_ids');
        $splpriceIds = FatUtility::int($splpriceIdArr);
        foreach ($splpriceIds as $voldiscount_id => $selProdId) {
            $volumeDiscountRow = SellerProductVolumeDiscount::getAttributesById($voldiscount_id);
            $sellerProductRow = SellerProduct::getAttributesById($volumeDiscountRow['voldiscount_selprod_id'], array('selprod_user_id'), false);
            if (!$volumeDiscountRow || !$sellerProductRow || $sellerProductRow['selprod_user_id'] != $this->userParentId) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            $this->deleteVolumeDiscount($voldiscount_id, $volumeDiscountRow['voldiscount_selprod_id']);
        }
        $this->set('msg', Labels::getLabel('LBL_Volume_Discount_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function deleteVolumeDiscount($volumeDiscountId, $volumeDiscountSelprodId)
    {
        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProductVolumeDiscount::DB_TBL, array('smt' => 'voldiscount_id = ? AND voldiscount_selprod_id = ?', 'vals' => array($volumeDiscountId, $volumeDiscountSelprodId)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        return true;
    }

    private function getSellerProductVolumeDiscountForm($langId)
    {
        $frm = new Form('frmSellerProductSpecialPrice');

        $frm->addHiddenField('', 'voldiscount_selprod_id', 0);
        $frm->addHiddenField('', 'voldiscount_id', 0);
        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Quantity", $langId), 'voldiscount_min_qty');
        $qtyFld->requirements()->setPositive();
        $discountFld = $frm->addFloatField(Labels::getLabel("LBL_Discount_in_(%)", $this->siteLangId), "voldiscount_percentage");
        $discountFld->requirements()->setPositive();
        $discountFld->requirements()->setRange(1, 100);
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $langId), array('onClick' => 'javascript:$("#sellerProductsForm").html(\'\')'));
        $fld1->attachField($fld2);
        return $frm;
    }
    /*    ]    */

    /* Seller Product Seo [ */
    public function productSeo()
    {
        $this->userPrivilege->canViewMetaTags(UserAuthentication::getLoggedUserId());
        $this->set('frmSearch', $this->getSellerProductSearchForm());
        $this->_template->render(true, true);
    }

    public function searchSeoProducts()
    {
        $userId = $this->userParentId;
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $srch = SellerProduct::searchSellerProducts($this->siteLangId, $userId, $keyword);
        $srch->addMultipleFields(
            array(
                'selprod_id', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title'
            )
        );
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : $post['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        $db = FatApp::getDb();

        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);

        $this->set("arrListing", $arrListing);
        $this->set('canEditMetaTag', $this->userPrivilege->canEditMetaTags(UserAuthentication::getLoggedUserId(), true));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    public function productSeoLangForm($selprodId, $langId)
    {
        $selprodId = FatUtility::int($selprodId);
        $langId = FatUtility::int($langId);

        $sellerProductRow = SellerProduct::getAttributesById($selprodId);
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;
        $this->set('metaType', $metaType);

        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));
        $prodMetaData = Product::getProductMetaData($selprodId);

        $metaId = 0;

        if (!empty($prodMetaData)) {
            $metaId = $prodMetaData['meta_id'];
        }

        $metaData = MetaTag::getAttributesByLangId($langId, $metaId);

        $prodSeoLangFrm = $this->getSeoLangForm($metaId, $langId, $selprodId, MetaTag::META_GROUP_PRODUCT_DETAIL);
        $prodSeoLangFrm->fill($metaData);

        $this->set('languages', Language::getAllNames());
        $this->set('productSeoLangForm', $prodSeoLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('selprodId', $selprodId);
        $this->set('selprod_lang_id', $langId);

        $this->_template->render(false, false);
    }

    private function getSeoLangForm($metaId = 0, $lang_id = 0, $recordId = 0, $metaType = 'default')
    {
        $frm = new Form('frmMetaTagLang');

        $frm->addHiddenField('', 'meta_id', $metaId);
        $frm->addHiddenField('', 'meta_type', $metaType);
        $frm->addHiddenField('', 'meta_record_id', $recordId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel("LBL_Meta_Title", $this->siteLangId), 'meta_title');
        $frm->addTextarea(Labels::getLabel("LBL_Meta_Keywords", $this->siteLangId), 'meta_keywords');
        $frm->addTextarea(Labels::getLabel("LBL_Meta_Description", $this->siteLangId), 'meta_description');
        $fld = $frm->addTextarea(Labels::getLabel("LBL_Other_Meta_Tags", $this->siteLangId), 'meta_other_meta_tags');
        $fld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_For_Example:', $this->siteLangId) . ' ' . htmlspecialchars('<meta name="copyright" content="text">') . '</small>';
        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        $languages = Language::getAllNames();
        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId && count($languages) > 1) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addButton('', 'btn_next', Labels::getLabel("LBL_Save_&_Next", $this->siteLangId));
        $frm->addButton('', 'btn_exit', Labels::getLabel("LBL_Save_&_Exit", $this->siteLangId));
        return $frm;
    }

    public function setupProdMetaLang()
    {
        $this->userPrivilege->canEditMetaTags(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        if ($lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        $metaId = FatUtility::int($post['meta_id']);
        $metaRecordId = FatUtility::int($post['meta_record_id']);

        if (!UserPrivilege::canEditMetaTag($metaId, $metaRecordId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$post['meta_other_meta_tags'] == '' && $post['meta_other_meta_tags'] == strip_tags($post['meta_other_meta_tags'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Other_Meta_Tag', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $tabsArr = MetaTag::getTabsArr();
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;

        if ($metaType == '' || !isset($tabsArr[$metaType])) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post['meta_controller'] = $tabsArr[$metaType]['controller'];
        $post['meta_action'] = $tabsArr[$metaType]['action'];
        if ($metaId == 0) {
            $post['meta_subrecord_id'] = 0;
        }
        $record = new MetaTag($metaId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $metaId = $record->getMainTableRecordId();
        $frm = $this->getSeoLangForm($metaId, $lang_id, $metaRecordId, MetaTag::META_GROUP_PRODUCT_DETAIL);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $data = array(
            'metalang_lang_id' => $lang_id,
            'metalang_meta_id' => $metaId,
            'meta_title' => strip_tags($post['meta_title']),
            'meta_keywords' => strip_tags($post['meta_keywords']),
            'meta_description' => strip_tags($post['meta_description']),
            'meta_other_meta_tags' => $post['meta_other_meta_tags'],
        );

        $metaObj = new MetaTag($metaId);

        if (!$metaObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($metaObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(MetaTag::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($metaId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $languages = Language::getAllNames();

        $newTabLangId = $this->siteLangId;
        $keys = array_keys($languages);
        $index = array_search($lang_id, $keys);
        if (count($languages) > $index + 1) {
            $newTabLangId = $keys[$index + 1];
        }

        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->siteLangId));
        $this->set('metaRecordId', $metaRecordId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /*  --- ] Seller Product Seo  --- -   */

    /* Seller Product URL Rewriting [ */
    public function productUrlRewriting()
    {
        $this->userPrivilege->canViewUrlRewriting(UserAuthentication::getLoggedUserId());
        $this->set('frmSearch', $this->getSellerProductSearchForm());
        $this->_template->render(true, true);
    }

    public function productUrlForm($selprodId)
    {
        $this->userPrivilege->canViewUrlRewriting(UserAuthentication::getLoggedUserId());
        $selprodId = FatUtility::int($selprodId);

        $sellerProductRow = SellerProduct::getAttributesById($selprodId);
        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getUrlRewriteForm();

        $tabsArr = MetaTag::getTabsArr();
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;

        if ($metaType == '' || !isset($tabsArr[$metaType])) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $url = $tabsArr[$metaType]['controller'] . '/' . $tabsArr[$metaType]['action'] . '/' . $selprodId;
        $url = trim($url, '/\\');

        if (0 < $selprodId) {
            $srch = UrlRewrite::getSearchObject();
            $srch->joinTable(UrlRewrite::DB_TBL, 'LEFT OUTER JOIN', 'temp.urlrewrite_original = ur.urlrewrite_original', 'temp');
            $srch->addCondition('ur.urlrewrite_original', '=', $url);
            $rs = $srch->getResultSet();
            $data = [
                'selprod_id' => $selprodId
            ];
            while ($row = FatApp::getDb()->fetch($rs)) {
                $data['urlrewrite_original'] = $row['urlrewrite_original'];
                $data['urlrewrite_custom'][$row['urlrewrite_lang_id']] = $row['urlrewrite_custom'];
            }

            if (empty($data)) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        $this->set('frm', $frm);
        $this->set('selprodId', $selprodId);
        $this->_template->render(false, false);
    }

    public function searchUrlRewritingProducts()
    {
        $userId = $this->userParentId;
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $srch = SellerProduct::searchSellerProducts($this->siteLangId, $userId, $keyword);
        $srch->addMultipleFields(
            array(
                'selprod_id', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title'
            )
        );
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : $post['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        $db = FatApp::getDb();

        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);

        foreach ($arrListing as $key => $sellerProduct) {
            $urlRewriteData = UrlRewrite::getAttributesById($sellerProduct['selprod_id']);
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addMultipleFields(array('urlrewrite_id', 'urlrewrite_custom'));
            $urlSrch->addCondition('urlrewrite_original', '=', 'products/view/' . $sellerProduct['selprod_id']);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $arrListing[$key]['urlrewrite_id'] = $urlRow['urlrewrite_id'];
                $arrListing[$key]['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
        }
        $this->set('canEditUrlRewrite', $this->userPrivilege->canEditUrlRewriting(UserAuthentication::getLoggedUserId(), true));
        $this->set("arrListing", $arrListing);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    public function setupCustomUrl()
    {
        $this->userPrivilege->canEditUrlRewriting(UserAuthentication::getLoggedUserId());
        $frm = $this->getUrlRewriteForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $selprodId = $post['selprod_id'];

        if (!UserPrivilege::canEditSellerProduct($this->userParentId, $selprodId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $tabsArr = MetaTag::getTabsArr();
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;

        if ($metaType == '' || !isset($tabsArr[$metaType])) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $url = $tabsArr[$metaType]['controller'] . '/' . $tabsArr[$metaType]['action'] . '/' . $selprodId;
        $originalUrl = trim(strtolower($url), '/\\');

        $srch = UrlRewrite::getSearchObject();
        $srch->joinTable(UrlRewrite::DB_TBL, 'LEFT OUTER JOIN', 'temp.urlrewrite_original = ur.urlrewrite_original', 'temp');
        $srch->addCondition('ur.urlrewrite_original', '=', $originalUrl);
        $srch->addMultipleFields(array('temp.*'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'urlrewrite_lang_id');

        $langArr = Language::getAllNames();
        foreach ($langArr as $langId => $langName) {
            if (!FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0) && $langId != FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1)) {
                continue;
            }

            $recordId = 0;
            if (array_key_exists($langId, $row)) {
                $recordId = $row[$langId]['urlrewrite_id'];
            }
            $url = $post['urlrewrite_custom'][$langId];

            $srch = UrlRewrite::getSearchObject();
            $srch->addCondition('ur.urlrewrite_custom', '=', $url);
            $srch->addCondition('ur.urlrewrite_id', '!=', $recordId);
            $srch->addMultipleFields(['ur.urlrewrite_id']);
            $rs = $srch->getResultSet();
            if (FatApp::getDb()->fetch($rs)) {
                Message::addErrorMessage(Labels::getLabel('MSG_DUPLICATE_CUSTOM_URL', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $data = [
                'urlrewrite_original' => $originalUrl,
                'urlrewrite_lang_id' => $langId,
                'urlrewrite_custom' => CommonHelper::seoUrl($url)
            ];
            $record = new UrlRewrite($recordId);
            $record->assignValues($data);

            if (!$record->save()) {
                Message::addErrorMessage($record->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getUrlRewriteForm()
    {
        $frm = new Form('frmUrlRewrite');
        $frm->addHiddenField('', 'selprod_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Original_URL', $this->siteLangId), 'urlrewrite_original');
        $langArr = Language::getAllNames();
        foreach ($langArr as $langId => $langName) {
            if (!FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0) && $langId != FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1)) {
                continue;
            }

            $fieldName = Labels::getLabel('LBL_Custom_URL', $this->siteLangId);
            if (FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0)) {
                $fieldName .=  '(' . $langName . ')';
            }
            $frm->addRequiredField($fieldName, 'urlrewrite_custom[' . $langId . ']');
        }
        $fld =  $frm->addHTML('', '', '');
        //$fld = $frm->addRequiredField(Labels::getLabel('LBL_Custom_URL', $this->siteLangId), 'urlrewrite_custom');
        $fld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Example:_Custom_URL_Example', $this->siteLangId) . '</small>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }
    /*  --- ] Seller Product URL Rewriting  ----   */

    /*  ---- Seller Product Links  ----- [*/

    public function sellerProductLinkFrm($selProd_id)
    {
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($selProd_id);
        if (!UserPrivilege::canEditSellerProduct($this->userParentId, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $sellProdObj = new SellerProduct();
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        $upsellProds = $sellProdObj->getUpsellProducts($selprod_id, $this->siteLangId);
        $relatedProds = $sellProdObj->getRelatedProducts($this->siteLangId, $selprod_id);
        $sellerproductLinkFrm = $this->getLinksFrm();
        $data['selprod_id'] = $selProd_id;
        $sellerproductLinkFrm->fill($data);
        $this->set('sellerproductLinkFrm', $sellerproductLinkFrm);
        $this->set('upsellProducts', $upsellProds);
        $this->set('relatedProducts', $relatedProds);
        $this->set('selprod_id', $selProd_id);
        $this->set('product_id', $sellerProductRow[SellerProduct::DB_TBL_PREFIX . 'product_id']);
        $this->set('activeTab', 'LINKS');
        $this->set('product_type', $productRow['product_type']);
        $this->_template->render(false, false);
    }

    private function getDownloadForm($product_id, $selprod_id, $langId)
    {
        $product_id = FatUtility::int($product_id);
        $selprod_id = FatUtility::int($selprod_id);

        $savedOptions = array();
        $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        foreach ($optionCombinations as $optionKey => $optionValue) {
            /* Check if product is added for this option [ */
            $selProdCode = $product_id . '_' . $optionKey;
            $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $langId, $this->userParentId);
            if (empty($selProdAvailable) || $selProdAvailable['selprod_deleted']) {
                continue;
            }
            $savedOptions[$selProdAvailable['selprod_id']] = $optionValue;
            /* ] */
        }
        if ($selprod_id > 0) {
            $currentOption[$selprod_id] = (array_key_exists($selprod_id, $savedOptions)) ? $savedOptions[$selprod_id] : '';
            $savedOptions = $currentOption;
        }
        $frm = new Form('frmDownload');
        $bannerTypeArr = applicationConstants::bannerTypeArr($langId);
        $digitalDownloadTypeArr = applicationConstants::digitalDownloadTypeArr($langId);

        foreach ($savedOptions as $key => $val) {
            /* $frm->addSelectBox(Labels::getLabel('LBL_Digital_Download_Type', $langId), 'download_type'.$key, $digitalDownloadTypeArr, '', array('class' => 'downloadType-js', 'id' => $key), '')->requirements()->setRequired(); */
            $fld = $frm->addTextArea(Labels::getLabel('LBL_Downloadable_Link', $langId), 'selprod_downloadable_link' . $key);
            $fld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_Add_links_comma_separated_or_with_new_line', $langId) . '</small>';
            $fld->requirements()->setRequired();
            $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id' . $key, $bannerTypeArr, '', array('class' => 'file-language-js' . $key), '')->requirements()->setRequired();
            $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Upload_File', $langId), 'downloadable_file' . $key, array('id' => 'downloadable_file' . $key, 'multiple' => 'multiple'));
            $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename"></span></div>';
            $frm->addSubmitButton('', 'btn_submit' . $key, Labels::getLabel("LBL_Save_Changes", $langId));
        }
        return $frm;
    }

    public function sellerProductDownloadFrm($product_id, $selProd_id = 0)
    {
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($product_id);
        $selprod_id = FatUtility::int($selProd_id);

        if ($selprod_id == 0 && !UserPrivilege::canSellerAddProductInCatalog($product_id, $this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Please_Upgrade_your_package_to_add_new_products", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $selprodDownloadFrm = $this->getDownloadForm($product_id, $selProd_id, $this->siteLangId);


        $savedOptions = array();
        $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        foreach ($optionCombinations as $optionKey => $optionValue) {
            /* Check if product is added for this option [ */
            $selProdCode = $product_id . '_' . $optionKey;
            $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId);
            if (empty($selProdAvailable) || $selProdAvailable['selprod_deleted']) {
                continue;
            }
            $savedOptions[$selProdAvailable['selprod_id']] = $optionValue;
            /* ] */
        }
        if ($selprod_id > 0) {
            $currentOption[$selprod_id] = (array_key_exists($selprod_id, $savedOptions)) ? $savedOptions[$selprod_id] : '';
            $savedOptions = $currentOption;
        }
        foreach ($savedOptions as $key => $val) {
            $sellerProductRow = SellerProduct::getAttributesById($key);
            $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));
            $data['selprod_downloadable_link' . $key] = $sellerProductRow['selprod_downloadable_link'];
        }
        $selprodDownloadFrm->fill($data);
        $this->set('savedOptions', $savedOptions);
        $this->set('selprodDownloadFrm', $selprodDownloadFrm);
        $this->set('product_id', $product_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function uploadDigitalFile()
    {
        if (!$this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Unauthorized_Access!', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $userId = $this->userParentId;
        $post = FatApp::getPostedData();
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id' . $selprod_id, FatUtility::VAR_INT, 0);
        /* $download_type = FatApp::getPostedData('download_type'.$selprod_id, FatUtility::VAR_INT, 0); */
        if (!$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $selProdData = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id'));
        if ($selProdData == false || ($selProdData && $selProdData['selprod_user_id'] !== $userId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (isset($_FILES['downloadable_file']) && is_uploaded_file($_FILES['downloadable_file']['tmp_name'])) {
            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveAttachment(
                $_FILES['downloadable_file']['tmp_name'],
                AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD,
                $selprod_id,
                0,
                $_FILES['downloadable_file']['name'],
                -1,
                $unique_record = false,
                $lang_id
            )) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            Message::addMessage(Labels::getLabel('MSG_File_Uploaded_Successfully.', $this->siteLangId));
            FatUtility::dieJsonSuccess(Message::getHtml());
        }

        if (!empty($post['selprod_downloadable_link' . $selprod_id])) {
            $data_to_be_save = array();
            $data_to_be_save['selprod_downloadable_link'] = $post['selprod_downloadable_link' . $selprod_id];
            $sellerProdObj = new SellerProduct($selprod_id);
            $sellerProdObj->assignValues($data_to_be_save);

            if (!$sellerProdObj->save()) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            Message::addMessage(Labels::getLabel('MSG_Setup_Successful.', $this->siteLangId));
            FatUtility::dieJsonSuccess(Message::getHtml());
        }
    }

    public function deleteDigitalFile($selprodId, $afileId = 0)
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $selprodId = FatUtility::int($selprodId);
        $afileId = FatUtility::int($afileId);

        if (!$selprodId || !$afileId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* Validate product belongs to current logged seller[ */
        $productRow = SellerProduct::getAttributesById($selprodId, array('selprod_user_id'));
        if ($productRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD, $selprodId, $afileId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel('LBL_Removed_successfully.', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function downloadDigitalFile($aFileId, $recordId = 0, $fileType = AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD)
    {
        $aFileId = FatUtility::int($aFileId);
        $recordId = FatUtility::int($recordId);
        $fileType = FatUtility::int($fileType);
        $userId = $this->userParentId;

        if (1 > $aFileId || 1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'products'));
        }

        if ($fileType == AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD) {
            $selProdData = SellerProduct::getAttributesById($recordId, array('selprod_user_id'));
            if ($selProdData == false || ($selProdData && $selProdData['selprod_user_id'] !== $userId)) {
                Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrder', array($recordId)));
            }
        } else {
            $srch = new OrderProductSearch(0, true);
            $srch->addMultipleFields(array('op_id', 'op_selprod_user_id'));
            $srch->addCondition('op_id', '=', $recordId);
            $srch->doNotCalculateRecords();
            $srch->setPageSize(1);
            $row = FatApp::getDb()->fetch($srch->getResultSet());
            if ($row == false || ($row && $row['op_selprod_user_id'] !== $userId)) {
                Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrder', array($recordId)));
            }
        }

        $file_row = AttachedFile::getAttributesById($aFileId);
        if ($file_row == false || $file_row['afile_record_id'] != $recordId || $file_row['afile_type'] != $fileType) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrder', array($recordId)));
        }

        if (!file_exists(CONF_UPLOADS_PATH . $file_row['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrder', array($recordId)));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    private function getLinksFrm()
    {
        $frm = new Form('frmLinks', array('id' => 'frmLinks'));

        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Buy_Together_Products', $this->siteLangId), 'products_buy_together');
        $fld1->htmlAfterField = '<div class="row"><div class="col-md-12"><ul class="list-vertical" id="buy-together-products"></ul></div></div>';

        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Related_Products', $this->siteLangId), 'products_related');
        $fld1->htmlAfterField = '<div class="row"><div class="col-md-12"><ul class="list-vertical" id="related-products"></ul></div></div>';

        $frm->addHiddenField('', 'selprod_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    public function autoCompleteProducts()
    {
        $pagesize = 20;
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');

        if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', 'tb.brand_id = product_brand_id and tb.brand_active = ' . applicationConstants::YES . ' and tb.brand_deleted = ' . applicationConstants::NO, 'tb');
        } else {
            $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tb.brand_id = product_brand_id', 'tb');
            $srch->addDirectCondition("(case WHEN brand_id > 0 THEN (tb.brand_active = " . applicationConstants::YES . " AND tb.brand_deleted = " . applicationConstants::NO . ") else TRUE end)");
        }

        $srch->addOrder('product_name');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd = $cnd->attachCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        if (isset($post['selprod_id'])) {
            $srch->addCondition('selprod_id', '!=', $post['selprod_id']);
        }
        if (isset($post['selected_products'])) {
            $srch->addCondition('selprod_id', 'NOT IN', array_values($post['selected_products']));
        }
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(
            array(
                'selprod_id as id', 'IFNULL(selprod_title ,product_name) as product_name', 'product_identifier', 'selprod_price'
            )
        );
        $srch->setPageSize($pagesize);
        $srch->setPageNumber($page);
        $srch->addOrder('selprod_active', 'DESC');
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'id');
        $arrListing = $db->fetchAll($rs);
        $pageCount = $srch->pages();

        $json = array();
        foreach ($products as $key => $option) {
            $options = SellerProduct::getSellerProductOptions($key, true, $this->siteLangId);
            $variantsStr = '';
            array_walk($options, function ($item, $key) use (&$variantsStr) {
                $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
            });

            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')) . $variantsStr,
                'product_identifier' => strip_tags(html_entity_decode($option['product_identifier'], ENT_QUOTES, 'UTF-8')),
                'price' => $option['selprod_price']
            );
        }
        die(json_encode(['pageCount' => $pageCount, 'products' => $json]));
    }

    public function setupSellerProductLinks()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!UserPrivilege::canEditSellerProduct($this->userParentId, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $upsellProducts = (isset($post['product_upsell'])) ? $post['product_upsell'] : array();
        $relatedProducts = (isset($post['product_related'])) ? $post['product_related'] : array();
        unset($post['selprod_id']);

        if ($selprod_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $sellerProdObj = new sellerProduct();
        /* saving of product Upsell Product[ */
        if (!$sellerProdObj->addUpdateSellerUpsellProducts($selprod_id, $upsellProducts)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        /* ] */
        /* saving of Related Products[ */


        if (!$sellerProdObj->addUpdateSellerRelatedProdcts($selprod_id, $relatedProducts)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    /*  - ---  ] Seller Product Links  ----- */

    public function linkPoliciesForm($product_id, $selprod_id, $ppoint_type)
    {
        $product_id = FatUtility::int($product_id);
        $ppoint_type = FatUtility::int($ppoint_type);
        $selprod_id = FatUtility::int($selprod_id);
        if ($product_id <= 0 || $selprod_id <= 0 || $ppoint_type <= 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $productRow = Product::getAttributesById($product_id, array('product_type'));
        $frm = $this->getLinkPoliciesForm($selprod_id, $ppoint_type);
        $data = array('selprod_id' => $selprod_id);
        $frm->fill($data);
        $this->set('product_id', $product_id);
        $this->set('selprod_id', $selprod_id);
        $this->set('frm', $frm);
        $this->set('language', Language::getAllNames());
        $this->set('activeTab', 'GENERAL');
        $this->set('product_type', $productRow['product_type']);
        $this->set('ppoint_type', $ppoint_type);
        $this->_template->render(false, false);
    }

    public function searchPoliciesToLink()
    {
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $ppoint_type = FatApp::getPostedData('ppoint_type', FatUtility::VAR_INT, 0);
        $searchForm = $this->getLinkPoliciesForm($selprod_id, $ppoint_type);
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $post = $searchForm->getFormDataFromArray($data);
        $srch = PolicyPoint::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_seller_product_policies', 'left outer join', 'spp.sppolicy_ppoint_id = pp.ppoint_id and spp.sppolicy_selprod_id=' . $selprod_id, 'spp');
        $srch->addCondition('pp.ppoint_type', '=', $ppoint_type);
        $srch->addMultipleFields(array('*', 'ifnull(sppolicy_selprod_id,0) selProdId'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('selProdId', 'desc');
        $records = FatApp::getDb()->fetchAll($srch->getResultSet(), 'ppoint_id');
        $this->set("selprod_id", $selprod_id);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'seller/search-policies-to-link.php', false, false);
    }

    public function getSpecialPriceDiscountString()
    {
        $post = FatApp::getPostedData();
        $str = Labels::getLabel("LBL_[Save_nn_(XX%_Off)]", $this->siteLangId);
        $str = str_replace(array("nn", "Nn", "NN", "nN"), CommonHelper::displayMoneyFormat($post['splprice_display_list_price']), $str);
        if ($post['splprice_display_dis_type'] == applicationConstants::PERCENTAGE) {
            $str = str_replace(array("XX", "xx", "Xx", "xX"), $post['splprice_display_dis_val'], $str);
        } elseif ($post['splprice_display_dis_type'] == applicationConstants::FLAT) {
            $str = str_replace(array("XX%", "xx%", "Xx%", "xX%"), CommonHelper::displayMoneyFormat($post['splprice_display_dis_val']), $str);
        } else {
            $str = str_replace(array("XX%", "xx%", "Xx%", "xX%"), CommonHelper::displayMoneyFormat($post['splprice_display_dis_val']), $str);
        }
        echo $str;
    }

    private function getLinkPoliciesForm($selprod_id, $ppoint_type)
    {
        $frm = new Form('frmLinkWarrantyPolicies');
        $frm->addHiddenField('', 'selprod_id', $selprod_id);
        $frm->addHiddenField('', 'ppoint_type', $ppoint_type);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function addPolicyPoint()
    {
        $post = FatApp::getPostedData();
        if (empty($post['selprod_id']) || empty($post['ppoint_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = FatUtility::int($post['selprod_id']);
        $ppoint_id = FatUtility::int($post['ppoint_id']);
        $dataToSave = array('sppolicy_ppoint_id' => $ppoint_id, 'sppolicy_selprod_id' => $selprod_id);
        $obj = new SellerProduct();
        if (!$obj->addPolicyPointToSelProd($dataToSave)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Policy_Added_Successfully", $this->siteLangId));
    }

    public function removePolicyPoint()
    {
        $post = FatApp::getPostedData();
        if (empty($post['selprod_id']) || empty($post['ppoint_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = FatUtility::int($post['selprod_id']);
        $ppoint_id = FatUtility::int($post['ppoint_id']);
        $whereCond = array('smt' => 'sppolicy_ppoint_id = ? and sppolicy_selprod_id = ?', 'vals' => array($ppoint_id, $selprod_id));
        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_POLICY, $whereCond)) {
            Message::addErrorMessage($db->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_Policy_Removed_Successfully", $this->siteLangId));
    }

    public function deleteBulkSellerProducts()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $selprodId_arr = FatUtility::int(FatApp::getPostedData('selprod_ids'));
        if (empty($selprodId_arr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }
        foreach ($selprodId_arr as $selprod_id) {
            $this->deleteSellerProduct($selprod_id);
        }
        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->siteLangId)
        );
    }

    public function sellerProductDelete()
    {
        $selprod_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);

        $this->deleteSellerProduct($selprod_id);

        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->siteLangId)
        );
    }

    private function deleteSellerProduct($selprod_id)
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $selprod_id = FatUtility::int($selprod_id);
        if (1 > $selprod_id) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        $selprodObj = new SellerProduct($selprod_id);
        if (!$selprodObj->deleteSellerProduct($selprod_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function sellerProductCloneForm($product_id, $selprod_id)
    {
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && SellerProduct::getActiveCount($this->userParentId) >= SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_inventory_allowed')) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_have_crossed_your_package_limit", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = FatUtility::int($selprod_id);
        $product_id = FatUtility::int($product_id);
        $userId = $this->userParentId;

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id', 'selprod_id', 'selprod_product_id', 'selprod_url_keyword', 'selprod_cost', 'selprod_price', 'selprod_stock', 'selprod_return_age', 'selprod_cancellation_age'), false, true);

        if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $sellerProductRow['selprod_available_from'] = date('Y-m-d');
        $frm = $this->getSellerProductCloneForm($product_id, $selprod_id);

        $returnAge = isset($sellerProductRow['selprod_return_age']) ? FatUtility::int($sellerProductRow['selprod_return_age']) : '';
        $cancellationAge = isset($sellerProductRow['selprod_cancellation_age']) ? FatUtility::int($sellerProductRow['selprod_cancellation_age']) : '';

        if ('' === $returnAge || '' === $cancellationAge) {
            $sellerProductRow['use_shop_policy'] = 1;
        }
        $frm->fill($sellerProductRow);

        $this->set('frm', $frm);
        $this->set('userId', $this->userParentId);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->_template->render(false, false);
    }

    public function getSellerProductCloneForm($product_id, $selprod_id)
    {
        $frm = new Form('frmSellerProduct');
        $productData = Product::getAttributesById($product_id, array('product_identifier', 'product_min_selling_price'));

        $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);
        if ($productOptions) {
            $frm->addHtml('', 'optionSectionHeading', '');
            foreach ($productOptions as $option) {
                $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id[' . $option['option_id'] . ']', $option['optionValues'], '', array('class' => 'selprodoption_optionvalue_id'), Labels::getLabel('LBL_Select', $this->siteLangId));
                $fld->requirements()->setRequired();
            }
        }
        $frm->addTextBox(Labels::getLabel('LBL_Url_Keyword', $this->siteLangId), 'selprod_url_keyword')->requirements()->setRequired();

        $costPrice = $frm->addFloatField(Labels::getLabel('LBL_Cost_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_cost');
        $costPrice->requirements()->setPositive();

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_price');
        if (isset($productData['product_min_selling_price'])) {
            $fld->requirements()->setRange($productData['product_min_selling_price'], 9999999999);
            $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Minimum_selling_price_for_this_product_is', $this->siteLangId) . ' ' . CommonHelper::displayMoneyFormat($productData['product_min_selling_price'], true, true));
        }
        $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $this->siteLangId), 'selprod_stock');
        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $this->siteLangId), 'selprod_available_from', '', array('readonly' => 'readonly'))->requirements()->setRequired();

        $useShopPolicy = $frm->addCheckBox(Labels::getLabel('LBL_USE_SHOP_RETURN_AND_CANCELLATION_AGE_POLICY', $this->siteLangId), 'use_shop_policy', 1, ['id' => 'use_shop_policy'], false, 0);

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_ORDER_RETURN_AGE', $this->siteLangId), 'selprod_return_age');

        $orderReturnAgeReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_ORDER_RETURN_AGE', $this->siteLangId));
        $orderReturnAgeReqFld->setRequired(true);
        $orderReturnAgeReqFld->setPositive();
        $orderReturnAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $orderReturnAgeUnReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_ORDER_RETURN_AGE', $this->siteLangId));
        $orderReturnAgeUnReqFld->setRequired(false);
        $orderReturnAgeUnReqFld->setPositive();
        $orderReturnAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_ORDER_CANCELLATION_AGE', $this->siteLangId), 'selprod_cancellation_age');

        $orderCancellationAgeReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_ORDER_CANCELLATION_AGE', $this->siteLangId));
        $orderCancellationAgeReqFld->setRequired(true);
        $orderCancellationAgeReqFld->setPositive();
        $orderCancellationAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $orderCancellationAgeUnReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_ORDER_CANCELLATION_AGE', $this->siteLangId));
        $orderCancellationAgeUnReqFld->setRequired(false);
        $orderCancellationAgeUnReqFld->setPositive();
        $orderCancellationAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_return_age', $orderReturnAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_return_age', $orderReturnAgeReqFld);

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_cancellation_age', $orderCancellationAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_cancellation_age', $orderCancellationAgeReqFld);

        $frm->addHiddenField('', 'selprod_product_id', $product_id);
        $frm->addHiddenField('', 'selprod_id', $selprod_id);
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    public function setUpSellerProductClone()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
        $selprod_id = Fatutility::int($post['selprod_id']);

        $selprod_product_id = Fatutility::int($post['selprod_product_id']);

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        if (!$selprod_product_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $productRow = Product::getAttributesById($selprod_product_id, array('product_id', 'product_active', 'product_seller_id', 'product_added_by_admin_id'));
        if (!$productRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (($productRow['product_seller_id'] != $this->userParentId) && $productRow['product_added_by_admin_id'] == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }
        $frm = $this->getSellerProductCloneForm($selprod_product_id, $selprod_id);
        $post['use_shop_policy'] = $useShopPolicy;
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        /* Validate product belongs to current logged seller[ */
        if ($selprod_id) {
            $sellerProductRow = SellerProduct::getAttributesById($selprod_id, null, true, true);
            if ($sellerProductRow['selprod_user_id'] != $this->userParentId) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */
        $post['selprod_url_keyword'] = strtolower(CommonHelper::createSlug($post['selprod_url_keyword']));

        $options = array();
        if (isset($post['selprodoption_optionvalue_id']) && count($post['selprodoption_optionvalue_id'])) {
            $options = $post['selprodoption_optionvalue_id'];
            unset($post['selprodoption_optionvalue_id']);
        }
        asort($options);
        $sellerProdObj = new SellerProduct();
        $selProdCode = $productRow['product_id'] . '_' . implode('_', $options);
        $sellerProductRow['selprod_code'] = $selProdCode;

        $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId, 0);

        unset($sellerProductRow['selprod_id']);
        $data_to_be_save = $sellerProductRow;
        $data_to_be_save['selprod_price'] = $post['selprod_price'];
        $data_to_be_save['selprod_stock'] = $post['selprod_stock'];
        $data_to_be_save['selprod_available_from'] = $post['selprod_available_from'];

        if (!empty($selProdAvailable)) {
            if (!$selProdAvailable['selprod_deleted']) {
                Message::addErrorMessage(Labels::getLabel("LBL_Inventory_for_this_option_have_been_added", $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $sellerProdObj = new SellerProduct($selProdAvailable['selprod_id']);
            $data_to_be_save['selprod_deleted'] = applicationConstants::NO;
            $sellerProdObj->assignValues($data_to_be_save);
            if (!$sellerProdObj->save()) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatApp::redirectUser($_SESSION['referer_page_url']);
            }
            $this->set('msg', Labels::getLabel('Product_was_deleted._Reactivate_the_same', $this->siteLangId));
            $this->_template->render(false, false, 'json-success.php');
        } else {
            $data_to_be_save['selprod_user_id'] = $this->userParentId;
            $data_to_be_save['selprod_added_on'] = date("Y-m-d H:i:s");
            $sellerProdObj->assignValues($data_to_be_save);

            if (!$sellerProdObj->save()) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatApp::redirectUser($_SESSION['referer_page_url']);
            }
        }

        $selprod_id = $sellerProdObj->getMainTableRecordId();

        if (!empty($selprod_id)) {
            $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
            if (0 < $useShopPolicy) {
                if (!$selProdSpecificsObj->deleteRecord()) {
                    FatUtility::dieJsonError($selProdSpecificsObj->getError());
                }
            } else {
                $post['sps_selprod_id'] = $selprod_id;
                $selProdSpecificsObj->assignValues($post);
                $selProdSepc = $selProdSpecificsObj->getFlds();
                if (!$selProdSpecificsObj->addNew(array(), $selProdSepc)) {
                    Message::addErrorMessage($selProdSpecificsObj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }

        $sellerProdObj->rewriteUrlProduct($post['selprod_url_keyword']);
        $sellerProdObj->rewriteUrlReviews($post['selprod_url_keyword']);
        $sellerProdObj->rewriteUrlMoreSellers($post['selprod_url_keyword']);

        /* save options data, if any[ */
        if ($selprod_id) {
            if (!$sellerProdObj->addUpdateSellerProductOptions($selprod_id, $options)) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatApp::redirectUser($_SESSION['referer_page_url']);
            }
        }
        /* ] */

        $languages = Language::getAllNames();

        /* Clone seller product Lang Data and SEO data automatically[ */

        $metaData = array();


        $tabsArr = MetaTag::getTabsArr();
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;

        if ($metaType == '' || !isset($tabsArr[$metaType])) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $metaData['meta_controller'] = $tabsArr[$metaType]['controller'];
        $metaData['meta_action'] = $tabsArr[$metaType]['action'];
        $metaData['meta_record_id'] = $selprod_id;
        $metaIdentifier = SellerProduct::getProductDisplayTitle($selprod_id, FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1));
        $meta = new MetaTag();

        /* $count = 1;
        while ($metaRow = MetaTag::getAttributesByIdentifier($metaIdentifier, array('meta_identifier'))) {
            $metaIdentifier = $metaRow['meta_identifier']."-".$count;
            $count++;
        }
        $metaData['meta_identifier'] = $metaIdentifier; */
        $meta->assignValues($metaData);

        if (!$meta->save()) {
            Message::addErrorMessage($meta->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $metaId = $meta->getMainTableRecordId();

        foreach ($languages as $langId => $langName) {
            $langData = SellerProduct::getAttributesByLangId($langId, $post['selprod_id']);
            $langData = array(
                'selprodlang_selprod_id' => $selprod_id,
                'selprod_title' => SellerProduct::getProductDisplayTitle($selprod_id, $langId)
            );
            if (!$sellerProdObj->updateLangData($langId, $langData)) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $selProdMeta = array(
                'metalang_lang_id' => $langId,
                'metalang_meta_id' => $metaId,
                'meta_title' => SellerProduct::getProductDisplayTitle($selprod_id, $langId),
            );

            $metaObj = new MetaTag($metaId);

            if (!$metaObj->updateLangData($langId, $selProdMeta)) {
                Message::addErrorMessage($metaObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* ] */

        /* Search policies to link [ */
        $srch = PolicyPoint::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_seller_product_policies', 'left outer join', 'spp.sppolicy_ppoint_id = pp.ppoint_id and spp.sppolicy_selprod_id=' . $post['selprod_id'], 'spp');
        $srch->addMultipleFields(array('*', 'ifnull(sppolicy_selprod_id,0) selProdId'));
        $srch->addCondition('sppolicy_selprod_id', '=', $post['selprod_id']);
        $policies = FatApp::getDb()->fetchAll($srch->getResultSet(), 'ppoint_id');
        foreach ($policies as $linkData) {
            $dataToSave = array('sppolicy_selprod_id' => $selprod_id, 'sppolicy_ppoint_id' => $linkData['sppolicy_ppoint_id']);
            if (!$sellerProdObj->addPolicyPointToSelProd($dataToSave)) {
                Message::addErrorMessage($sellerProdObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }


    public function toggleBulkStatuses()
    {
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $selprodIdsArr = FatUtility::int(FatApp::getPostedData('selprod_ids'));
        if (empty($selprodIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }

        foreach ($selprodIdsArr as $selprod_id) {
            if (1 > $selprod_id) {
                continue;
            }

            $this->updateSellerProductStatus($selprod_id, $status);
        }
        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeProductStatus()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $selprodId = FatApp::getPostedData('selprodId', FatUtility::VAR_INT, 0);

        $sellerProductData = SellerProduct::getAttributesById($selprodId, array('selprod_active'));

        if (!$sellerProductData) {
            LibHelper::exitWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId), true);
        }

        $status = ($sellerProductData['selprod_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;
        if ($status == applicationConstants::ACTIVE && FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && SellerProduct::getActiveCount($this->userParentId) >= SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_inventory_allowed')) {
            LibHelper::exitWithError(Labels::getLabel('MSG_You_have_crossed_your_package_limit', $this->siteLangId), true);
        }

        $this->updateSellerProductStatus($selprodId, $status);

        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSellerProductStatus($selprodId, $status)
    {
        $status = FatUtility::int($status);
        $selprodId = FatUtility::int($selprodId);
        if (1 > $selprodId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }

        $sellerProdObj = new SellerProduct($selprodId);
        if (!$sellerProdObj->changeStatus($status)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $productId = SellerProduct::getAttributesById($selprodId, 'selprod_product_id', false);
        Product::updateMinPrices($productId);
    }

    public function volumeDiscount($selProd_id = 0)
    {
        $this->userPrivilege->canViewVolumeDiscount(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $selProd_id = FatUtility::int($selProd_id);
        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'volumeDiscount'));
            }
        }

        $srchFrm = $this->getVolumeDiscountSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->siteLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'voldiscount_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProdId]));
        }
        $this->set("canEdit", $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId(), true));
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchVolumeDiscountProducts()
    {
        $this->userPrivilege->canViewVolumeDiscount(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');

        $srch = SellerProduct::searchVolumeDiscountProducts($this->siteLangId, $selProdId, $keyword, $userId);

        $srch->setPageNumber($page);
        $srch->addOrder('voldiscount_id', 'DESC');

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);

        $this->set("arrListing", $arrListing);
        $this->set('canEdit', $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId(), true));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getVolumeDiscountSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function updateVolumeDiscountRow()
    {
        $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId());
        $data = FatApp::getPostedData();

        if (empty($data)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $selprod_id = FatUtility::int($data['voldiscount_selprod_id']);

        if (1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $volDiscountId = $this->updateSelProdVolDiscount($selprod_id, 0, $data['voldiscount_min_qty'], $data['voldiscount_percentage']);
        if (!$volDiscountId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Response', $this->siteLangId));
        }

        // last Param of getProductDisplayTitle function used to get title in html form.
        $productName = SellerProduct::getProductDisplayTitle($data['voldiscount_selprod_id'], $this->siteLangId, true);

        $data['product_name'] = $productName;
        $this->set('post', $data);
        $this->set('volDiscountId', $volDiscountId);
        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('LBL_Volume_Discount_Setup_Successful', $this->siteLangId),
            'data' => $this->_template->render(false, false, 'seller/update-volume-discount-row.php', true)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function updateVolumeDiscountColValue()
    {
        $this->userPrivilege->canEditVolumeDiscount(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $volDiscountId = FatApp::getPostedData('voldiscount_id', FatUtility::VAR_INT, 0);
        if (1 > $volDiscountId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $attribute = FatApp::getPostedData('attribute', FatUtility::VAR_STRING, '');
        $columns = array('voldiscount_min_qty', 'voldiscount_percentage');
        if (!in_array($attribute, $columns)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $otherColumns = array_values(array_diff($columns, [$attribute]));
        $otherColumnsValue = SellerProductVolumeDiscount::getAttributesById($volDiscountId, $otherColumns);
        if (empty($otherColumnsValue)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $value = FatApp::getPostedData('value');
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);

        $dataToUpdate = array(
            'voldiscount_id' => $volDiscountId,
            'voldiscount_selprod_id' => $selProdId,
            $attribute => $value
        );
        $dataToUpdate += $otherColumnsValue;

        $volDiscountId = $this->updateSelProdVolDiscount($selProdId, $volDiscountId, $dataToUpdate['voldiscount_min_qty'], $dataToUpdate['voldiscount_percentage']);
        if (!$volDiscountId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Response', $this->siteLangId));
        }

        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('MSG_Success', $this->siteLangId),
            'data' => array('value' => $value)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function getRelatedProductsList($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $srch = SellerProduct::searchRelatedProducts($this->siteLangId);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srch->addCondition(SellerProduct::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $selprod_id);
        $srch->addOrder('selprod_id', 'DESC');
        $rs = $srch->getResultSet();
        $relatedProds = FatApp::getDb()->fetchAll($rs);
        $json = array(
            'selprodId' => $selprod_id,
            'relatedProducts' => $relatedProds
        );
        FatUtility::dieJsonSuccess($json);
        /*$this->set('relatedProducts', $relatedProds);
        $this->set('selprod_id', $selprod_id);
        $this->_template->render(false, false, 'json-success.php');*/
    }

    private function getRelatedProductsForm()
    {
        $frm = new Form('frmRelatedSellerProduct');

        $frm->addHiddenField('', 'selprod_id', 0);
        $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'product_name', [], '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->siteLangId)));
        //$prodName = $frm->addTextBox('', 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->siteLangId)));
        $prodName->requirements()->setRequired();
        //$fld1 = $frm->addTextBox('', 'products_related');
        $fld1 = $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'products_related', [], '');
        // $fld1->htmlAfterField= '<div class="row"><div class="col-md-12"><ul class="list-vertical" id="related-products"></ul></div></div>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->siteLangId));
        return $frm;
    }

    public function relatedProducts($selProd_id = 0)
    {
        $this->userPrivilege->canViewRelatedProducts(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $selProd_id = FatUtility::int($selProd_id);
        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'volumeDiscount'));
            }
        }

        $srchFrm = $this->getVolumeDiscountSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->siteLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'voldiscount_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProdId]));
        }
        $this->set("canEdit", $this->userPrivilege->canEditRelatedProducts(UserAuthentication::getLoggedUserId(), true));
        $relProdFrm = $this->getRelatedProductsForm();
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("relProdFrm", $relProdFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchRelatedProducts()
    {
        $this->userPrivilege->canViewRelatedProducts(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');

        $srch = SellerProduct::searchRelatedProducts($this->siteLangId);

        if ($keyword != '') {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srch->addFld('if(related_sellerproduct_id = ' . $selProdId . ', 1 , 0) as priority');
        $srch->addOrder('priority', 'DESC');
        $srch->setPageNumber($page);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $relatedProds = $db->fetchAll($rs);
        $arrListing = array();
        foreach ($relatedProds as $key => $relatedProd) {
            $arrListing[$relatedProd['related_sellerproduct_id']][$key] = $relatedProd;
        }
        $this->set("arrListing", $arrListing);
        $this->set('canEdit', $this->userPrivilege->canEditRelatedProducts(UserAuthentication::getLoggedUserId(), true));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getRelatedProductsSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function setupRelatedProduct()
    {
        $this->userPrivilege->canEditRelatedProducts(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!UserPrivilege::canEditSellerProduct($this->userParentId, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_Select_A_Valid_Product", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($selprod_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_Valid_Product', $this->siteLangId));
            //FatApp::redirectUser($_SESSION['referer_page_url']);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $relatedProducts = (isset($post['selected_products'])) ? $post['selected_products'] : array();
        if (count($relatedProducts) < 1) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_need_to_add_atleast_one_related_product", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['selprod_id']);
        $sellerProdObj = new sellerProduct();
        if (!$sellerProdObj->addUpdateSellerRelatedProdcts($selprod_id, $relatedProducts)) {
            Message::addErrorMessage($sellerProdObj->getError());
            //FatApp::redirectUser($_SESSION['referer_page_url']);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Related_Product_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelprodRelatedProduct($selprod_id, $relprod_id)
    {
        $this->userPrivilege->canEditRelatedProducts(UserAuthentication::getLoggedUserId());
        $selprod_id = FatUtility::int($selprod_id);
        $relprod_id = FatUtility::int($relprod_id);
        if (!$selprod_id || !$relprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_RELATED_PRODUCTS, array('smt' => 'related_sellerproduct_id = ? AND related_recommend_sellerproduct_id = ?', 'vals' => array($selprod_id, $relprod_id)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getUpsellProductsList($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $srch = SellerProduct::searchUpsellProducts($this->siteLangId);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srch->addCondition(SellerProduct::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $selprod_id);
        $srch->addGroupBy('selprod_id');
        $srch->addGroupBy('upsell_sellerproduct_id');
        $srch->addOrder('selprod_id', 'DESC');
        $rs = $srch->getResultSet();
        $upsellProds = FatApp::getDb()->fetchAll($rs);
        $json = array(
            'selprodId' => $selprod_id,
            'upsellProducts' => $upsellProds
        );
        FatUtility::dieJsonSuccess($json);
    }

    private function getUpsellProductsForm()
    {
        $frm = new Form('frmUpsellSellerProduct');

        $frm->addHiddenField('', 'selprod_id', 0);
        $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'product_name', [], '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->siteLangId)));
        //$prodName = $frm->addTextBox('', 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->siteLangId)));
        $prodName->requirements()->setRequired();
        $fld1 = $frm->addSelectBox(Labels::getLabel('LBL_Buy_Together_Products', $this->siteLangId), 'products_upsell', [], '');
        // $fld1->htmlAfterField= '<div class="row"><div class="col-md-12"><ul class="list-vertical" id="upsell-products"></ul></div></div>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->siteLangId));
        return $frm;
    }

    public function upsellProducts($selProd_id = 0)
    {
        $this->userPrivilege->canViewBuyTogetherProducts(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $selProd_id = FatUtility::int($selProd_id);
        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'volumeDiscount'));
            }
        }

        $srchFrm = $this->getVolumeDiscountSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->siteLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'voldiscount_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProdId]));
        }
        $this->set("canEdit", $this->userPrivilege->canEditBuyTogetherProducts(UserAuthentication::getLoggedUserId(), true));
        $relProdFrm = $this->getUpsellProductsForm();
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("relProdFrm", $relProdFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchUpsellProducts()
    {
        $this->userPrivilege->canViewBuyTogetherProducts(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');

        $srch = SellerProduct::searchUpsellProducts($this->siteLangId);
        if ($keyword != '') {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
        }
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srch->addFld('if(upsell_sellerproduct_id = ' . $selProdId . ', 1 , 0) as priority');
        $srch->addGroupBy('selprod_id');
        $srch->addGroupBy('upsell_sellerproduct_id');
        $srch->addOrder('priority', 'DESC');
        $srch->setPageNumber($page);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $upsellProds = $db->fetchAll($rs);
        $arrListing = array();
        // CommonHelper::printArray($upsellProds); die;
        foreach ($upsellProds as $key => $upsellProd) {
            $arrListing[$upsellProd['upsell_sellerproduct_id']][$key] = $upsellProd;
        }

        $this->set("arrListing", $arrListing);
        $this->set('canEdit', $this->userPrivilege->canEditBuyTogetherProducts(UserAuthentication::getLoggedUserId(), true));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getUpsellProductsSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function setupUpsellProduct()
    {
        $this->userPrivilege->canEditBuyTogetherProducts(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!UserPrivilege::canEditSellerProduct($this->userParentId, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_Select_A_Valid_Product", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($selprod_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_Valid_Product', $this->siteLangId));
            //FatApp::redirectUser($_SESSION['referer_page_url']);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $upsellProducts = (isset($post['selected_products'])) ? $post['selected_products'] : array();
        if (count($upsellProducts) < 1) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_need_to_add_atleast_one_buy_together_product", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $sellerProdObj = new sellerProduct();
        /* saving of product Upsell Product[ */
        if (!$sellerProdObj->addUpdateSellerUpsellProducts($selprod_id, $upsellProducts)) {
            Message::addErrorMessage($sellerProdObj->getError());
            //FatApp::redirectUser($_SESSION['referer_page_url']);
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Buy_Together_Product_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelprodUpsellProduct($selprod_id, $relprod_id)
    {
        $this->userPrivilege->canEditBuyTogetherProducts(UserAuthentication::getLoggedUserId());
        $selprod_id = FatUtility::int($selprod_id);
        $relprod_id = FatUtility::int($relprod_id);
        if (!$selprod_id || !$relprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_UPSELL_PRODUCTS, array('smt' => 'upsell_sellerproduct_id = ? AND upsell_recommend_sellerproduct_id = ?', 'vals' => array($selprod_id, $relprod_id)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function isProductRewriteUrlUnique()
    {
        $selprod_id = FatApp::getPostedData('recordId', FatUtility::VAR_INT, 0);
        $urlKeyword = FatApp::getPostedData('url_keyword');
        $sellerProdObj = new SellerProduct($selprod_id);
        $seoUrl = $sellerProdObj->sanitizeSeoUrl($urlKeyword);
        if (1 > $selprod_id) {
            $isUnique = UrlRewrite::isCustomUrlUnique($seoUrl);
            if ($isUnique) {
                FatUtility::dieJsonSuccess(UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . $seoUrl);
            }
            FatUtility::dieJsonError(Labels::getLabel('MSG_NOT_AVAILABLE._PLEASE_TRY_USING_ANOTHER_KEYWORD', $this->siteLangId));
        }

        $originalUrl = $sellerProdObj->getRewriteProductOriginalUrl();
        $customUrlData = UrlRewrite::getDataByCustomUrl($seoUrl, $originalUrl);
        if (empty($customUrlData)) {
            FatUtility::dieJsonSuccess(UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . $seoUrl);
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_NOT_AVAILABLE._PLEASE_TRY_USING_ANOTHER_KEYWORD', $this->siteLangId));
    }
}
