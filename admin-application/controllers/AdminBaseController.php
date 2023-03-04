<?php

class AdminBaseController extends FatController
{
    protected $objPrivilege;
    protected $unAuthorizeAccess;
    protected $admin_id;
    protected $str_add_record;
    protected $str_update_record;
    protected $str_export_successfull;
    protected $str_no_record;
    protected $str_invalid_request;
    protected $str_invalid_request_id;
    protected $str_delete_record;
    protected $str_invalid_Action;
    protected $str_setup_successful;
    protected $adminLangId;
    protected $nodes = [];

    public function __construct($action)
    {
        parent::__construct($action);

        $controllerName = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($controllerName));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $controllerName = ucfirst(FatUtility::dashed2Camel($urlController));
        if ($controllerName != 'AdminGuest') {
            $_SESSION['admin_referer_page_url'] = UrlHelper::getCurrUrl();
        }

        if (!AdminAuthentication::isAdminLogged()) {
            CommonHelper::initCommonVariables(true);
            if (FatUtility::isAjaxCall()) {
                // FatUtility::dieWithError("Your session seems to be expired, Please try after reloading the page.");
                Message::addErrorMessage(Labels::getLabel('LBL_Your_session_seems_to_be_expired', CommonHelper::getLangId()));
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(UrlHelper::generateUrl('AdminGuest', 'loginForm'));
        }

        $this->objPrivilege = AdminPrivilege::getInstance();
        /* $this->checkPermissions(); */
        $this->admin_id = AdminAuthentication::getLoggedAdminId();

        if (!FatUtility::isAjaxCall()) {
            $session_element_name = AdminAuthentication::SESSION_ELEMENT_NAME;
            $cookie_name = $session_element_name . 'layout';
            //@todo-ask::: Confirm about the usage of $_COOKIE.
            $selected_admin_dashboard_layout = isset($_COOKIE[$cookie_name]) ? (int) $_COOKIE[$cookie_name] : 0;
            $this->set('selected_admin_dashboard_layout', $selected_admin_dashboard_layout);

            $admin_dashboard_layouts = Admin::$admin_dashboard_layouts;
            $this->set('admin_dashboard_layouts', $admin_dashboard_layouts);
        }
        $this->set("bodyClass", '');
        $this->setCommonValues();
    }

