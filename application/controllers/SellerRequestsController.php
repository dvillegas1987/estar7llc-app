<?php
class SellerRequestsController extends SellerBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId());
    }

    public function index()
    {
        $this->set('canEdit', $this->userPrivilege->canEditSellerRequests(0, true));

        $srch = $this->getRequestedbrandObj();
        $rs = $srch->getResultSet();
        $reqBrands = FatApp::getDb()->fetchAll($rs);

        $srch = $this->getRequestedCatObj();
        $rs = $srch->getResultSet();
        $reqCategories = FatApp::getDb()->fetchAll($rs);

        $srch = $this->getRequestedProdObj();
        $rs = $srch->getResultSet();
        $reqProducts = FatApp::getDb()->fetchAll($rs);

        $noRecordFound = false;
        if (empty($reqBrands) && empty($reqCategories) && empty($reqProducts)) {
            $noRecordFound = true;
        }

        $this->set('noRecordFound', $noRecordFound);
        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js'));
        $this->_template->render();
    }

    public function searchBrandRequests()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = $this->getRequestedbrandObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $requestedBrands = FatApp::getDb()->fetchAll($rs);

        $this->set('canEdit', $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true));
        $this->set("arr_listing", $requestedBrands);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('statusArr', Brand::getBrandReqStatusArr($this->siteLangId));
        $this->set('statusClassArr', Brand::getBrandReqStatusClassArr());
        $this->_template->render(false, false);
    }

    public function searchProdCategoryRequests()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = $this->getRequestedCatObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $requestedCategories = FatApp::getDb()->fetchAll($rs);

        $this->set('canEdit', $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true));
        $this->set("arr_listing", $requestedCategories);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('statusArr', ProductCategory::getStatusArr($this->siteLangId));
        $this->set('statusClassArr', ProductCategory::getStatusClassArr());
        $this->_template->render(false, false);
    }

    public function searchCustomCatalogProducts()
    {
        // $this->canAddCustomCatalogProduct();
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = $this->getRequestedProdObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $arr_listing = FatApp::getDb()->fetchAll($rs);

        foreach ($arr_listing as $key => $row) {
            $content = (!empty($row['preq_content'])) ? json_decode($row['preq_content'], true) : array();
            $langContent = (!empty($row['preq_lang_data'])) ? json_decode($row['preq_lang_data'], true) : array();

            $row = array_merge($row, $content);
            if (!empty($langContent)) {
                $row = array_merge($row, $langContent);
            }

            $arr = array(
                'preq_id' => $row['preq_id'],
                'preq_user_id' => $row['preq_user_id'],
                'preq_added_on' => $row['preq_added_on'],
                'preq_requested_on' => isset($row['preq_requested_on']) ?? $row['preq_requested_on'],
                'preq_status_updated_on' => isset($row['preq_status_updated_on']) ?? $row['preq_status_updated_on'],
                'preq_status' => $row['preq_status'],
                'product_identifier' => $row['product_identifier'],
                'product_name' => (!empty($row['product_name'])) ? $row['product_name'] : '',
            );
            $arr_listing[$key] = $arr;
        }
        $this->set('canEdit', $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true));
        $this->set("arr_listing", $arr_listing);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('statusArr', ProductRequest::getStatusArr($this->siteLangId));
        $this->set('statusClassArr', ProductRequest::getStatusClassArr());
        $this->set('CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL', FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1));
        $this->_template->render(false, false);
    }

    private function getRequestedbrandObj()
    {
        $srch = Brand::getSearchObject($this->siteLangId);

        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        $srch->addCondition('brand_seller_id', 'in', $userArr);
        $srch->addCondition('brand_deleted', '=', applicationConstants::NO);

        $srch->addOrder('brand_updated_on', 'DESC');

        return $srch;
    }

    private function getRequestedCatObj()
    {
        $srch = ProductCategory::getSearchObject(false, $this->siteLangId, false, -1);
        $srch->addOrder('m.prodcat_active', 'DESC');

        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        $srch->addCondition('prodcat_seller_id', 'in', $userArr);
        $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);

        $srch->addOrder('prodcat_updated_on', 'DESC');

        return $srch;
    }

    private function getRequestedProdObj()
    {
        $srch = ProductRequest::getSearchObject($this->siteLangId);

        $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $this->userParentId);
        $srch->addCondition('preq_user_id', 'in', $userArr);
        $srch->addCondition('preq_deleted', '=', applicationConstants::NO);

        $srch->addOrder('preq_added_on', 'DESC');
        return $srch;
    }

    /* ------Product Category Request [------*/

    public function categoryReqForm($categoryReqId = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true);
        $frm = $this->getCategoryForm();
        $this->set('languages', Language::getAllNames());
        if (0 < $categoryReqId) {
            $data = ProductCategory::getAttributesById($categoryReqId, array('prodcat_id', 'prodcat_identifier', 'prodcat_parent'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $langData = ProductCategory::getLangDataArr($categoryReqId, array(ProductCategory::DB_TBL_LANG_PREFIX . 'lang_id', ProductCategory::DB_TBL_PREFIX . 'name'));
            $catnameArr = array();
            foreach ($langData as $value) {
                $catnameArr[ProductCategory::DB_TBL_PREFIX . 'name'][$value[ProductCategory::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[ProductCategory::DB_TBL_PREFIX . 'name'];
            }

            $data = array_merge($data, $catnameArr);
            $frm->fill($data);
            $frm->fill($data);
        }
        $this->set('frm', $frm);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        $auto_update_other_langs_data = false;
        if (!empty($translatorSubscriptionKey) && count($langData) > 0) {
            $auto_update_other_langs_data = true;
        }
        $this->set('auto_update_other_langs_data', $auto_update_other_langs_data);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('categoryReqId', $categoryReqId);
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    private function getCategoryForm($prodCatId = 0)
    {

        $prodCatId = FatUtility::int($prodCatId);
        $frm = new Form('frmCategoryReq', array('id' => 'frmCategoryReq'));
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Name', $this->siteLangId), 'prodcat_name[' . $siteDefaultLangId . ']');
        $prodCat = new ProductCategory();
        $categoriesArr = $prodCat->getCategoriesForSelectBox($this->siteLangId, $prodCatId);
        $categories = array(0 => Labels::getLabel('LBL_Root_Category', $this->siteLangId)) + $prodCat->makeAssociativeArray($categoriesArr);
        $frm->addSelectBox(Labels::getLabel('LBL_Parent_Category', $this->siteLangId), 'prodcat_parent', $categories, '', array(), '');

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($langData) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        foreach ($langData as $langId => $data) {
            $frm->addTextBox(Labels::getLabel('LBL_Category_Name', $this->siteLangId), 'prodcat_name[' . $langId . ']');
        }

        $frm->addHiddenField('', 'prodcat_id', $prodCatId);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    public function setupCategoryReq()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true);
        $post = FatApp::getPostedData();
        $frm = $this->getCategoryForm();
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $categoryReqId = $post['prodcat_id'];

        if ($categoryReqId > 0 && !UserPrivilege::canSellerUpdateCategoryRequest(UserAuthentication::getLoggedUserId(), $categoryReqId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $approvalRequired = FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0);
        if (!$approvalRequired) {
            $post['prodcat_active'] = applicationConstants::ACTIVE;
            $post['prodcat_status'] = ProductCategory::REQUEST_APPROVED;
            $post['prodcat_status_updated_on'] = date('Y-m-d H:i:s');
        } else {
            $post['prodcat_active'] = applicationConstants::INACTIVE;
            $post['prodcat_status'] = ProductCategory::REQUEST_PENDING;
        }

        $post['prodcat_requested_on'] = date('Y-m-d H:i:s');

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $post['prodcat_identifier'] = $post['prodcat_name'][$siteDefaultLangId];

        $post['prodcat_seller_id'] = UserAuthentication::getLoggedUserId();

        $productCategory = new ProductCategory($categoryReqId);
        if (!$productCategory->saveCategoryData($post)) {
            Message::addErrorMessage($productCategory->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $categoryReqId = $productCategory->getMainTableRecordId();

        $notificationData = array(
            'notification_record_type' => Notification::TYPE_PRODUCT_CATEGORY,
            'notification_record_id' => $categoryReqId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(true),
            'notification_label_key' => Notification::PRODUCT_CATEGORY_REQUEST_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $categoryData = ProductCategory::getAttributesById($categoryReqId);
        $email = new EmailHandler();
        if (!$email->sendCategoryRequestAdminNotification($this->siteLangId, $categoryData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $msg = Labels::getLabel("MSG_Category_Setup_Successful", $this->siteLangId);
        if ($approvalRequired) {
            $msg = Labels::getLabel("MSG_CATEGORY_REQUEST_SUBMITTED_SUCCESSFULLY", $this->siteLangId);
        }
        $this->set('msg', $msg);
        $this->set('categoryReqId', $categoryReqId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* ] */

    /* ------Brand Request ------*/

    public function addBrandReqForm($brandReqId = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true);
        $frm = $this->getBrandForm();
        $this->set('languages', Language::getAllNames());
        if (0 < $brandReqId) {
            $data = Brand::getAttributesById($brandReqId, array('brand_id', 'brand_identifier'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        $this->set('frmBrandReq', $frm);
        $this->set('brandReqId', $brandReqId);
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function setupBrandReq()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true);
        $post = FatApp::getPostedData();
        $frm = $this->getBrandForm();
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandReqId = $post['brand_id'];

        if ($brandReqId > 0 && !UserPrivilege::canSellerUpdateBrandRequest(UserAuthentication::getLoggedUserId(), $brandReqId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        unset($post['brandReqId']);

        if (!FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) {
            $post['brand_active'] = applicationConstants::ACTIVE;
            $post['brand_status'] = applicationConstants::YES;
            $post['brand_status_updated_on'] = date('Y-m-d H:i:s');
        }

        $post['brand_requested_on'] = date('Y-m-d H:i:s');

        $post['brand_seller_id'] = UserAuthentication::getLoggedUserId();
        $record = new Brand($brandReqId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandReqId = $record->getMainTableRecordId();

        $notificationData = array(
            'notification_record_type' => Notification::TYPE_BRAND,
            'notification_record_id' => $brandReqId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(true),
            'notification_label_key' => Notification::BRAND_REQUEST_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($brandReqId == 0) {
            $brandReqId = $record->getMainTableRecordId();
            $brandData = Brand::getAttributesById($brandReqId);
            $email = new EmailHandler();
            if (!$email->sendBrandRequestAdminNotification($this->siteLangId, $brandData)) {
            }
        }

        $newTabLangId = 0;
        if ($brandReqId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Brand::getAttributesByLangId($langId, $brandReqId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $brandReqId = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }


        $this->set('msg', Labels::getLabel("MSG_Brand_Setup_Successful", $this->siteLangId));
        $this->set('brandReqId', $brandReqId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function brandReqLangSetup()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId(), true);
        $post = FatApp::getPostedData();

        $brandReqId = $post['brand_id'];
        $lang_id = $post['lang_id'];

        if ($brandReqId == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!UserPrivilege::canSellerUpdateBrandRequest(UserAuthentication::getLoggedUserId(), $brandReqId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getBrandReqLangForm($brandReqId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['brand_id']);
        unset($post['lang_id']);
        $data = array(
            'brandlang_lang_id' => $lang_id,
            'brandlang_brand_id' => $brandReqId,
            'brand_name' => $post['brand_name'],
        );

        $brandObj = new Brand($brandReqId);
        if (!$brandObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($brandObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Brand::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($brandReqId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Brand::getAttributesByLangId($langId, $brandReqId)) {
                $newTabLangId = $langId;
                break;
            }
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($brandReqId)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Brand_Request_Sent_Successful', $this->siteLangId));
        $this->set('brandReqId', $brandReqId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function brandReqLangForm($brandReqId = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $brandReqId = FatUtility::int($brandReqId);
        $lang_id = FatUtility::int($lang_id);

        if ($brandReqId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        if (!UserPrivilege::canSellerUpdateBrandRequest(UserAuthentication::getLoggedUserId(), $brandReqId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $brandReqLangFrm = $this->getBrandReqLangForm($brandReqId, $lang_id);

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Brand::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($brandReqId, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Brand::getAttributesByLangId($lang_id, $brandReqId);
        }

        if ($langData) {
            $brandReqLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('brandReqId', $brandReqId);
        $this->set('brandReqLangId', $lang_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('brandReqLangFrm', $brandReqLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function brandMediaForm($brand_id = 0)
    {
        $brand_id = FatUtility::int($brand_id);
        if (!UserPrivilege::canSellerUpdateBrandRequest(UserAuthentication::getLoggedUserId(), $brand_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $brandMediaFrm = $this->getMediaForm($brand_id);
        $brandImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_LOGO, $brand_id, 0, -1);
        $bannerTypeArr = applicationConstants::bannerTypeArr();

        $this->set('languages', Language::getAllNames());
        $this->set('brandReqId', $brand_id);
        $this->set('brandReqMediaFrm', $brandMediaFrm);
        $this->set('brandImages', $brandImages);
        $this->set('bannerTypeArr', $bannerTypeArr);
        $this->_template->render(false, false);
    }

    public function uploadBrandLogo()
    {
        $brand_id = FatApp::getPostedData('brand_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if (!$brand_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!UserPrivilege::canSellerUpdateBrandRequest(UserAuthentication::getLoggedUserId(), $brand_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $aspectRatio = FatApp::getPostedData('ratio_type', FatUtility::VAR_INT, 0);

        $fileHandlerObj = new AttachedFile();
        $fileHandlerObj->deleteFile($fileHandlerObj::FILETYPE_BRAND_LOGO, $brand_id, 0, 0, $lang_id);

        if (!$fileHandlerObj->saveAttachment(
            $_FILES['cropped_image']['tmp_name'],
            $fileHandlerObj::FILETYPE_BRAND_LOGO,
            $brand_id,
            0,
            $_FILES['cropped_image']['name'],
            -1,
            $unique_record = false,
            $lang_id,
            0,
            $aspectRatio
        )) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('brandId', $brand_id);
        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('msg', $_FILES['cropped_image']['name'] . Labels::getLabel('MSG_File_Uploaded_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getMediaForm($brand_id)
    {
        $frm = new Form('frmBrandMedia');
        $languagesAssocArr = Language::getAllNames();
        $frm->addHiddenField('', 'brand_id', $brand_id);
        $frm->addHTML('', 'brand_logo_heading', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'brand_lang_id', array(0 => Labels::getLabel('LBL_Universal', $this->siteLangId)) + $languagesAssocArr, '', array(), '');
        $ratioArr = AttachedFile::getRatioTypeArray($this->siteLangId);
        $frm->addRadioButtons(Labels::getLabel('LBL_Ratio', $this->siteLangId), 'ratio_type', $ratioArr, AttachedFile::RATIO_TYPE_SQUARE);
        $frm->addFileUpload(Labels::getLabel('Lbl_Logo', $this->siteLangId), 'logo', array('accept' => 'image/*', 'data-frm' => 'frmBrandMedia'));

        $frm->addHtml('', 'brand_logo_display_div', '');

        return $frm;
    }

    public function removeBrandLogo($brand_id = 0, $lang_id = 0)
    {
        $brand_id = FatUtility::int($brand_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$brand_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!UserPrivilege::canSellerUpdateBrandRequest(UserAuthentication::getLoggedUserId(), $brand_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BRAND_LOGO, $brand_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getBrandForm()
    {
        $frm = new Form('frmBrandReq', array('id' => 'frmBrandReq'));
        $frm->addRequiredField(Labels::getlabel('LBL_Brand_Identifier', $this->siteLangId), 'brand_identifier')->setUnique(Brand::DB_TBL, Brand::DB_TBL_PREFIX . 'identifier', Brand::DB_TBL_PREFIX . 'id', Brand::DB_TBL_PREFIX . 'id', Brand::DB_TBL_PREFIX . 'identifier');
        $frm->addHiddenField('', 'brand_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    private function getBrandReqLangForm($brandReqId = 0, $lang_id = 0)
    {
        $frm = new Form('frmBrandReqLang', array('id' => 'frmBrandReqLang'));
        $frm->addHiddenField('', 'brand_id', $brandReqId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Brand_Name', $this->siteLangId), 'brand_name');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $lang_id), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Update", $this->siteLangId));
        return $frm;
    }

    private function isMediaUploaded($brandId)
    {
        if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $brandId, 0)) {
            return true;
        }
        return false;
    }

    public function customCatalogInfo($prodReqId)
    {
        $prodReqId = FatUtility::int($prodReqId);
        $srch = $this->getRequestedProdObj();
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tb.brand_id = preq_brand_id', 'tb');
        $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'brandlang_brand_id = tb.brand_id	AND brandlang_lang_id = ' . $this->siteLangId, 'tb_l');
        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', 'tc.prodcat_id = preq_prodcat_id', 'tc');
        $srch->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'prodcatlang_prodcat_id = tc.prodcat_id AND prodcatlang_lang_id = ' . $this->siteLangId, 'tc_l');
        $srch->addMultipleFields(array('preq.*', 'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name'));
        $srch->addCondition('preq_id', '=', $prodReqId);
        $rs = $srch->getResultSet();
        $product = FatApp::getDb()->fetchAll($rs);

        $productSpecData = [];
        foreach ($product as $key => $row) {
            $content = (!empty($row['preq_content'])) ? json_decode($row['preq_content'], true) : array();
            $langContent = (!empty($row['preq_lang_data'])) ? json_decode($row['preq_lang_data'], true) : array();

            $row = array_merge($row, $content);
            if (!empty($langContent)) {
                $row = array_merge($row, $langContent);
            }
            $arr = array(
                'preq_id' => $row['preq_id'],
                'product_name' => (!empty($row['product_name'])) ? $row['product_name'] : $row['product_identifier'],
                'product_min_selling_price' => $row['product_min_selling_price'],
                'product_model' => (isset($row['product_model'])) ? $row['product_model'] : '',
                'ptt_taxcat_id' => $row['ptt_taxcat_id'],
                'brand_name' => $row['brand_name'],
                'prodcat_name' => $row['prodcat_name'],
            );
            $productInfo = $arr;
        }
        /* ] */

        $this->set('product', $productInfo);
        $this->_template->render(false, false);
    }
}
