<?php

trait CustomCatalogProducts
{
    public function customCatalogProducts()
    {
        $this->userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId());
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        if (!User::canAddCustomProductAvailableToAllSellers()) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'catalog'));
        }

        $frmSearchCustomCatalogProducts = $this->getCustomCatalogProductsSearchForm();
        $this->set('canEdit', $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true));
        $this->set("frmSearchCustomCatalogProducts", $frmSearchCustomCatalogProducts);
        $this->_template->render(true, true);
    }

    /* public function customCatalogProductForm($preqId = 0, $preqCatId = 0)
      {
      $this->canAddCustomCatalogProduct(true);
      $preqId = FatUtility::int($preqId);
      $preqCatId = FatUtility::int($preqCatId);

      if ($preqId > 0) {
      $row = ProductRequest::getAttributesById($preqId);
      if (!empty($row) && $row['preq_id'] == $preqId && $row['preq_user_id'] == $this->userParentId) {
      $preqCatId = $row['preq_prodcat_id'];
      } else {
      $preqId = 0;
      }
      }

      $this->set('preqId', $preqId);
      $this->set('preqCatId', $preqCatId);
      $this->_template->addJs('js/slick.js');
      $this->_template->addJs('js/jquery.tablednd.js');
      $this->_template->render(true, true);
      } */

    /* public function customCatalogProductCategoryForm() {
      $frm = $this->getCustomCatalogProductCategoryForm();
      $this->set('frm', $frm);
      $this->_template->render(false, false);
      } */

    /* public function customCatalogGeneralForm($preqId = 0, $prodcat_id = 0)
      {
      if (!$this->isShopActive($this->userParentId, 0, true)) {
      FatUtility::dieWithError(Labels::getLabel('MSG_Your_shop_is_inactive', $this->siteLangId));
      }

      if (!User::canAddCustomProductAvailableToAllSellers()) {
      FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
      }

      if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
      FatUtility::dieWithError(Labels::getLabel('MSG_Please_buy_subscription', $this->siteLangId));
      }

      $preqId = FatUtility::int($preqId);
      $productReqRow = array();
      $productOptions = array();
      if ($preqId) {
      $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_id','preq_user_id','preq_prodcat_id','preq_content'));
      if ($productReqRow['preq_user_id'] != $this->userParentId || $productReqRow['preq_prodcat_id'] === false) {
      FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
      }

      $prodcat_id = $productReqRow['preq_prodcat_id'];
      $productData = json_decode($productReqRow['preq_content'], true);
      unset($productReqRow['preq_content']);
      $productReqRow = array_merge($productReqRow, $productData, array('preq_prodcat_id'=>$prodcat_id));
      $productOptions = $productReqRow['product_option'];
      }


      $customProductFrm = $this->getCustomProductForm('CATALOG_PRODUCT', $prodcat_id);
      if (!empty($productReqRow)) {
      $customProductFrm->fill($productReqRow);
      }

      $this->set('languages', Language::getAllNames());
      $this->set('activeTab', 'GENERAL');
      $this->set('customProductFrm', $customProductFrm);
      $this->set('preqCatId', $prodcat_id);
      $this->set('preqId', $preqId);
      $this->set('productReqRow', $productReqRow);
      $this->set('productOptions', $productOptions);
      $this->set('includeEditor', true);
      $this->_template->addJs('js/jscolor.js');
      $this->_template->addJs('js/multi-list.js');
      $this->_template->render(false, false);
      } */

    /* public function setupCustomCatalogProduct()
      {
      $this->canAddCustomCatalogProduct();

      $post = FatApp::getPostedData();
      $product_tags = FatApp::getPostedData('product_tags');
      $product_option = FatApp::getPostedData('product_option');
      //$prodcat_id = FatApp::getPostedData('prodcat_id',FatUtility::VAR_INT,0);
      $frm = $this->getCustomProductForm('CATALOG_PRODUCT');
      $post = $frm->getFormDataFromArray($post);

      if (false === $post) {
      FatUtility::dieWithError(current($frm->getValidationErrors()));
      }

      $preq_id = FatUtility::int($post['preq_id']);
      $preq_prodcat_id = FatUtility::int($post['preq_prodcat_id']);
      $productShiping = FatApp::getPostedData('product_shipping');
      $productTaxCategory = $post['ptt_taxcat_id'];


      if ($preq_id) {
      $productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id', 'preq_status'));
      if ($productRow['preq_user_id'] != $this->userParentId || $productRow['preq_status'] != ProductRequest::STATUS_PENDING) {
      FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
      }
      }


      unset($post['preq_id']);
      unset($post['btn_submit']);

      $prodReqObj = new ProductRequest($preq_id);
      $data_to_be_save = $post;
      $data_to_be_save['product_added_by_admin_id'] = 0;
      $data_to_be_save['product_tags'] = $product_tags;
      $data_to_be_save['product_option'] = $product_option;
      $data_to_be_save['product_shipping'] = $productShiping;
      $data_to_be_save['product_seller_id'] = $this->userParentId;
      if ($post['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
      $data_to_be_save['product_length'] = 0;
      $data_to_be_save['product_width'] = 0;
      $data_to_be_save['product_height'] = 0;
      $data_to_be_save['product_dimension_unit'] = 0;
      $data_to_be_save['product_weight'] = 0;
      $data_to_be_save['product_weight_unit'] = 0;
      $data_to_be_save['product_cod_enabled'] = applicationConstants::NO;
      }

      $data = array(
      'preq_user_id' => $this->userParentId,
      'preq_prodcat_id' => $preq_prodcat_id,
      'preq_content' => FatUtility::convertToJson($data_to_be_save),
      'preq_status' => ProductRequest::STATUS_PENDING,
      'preq_added_on' => date('Y-m-d H:i:s')
      );

      $prodReqObj->assignValues($data);

      if (!$prodReqObj->save()) {
      FatUtility::dieWithError($prodReqObj->getError());
      }


      $preq_id = $prodReqObj->getMainTableRecordId();
      $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
      $this->set('preq_id', $preq_id);
      $this->_template->render(false, false, 'json-success.php');
      } */

    public function validateUpcCode()
    {
        $post = FatApp::getPostedData();
        if (empty($post) || $post['code'] == '') {
            FatUtility::dieWithError(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->siteLangId));
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_code', '=', $post['code']);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $totalRecords = FatApp::getDb()->totalRecords($rs);
        if ($totalRecords > 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_This_UPC/EAN_code_already_assigned_to_another_product', $this->siteLangId));
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customCatalogSellerProductForm($preqId = 0)
    {
        $this->userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);

        if (!$preqId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Validate product request belongs to current logged seller[ */
        $productOptions = array();

        $productReqRow = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $prodcat_id = $productReqRow['preq_prodcat_id'];
        if ($productReqRow['preq_sel_prod_data'] != '') {
            $productReqRow = array_merge($productReqRow, json_decode($productReqRow['preq_sel_prod_data'], true));
        }
        $productData = json_decode($productReqRow['preq_content'], true);
        $productOptions = $productData['product_option'];

        /* ] */

        $frmSellerProduct = $this->getSellerProductForm($preqId, 'CUSTOM_CATALOG');
        if ($preqId) {
            $frmSellerProduct->fill($productReqRow);
        }
        $this->set('frmSellerProduct', $frmSellerProduct);
        $this->set('preqId', $preqId);
        $this->set('preqCatId', $prodcat_id);
        $this->set('productOptions', $productOptions);
        $this->set('productReqRow', $productReqRow);
        $this->set('languages', Language::getAllNames());
        $this->set('activeTab', 'INVENTORY');
        $this->_template->render(false, false);
    }

    /* Specification Module [ */

    public function customCatalogSpecifications($preqId = 0, $prodspecId = 0)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);

        $productOptions = array();
        $productRow = array();
        /* Validate product request belongs to current logged seller[ */
        if ($preqId) {
            $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_prodcat_id', 'preq_content', 'preq_specifications'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productRow['preq_user_id'], $userArr)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $preqCatId = $productRow['preq_prodcat_id'];
            $productReqData = json_decode($productRow['preq_content'], true);
            $productOptions = $productReqData['product_option'];
        }
        /* ] */
        $productSpecData = json_decode($productRow['preq_specifications'], true);
        $this->set('productSpecifications', $productSpecData);
        $this->set('preqId', $preqId);
        $this->set('preqCatId', $preqCatId);
        $this->set('activeTab', 'SPECIFICATIONS');
        $this->set('productOptions', $productOptions);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function getCustomCatalogSpecificationForm($preqId, $prodspecId = 0, $divCount = 0)
    {
        $post = FatApp::getPostedData();
        $data = array();
        $data['product_id'] = $preqId;
        $data['prodspec_id'] = $prodspecId;
        $this->set('siteLangId', $this->siteLangId);
        $this->set('languages', Language::getAllNames());
        $this->set('preqId', $preqId);
        $this->set('divCount', $divCount);
        $this->_template->render(false, false);
    }

    public function setupCustomCatalogSpecification($preqId, $prodSpecId = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);

        /* Validate product request belongs to current logged seller[ */
        if ($preqId) {
            $productReqRow = ProductRequest::getAttributesById($preqId);
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productReqRow['preq_user_id'], $userArr)) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
            $prodcat_id = $productReqRow['preq_prodcat_id'];
        }
        /* ] */

        $post = FatApp::getPostedData();
        if (false === $post) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Please_fill_Specifications', $this->siteLangId));
        }

        $languages = Language::getAllNames();
        foreach ($post['prod_spec_name'][CommonHelper::getLangId()] as $specKey => $specval) {
            $count = 0;
            foreach ($languages as $langId => $langName) {
                if ($post['prod_spec_name'][$langId][$specKey] == '') {
                    $count++;
                }

                if ($count == count($languages)) {
                    foreach ($languages as $langId => $langName) {
                        unset($post['prod_spec_name'][$langId][$specKey]);
                        unset($post['prod_spec_value'][$langId][$specKey]);
                    }
                }
            }
        }

        unset($post['btn_submit']);
        unset($post['fOutMode']);
        unset($post['fIsAjax']);
        $prodReqObj = new ProductRequest($preqId);
        $data = array(
            'preq_specifications' => FatUtility::convertToJson($post)
        );

        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            FatUtility::dieWithError($prodReqObj->getError());
        }
        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $preqId = $prodReqObj->getMainTableRecordId();
        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('preqId', $preqId);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* ] */

    public function setUpCustomSellerProduct()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('selprod_product_id', FatUtility::VAR_INT, 0);
        if (!$preqId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $post = FatApp::getPostedData();
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
        $post['use_shop_policy'] = $useShopPolicy;
        $frm = $this->getSellerProductForm($preqId, 'CUSTOM_CATALOG');
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            FatUtility::dieWithError(current($frm->getValidationErrors()));
        }

        /* Validate product belongs to current logged seller[ */
        if ($preqId) {
            $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_status', 'preq_content'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!$productRow || !in_array($productRow['preq_user_id'], $userArr) || $productRow['preq_status'] != ProductRequest::STATUS_PENDING) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
            $productData = json_decode($productRow['preq_content'], true);
        }
        /* ] */

        unset($post['selprod_product_id']);
        unset($post['selprod_id']);
        unset($post['btn_cancel']);
        unset($post['btn_submit']);

        $post['selprod_cod_enabled'] = (!empty($productData['product_cod_enabled'])) ? $productData['product_cod_enabled'] : 0;
        $prodReqObj = new ProductRequest($preqId);
        $data = array(
            'preq_sel_prod_data' => FatUtility::convertToJson($post),
        );
        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            FatUtility::dieWithError($prodReqObj->getError());
        }

        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('preq_id', $preqId);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customCatalogProductLangForm($preqId = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();

        $preqId = FatUtility::int($preqId);
        $lang_id = FatUtility::int($lang_id);

        if ($preqId == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $productOptions = array();
        /* Validate product request belongs to current logged seller[ */
        if ($preqId) {
            $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_prodcat_id', 'preq_content'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productRow['preq_user_id'], $userArr)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $preqCatId = $productRow['preq_prodcat_id'];
            $productReqData = json_decode($productRow['preq_content'], true);
            $productOptions = $productReqData['product_option'];
        }
        /* ] */

        $customProductLangFrm = $this->getCustomCatalogProductLangForm($preqId, $lang_id);
        $prodObj = new ProductRequest($preqId);
        if (0 < $autoFillLangData) {
            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            $customProductLangData = $prodObj->getAttributesByLangId($siteDefaultLangId, $preqId);
        } else {
            $customProductLangData = $prodObj->getAttributesByLangId($lang_id, $preqId);
        }

        if ($customProductLangData) {
            $customProductLangData['preq_id'] = $preqId;
            $productData = json_decode($customProductLangData['preq_lang_data'], true);
            unset($customProductLangData['preq_lang_data']);
            if (0 < $autoFillLangData) {
                $updateLangDataobj = new TranslateLangData(ProductRequest::DB_TBL_LANG);
                $translatedData = $updateLangDataobj->directTranslate($productData, $lang_id);
                if (false === $translatedData) {
                    Message::addErrorMessage($updateLangDataobj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                }
                $productData = current($translatedData);
            }

            if (!empty($productData)) {
                $customProductLangData = array_merge($customProductLangData, $productData);
            }
            $customProductLangFrm->fill($customProductLangData);
        }
        $customProductLangData['preq_id'] = $preqId;

        $this->set('languages', Language::getAllNames());
        $this->set('preqId', $preqId);
        $this->set('preqCatId', $preqCatId);
        $this->set('activeTab', 'PRODUCTLANGFORM');
        $this->set('siteLangId', $this->siteLangId);
        $this->set('product_lang_id', $lang_id);
        $this->set('productOptions', $productOptions);
        $this->set('customProductLangFrm', $customProductLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setupCustomCatalogProductLangForm()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $preq_id = FatUtility::int($post['preq_id']);

        if ($preq_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        /* Validate product belongs to current logged seller[ */
        if ($preq_id) {
            $productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id', 'preq_status', 'preq_content'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productRow['preq_user_id'], $userArr) || $productRow['preq_status'] != ProductRequest::STATUS_PENDING) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */
        $productReqData = json_decode($productRow['preq_content'], true);
        $productOptions = $productReqData['product_option'];
        $frm = $this->getCustomCatalogProductLangForm($preq_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['preq_id']);
        unset($post['lang_id']);
        unset($post['btn_submit']);

        if (array_key_exists('auto_update_other_langs_data', $post)) {
            unset($post['auto_update_other_langs_data']);
        }

        $data_to_update = array(
            'preqlang_preq_id' => $preq_id,
            'preqlang_lang_id' => $lang_id,
            'preq_lang_data' => FatUtility::convertToJson($post),
        );

        $prodObj = new ProductRequest($preq_id);
        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
            FatUtility::dieWithError($prodObj->getError());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();

        foreach ($languages as $langId => $langName) {
            if (!$row = ProductRequest::getAttributesByLangId($langId, $preq_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('preq_id', $preq_id);
        $this->set('lang_id', $newTabLangId);
        $this->set('productOptions', $productOptions);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getCustomCatalogProductLangForm($preqId, $langId)
    {
        $siteLangId = $this->siteLangId;
        $frm = new Form('frmCustomProductLang');
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $this->siteLangId), 'product_name');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Seller_Product_Title', $this->siteLangId), 'selprod_title');
        $fld->htmlAfterField = "<small class='text--small'>" . Labels::getLabel('LBL_This_product_title_will_be_displayed_on_the_site', $this->siteLangId) . '</small>';
        $frm->addTextBox(Labels::getLabel('LBL_Any_extra_comment_for_buyer', $this->siteLangId), 'selprod_comments');
        $frm->addTextBox(Labels::getLabel('LBL_YouTube_Video', $this->siteLangId), 'product_youtube_video');
        //$frm->addHtmlEditor(Labels::getLabel('LBL_Description',$this->siteLangId),'product_description');
        $frm->addTextarea(Labels::getLabel('LBL_Description', $this->siteLangId), 'product_description');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $siteLangId));
        return $frm;
    }

    public function customCatalogProductImages($preqId)
    {
        $this->userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $imagesFrm = $this->getCustomProductImagesFrm($preqId, $this->siteLangId);
        $this->set('imagesFrm', $imagesFrm);
        $this->set('preqId', $preqId);
        $this->set('productType', $preqContentData['product_type']);
        $this->_template->render(false, false);
    }

    public function deleteCustomCatalogProductImage($preq_id, $image_id)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preq_id = FatUtility::int($preq_id);
        $image_id = FatUtility::int($image_id);
        if (!$image_id || !$preq_id) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request!", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* Validate product belongs to current logged seller[ */
        $productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id'));
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productRow['preq_user_id'], $userArr)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */

        $preqObj = new ProductRequest();
        if (!$preqObj->deleteProductImage($preq_id, $image_id)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        Message::addMessage(Labels::getLabel('LBL_Image_removed_successfully.', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function customCatalogImages($preq_id, $option_id = 0, $lang_id = 0)
    {
        $this->userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preq_id = FatUtility::int($preq_id);

        if (!$preq_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (!$productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id'))) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $lang_id, false, 0, 0, true);
        $imgTypesArr = $this->getSeparateImageOptionsOfCustomProduct($preq_id, $this->siteLangId);

        $this->set('images', $product_images);
        $this->set('preq_id', $preq_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setCustomCatalogProductImagesOrder()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();

        $preqObj = new ProductRequest();
        $post = FatApp::getPostedData();
        $preq_id = FatUtility::int($post['preq_id']);
        /* Validate product belongs to current logged seller[ */
        $productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id'));
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */
        $imageIds = explode('-', $post['ids']);
        $count = 1;
        foreach ($imageIds as $row) {
            $order[$count] = $row;
            $count++;
        }

        if (!$preqObj->updateProdImagesOrder($preq_id, $order)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_Ordered_Successfully!", $this->siteLangId));
    }

    public function setupCustomCatalogProductImages()
    {
        if (!$this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Unauthorized_Access!', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->canAddCustomCatalogProduct();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
        }
        $preq_id = FatUtility::int($post['preq_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);


        /* Validate product belongs to current logged seller[ */
        if ($preq_id) {
            $productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productRow['preq_user_id'], $userArr)) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_select_a_file", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function customCategoryListing()
    {
        $post = FatApp::getPostedData();
        $prodCatId = $post['prodCatId'];
        $blockCount = $post['blockCount'];
        //$prodCatId = FatUtility::convertToType($prodCatId,FATUtility::VAR_INT);
        $srch = ProductCategory::getSearchObject(true, $this->siteLangId, true);
        $srch->addOrder('m.prodcat_active', 'DESC');
        $srch->addMultipleFields(array('m.prodcat_id', 'IFNULL(pc_l.prodcat_name,m.prodcat_identifier) as prodcat_name'));
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_parent', '=', $prodCatId);
        $srch->addOrder('prodcat_name');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $listing = $db->fetchAll($rs);

        $result = array();
        $result['prodcat_id'] = $prodCatId;
        $str = "<div class='slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12 slider-item-js categoryblock-js' rel=" . $blockCount . " id='categoryblock" . $blockCount . "' ><div class='box-border box-categories' data-simplebar>";
        //$result['msg'] = Labels::getLabel('MSG_Loaded_successfully',$this->siteLangId);
        if (!empty($listing)) {
            $str .= "<ul>";
            foreach ($listing as $category) {
                $arrow = "";
                if ($category['child_count'] > 0) {
                    //$arrow = "<i class='fa  fa-long-arrow-right'></i>";
                    $arrow = ' (' . $category['child_count'] . ')';
                }
                $str .= "<li onClick='customCategoryListing(" . $category['prodcat_id'] . ",this)'><a class='selectCategory' href='javascript:void(0)'>" . strip_tags($category['prodcat_name']) . $arrow . "</a></li>";
            }
            $str .= "</ul>";
            //$result['msg'] = Labels::getLabel('MSG_updated_successfully',$this->siteLangId);
        } else {
            $srch = ProductCategory::getSearchObject(false, $this->siteLangId, true);
            $srch->addOrder('m.prodcat_active', 'DESC');
            $srch->addMultipleFields(array('m.prodcat_id', 'IFNULL(pc_l.prodcat_name,m.prodcat_identifier) as prodcat_name'));
            $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
            $srch->addCondition('m.prodcat_id', '=', $prodCatId);
            $db = FatApp::getDb();
            $rs = $srch->getResultSet();
            $category = $db->fetch($rs);
            $str .= "<ul><li>" . strip_tags($category['prodcat_name']) . " <a href='javascript:void(0)' onClick='customCatalogProductForm(0," . $category['prodcat_id'] . ")' ></a></li><li class='align--center'><a onClick='customCatalogProductForm(0," . $category['prodcat_id'] . ")' class='btn btn-brand'>" . Labels::getLabel('LBL_Select', $this->siteLangId) . "</a></li>";
            $str .= "</ul>";
            //$result['msg'] = Labels::getLabel('MSG_updated_successfully',$this->siteLangId);
        }
        $str .= "</div></div>";

        $emptyBlock = '';
        for ($i = $blockCount + 1; $i <= 3; $i++) {
            $str .= "<div class='slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12 slider-item-js categoryblock-js' id='categoryblock" . $blockCount . "' ><div class='box-border box-categories ' data-simplebar></div></div>";
        }

        $result['structure'] = $str;
        echo FatUtility::dieJsonSuccess($result);
        exit;
    }

    private function getSubCatRecordCount($rootCategories, &$childCountArr, $keyword)
    {
        foreach ($rootCategories as $catId => $category) {
            $childCountArr[$catId]['total_child_count'] = 0;

            $id = ltrim($category['prodrootcat_code'], 0);
            $childCount = count($category['children']);

            if ($childCount > 0) {
                if (strpos($category['prodcat_name'], $keyword) !== false && $id != $catId) {
                    $childCountArr[$id]['total_child_count'] += 1;
                }
                $this->getSubCatRecordCount($category['children'], $childCountArr, $keyword);
            } else {
                $childCountArr[$id]['total_child_count'] += 1;
            }
        }
    }

    public function searchCategory($prodRootCatCode = false)
    {
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $prodCatObj = new ProductCategory();

        $rootCategories = ProductCategory::getTreeArr($this->siteLangId, 0, false, false, false, $keyword);
        /* $rootCategories = $prodCatObj->getProdRootCategoriesWithKeyword($this->siteLangId, $keyword, false, false, true);
         */
        $childCountArr = array();
        $this->getSubCatRecordCount($rootCategories, $childCountArr, $keyword);

        $childCategories = array();

        if (!empty($rootCategories)) {
            if ($prodRootCatCode == '') {
                $arr = current($rootCategories);
                $prodRootCatCode = $arr['prodrootcat_code'];
            }
            $childCategories = $prodCatObj->getProdRootCategoriesWithKeyword($this->siteLangId, $keyword, true, $prodRootCatCode);
        }

        $this->set('rootCategories', $rootCategories);
        $this->set('childCountArr', $childCountArr);
        $this->set('childCategories', $childCategories);
        $this->set('prodRootCatCode', $prodRootCatCode);
        $this->set('keyword', $keyword);
        // $html = $this->_template->render( false, false, 'seller/search-category.php', true);
        $this->_template->render(false, false, 'seller/search-category.php', false, true);
        // FatUtility::dieJsonSuccess($html);
    }

    public function loadCustomProductTags()
    {
        $this->canAddCustomCatalogProduct();
        $post = FatApp::getPostedData();
        if (empty($post['tags'])) {
            return false;
        }

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
            Tag::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->siteLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));
        $srch->addCondition('tag_id', 'IN', $post['tags']);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $tags = $db->fetchAll($rs, 'tag_id');
        $li = '';
        foreach ($tags as $key => $tag) {
            $li .= '<li id="product-tag' . $tag['tag_id'] . '"> <i class="remove_tag-js remove_param fa fa-trash"></i> ';
            $li .= $tag['tag_name'] . ' (' . $tag['tag_identifier'] . ')' . '<input type="hidden" value="' . $tag['tag_id'] . '"  name="product_tags[]"></li>';
        }

        echo $li;
        exit;
    }

    public function loadCustomProductOptionss()
    {
        $this->canAddCustomCatalogProduct();
        $post = FatApp::getPostedData();
        if (empty($post['options'])) {
            return false;
        }

        $srch = Option::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array('option_id, option_name, option_identifier'));
        $srch->addCondition('option_id', 'IN', $post['options']);
        $srch->addOrder('option_identifier');

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $tags = $db->fetchAll($rs, 'option_id');
        $li = '';
        foreach ($tags as $key => $tag) {
            $li .= '<li id="product-option' . $tag['option_id'] . '"> <i class="remove_option-js remove_param fa fa-trash"></i> ';
            $li .= $tag['option_name'] . ' (' . $tag['option_identifier'] . ')' . '<input type="hidden" value="' . $tag['option_id'] . '"  name="product_option[]"></li>';
        }

        echo $li;
        exit;
    }

    public function getCustomCatalogShippingTab()
    {
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $preq_id = $post['preq_id'];
        $this->set('siteLangId', $this->siteLangId);
        $shipping_rates = array();
        $shipping_rates = ProductRequest::getProductShippingRates($preq_id, $this->siteLangId, 0, $userId);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('preq_id', $preq_id);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false);
    }

    public function approveCustomCatalogProducts($preqId = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct(true);
        $preqId = FatUtility::int($preqId);
        if (!$preqId) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerRequests'));
        }

        if (!$productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'))) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerRequests'));
        }

        $content = (!empty($productRow['preq_content'])) ? json_decode($productRow['preq_content'], true) : array();

        $prodReqObj = new ProductRequest($preqId);
        $data = array(
            'preq_submitted_for_approval' => applicationConstants::YES,
            'preq_requested_on' => date('Y-m-d H:i:s'),
        );
        $prodReqObj->assignValues($data);
        if (!$prodReqObj->save()) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'SellerRequests'));
        }

        $mailData = array(
            'request_title' => $content['product_identifier'],
            'brand_name' => (!empty($content['brand_name'])) ? $content['brand_name'] : '',
            'product_model' => (!empty($content['product_model'])) ? $content['product_model'] : '',
        );

        $email = new EmailHandler();
        if (!$email->sendNewCustomCatalogNotification($this->siteLangId, $mailData)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Email_could_not_be_sent', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerRequests'));
        }

        /* send notification to admin [ */
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_CATALOG,
            'notification_record_id' => $preqId,
            'notification_user_id' => $this->userParentId,
            'notification_label_key' => Notification::NEW_CUSTOM_CATALOG_REQUEST_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        Message::addMessage(Labels::getLabel('MSG_Your_catalog_request_submitted_for_approval', $this->siteLangId));
        FatApp::redirectUser(UrlHelper::generateUrl('SellerRequests'));
    }

    /* private function getCustomCatalogProductCategoryForm() {
      $frm = new Form('frmCustomCatalogProductCategoryForm');
      $frm->addTextBox('', 'keyword');
      $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
      $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearCategorySearch();'));
      return $frm;
      } */

    private function getCustomCatalogProductsSearchForm()
    {
        $frm = new Form('frmSearchCustomCatalogProducts');
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getCustomProductImagesFrm($preq_id = 0, $lang_id = 0)
    {
        $imgTypesArr = $this->getSeparateImageOptionsOfCustomProduct($preq_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->siteLangId), 'option_id', $imgTypesArr, 0, array('class' => 'option'), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->siteLangId)) + $languagesAssocArr, '', array('class' => 'language'), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->siteLangId), 'prod_image', array('id' => 'prod_image'));
        $fldImg->htmlBeforeField = '<div class="filefield">';
        $fldImg->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500', $this->siteLangId) . '</span>';
        $frm->addHiddenField('', 'min_width', 500);
        $frm->addHiddenField('', 'min_height', 500);
        $frm->addHiddenField('', 'preq_id', $preq_id);
        return $frm;
    }

    private function getSeparateImageOptionsOfCustomProduct($preq_id = 0, $lang_id = 0)
    {
        $preq_id = FatUtility::int($preq_id);
        $imgTypesArr = array(0 => Labels::getLabel('LBL_For_All_Options', $this->siteLangId));
        if ($preq_id) {
            $reqData = ProductRequest::getAttributesById($preq_id, array('preq_content'));
            if (!empty($reqData)) {
                $reqData = json_decode($reqData['preq_content'], true);
            }
            $productOptions = isset($reqData['product_option']) ? $reqData['product_option'] : array();
            if (!empty($productOptions)) {
                foreach ($productOptions as $optionId) {
                    $optionData = Option::getAttributesById($optionId, array('option_is_separate_images'));

                    if (!$optionData || !$optionData['option_is_separate_images']) {
                        continue;
                    }

                    $optionValues = Product::getOptionValues($optionId, $lang_id);
                    if (!empty($optionValues)) {
                        foreach ($optionValues as $k => $v) {
                            $imgTypesArr[$k] = $v;
                        }
                    }
                }
            }
        }
        return $imgTypesArr;
    }

    private function canAddCustomCatalogProduct($redirect = false)
    {
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            if ($redirect) {
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
            }
            FatUtility::dieWithError(Labels::getLabel('MSG_Your_shop_is_inactive', $this->siteLangId));
        }

        if (!User::canAddCustomProductAvailableToAllSellers()) {
            if ($redirect) {
                Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
            }
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            if ($redirect) {
                Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'catalog'));
            }
            FatUtility::dieWithError(Labels::getLabel('MSG_Please_buy_subscription', $this->siteLangId));
        }
    }

    public function customCatalogProductForm($preqId = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct(true);
        $preqId = FatUtility::int($preqId);
        $productType = 0;
        if (0 < $preqId) {
            $productReqContent = ProductRequest::getAttributesById($preqId, 'preq_content');
            if (!empty($productReqContent)) {
                $productData = json_decode($productReqContent, true);
                $productType = array_key_exists('product_type', $productData) ? $productData['product_type'] : 0;
            }
        }
        $this->set('productType', $productType);
        $this->set('preqId', $preqId);
        $this->_template->addJs(array('js/tagify.min.js', 'js/tagify.polyfills.min.js', 'js/cropper.js', 'js/cropper-main.js'));

        $this->set('includeEditor', true);
        $this->_template->render();
    }

    public function customCatalogGeneralForm($preqId)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $customProductFrm = $this->getCustomProductIntialSetUpFrm(0, $preqId);
        $languages = Language::getAllNames();
        if ($preqId > 0) {
            $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_prodcat_id', 'preq_content'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productReqRow['preq_user_id'], $userArr)) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }

            $prodcatId = $productReqRow['preq_prodcat_id'];
            $prodcatId = FatUtility::int($prodcatId);
            $productData = json_decode($productReqRow['preq_content'], true);
            unset($productReqRow['preq_content']);
            $productReqRow = array_merge($productReqRow, $productData, array('preq_prodcat_id' => $prodcatId));
            $productReqRow['ptc_prodcat_id'] = $prodcatId;
            $prodCat = new ProductCategory();
            $selectedCatName = $prodCat->getParentTreeStructure($prodcatId, 0, '', $this->siteLangId);
            $productReqRow['category_name'] = html_entity_decode($selectedCatName);

            $langData = array();
            foreach ($languages as $langId => $data) {
                $prodReq = new ProductRequest($preqId);
                $customProductLangData = $prodReq->getAttributesByLangId($langId, $preqId);
                if (is_array($customProductLangData)) {
                    $langContent = json_decode($customProductLangData['preq_lang_data'], true);
                    $langData['product_name'][$langId] = $langContent['product_name'];
                    $langData['product_youtube_video'][$langId] = $langContent['product_youtube_video'];
                    //$langData['product_description'][$langId] = $langContent['product_description'];
                    $langData['product_description_' . $langId] = $langContent['product_description'];
                }
            }
            $productReqRow = array_merge($productReqRow, $langData);
            $customProductFrm->fill($productReqRow);
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        unset($languages[$siteDefaultLangId]);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->set('productFrm', $customProductFrm);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function setupCustomCatalogProduct()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $frm = $this->getCustomProductIntialSetUpFrm(0, $preqId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($post['product_brand_id'] < 1 && FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Brand_From_List', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($post['ptc_prodcat_id'] < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Category_From_List', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = array();
        if ($preqId > 0) {
            $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_status', 'preq_content'));
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
            if (!in_array($productRow['preq_user_id'], $userArr) || $productRow['preq_status'] != ProductRequest::STATUS_PENDING) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $prodContent = json_decode($productRow['preq_content'], true);
        }

        $preqProdCatId = FatUtility::int($post['ptc_prodcat_id']);
        $autoUpdateOtherLangsData = isset($post['auto_update_other_langs_data']) ? FatUtility::int($post['auto_update_other_langs_data']) : 0;
        $prodName = $post['product_name'];
        $prodYouTubeUrl = $post['product_youtube_video'];
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $data) {
            $prodDesc[$langId] = $post['product_description_' . $langId];
            unset($post['product_description_' . $langId]);
        }
        unset($post['preq_id']);
        unset($post['ptc_prodcat_id']);
        unset($post['product_name']);
        unset($post['product_youtube_video']);
        unset($post['btn_submit']);
        unset($post['auto_update_other_langs_data']);

        $dataForSave = array_merge($prodContent, $post);
        $dataForSave['preq_prodcat_id'] = $preqProdCatId;
        $dataForSave['product_added_by_admin_id'] = 0;
        $dataForSave['product_seller_id'] = ($this->userParentId > 0) ? $this->userParentId : UserAuthentication::getLoggedUserId();
        if ($post['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $dataForSave['product_length'] = 0;
            $dataForSave['product_width'] = 0;
            $dataForSave['product_height'] = 0;
            $dataForSave['product_dimension_unit'] = 0;
            $dataForSave['product_weight'] = 0;
            $dataForSave['product_weight_unit'] = 0;
            $dataForSave['product_cod_enabled'] = applicationConstants::NO;
        }

        $data = array(
            'preq_user_id' => UserAuthentication::getLoggedUserId(),
            'preq_prodcat_id' => $preqProdCatId,
            'preq_content' => FatUtility::convertToJson($dataForSave),
            'preq_status' => ProductRequest::STATUS_PENDING,
            'preq_added_on' => date('Y-m-d H:i:s')
        );
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        if (!$prodReq->saveProductRequestLangData($siteDefaultLangId, $autoUpdateOtherLangsData, $prodName, $prodDesc, $prodYouTubeUrl)) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->set('productType', $post['product_type']);
        $this->set('productTypeDigital', Product::PRODUCT_TYPE_DIGITAL);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productAttributeAndSpecifications($preqId)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $productFrm = $this->getProductAttributeAndSpecificationsFrm(0, $preqId);
        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $productFrm->fill($preqContentData);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);
        $this->set('productFrm', $productFrm);
        $this->set('productType', $preqContentData['product_type']);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false, 'seller/catalog-attribute-and-specifications-frm.php');
    }

    public function setUpCatalogProductAttributes()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $frm = $this->getProductAttributeAndSpecificationsFrm(0, $preqId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);

        if (!in_array($productData['preq_user_id'], $userArr) || $productData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        unset($post['preq_id']);
        unset($post['btn_submit']);
        $prodContent = json_decode($productData['preq_content'], true);
        $data['preq_content'] = FatUtility::convertToJson(array_merge($prodContent, $post));
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Attributes_Setup_Successful', $this->siteLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    public function catalogProdSpecForm($preqId)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        if ($preqId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqRow = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $prodSpecData = array();
        if ($key >= 0) {
            $specifications = json_decode($productReqRow['preq_specifications'], true);
            $prodSpecData['prod_spec_name'] = $specifications['prod_spec_name'][$langId][$key];
            $prodSpecData['prod_spec_value'] = $specifications['prod_spec_value'][$langId][$key];
            $prodSpecData['prod_spec_group'] = isset($specifications['prod_spec_group'][$langId][$key]) ? $specifications['prod_spec_group'][$langId][$key] : '';
            $prodSpecData['key'] = $key;
        }
        $this->set('langId', $langId);
        $this->set('prodSpecData', $prodSpecData);
        $this->_template->render(false, false, 'seller/custom-catalog-prod-spec-form.php');
    }

    public function catalogSpecificationsByLangId()
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        if ($preqId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqRow = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $productSpecifications = array();
        $specifications = json_decode($productReqRow['preq_specifications'], true);
        if (!empty($specifications['prod_spec_name'][$langId]) && !empty($specifications['prod_spec_value'][$langId])) {
            $productSpecifications['prod_spec_name'] = $specifications['prod_spec_name'][$langId];
            $productSpecifications['prod_spec_value'] = $specifications['prod_spec_value'][$langId];
            $productSpecifications['prod_spec_group'] = isset($specifications['prod_spec_group'][$langId]) ? $specifications['prod_spec_group'][$langId] : [];
        }
        $this->set('productSpecifications', $productSpecifications);
        $this->set('langId', $langId);
        $this->_template->render(false, false, 'seller/catalog-specifications.php');
    }

    public function deleteCustomCatalogSpecification($preqId)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        if ($langId < 1 || $key < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodReqSpecification = json_decode($prodReqData['preq_specifications'], true);
        unset($prodReqSpecification['prod_spec_name'][$langId][$key]);
        unset($prodReqSpecification['prod_spec_value'][$langId][$key]);
        unset($prodReqSpecification['prod_spec_group'][$langId][$key]);
        $prodReqSpecification['prod_spec_name'][$langId] = array_values($prodReqSpecification['prod_spec_name'][$langId]);
        $prodReqSpecification['prod_spec_value'][$langId] = array_values($prodReqSpecification['prod_spec_value'][$langId]);
        $prodReqSpecification['prod_spec_group'][$langId] = array_values($prodReqSpecification['prod_spec_group'][$langId]);

        $data['preq_specifications'] = FatUtility::convertToJson($prodReqSpecification);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Specification_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setUpCustomCatalogSpecifications()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        $prodSpecName = FatApp::getPostedData('prodspec_name', FatUtility::VAR_STRING, '');
        $prodSpecValue = FatApp::getPostedData('prodspec_value', FatUtility::VAR_STRING, '');
        $prodSpecGroup = FatApp::getPostedData('prodspec_group', FatUtility::VAR_STRING, '');
        if ($langId < 1 || empty($prodSpecName) || empty($prodSpecValue)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodReqSpecification = json_decode($prodReqData['preq_specifications'], true);
        if ($key >= 0) {
            $prodReqSpecification['prod_spec_name'][$langId][$key] = $prodSpecName;
            $prodReqSpecification['prod_spec_value'][$langId][$key] = $prodSpecValue;
            $prodReqSpecification['prod_spec_group'][$langId][$key] = $prodSpecGroup;
        } else {
            $prodReqSpecification['prod_spec_name'][$langId][] = $prodSpecName;
            $prodReqSpecification['prod_spec_value'][$langId][] = $prodSpecValue;
            $prodReqSpecification['prod_spec_group'][$langId][] = $prodSpecGroup;
        }

        $data['preq_specifications'] = FatUtility::convertToJson($prodReqSpecification);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Specification_updated_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customCatalogShippingFrm($preqId)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $productFrm = $this->getProductShippingFrm(0, $preqId, true);
        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $productFrm->fill($preqContentData);

        $this->set('productFrm', $productFrm);
        $this->set('productType', $preqContentData['product_type']);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false, 'seller/custom-catalog-shipping-frm.php');
    }

    public function setUpCustomCatalogShipping()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $frm = $this->getProductShippingFrm(0, $preqId, true);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqData['preq_user_id'], $userArr) || $productReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $post['ps_from_country_id'] = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
        }

        unset($post['preq_id']);
        unset($post['product_id']);
        unset($post['btn_submit']);
        $prodContent = json_decode($productReqData['preq_content'], true);
        $prodContent = array_merge($prodContent, $post);
        /* $productShiping = FatApp::getPostedData('product_shipping');
        if (!empty($productShiping)) {
            $prodContent['product_shipping'] = $productShiping;
        } */
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Shipping_Setup_Successful', $this->siteLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customCatalogOptionsAndTag($preqId)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $productOptions = array();
        if (!empty($preqContentData['product_option'])) {
            $srch = Option::getSearchObject($this->siteLangId);
            $srch->addMultipleFields(array('option_id, option_name, option_identifier'));
            $srch->addCondition('option_id', 'IN', $preqContentData['product_option']);
            $srch->addOrder('option_identifier');
            $rs = $srch->getResultSet();
            $productOptions = FatApp::getDb()->fetchAll($rs);
        }
        $productTags = array();
        if (!empty($preqContentData['product_tags'])) {
            $srch = Tag::getSearchObject();
            $srch->addOrder('tag_identifier');
            $srch->joinTable(
                Tag::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->siteLangId
            );
            $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));
            $srch->addCondition('tag_id', 'IN', $preqContentData['product_tags']);

            $rs = $srch->getResultSet();
            $productTags = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('productOptions', $productOptions);
        $this->set('productTags', $productTags);
        $this->set('preqId', $preqId);
        $this->set('productType', $preqContentData['product_type']);
        $this->_template->render(false, false, 'seller/custom-catalog-options-and-tag.php');
    }

    public function updateCustomCatalogOption()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $optionId = FatApp::getPostedData('option_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if ($preqId < 1 || $optionId < 0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);

        $separateImageOptionAdded = false;
        if (!empty($prodContent['product_option'])) {
            foreach ($prodContent['product_option'] as $option) {
                $optionWithImage = Option::getAttributesById($option, 'option_is_separate_images');
                if ($optionWithImage == 1) {
                    $separateImageOptionAdded = true;
                    break;
                }
            }
        }
        $optionSeparateImage = Option::getAttributesById($optionId, 'option_is_separate_images');
        if ($separateImageOptionAdded == true && $optionSeparateImage == 1) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_you_have_already_added_option_having_separate_image', $this->siteLangId));
        }


        $prodContent['product_option'][] = $optionId;
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            FatUtility::dieJsonError($prodReq->getError());
        }
        $this->set('msg', Labels::getLabel('LBL_Option_updated_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCustomCatalogOption()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $optionId = FatApp::getPostedData('option_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($preqId < 1 || $optionId < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);
        $key = array_search($optionId, $prodContent['product_option']);
        unset($prodContent['product_option'][$key]);
        $prodContent['product_option'] = array_values($prodContent['product_option']);
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Option_removed_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateCustomCatalogTag()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $tagId = FatApp::getPostedData('tag_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($preqId < 1 || $tagId < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);
        $prodContent['product_tags'][] = $tagId;
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Tag_updated_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCustomCatalogTag()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $tagId = FatApp::getPostedData('tag_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($preqId < 1 || $tagId < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);
        $key = array_search($tagId, $prodContent['product_tags']);
        unset($prodContent['product_tags'][$key]);
        $prodContent['product_tags'] = array_values($prodContent['product_tags']);
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Tag_removed_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customEanUpcForm($preqId)
    {
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($productReqRow['preq_user_id'], $userArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $upcCodeData = array();
        if (!empty($productReqRow['preq_ean_upc_code'])) {
            $upcCodeData = json_decode($productReqRow['preq_ean_upc_code'], true);
        }
        $optionCombinations = array();
        $productOptions = ProductRequest::getProductReqOptions($preqId, $this->siteLangId, true);
        if (!empty($productOptions)) {
            $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '|');
        }
        $this->set('upcCodeData', $upcCodeData);
        $this->set('optionCombinations', $optionCombinations);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function setupEanUpcCode($preqId)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $this->canAddCustomCatalogProduct();
        $preqId = FatUtility::int($preqId);
        $prodReqData = ProductRequest::getAttributesById($preqId);
        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        if (!in_array($prodReqData['preq_user_id'], $userArr) || $prodReqData['preq_status'] != ProductRequest::STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $optionValueId = FatApp::getPostedData('optionValueId', FatUtility::VAR_INT, 0);
        if ($optionValueId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $code = FatApp::getPostedData('code', FatUtility::VAR_STRING, '');
        if (empty($code)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $productUpcData = array();
        if (!empty($prodReqData['preq_ean_upc_code'])) {
            $productUpcData = json_decode($prodReqData['preq_ean_upc_code'], true);
        }
        $productUpcData[$optionValueId] = $code;
        $data['preq_ean_upc_code'] = FatUtility::convertToJson($productUpcData);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_ean/upc_code_added_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productRequestApprovalButton($preqId)
    {
        $preqId = FatUtility::int($preqId);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }
}