    /*
    # Function: setCommonValues
    # Description: Function to set the common values.
    */
    private function setCommonValues()
    {
        CommonHelper::initCommonVariables(true);
        $this->adminLangId = CommonHelper::getLangId();
        $this->layoutDirection = CommonHelper::getLayoutDirection();
        $this->siteLangCode = CommonHelper::getLangCode();
        $this->siteLangCountryCode = CommonHelper::getLangCountryCode();

        $this->unAuthorizeAccess = Labels::getLabel('LBL_Unauthorized_Access', $this->adminLangId);
        $this->str_add_record = Labels::getLabel('LBL_Record_Added_Successfully', $this->adminLangId);
        $this->str_update_record = Labels::getLabel('LBL_Record_Updated_Successfully', $this->adminLangId);
        $this->str_no_record = Labels::getLabel('LBL_No_Record_Found', $this->adminLangId);
        $this->str_invalid_request_id = Labels::getLabel('LBL_Invalid_Request_Id', $this->adminLangId);
        $this->str_invalid_request = Labels::getLabel('LBL_Invalid_Request', $this->adminLangId);
        $this->str_delete_record = Labels::getLabel('LBL_Record_Deleted_Successfully', $this->adminLangId);
        $this->str_invalid_Action = Labels::getLabel('LBL_Invalid_Action', $this->adminLangId);
        $this->str_setup_successful = Labels::getLabel('LBL_Setup_Successful', $this->adminLangId);
        $this->str_export_successfull = Labels::getLabel('LBL_Export_Successful', $this->adminLangId);
        $this->str_add_update_record = $this->str_update_record;
        
        $defultCountryId = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
        $defaultCountryCode = Countries::getAttributesById($defultCountryId, 'country_code');

        $jsVariables = array(
            'confirmRemove' => Labels::getLabel('LBL_Do_you_want_to_remove', $this->adminLangId),
            'confirmRemoveOption' => Labels::getLabel('LBL_Do_you_want_to_remove_this_option', $this->adminLangId),
            'confirmRemoveShop' => Labels::getLabel('LBL_Do_you_want_to_remove_this_shop', $this->adminLangId),
            'confirmRemoveBrand' => Labels::getLabel('LBL_Do_you_want_to_remove_this_brand', $this->adminLangId),
            'confirmRemoveProduct' => Labels::getLabel('LBL_Do_you_want_to_remove_this_product', $this->adminLangId),
            'confirmRemoveCategory' => Labels::getLabel('LBL_Do_you_want_to_remove_this_category', $this->adminLangId),
            'confirmReset' => Labels::getLabel('LBL_Do_you_want_to_reset_settings', $this->adminLangId),
            'confirmActivate' => Labels::getLabel('LBL_Do_you_want_to_activate_status', $this->adminLangId),
            'confirmUpdate' => Labels::getLabel('LBL_Do_you_want_to_update', $this->adminLangId),
            'confirmUpdateStatus' => Labels::getLabel('LBL_Do_you_want_to_update', $this->adminLangId),
            'confirmDelete' => Labels::getLabel('LBL_Do_you_want_to_delete', $this->adminLangId),
            'confirmDeleteImage' => Labels::getLabel('LBL_Do_you_want_to_delete_image', $this->adminLangId),
            'confirmDeleteBackgroundImage' => Labels::getLabel('LBL_Do_you_want_to_delete_background_image', $this->adminLangId),
            'confirmDeleteLogo' => Labels::getLabel('LBL_Do_you_want_to_delete_logo', $this->adminLangId),
            'confirmDeleteBanner' => Labels::getLabel('LBL_Do_you_want_to_delete_banner', $this->adminLangId),
            'confirmDeleteIcon' => Labels::getLabel('LBL_Do_you_want_to_delete_icon', $this->adminLangId),
            'confirmDefault' => Labels::getLabel('LBL_Do_you_want_to_set_default', $this->adminLangId),
            'setMainProduct' => Labels::getLabel('LBL_Set_as_main_product', $this->adminLangId),
            'layoutDirection' => CommonHelper::getLayoutDirection(),
            'selectPlan' => Labels::getLabel('LBL_Please_Select_any_Plan', $this->adminLangId),
            'alreadyHaveThisPlan' => Labels::getLabel('LBL_You_have_already_Bought_this_plan,_Please_choose_some_other_Plan', $this->adminLangId),
            'invalidRequest' => Labels::getLabel('LBL_Invalid_Request!', $this->adminLangId),
            'pleaseWait' => Labels::getLabel('LBL_Please_Wait...', $this->adminLangId),
            'DoYouWantTo' => Labels::getLabel('LBL_Do_you_really_want_to', $this->adminLangId),
            'theRequest' => Labels::getLabel('LBL_the_request', $this->adminLangId),
            'confirmCancelOrder' => Labels::getLabel('LBL_Are_you_sure_to_cancel_this_order', $this->adminLangId),
            'confirmReplaceCurrentToDefault' => Labels::getLabel('LBL_Do_you_want_to_replace_current_content_to_default_content', $this->adminLangId),
            'processing' => Labels::getLabel('LBL_Processing...', $this->adminLangId),
            'preferredDimensions' => Labels::getLabel('LBL_Preferred_Dimensions_%s', $this->adminLangId),
            'confirmRestore' => Labels::getLabel('LBL_Do_you_want_to_restore', $this->adminLangId),
            'thanksForSharing' => Labels::getLabel('LBL_Msg_Thanks_for_sharing', $this->adminLangId),
            'isMandatory' => Labels::getLabel('VLBL_is_mandatory', $this->adminLangId),
            'pleaseEnterValidEmailId' => Labels::getLabel('VLBL_Please_enter_valid_email_ID_for', $this->adminLangId),
            'charactersSupportedFor' => Labels::getLabel('VLBL_Only_characters_are_supported_for', $this->adminLangId),
            'pleaseEnterIntegerValue' => Labels::getLabel('VLBL_Please_enter_integer_value_for', $this->adminLangId),
            'pleaseEnterNumericValue' => Labels::getLabel('VLBL_Please_enter_numeric_value_for', $this->adminLangId),
            'startWithLetterOnlyAlphanumeric' => Labels::getLabel('VLBL_must_start_with_a_letter_and_can_contain_only_alphanumeric_characters._Length_must_be_between_4_to_20_characters', $this->adminLangId),
            'mustBeBetweenCharacters' => Labels::getLabel('VLBL_Length_Must_be_between_6_to_20_characters', $this->adminLangId),
            'invalidValues' => Labels::getLabel('VLBL_Length_Invalid_value_for', $this->adminLangId),
            'shouldNotBeSameAs' => Labels::getLabel('VLBL_should_not_be_same_as', $this->adminLangId),
            'mustBeSameAs' => Labels::getLabel('VLBL_must_be_same_as', $this->adminLangId),
            'mustBeGreaterOrEqual' => Labels::getLabel('VLBL_must_be_greater_than_or_equal_to', $this->adminLangId),
            'mustBeGreaterThan' => Labels::getLabel('VLBL_must_be_greater_than', $this->adminLangId),
            'mustBeLessOrEqual' => Labels::getLabel('VLBL_must_be_less_than_or_equal_to', $this->adminLangId),
            'mustBeLessThan' => Labels::getLabel('VLBL_must_be_less_than', $this->adminLangId),
            'lengthOf' => Labels::getLabel('VLBL_Length_of', $this->adminLangId),
            'valueOf' => Labels::getLabel('VLBL_Value_of', $this->adminLangId),
            'mustBeBetween' => Labels::getLabel('VLBL_must_be_between', $this->adminLangId),
            'mustBeBetween' => Labels::getLabel('VLBL_must_be_between', $this->adminLangId),
            'and' => Labels::getLabel('VLBL_and', $this->adminLangId),
            'pleaseSelect' => Labels::getLabel('VLBL_Please_select', $this->adminLangId),
            'to' => Labels::getLabel('VLBL_to', $this->adminLangId),
            'options' => Labels::getLabel('VLBL_options', $this->adminLangId),
            'isNotAvailable' => Labels::getLabel('VLBL_is_not_available', $this->adminLangId),
            'confirmRestoreBackup' => Labels::getLabel('LBL_Do_you_want_to_restore_database_to_this_record', $this->adminLangId),
            'confirmChangeRequestStatus' => Labels::getLabel('LBL_Do_you_want_to_change_request_status', $this->adminLangId),
            'confirmTruncateUserData' => Labels::getLabel('LBL_Do_you_want_to_truncate_User_Data', $this->adminLangId),
            'atleastOneRecord' => Labels::getLabel('LBL_Please_select_atleast_one_record.', $this->adminLangId),
            'primaryLanguageField' => Labels::getLabel('LBL_PRIMARY_LANGUAGE_DATA_NEEDS_TO_BE_FILLED_FOR_SYSTEM_TO_TRANSLATE_TO_OTHER_LANGUAGES.', $this->adminLangId),
            'updateCurrencyRates' => Labels::getLabel('LBL_WANT_TO_UPDATE_CURRENCY_RATES?.', $this->adminLangId),
            'cloneNotification' => Labels::getLabel('LBL_DO_YOU_REALLY_WANT_TO_CLONE?', $this->adminLangId),
            'clonedNotification' => Labels::getLabel('LBL_NOTIFICATION_CLONED_SUCCESSFULLY', $this->adminLangId),
            'confirmRemoveBlog' => Labels::getLabel('LBL_Do_you_want_to_remove_this_blog', $this->adminLangId),
            'actionButtonsClass' => Labels::getLabel('LBL_PLEASE_ADD_"actionButtons-js"_CLASS_TO_FORM_TO_PERFORM_ACTION', $this->adminLangId),
            'allowedFileSize' => LibHelper::getMaximumFileUploadSize(),
            'fileSizeExceeded' => Labels::getLabel("MSG_FILE_SIZE_SHOULD_BE_LESSER_THAN_{SIZE-LIMIT}", $this->adminLangId),
            'currentPrice' => Labels::getLabel('LBL_Current_Price', $this->adminLangId),
            'currentStock' => Labels::getLabel('LBL_Current_Stock', $this->adminLangId),
            'discountPercentage' => Labels::getLabel('LBL_Discount_Percentage', $this->adminLangId),
            'shippingUser' => Labels::getLabel('MSG_Please_assign_shipping_user', $this->adminLangId),
            'saveProfileFirst' => Labels::getLabel('LBL_Save_Profile_First', $this->adminLangId),
            'minimumOneLocationRequired' => Labels::getLabel('LBL_Minimum_one_location_is_required', $this->adminLangId),
            'confirmTransfer' => Labels::getLabel('LBL_CONFIRM_TRANSFER_?', $this->adminLangId),
            'invalidFromTime' => Labels::getLabel('LBL_PLEASE_SELECT_VALID_FROM_TIME', $this->adminLangId),
            'selectTimeslotDay' => Labels::getLabel('LBL_ATLEAST_ONE_DAY_AND_TIMESLOT_NEEDS_TO_BE_CONFIGURED', $this->adminLangId),
            'invalidTimeSlot' => Labels::getLabel('LBL_PLEASE_CONFIGURE_FROM_AND_TO_TIME', $this->adminLangId),
            'noRecordFound' => Labels::getLabel('LBL_No_Record_Found', $this->adminLangId),
            'disableChildCategories' => Labels::getLabel('LBL_CHANGING_PARENT_CATEGORY_TO_INACTIVE_WILL_MAKE_ALL_OF_ITS_CHILD_CATEGORIES_INACTIVE._ARE_YOU_SURE_YOU_WANT_TO_PROCEED?', $this->adminLangId),
            'enableParentCategories' => Labels::getLabel('LBL_CHANGING_CHILD_CATEGORY_TO_ACTIVE_WILL_MAKE_ALL_OF_ITS_PARENT_CATEGORIES_ACTIVE._ARE_YOU_SURE_YOU_WANT_TO_PROCEED?', $this->adminLangId),
            'defaultCountryCode' => $defaultCountryCode,
            'dialCodeFieldNotFound' => Labels::getLabel('LBL_DIAL_CODE_FIELD_NOT_FOUND', $this->adminLangId),
        );

        $languages = Language::getAllNames(false);
        foreach ($languages as $val) {
            $jsVariables['language' . $val['language_id']] = $val['language_layout_direction'];
        }
        $jsVariables['languages'] = $languages;
        //get notifications count
        $db = FatApp::getDb();
        $notifyObject = Notification::getSearchObject();
        if (!AdminPrivilege::isAdminSuperAdmin($this->admin_id)) {
            $recordTypeArr = Notification::getAllowedRecordTypeArr($this->admin_id);
            $notifyObject->addCondition('notification_record_type', 'IN', $recordTypeArr);
        }
        $notifyObject->addCondition('n.' . Notification::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        $notifyObject->addCondition('n.' . Notification::DB_TBL_PREFIX . 'marked_read', '=', applicationConstants::NO);
        $notifyObject->addMultipleFields(array('count(notification_id) as countOfRec'));
        $notifyCountResult = $db->fetch($notifyObject->getResultset());
        $notifyCount = FatUtility::int($notifyCountResult['countOfRec']);

        $this->siteDefaultCurrencyCode = CommonHelper::getCurrencyCode();       
        
        $this->set('adminLangId', $this->adminLangId);
        $this->set('siteDefaultCurrencyCode', $this->siteDefaultCurrencyCode);
        $this->set('jsVariables', $jsVariables);
        $this->set('notifyCount', $notifyCount);
        $this->set('languages', Language::getAllNames(false));
        $this->set('isAdminLogged', AdminAuthentication::isAdminLogged());
        $this->set('layoutDirection', $this->layoutDirection);
        
        $this->includeDatePickerLangJs();
        
        if ($this->layoutDirection == 'rtl') {
            $this->_template->addCss('css/style--arabic.css');
        }
        if (CommonHelper::demoUrl() == true) {
            $this->_template->addCss('css/demo.css');
        }
    }

    public function getNavigationBreadcrumbArr($action)
    {
        switch ($action) {
            case 'shops':
            case 'shops':
            case 'shops':
                $link = Labels::getLabel('MSG_Catalog', $this->adminLangId);
                break;
        }
        return $link;
    }

    public function getBreadcrumbNodes($action)
    {
        $className = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($className));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $className = ucwords(implode(' ', $arr));
        if ($action == 'index') {
            $this->nodes[] = array('title' => $className);
        } else {
            $arr = explode('-', FatUtility::camel2dashed($action));
            $action = ucwords(implode(' ', $arr));
            $this->nodes[] = array('title' => $className, 'href' => UrlHelper::generateUrl($urlController));
            $this->nodes[] = array('title' => $action);
        }
        return $this->nodes;
    }
    
    public function includeDatePickerLangJs()
    {
        $langCode = strtolower($this->siteLangCode);
        $langCountryCode = strtoupper($this->siteLangCountryCode);
        $jsPath = FatCache::get('datepickerlangfilePath' . $langCode . "-" . $langCountryCode, CONF_DEF_CACHE_TIME, '.txt');        
        if ($jsPath) {
            if ($jsPath == 'notfound') {
                return;
            }
            $this->_template->addJs($jsPath);
            return;
        } elseif ($jsPath == 'notfound') {
            return;
        }        
        $jsPath = 'js/jqueryui-i18n/datepicker-' . $langCode . '-' . $langCountryCode . '.js';
        $filePath = CONF_APPLICATION_PATH . '/views/' . $jsPath;

        $fileFound = false;
        if (file_exists($filePath)) {
            $fileFound = true;
        }
        if (false == $fileFound) {
            $jsPath = 'js/jqueryui-i18n/datepicker-' . $langCode . '.js';
            $filePath = CONF_APPLICATION_PATH . '/views/' . $jsPath;
            if (file_exists($filePath)) {
                $fileFound = true;
            }
        }

        if (true == $fileFound) {
            $this->_template->addJs($jsPath);
        } else {
            $jsPath = 'notfound';
        }
        FatCache::set('datepickerlangfilePath' . $langCode . "-" . $langCountryCode, $jsPath, '.txt');
    }

    public function getStates($countryId, $stateId = 0, $langId = 0, $idCol = 'state_id')
    {
        $countryId = FatUtility::int($countryId);
        $langId = FatUtility::int($langId);

        if ($langId == 0) {
            $langId = $this->adminLangId;
        }

        $stateObj = new States();
        $statesArr = $stateObj->getStatesByCountryId($countryId, $this->adminLangId, true, $idCol);

        $this->set('statesArr', $statesArr);
        $this->set('stateId', $stateId);
        $this->_template->render(false, false, '_partial/states-list.php');
    }

    public function getStatesByCountryCode($countryCode, $stateCode = '', $idCol = 'state_id')
    {
        $countryId = Countries::getCountryByCode($countryCode, 'country_id');
        $this->getStates($countryId, $stateCode, $this->adminLangId, $idCol);
    }

    protected function getUserSearchForm()
    {
        $frm = new Form('frmUserSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Name_Or_Email', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        //$keyword->setFieldTagAttribute('onKeyUp','usersAutocomplete(this)');

        $arr_options = array('-1' => Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + applicationConstants::getActiveInactiveArr($this->adminLangId);
        $arr_options1 = array('-1' => Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + applicationConstants::getYesNoArr($this->adminLangId);

        $arr_options2 = array('-1' => Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + User::getUserTypesArr($this->adminLangId);
        $arr_options2 = $arr_options2 + array(User::USER_TYPE_BUYER_SELLER => Labels::getLabel('LBL_Buyer', $this->adminLangId) . '+' . Labels::getLabel('LBL_Seller', $this->adminLangId));
        $arr_options2 = $arr_options2 + array(User::USER_TYPE_SUB_USER => Labels::getLabel('LBL_Sub_User', $this->adminLangId));

        $frm->addSelectBox(Labels::getLabel('LBL_Active_Users', $this->adminLangId), 'user_active', $arr_options, -1, array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_Email_Verified', $this->adminLangId), 'user_verified', $arr_options1, -1, array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_User_Type', $this->adminLangId), 'type', $arr_options2, -1, array(), '');

        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_From', $this->adminLangId), 'user_regdate_from', '', array('readonly' => 'readonly'));
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_To', $this->adminLangId), 'user_regdate_to', '', array('readonly' => 'readonly'));

        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'user_id', '');
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    protected function getUserForm($user_id = 0, $userType = 0)
    {
        $user_id = FatUtility::int($user_id);
        $userType = FatUtility::int($userType);

        $frm = new Form('frmUser', array('id' => 'frmUser'));
        $frm->addHiddenField('', 'user_id', $user_id);
        $frm->addHiddenField('', 'user_type');
        $frm->addTextBox(Labels::getLabel('LBL_Username', $this->adminLangId), 'credential_username', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Customer_name', $this->adminLangId), 'user_name');
        $frm->addDateField(Labels::getLabel('LBL_Date_of_birth', $this->adminLangId), 'user_dob', '', array('readonly' => 'readonly'));
        $frm->addHiddenField('', 'user_phone_dcode');
        $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Phone', $this->adminLangId), 'user_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $frm->addEmailField(Labels::getLabel('LBL_Email', $this->adminLangId), 'credential_email', '');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'user_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 223), array(), Labels::getLabel('LBL_Select', $this->adminLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'user_state_id', array(), '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirement->setRequired(true);
        $frm->addTextBox(Labels::getLabel('LBL_City', $this->adminLangId), 'user_city');

        switch ($userType) {
            case User::USER_TYPE_SHIPPING_COMPANY:
                $frm->addTextBox(Labels::getLabel('LBL_Tracking_Site_Url', $this->adminLangId), 'user_order_tracking_url');
                break;
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    protected function getSellerOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmVendorOrderSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keywords', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $frm->addTextBox(Labels::getLabel('LBL_Buyer', $this->adminLangId), 'buyer', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'op_status_id', Orders::getOrderStatusArr($langId), '', array(), Labels::getLabel('LBL_All', $langId));
        $frm->addTextBox(Labels::getLabel('LBL_Seller/Shop', $this->adminLangId), 'shop_name');
        /* $frm->addTextBox(Labels::getLabel('LBL_Customer',$this->adminLangId),'customer_name'); */

        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $this->adminLangId), 'readonly' => 'readonly'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $this->adminLangId), 'readonly' => 'readonly'));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Order_From', $this->adminLangId) . ' [' . $currencySymbol . ']'));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Order_To', $this->adminLangId) . ' [' . $currencySymbol . ']'));

        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'user_id');
        $frm->addHiddenField('', 'order_id');
        $frm->addHiddenField('', 'shipping_company_user_id', 0);
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    protected function getProductCatalogForm($attrgrp_id = 0, $type = 'CUSTOM_PRODUCT', $productType = Product::PRODUCT_TYPE_PHYSICAL)
    {
        $langId = $this->adminLangId;
        $this->objPrivilege->canViewProducts();
        $frm = new Form('frmProduct', array('id' => 'frmProduct'));
        if ($type == 'CUSTOM_PRODUCT') {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'selprod_user_shop_name', '', array(' ' => ' '));
            $fld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_Please_leave_empty_if_you_want_to_add_product_in_system_catalog', $this->adminLangId) . ' </small>';
            $frm->addHtml('', 'user_shop', '<div id="user_shop_name"></div>');
        }

        $frm->addHiddenField('', 'product_seller_id');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Product_Identifier', $this->adminLangId), 'product_identifier');
        $fld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_It_may_be_same_as_of_Product_Name', $this->adminLangId) . ' </small>';

        $pTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $this->adminLangId), 'product_type', Product::getProductTypes($langId), Product::PRODUCT_TYPE_PHYSICAL, array('id' => 'product_type'), '');

        if ($type == 'REQUESTED_CATALOG_PRODUCT') {
            $brandFld = $frm->addTextBox(Labels::getLabel('LBL_Brand/Manfacturer', $this->adminLangId), 'brand_name');
            if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
                $brandFld->requirements()->setRequired();
            }

            //$fld1 = $frm->addTextBox(Labels::getLabel('LBL_Category',$this->adminLangId),'category_name');

            $frm->addHiddenField('', 'product_brand_id');
            $frm->addHiddenField('', 'product_category_id');
            $frm->addHiddenField('', 'preq_id');
            $frm->addHiddenField('', 'product_options');
        }

        $fld_model = $frm->addTextBox(Labels::getLabel('LBL_Model', $this->adminLangId), 'product_model');
        if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fld_model->requirements()->setRequired();
        }
        $frm->addCheckBox(Labels::getLabel('LBL_Product_Featured', $this->adminLangId), 'product_featured', 1, array(), false, 0);

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Minimum_Selling_Price', $langId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'product_min_selling_price', '');
        $fld->requirements()->setPositive();

        $fld = $frm->addRequiredField(Labels::getLabel('LBL_PRODUCT_WARRANTY', $this->adminLangId), 'product_warranty');
        $fld->requirements()->setInt();
        $fld->requirements()->setPositive();
        $fld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->adminLangId) . ' </small>';
        if (Product::PRODUCT_TYPE_DIGITAL == $productType) {
            $fld->requirements()->setRequired(false);
        }    
        $taxCategories = Tax::getSaleTaxCatArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Tax_Category', $this->adminLangId), 'ptt_taxcat_id', $taxCategories, '', array(), Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired(true);

        if (Product::PRODUCT_TYPE_PHYSICAL == $productType) {
            $shipProfileArr = ShippingProfile::getProfileArr($this->adminLangId, 0, true, true);
            $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->adminLangId), 'shipping_profile', $shipProfileArr, '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();

            if ($type == 'REQUESTED_CATALOG_PRODUCT') {
                $fulFillmentArr = Shipping::getFulFillmentArr($this->adminLangId, FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1));
                $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_METHOD', $this->adminLangId), 'product_fulfillment_type', $fulFillmentArr, applicationConstants::NO, [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();
            }
        }

        if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
            if (Product::PRODUCT_TYPE_PHYSICAL == $productType) {
                $shipPackArr = ShippingPackage::getAllNames();
                $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Package', $this->adminLangId), 'product_ship_package', $shipPackArr, '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();
            }

            /* weight unit[ */
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
            $frm->addSelectBox(Labels::getLabel('LBL_Weight_Unit', $langId), 'product_weight_unit', $weightUnitsArr, '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();
            $pWeightUnitUnReqObj = new FormFieldRequirement('product_weight_unit', Labels::getLabel('LBL_Weight_Unit', $langId));
            $pWeightUnitUnReqObj->setRequired(false);

            $pWeightUnitReqObj = new FormFieldRequirement('product_weight_unit', Labels::getLabel('LBL_Weight_Unit', $langId));
            $pWeightUnitReqObj->setRequired(true);
            /* ] */

            /* weight[ */
            $frm->addFloatField(Labels::getLabel('LBL_Weight', $langId), 'product_weight', '0.00');
            $pWeightUnReqObj = new FormFieldRequirement('product_weight', Labels::getLabel('LBL_Weight', $langId));
            $pWeightUnReqObj->setRequired(false);

            $pWeightReqObj = new FormFieldRequirement('product_weight', Labels::getLabel('LBL_Weight', $langId));
            $pWeightReqObj->setRequired(true);
            $pWeightReqObj->setFloatPositive();
            $pWeightReqObj->setRange('0.01', '9999999999');
            /* ] */

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_weight', $pWeightUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_weight', $pWeightReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_weight_unit', $pWeightUnitUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_weight_unit', $pWeightUnitReqObj);
        }

        /* $frm->addTextBox('UPC','product_upc');
        $frm->addTextBox('ISBN Code','product_isbn'); */
        if ($type == 'CUSTOM_PRODUCT') {
            $approveUnApproveArr = Product::getApproveUnApproveArr($langId);
            $frm->addSelectBox(Labels::getLabel('LBL_Approval_Status', $this->adminLangId), 'product_approved', $approveUnApproveArr, Product::APPROVED, array(), '');
        }

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Product_Status', $this->adminLangId), 'product_active', $activeInactiveArr, applicationConstants::NO, array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($langId);
        $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $this->adminLangId), 'product_cod_enabled', $yesNoArr, applicationConstants::NO, array(), '');

        $paymentMethod = new PaymentMethods();
        if (!$paymentMethod->cashOnDeliveryIsActive()) {
            $codFld->addFieldTagAttribute('disabled', 'disabled');
            $codFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->adminLangId) . '</small>';
        }

        if ($type == 'REQUESTED_CATALOG_PRODUCT') {
            $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Option_Groups', $this->adminLangId), 'option_name');
            $fld1->htmlAfterField = '<div class="box--scroller"><ul class="columlist list--vertical" id="product-option-js"></ul></div>';

            $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Tag', $this->adminLangId), 'tag_name');
            $fld1->htmlAfterField = '<div class="box--scroller"><ul class="columlist list--vertical" id="product-tag-js"></ul></div>';
        }
        if ($type != 'REQUESTED_CATALOG_PRODUCT') {
            $frm->addTextBox(Labels::getLabel('LBL_EAN/UPC/GTIN_code', $this->adminLangId), 'product_upc');
        }

        if ($type != 'REQUESTED_CATALOG_PRODUCT') {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Country_Of_Origin', $langId), 'shipping_country');
            //$fld = $frm->addCheckBox(Labels::getLabel('LBL_Free_Shipping', $langId), 'ps_free', 1);
            $frm->addHtml('', '', '<table id="tab_shipping" width="100%"></table><div class="gap"></div>');
        }

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'product_options');


        /* code to input values for the comparison attributes[ */
        if ($attrgrp_id) {
            $db = FatApp::getDb();
            //$attrGrpAttrObj = new AttrGroupAttribute();
            $srch = AttrGroupAttribute::getSearchObject();
            $srch->joinTable(AttrGroupAttribute::DB_TBL . '_lang', 'LEFT JOIN', 'lang.attrlang_attr_id = ' . AttrGroupAttribute::DB_TBL_PREFIX . 'id AND attrlang_lang_id = ' . $langId, 'lang');
            $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'attrgrp_id', '=', $attrgrp_id);
            $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'type', '!=', AttrGroupAttribute::ATTRTYPE_TEXT);
            $srch->addOrder(AttrGroupAttribute::DB_TBL_PREFIX . 'display_order');
            $srch->addMultipleFields(array('attr_identifier', 'attr_type', 'attr_fld_name', 'attr_name', 'attr_options', 'attr_prefix', 'attr_postfix'));
            $rs = $srch->getResultSet();
            $attributes = $db->fetchAll($rs);
            if ($attributes) {
                foreach ($attributes as $attr) {
                    $caption = ($attr['attr_name'] != '') ? $attr['attr_name'] : $attr['attr_identifier'];
                    switch ($attr['attr_type']) {
                        case AttrGroupAttribute::ATTRTYPE_NUMBER:
                            //$fld = $frm->addIntegerField($caption, $attr['attr_fld_name']);
                            $fld = $frm->addFloatField($caption, $attr['attr_fld_name']);
                            break;
                        case AttrGroupAttribute::ATTRTYPE_DECIMAL:
                            $fld = $frm->addFloatField($caption, $attr['attr_fld_name']);
                            break;
                        case AttrGroupAttribute::ATTRTYPE_SELECT_BOX:
                            $arr_options = array();
                            if ($attr['attr_options'] != '') {
                                $arr_options = explode("\n", $attr['attr_options']);
                                if (is_array($arr_options)) {
                                    $arr_options = array_map('trim', $arr_options);
                                }
                            }
                            $fld_txt_box = $frm->addSelectBox($caption, $attr['attr_fld_name'], $arr_options, '', array(), '');
                            break;
                    }
                    if ($attr['attr_prefix'] != '') {
                        $fld->htmlBeforeField = $attr['attr_prefix'];
                    }
                    $postfix_hint = '';
                    if ($attr['attr_postfix'] != '') {
                        $postfix_hint = '(' . $attr['attr_postfix'] . ') ';
                    }
                    $postfix_hint .= " Enter -1 for N.A";
                    $fld->htmlAfterField = '<small>' . $postfix_hint . '</small>';
                }
            }
        }
        $frm->addHiddenField('', 'product_attrgrp_id', $attrgrp_id);
        $frm->addHiddenField('', 'product_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    protected function getSellerProductForm($product_id, $type = 'SELLER_PRODUCT')
    {
        $frm = new Form('frmSellerProduct');
        $defaultProductCond = '';

        if ($type == 'REQUESTED_CATALOG_PRODUCT') {
            $reqData = ProductRequest::getAttributesById($product_id, array('preq_content'));
            $productData = array_merge($reqData, json_decode($reqData['preq_content'], true));
            $productData['sellerProduct'] = 0;
            $optionArr = isset($productData['product_option']) ? $productData['product_option'] : array();
            if (!empty($optionArr)) {
                $frm->addHtml('', 'optionSectionHeading', '');
            }
            foreach ($optionArr as $val) {
                $optionSrch = Option::getSearchObject($this->adminLangId);
                $optionSrch->addMultipleFields(array('IFNULL(option_name,option_identifier) as option_name', 'option_id'));
                $optionSrch->doNotCalculateRecords();
                $optionSrch->setPageSize(1);
                $optionSrch->addCondition('option_id', '=', $val);
                $rs = $optionSrch->getResultSet();
                $option = FatApp::getDb()->fetch($rs);
                if ($option == false) {
                    continue;
                }
                $optionValues = Product::getOptionValues($option['option_id'], $this->adminLangId);
                $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id[' . $option['option_id'] . ']', $optionValues, '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
                $fld->requirements()->setRequired();
            }
        } else {
            $productData = Product::getAttributesById($product_id, array('product_type', 'product_min_selling_price', 'if(product_seller_id > 0, 1, 0) as sellerProduct', 'product_seller_id'));
            if ($productData['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                $defaultProductCond = Product::CONDITION_NEW;
            }

            $productOptions = Product::getProductOptions($product_id, $this->adminLangId, true);
            if ($productOptions) {
                $frm->addHtml('', 'optionSectionHeading', '');
                foreach ($productOptions as $option) {
                    $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                    $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id[' . $option['option_id'] . ']', $option['optionValues'], '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
                    // $fld->requirements()->setRequired();
                }
            }
            $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'selprod_user_shop_name', '', array(' ' => ' '))->requirements()->setRequired();
            $frm->addHtml('', 'user_shop', '<div id="user_shop_name"></div>');
        }

        $isPickupEnabled = applicationConstants::NO;
        if ($productData['sellerProduct'] > 0) {
            $isPickupEnabled = Shop::getAttributesByUserId($productData['product_seller_id'], 'shop_fulfillment_type');
        } else {
            $isPickupEnabled = FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1);
        }

        $frm->addHiddenField('', 'selprod_user_id');
        $frm->addTextBox(Labels::getLabel('LBL_Url_Keyword', $this->adminLangId), 'selprod_url_keyword');

        $costPrice = $frm->addFloatField(Labels::getLabel('LBL_Cost_Price', $this->adminLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_cost');
        $costPrice->requirements()->setPositive();

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Price', $this->adminLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_price');
        $fld->requirements()->setPositive();
        if (isset($productData['product_min_selling_price'])) {
            $fld->requirements()->setRange($productData['product_min_selling_price'], 9999999999);
        }

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $this->adminLangId), 'selprod_stock');
        $fld->requirements()->setPositive();
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Quantity', $this->adminLangId), 'selprod_min_order_qty');
        $fld->requirements()->setPositive();
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Maintain_Stock_Levels', $this->adminLangId), 'selprod_subtract_stock', applicationConstants::YES, array(), false, 0);
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Track_Product_Inventory', $this->adminLangId), 'selprod_track_inventory', Product::INVENTORY_TRACK, array(), false, 0);
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Alert_Stock_Level', $this->adminLangId), 'selprod_threshold_stock_level');
        $fld->requirements()->setInt();

        /* $threshold_stock_levelUnReqObj = new FormFieldRequirement( 'selprod_threshold_stock_level', Labels::getLabel('LBL_Alert_Stock_Level', $this->adminLangId) );
        $threshold_stock_levelUnReqObj->setRequired(false);

        $threshold_stock_levelReqObj = new FormFieldRequirement( 'selprod_threshold_stock_level', Labels::getLabel('LBL_Alert_Stock_Level', $this->adminLangId) );
        $threshold_stock_levelReqObj->setRequired(true);

        $selprod_track_inventoryFld->requirements()->addOnChangerequirementUpdate(Product::INVENTORY_TRACK, 'eq', 'selprod_threshold_stock_level', $threshold_stock_levelUnReqObj);
        $selprod_track_inventoryFld->requirements()->addOnChangerequirementUpdate(Product::INVENTORY_NOT_TRACK, 'eq', 'selprod_threshold_stock_level', $threshold_stock_levelReqObj); */

        $fld_sku = $frm->addTextBox(Labels::getLabel('LBL_Product_SKU', $this->adminLangId), 'selprod_sku');
        if (FatApp::getConfig("CONF_PRODUCT_SKU_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fld_sku->requirements()->setRequired();
        }

        if ($productData['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $fld = $frm->addIntegerField(Labels::getLabel('LBL_Max_Download_Times', $this->adminLangId), 'selprod_max_download_times');
            $fld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_-1_for_unlimited', $this->adminLangId) . '</small>';

            $fld1 = $frm->addIntegerField(Labels::getLabel('LBL_Validity_(days)', $this->adminLangId), 'selprod_download_validity_in_days');
            $fld1->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_-1_for_unlimited', $this->adminLangId) . '</small>';
            $frm->addHiddenField('', 'selprod_condition', $defaultProductCond);
        } else {
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $this->adminLangId), 'selprod_condition', Product::getConditionArr($this->adminLangId), '', array(), Labels::getLabel('LBL_Select_Condition', $this->adminLangId));
            $fld->requirements()->setRequired();
        }

        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $this->adminLangId), 'selprod_available_from', '', array('readonly' => 'readonly'))->requirements()->setRequired();

        /* $frm->addDateTimeField( Labels::getLabel('LBL_Date_Available', $this->adminLangId), 'selprod_available_from', '' , array('readonly' => 'readonly')); */

        /* $frm->addTextArea( Labels::getLabel( 'LBL_Any_Extra_Comment_for_buyer', $this->adminLangId), 'selprod_comments'); */

        $useShopPolicy = $frm->addCheckBox(Labels::getLabel('LBL_USE_SHOP_RETURN_AND_CANCELLATION_AGE_POLICY', $this->adminLangId), 'use_shop_policy', 1, ['id' => 'use_shop_policy'], false, 0);

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $this->adminLangId), 'selprod_return_age');

        $orderReturnAgeReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $this->adminLangId));
        $orderReturnAgeReqFld->setRequired(true);
        $orderReturnAgeReqFld->setPositive();
        $orderReturnAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->adminLangId) . ' </small>';

        $orderReturnAgeUnReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $this->adminLangId));
        $orderReturnAgeUnReqFld->setRequired(false);
        $orderReturnAgeUnReqFld->setPositive();
        $orderReturnAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->adminLangId) . ' </small>';

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $this->adminLangId), 'selprod_cancellation_age');

        $orderCancellationAgeReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $this->adminLangId));
        $orderCancellationAgeReqFld->setRequired(true);
        $orderCancellationAgeReqFld->setPositive();
        $orderCancellationAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->adminLangId) . ' </small>';

        $orderCancellationAgeUnReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $this->adminLangId));
        $orderCancellationAgeUnReqFld->setRequired(false);
        $orderCancellationAgeUnReqFld->setPositive();
        $orderCancellationAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->adminLangId) . ' </small>';

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_return_age', $orderReturnAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_return_age', $orderReturnAgeReqFld);

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_cancellation_age', $orderCancellationAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_cancellation_age', $orderCancellationAgeReqFld);

        $frm->addSelectBox(Labels::getLabel('LBL_PUBLISH_INVENTORY', $this->adminLangId), 'selprod_active', applicationConstants::getYesNoArr($this->adminLangId), applicationConstants::YES, array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($this->adminLangId);
        $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $this->adminLangId), 'selprod_cod_enabled', $yesNoArr, '0', array(), '');
        $paymentMethod = new PaymentMethods();
        if (!$paymentMethod->cashOnDeliveryIsActive()) {
            $codFld->addFieldTagAttribute('disabled', 'disabled');
            $codFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->adminLangId) . '</small>';
        }

        $fulFillmentArr = Shipping::getFulFillmentArr($this->adminLangId, $isPickupEnabled);
        if ($productData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_METHOD', $this->adminLangId), 'selprod_fulfillment_type', $fulFillmentArr, applicationConstants::NO, array(), Labels::getLabel('LBL_Select', $this->adminLangId));
            $fld->requirement->setRequired(true);
        }
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->adminLangId), 'selprod_title' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $this->adminLangId), 'selprod_comments' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));

        $languages = Language::getAllNames();
        unset($languages[FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)]);
        foreach ($languages as $langId => $langName) {
            $frm->addTextBox(Labels::getLabel('LBL_Title', $this->adminLangId), 'selprod_title' . $langId);
            $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $this->adminLangId), 'selprod_comments' . $langId);
        }
        $frm->addHiddenField('', 'selprod_product_id', $product_id);
        $frm->addHiddenField('', 'selprod_id');
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    protected function renderJsonError($msg = '')
    {
        $this->set('msg', $msg);
        $this->_template->render(false, false, 'json-error.php', false, false);
    }

    protected function renderJsonSuccess($msg = '')
    {
        $this->set('msg', $msg);
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function includeDateTimeFiles()
    {
        $this->_template->addCss(array('css/1jquery-ui-timepicker-addon.css'), false);
        $this->_template->addJs(array('js/1jquery-ui-timepicker-addon.js'), false);
    }

    public function translateLangFields($tbl, $data)
    {
        if (!empty($tbl) && !empty($data)) {
            $updateLangDataobj = new TranslateLangData($tbl);
            $translatedText = $updateLangDataobj->directTranslate($data);
            if (false === $translatedText) {
                FatUtility::dieJsonError($updateLangDataobj->getError());
            }
            return $translatedText;
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
    }

    public function imgCropper()
    {
        $this->_template->render(false, false, 'cropper/index.php');
    }

    public function recordInfoSection()
    {
        $this->_template->render(false, false, '_partial/record-info-section.php');
    }
}
