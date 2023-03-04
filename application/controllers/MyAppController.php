<?php

class MyAppController extends FatController
{
    public $app_user = array();
    public $appToken = '';
    public $themeDetail = '';

    public function __construct($action)
    {
        parent::__construct($action);
        $this->action = $action;
        $this->checkMaintenance();
        $this->initCommonVariables();
        $this->tempTokenLogin();
        $this->_template->addCss(CONF_MAIN_CSS_DIR_PATH . '/main-' . CommonHelper::getLayoutDirection() . '.css');
    }

    public function initCommonVariables()
    {
        CommonHelper::initCommonVariables();

        $this->siteLangId = CommonHelper::getLangId();
        $this->siteLangCode = CommonHelper::getLangCode();
        $this->siteLangCountryCode = CommonHelper::getLangCountryCode();
        $this->siteCurrencyId = CommonHelper::getCurrencyId();

        $this->app_user['temp_user_id'] = 0;
        if (true === MOBILE_APP_API_CALL) {
            $this->setApiVariables();
        }

        if (0 < FatApp::getPostedData('appUser', FatUtility::VAR_INT, 0)) {
            CommonHelper::setAppUser();
        }

        $this->set('siteLangId', $this->siteLangId);
        $this->set('siteLangCode', $this->siteLangCode);
        $this->set('siteCurrencyId', $this->siteCurrencyId);
        $this->set('siteLangCountryCode', $this->siteLangCountryCode);
        $loginData = array(
            'loginFrm' => $this->getLoginForm(),
            'siteLangId' => $this->siteLangId,
            'showSignUpLink' => true
        );
        $this->set('loginData', $loginData);
        if (!defined('CONF_MESSAGE_ERROR_HEADING')) {
            define('CONF_MESSAGE_ERROR_HEADING', Labels::getLabel('LBL_Following_error_occurred', $this->siteLangId));
        }

        $controllerName = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($controllerName));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $controllerName = ucfirst(FatUtility::dashed2Camel($urlController));

        $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        if (!FatUtility::isAjaxCall() && !in_array($controllerName, ['Cart', 'Checkout'])) {
            /* to keep track of temporary hold the product stock, update time in each row of tbl_product_stock_hold against current user[ */
            $cartObj->excludeTax();
            $cartProducts = $cartObj->getBasketProducts($this->siteLangId);
            if ($cartProducts) {
                foreach ($cartProducts as $product) {
                    $cartObj->updateTempStockHold($product['selprod_id'], $product['quantity']);
                }
            }
            /* ] */
        }

        if (true === MOBILE_APP_API_CALL) {
            $this->cartItemsCount = $cartObj->countProducts();
            $this->set('cartItemsCount', $this->cartItemsCount);
        }
        $defultCountryId = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
        $defaultCountryCode = Countries::getAttributesById($defultCountryId, 'country_code');

        $jsVariables = [];
        if (!FatUtility::isAjaxCall() && false === MOBILE_APP_API_CALL) {
            $jsVariablesCache = FatCache::get('jsVariablesCache' . $this->siteLangId, CONF_DEF_CACHE_TIME, '.txt');
            if (!$jsVariablesCache) {
                $jsVariables = array(
                    'confirmRemove' => Labels::getLabel('LBL_Do_you_want_to_remove', $this->siteLangId),
                    'confirmReset' => Labels::getLabel('LBL_Do_you_want_to_reset_settings', $this->siteLangId),
                    'confirmDelete' => Labels::getLabel('LBL_Do_you_want_to_delete', $this->siteLangId),
                    'confirmUpdateStatus' => Labels::getLabel('LBL_Do_you_want_to_update_the_status', $this->siteLangId),
                    'confirmDeleteOption' => Labels::getLabel('LBL_Do_you_want_to_delete_this_option', $this->siteLangId),
                    'confirmDefault' => Labels::getLabel('LBL_Do_you_want_to_set_default', $this->siteLangId),
                    'setMainProduct' => Labels::getLabel('LBL_Set_as_main_product', $this->siteLangId),
                    'layoutDirection' => CommonHelper::getLayoutDirection(),
                    'selectPlan' => Labels::getLabel('LBL_Please_Select_any_Plan_From_The_Above_Plans', $this->siteLangId),
                    'alreadyHaveThisPlan' => str_replace("{clickhere}", '<a href="' . UrlHelper::generateUrl('seller', 'subscriptions') . '">' . Labels::getLabel('LBL_Click_Here', $this->siteLangId) . '</a>', Labels::getLabel('LBL_You_have_already_Bought_this_plan._Please_choose_some_other_Plan_or_renew_it_from_{clickhere}', $this->siteLangId)),
                    'processing' => Labels::getLabel('LBL_Processing...', $this->siteLangId),
                    'requestProcessing' => Labels::getLabel('LBL_Request_Processing...', $this->siteLangId),
                    'selectLocation' => Labels::getLabel('LBL_Select_Location_to_view_Wireframe', $this->siteLangId),
                    'favoriteToShop' => Labels::getLabel('LBL_Favorite_To_Shop', $this->siteLangId),
                    'unfavoriteToShop' => Labels::getLabel('LBL_Unfavorite_To_Shop', $this->siteLangId),
                    'userNotLogged' => Labels::getLabel('MSG_User_Not_Logged', $this->siteLangId),
                    'selectFile' => Labels::getLabel('MSG_File_not_uploaded', $this->siteLangId),
                    'thanksForSharing' => Labels::getLabel('MSG_Thanks_For_Sharing', $this->siteLangId),
                    'isMandatory' => Labels::getLabel('VLBL_is_mandatory', $this->siteLangId),
                    'pleaseEnterValidEmailId' => Labels::getLabel('VLBL_Please_enter_valid_email_ID_for', $this->siteLangId),
                    'charactersSupportedFor' => Labels::getLabel('VLBL_Only_characters_are_supported_for', $this->siteLangId),
                    'pleaseEnterIntegerValue' => Labels::getLabel('VLBL_Please_enter_integer_value_for', $this->siteLangId),
                    'pleaseEnterNumericValue' => Labels::getLabel('VLBL_Please_enter_numeric_value_for', $this->siteLangId),
                    'startWithLetterOnlyAlphanumeric' => Labels::getLabel('VLBL_must_start_with_a_letter_and_can_contain_only_alphanumeric_characters._Length_must_be_between_4_to_20_characters', $this->siteLangId),
                    'mustBeBetweenCharacters' => Labels::getLabel('VLBL_Length_Must_be_between_6_to_20_characters', $this->siteLangId),
                    'invalidValues' => Labels::getLabel('VLBL_Length_Invalid_value_for', $this->siteLangId),
                    'shouldNotBeSameAs' => Labels::getLabel('VLBL_should_not_be_same_as', $this->siteLangId),
                    'mustBeSameAs' => Labels::getLabel('VLBL_must_be_same_as', $this->siteLangId),
                    'mustBeGreaterOrEqual' => Labels::getLabel('VLBL_must_be_greater_than_or_equal_to', $this->siteLangId),
                    'mustBeGreaterThan' => Labels::getLabel('VLBL_must_be_greater_than', $this->siteLangId),
                    'mustBeLessOrEqual' => Labels::getLabel('VLBL_must_be_less_than_or_equal_to', $this->siteLangId),
                    'mustBeLessThan' => Labels::getLabel('VLBL_must_be_less_than', $this->siteLangId),
                    'lengthOf' => Labels::getLabel('VLBL_Length_of', $this->siteLangId),
                    'valueOf' => Labels::getLabel('VLBL_Value_of', $this->siteLangId),
                    'mustBeBetween' => Labels::getLabel('VLBL_must_be_between', $this->siteLangId),
                    'mustBeBetween' => Labels::getLabel('VLBL_must_be_between', $this->siteLangId),
                    'and' => Labels::getLabel('VLBL_and', $this->siteLangId),
                    'pleaseSelect' => Labels::getLabel('VLBL_Please_select', $this->siteLangId),
                    'to' => Labels::getLabel('VLBL_to', $this->siteLangId),
                    'options' => Labels::getLabel('VLBL_options', $this->siteLangId),
                    'isNotAvailable' => Labels::getLabel('VLBL_is_not_available', $this->siteLangId),
                    'RemoveProductFromFavourite' => Labels::getLabel('LBL_Remove_product_from_favourite_list', $this->siteLangId),
                    'AddProductToFavourite' => Labels::getLabel('LBL_Add_Product_To_favourite_list', $this->siteLangId),
                    'MovedSuccessfully' => Labels::getLabel('LBL_Moved_Successfully', $this->siteLangId),
                    'RemovedSuccessfully' => Labels::getLabel('LBL_Removed_Successfully', $this->siteLangId),
                    'controllerName' => $controllerName,
                    'confirmDeletePersonalInformation' => Labels::getLabel('LBL_Do_you_really_want_to_remove_all_your_personal_information', $this->siteLangId),
                    'preferredDimensions' => Labels::getLabel('LBL_Preferred_Dimensions_%s', $this->siteLangId),
                    'invalidCredentials' => Labels::getLabel('LBL_Invalid_Credentials', $this->siteLangId),
                    'searchString' => Labels::getLabel('LBL_Search_string_must_be_atleast_3_characters_long.', $this->siteLangId),
                    'atleastOneRecord' => Labels::getLabel('LBL_Please_select_atleast_one_record.', $this->siteLangId),
                    'primaryLanguageField' => Labels::getLabel('LBL_PRIMARY_LANGUAGE_DATA_NEEDS_TO_BE_FILLED_FOR_SYSTEM_TO_TRANSLATE_TO_OTHER_LANGUAGES.', $this->siteLangId),
                    'unknownPrimaryLanguageField' => Labels::getLabel('LBL_PRIMARY_LANGUAGE_FIELD_IS_NOT_SET.', $this->siteLangId),
                    'invalidRequest' => Labels::getLabel('LBL_INVALID_REQUEST', $this->siteLangId),
                    'defaultCountryCode' => $defaultCountryCode,
                    'scrollable' => Labels::getLabel('LBL_SCROLLABLE', $this->siteLangId),
                    'quantityAdjusted' => Labels::getLabel('MSG_MAX_QUANTITY_THAT_CAN_BE_PURCHASED_IS_{QTY}._SO,_YOUR_REQUESTED_QUANTITY_IS_ADJUSTED_TO_{QTY}.', $this->siteLangId),
                    'withUsernameOrEmail' => Labels::getLabel('LBL_USE_EMAIL_INSTEAD', $this->siteLangId),
                    'withPhoneNumber' => Labels::getLabel('LBL_USE_PHONE_NUMBER_INSTEAD', $this->siteLangId),
                    'otpInterval' => User::OTP_INTERVAL,
                    'captchaSiteKey' => FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, ''),
                    'allowedFileSize' => LibHelper::getMaximumFileUploadSize(),
                    'fileSizeExceeded' => Labels::getLabel("MSG_FILE_SIZE_SHOULD_BE_LESSER_THAN_{SIZE-LIMIT}", $this->siteLangId),
                    'copyToClipboard' => Labels::getLabel('LBL_Copy_to_clipboard', $this->siteLangId),
                    'copied' => Labels::getLabel('LBL_Copied', $this->siteLangId),
                    'invalidGRecaptchaKeys' => Labels::getLabel('LBL_YOU_MIGHT_HAVE_INVALID_GOOGLE_RECAPTCHA_V3_KEYS._PLEASE_VERIFY.', $this->siteLangId),
                    'saveProfileFirst' => Labels::getLabel('LBL_Save_Profile_First', $this->siteLangId),
                    'minimumOneLocationRequired' => Labels::getLabel('LBL_Minimum_one_location_is_required', $this->siteLangId),
                    'processing_counter' => Labels::getLabel('LBL_{counter}_OUT_OF_{count}_RECORD_BATCHES.', $this->siteLangId),
                    'loadingCaptcha' => Labels::getLabel('LBL_Loading_Captcha...', $this->siteLangId),
                    'confirmPayment' => Labels::getLabel('LBL_CONFIRM_PAYMENT', $this->siteLangId),
                    'currentPrice' => Labels::getLabel('LBL_Current_Price', $this->siteLangId),
                    'discountPercentage' => Labels::getLabel('LBL_Discount_Percentage', $this->siteLangId),
                    'paymentSucceeded' => Labels::getLabel('LBL_PAYMENT_SUCCEEDED._WAITING_FOR_CONFIRMATION', $this->siteLangId),
                    'otpSent' => Labels::getLabel('MSG_OTP_SENT!', $this->siteLangId),
                    'proceed' => Labels::getLabel('MSG_PROCEED', $this->siteLangId),
                    'invalidFromTime' => Labels::getLabel('LBL_PLEASE_SELECT_VALID_FROM_TIME', $this->siteLangId),
                    'selectTimeslotDay' => Labels::getLabel('LBL_ATLEAST_ONE_DAY_AND_TIMESLOT_NEEDS_TO_BE_CONFIGURED', $this->siteLangId),
                    'invalidTimeSlot' => Labels::getLabel('LBL_PLEASE_CONFIGURE_FROM_AND_TO_TIME', $this->siteLangId),
                    'changePickup' => Labels::getLabel('LBL_CHANGE_PICKUP', $this->siteLangId),
                    'selectProduct' => Labels::getLabel('LBL_PLEASE_SELECT_PRODUCT', $this->siteLangId),
                    'noRecordFound' => Labels::getLabel('LBL_No_Record_Found', $this->siteLangId),
                    'waitingForResponse' => Labels::getLabel('MSG_WAITING_FOR_PAYMENT_RESPONSE..', $this->siteLangId),
                    'updatingRecord' => Labels::getLabel('MSG_RESPONSE_RECEIVED._UPDATING_RECORDS..', $this->siteLangId),
                    'requiredFields' => Labels::getLabel('MSG_PLEASE_FILL_REQUIRED_FIELDS', $this->siteLangId),
                    'alreadySelected' => Labels::getLabel('MSG_ALREADY_SELECTED', $this->siteLangId),
                    'typeToSearch' => Labels::getLabel('MSG_TYPE_TO_SEARCH..', $this->siteLangId),
                    'resendOtp' => Labels::getLabel('LBL_RESEND_OTP?', $this->siteLangId),
                    'redirecting' => Labels::getLabel('MSG_REDIRECTING...', $this->siteLangId),
                    'uploadImageLimit' => Labels::getLabel('MSG_YOU_ARE_NOT_ALLOWED_TO_ADD_MORE_THAN_8_IMAGES', $this->siteLangId),
                    'deleteAccount' => Labels::getLabel('MSG_ARE_YOU_SURE_?_DELETING_ACCOUNT_WILL_UNLINK_ALL_TRANSACTIONS_RELATED_TO_THIS_ACCOUNT.', $this->siteLangId),
                    'unlinkAccount' => Labels::getLabel('MSG_ARE_YOU_SURE_?_UNLINKING_ACCOUNT_WILL_UNLINK_ALL_TRANSACTIONS_RELATED_TO_THIS_ACCOUNT.', $this->siteLangId),
                    'dialCodeFieldNotFound' => Labels::getLabel('LBL_DIAL_CODE_FIELD_NOT_FOUND', $this->siteLangId),
                );

                $languages = Language::getAllNames(false);
                foreach ($languages as $val) {
                    $jsVariables['language' . $val['language_id']] = $val['language_layout_direction'];
                }
                FatCache::set('jsVariablesCache' . $this->siteLangId, serialize($jsVariables), '.txt');
            } else {
                $jsVariables =  unserialize($jsVariablesCache);
            }
        }

        $jsVariables['siteCurrencyId'] = $this->siteCurrencyId;

        $themeId = FatApp::getConfig('CONF_FRONT_THEME', FatUtility::VAR_INT, 1);

        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $themeId = $_SESSION['preview_theme'];
        }

        $this->themeDetail = ThemeColor::getById($themeId, $this->siteLangId, false, true);

        $currencySymbolLeft = CommonHelper::getCurrencySymbolLeft();
        $currencySymbolRight = CommonHelper::getCurrencySymbolRight();
        $this->includeDatePickerLangJs();

        $this->set('isUserDashboard', false);
        $this->set('currencySymbolLeft', $currencySymbolLeft);
        $this->set('currencySymbolRight', $currencySymbolRight);
        $this->set('themeDetail', $this->themeDetail);
        $this->set('jsVariables', $jsVariables);
        $this->set('controllerName', $controllerName);
        $this->set('isAppUser', CommonHelper::isAppUser());
        $this->set('action', $this->action);
    }

    private function setApiVariables()
    {
        $this->db = FatApp::getDb();
        $post = FatApp::getPostedData();

        $this->appToken = CommonHelper::getAppToken();

        $this->app_user['temp_user_id'] = 0;
        if (!empty($_SERVER['HTTP_X_TEMP_USER_ID'])) {
            $this->app_user['temp_user_id'] = $_SERVER['HTTP_X_TEMP_USER_ID'];
        }

        $forTempTokenBasedActions = array('send_to_web');
        if (('1.0' == MOBILE_APP_API_VERSION || in_array($this->action, $forTempTokenBasedActions) || empty($this->appToken)) && array_key_exists('_token', $post)) {
            $this->appToken = ($post['_token'] != '') ? $post['_token'] : '';
        }

        if ($this->appToken) {
            if (!UserAuthentication::isUserLogged('', $this->appToken)) {
                $arr = array('status' => -1, 'msg' => Labels::getLabel('L_Invalid_Token', $this->siteLangId));
                die(json_encode($arr));
            }

            $userId = UserAuthentication::getLoggedUserId();
            $userObj = new User($userId);
            if (!$row = $userObj->getProfileData()) {
                $arr = array('status' => -1, 'msg' => Labels::getLabel('L_Invalid_Token', $this->siteLangId));
                die(json_encode($arr));
            }
            $this->app_user = $row;
            $this->app_user['temp_user_id'] = 0;
        }

        if (array_key_exists('language', $post)) {
            $this->siteLangId = FatUtility::int($post['language']);
            $_COOKIE['defaultSiteLang'] = $this->siteLangId;
        }

        if (array_key_exists('currency', $post)) {
            $this->siteCurrencyId = FatUtility::int($post['currency']);
            $_COOKIE['defaultSiteCurrency'] = $this->siteCurrencyId;
        }

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);

        $this->currencySymbol = !empty($currencyRow['currency_symbol_left']) ? $currencyRow['currency_symbol_left'] : $currencyRow['currency_symbol_right'];
        $this->set('currencySymbol', $this->currencySymbol);

        $user_id = $this->getAppLoggedUserId();
        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj();
        $srch->addMultipleFields(array('u.*'));
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $this->user_details = $this->db->fetch($rs, 'user_id');
        /*$cObj = new Cart($user_id, 0, $this->app_user['temp_user_id']);
        $this->cartItemsCount = $cObj->countProducts();
        $this->set('cartItemsCount', $this->cartItemsCount);*/

        if (array_key_exists('favouriteItemsCount', $post) || in_array($this->_actionName, ['searchWishListItems'])) {
            $this->totalFavouriteItems = UserFavorite::getUserFavouriteItemCount($user_id);
            $this->set('totalFavouriteItems', $this->totalFavouriteItems);
        }

        if (array_key_exists('unreadMessageCount', $post)) {
            $this->totalUnreadMessageCount = 0;
            if (0 < $user_id) {
                $threadObj = new Thread();
                $this->totalUnreadMessageCount = $threadObj->getMessageCount($user_id);
            }
            $this->set('totalUnreadMessageCount', $this->totalUnreadMessageCount);
        }

        if (array_key_exists('unreadNotificationCount', $post) || ($this->_controllerName == 'HomeController' && in_array($this->_actionName, ['index', 'splashScreenData']))) {
            $this->totalUnreadNotificationCount = 0;
            if (0 < $user_id) {
                $notificationObj = new Notifications();
                $this->totalUnreadNotificationCount = $notificationObj->getUnreadNotificationCount($user_id);
            }
            $this->set('totalUnreadNotificationCount', $this->totalUnreadNotificationCount);
        }
    }

    private function getAppLoggedUserId()
    {
        return isset($this->app_user["user_id"]) ? $this->app_user["user_id"] : 0;
    }

    public function getStates($countryId, $stateId = 0, $return = false, $idCol = 'state_id')
    {
        $countryId = FatUtility::int($countryId);

        $stateObj = new States();
        $statesArr = $stateObj->getStatesByCountryId($countryId, $this->siteLangId, true, $idCol);

        if (true === $return) {
            return $statesArr;
        }

        $this->set('statesArr', $statesArr);
        $this->set('stateId', $stateId);
        $this->_template->render(false, false, '_partial/states-list.php');
    }

    public function getStatesByCountryCode($countryCode, $stateCode = '', $idCol = 'state_id')
    {
        $countryId = Countries::getCountryByCode($countryCode, 'country_id');
        $this->getStates($countryId, $stateCode, false, $idCol);
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $className = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($className));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $className = ucwords(implode(' ', $arr));

        if ($action == 'index') {
            $nodes[] = array('title' => Labels::getLabel('LBL_' . ucwords($className), $this->siteLangId));
        } else {
            $nodes[] = array('title' => ucwords($className), 'href' => UrlHelper::generateUrl($urlController));
            $nodes[] = array('title' => Labels::getLabel('LBL_' . ucwords($action), $this->siteLangId));
        }
        return $nodes;
    }

    public function checkIsShippingMode()
    {
        $json = array();
        $post = FatApp::getPostedData();
        if (isset($post["val"])) {
            if ($post["val"] == FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) {
                $json["shipping"] = 1;
            }
        }
        echo json_encode($json);
    }

    public function setUpNewsLetter()
    {
        include_once CONF_INSTALLATION_PATH . 'library/Mailchimp.php';
        $siteLangId = CommonHelper::getLangId();
        $post = FatApp::getPostedData();
        $frm = Common::getNewsLetterForm(CommonHelper::getLangId());
        $post = $frm->getFormDataFromArray($post);

        $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
        $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
        if ($api_key == '' || $list_id == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_Newsletter_is_not_configured_yet,_Please_contact_admin", $siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $MailchimpObj = new Mailchimp($api_key);
        $Mailchimp_ListsObj = new Mailchimp_Lists($MailchimpObj);

        try {
            $subscriber = $Mailchimp_ListsObj->subscribe($list_id, array('email' => htmlentities($post['email'])));
            if (empty($subscriber['leid'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_Newsletter_subscription_valid_email', $siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        } catch (Mailchimp_Error $e) {
            Message::addErrorMessage($e->getMessage());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Successfully_subscribed', $siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    protected function getGuestUserForm($langId = 0)
    {
        $siteLangId = FatUtility::int($langId);
        $frm = new Form('frmGuestLogin');
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $siteLangId), 'user_name', '', array('placeholder' => Labels::getLabel('LBL_Name', $siteLangId)));
        $fld = $frm->addEmailField(Labels::getLabel('LBL_EMAIL', $siteLangId), 'user_email', '', array('placeholder' => Labels::getLabel('LBL_EMAIL_ADDRESS', $siteLangId)));
        $fld->requirement->setRequired(true);

        $frm->addHtml('', 'space', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Guest_Sign_in', $siteLangId));
        return $frm;
    }

    protected function getLoginForm()
    {
        $siteLangId = CommonHelper::getLangId();
        $frm = new Form('frmLogin');
        $userName = '';
        $pass = '';
        if (CommonHelper::demoUrl()) {
            $userName = 'login@dummyid.com';
            $pass = 'kanwar@123';
        }
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_USERNAME_OR_EMAIL', $siteLangId), 'username', $userName, array('placeholder' => Labels::getLabel('LBL_USERNAME_OR_EMAIL', $siteLangId), 'data-alt-placeholder' => Labels::getLabel('LBL_PHONE_NUMBER', $siteLangId)));
        $pwd = $frm->addPasswordField(Labels::getLabel('LBL_Password', $siteLangId), 'password', $pass, array('placeholder' => Labels::getLabel('LBL_Password', $siteLangId)));
        $pwd->requirements()->setRequired();
        $frm->addCheckbox(Labels::getLabel('LBL_Remember_Me', $siteLangId), 'remember_me', 1, array(), '', 0);
        $frm->addHtml('', 'forgot', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_LOGIN', $siteLangId));
        return $frm;
    }

    protected function getRegistrationForm($showNewsLetterCheckBox = true, $signUpWithPhone = 0)
    {
        $siteLangId = $this->siteLangId;

        $frm = new Form('frmRegister');
        $frm->addHiddenField('', 'user_id', 0, array('id' => 'user_id'));
        $frm->addRequiredField(Labels::getLabel('LBL_NAME', $siteLangId), 'user_name', '', array('placeholder' => Labels::getLabel('LBL_NAME', $siteLangId)));
        $fld = $frm->addTextBox(Labels::getLabel('LBL_USERNAME', $siteLangId), 'user_username', '', array('placeholder' => Labels::getLabel('LBL_USERNAME', $siteLangId)));
        if (false === MOBILE_APP_API_CALL) {
            $fld->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');
        }
        $fld->requirements()->setRequired();
        $fld->requirements()->setUsername();

        if (0 < $signUpWithPhone) {
            $frm->addHiddenField('', 'signUpWithPhone', 1);
            $frm->addHiddenField('', 'user_phone_dcode');
            $frm->addRequiredField(Labels::getLabel('LBL_PHONE_NUMBER', $siteLangId), 'user_phone', '', array('placeholder' => Labels::getLabel('LBL_PHONE_NUMBER', $siteLangId), 'class' => 'phone-js'));
        } else {
            $fld = $frm->addEmailField(Labels::getLabel('LBL_EMAIL', $siteLangId), 'user_email', '', array('placeholder' => Labels::getLabel('LBL_EMAIL', $siteLangId)));
            if (false === MOBILE_APP_API_CALL) {
                $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');
            }
        }

        $fld = $frm->addPasswordField(Labels::getLabel('LBL_PASSWORD', $siteLangId), 'user_password', '', array('placeholder' => Labels::getLabel('LBL_PASSWORD', $siteLangId)));
        $fld->requirements()->setRequired();
        $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $siteLangId));

        $fld1 = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_PASSWORD', $siteLangId), 'password1', '', array('placeholder' => Labels::getLabel('LBL_CONFIRM_PASSWORD', $siteLangId)));
        $fld1->requirements()->setRequired();
        $fld1->requirements()->setCompareWith('user_password', 'eq', Labels::getLabel('LBL_PASSWORD', $siteLangId));

        $fld = $frm->addCheckBox('', 'agree', 1);
        $fld->requirements()->setRequired();
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Terms_Condition_is_mandatory.', $siteLangId));

        if (1 > $signUpWithPhone && $showNewsLetterCheckBox && FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION')) {
            $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
            $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
            if ($api_key != '' || $list_id != '') {
                $frm->addCheckBox(Labels::getLabel('LBL_Newsletter_Signup', $siteLangId), 'user_newsletter_signup', 1);
            }
        }

        $isCheckOutPage = false;
        if (isset($_SESSION['referer_page_url'])) {
            $checkoutPage = basename(parse_url($_SESSION['referer_page_url'], PHP_URL_PATH));
            if ($checkoutPage == 'checkout') {
                $isCheckOutPage = true;
            }
        }
        if ($isCheckOutPage) {
            $frm->addHiddenField('', 'isCheckOutPage', 1);
        }

        //$frm->addDateField(Labels::getLabel('LBL_DOB',CommonHelper::getLangId()), 'user_dob', '',array('readonly' =>'readonly'));
        //$frm->addTextBox(Labels::getLabel('LBL_PHONE',CommonHelper::getLangId()), 'user_phone');
        $frm->addSubmitButton(Labels::getLabel('LBL_Register', $siteLangId), 'btn_submit', Labels::getLabel('LBL_Register', $siteLangId));
        return $frm;
    }

    protected function getUserAddressForm($siteLangId, $btnOrderFlip = false)
    {
        $siteLangId = FatUtility::int($siteLangId);
        $frm = new Form('frmAddress');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Address_Label', $siteLangId), 'addr_title');
        $fld->requirement->setRequired(true);
        $fld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $siteLangId));
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $siteLangId), 'addr_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Address_Line1', $siteLangId), 'addr_address1');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Line2', $siteLangId), 'addr_address2');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($siteLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $siteLangId), 'addr_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY'), array(), Labels::getLabel('LBL_Select', $siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $siteLangId), 'addr_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $siteLangId))->requirement->setRequired(true);
        $frm->addRequiredField(Labels::getLabel('LBL_City', $siteLangId), 'addr_city');

        $zipFld = $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $this->siteLangId), 'addr_zip');
        /* $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId)); */

        $frm->addHiddenField('', 'addr_phone_dcode');
        $phnFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $siteLangId), 'addr_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phnFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_e.g.', $this->siteLangId) . ': ' . implode(', ', ValidateElement::PHONE_FORMATS) . '</span>';
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $frm->addHiddenField('', 'addr_id');
        if ($btnOrderFlip) {
            $fldCancel = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $siteLangId));
            $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $siteLangId));
            return $frm;
        }
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $siteLangId));
        $fldCancel = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $siteLangId));
        //$fldCancel->attachField($fldSubmit);
        return $frm;
    }

    protected function getProductSearchForm($addKeywordRelvancy = false)
    {
        $sortByArr = array(
            'price_asc' => Labels::getLabel('LBL_Price_(Low_to_High)', $this->siteLangId),
            'price_desc' => Labels::getLabel('LBL_Price_(High_to_Low)', $this->siteLangId),
            'popularity_desc' => Labels::getLabel('LBL_Sort_by_Popularity', $this->siteLangId),
            'discounted' => Labels::getLabel('LBL_Most_discounted', $this->siteLangId),
        );

        if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
            $sortByArr['rating_desc'] = Labels::getLabel('LBL_Sort_by_Rating', $this->siteLangId);
        }

        $sortBy = 'popularity_desc';
        if ($addKeywordRelvancy) {
            $sortByArr = array('keyword_relevancy' => Labels::getLabel('LBL_Keyword_Relevancy', $this->siteLangId)) + $sortByArr;
            $sortBy = 'keyword_relevancy';
        }

        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        $pageSizeArr = FilterHelper::getPageSizeArr($this->siteLangId);
        $frm = new Form('frmProductSearch');
        $frm->addTextBox('', 'keyword', '', array('id' => 'keyword'));
        $frm->addSelectBox('', 'sortBy', $sortByArr, $sortBy, array('id' => 'sortBy'), '');
        $frm->addSelectBox('', 'pageSize', $pageSizeArr, $pageSize, array('id' => 'pageSize'), '');
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'sortOrder', 'asc');
        $frm->addHiddenField('', 'category', 0);
        $frm->addHiddenField('', 'shop_id', 0);
        $frm->addHiddenField('', 'brand_id', 0);
        $frm->addHiddenField('', 'collection_id', 0);
        $frm->addHiddenField('', 'join_price', 0);
        $frm->addHiddenField('', 'featured', 0);
        $frm->addHiddenField('', 'top_products', 0);
        $frm->addHiddenField('', 'currency_id', $this->siteCurrencyId);
        $frm->addSubmitButton('', 'btnProductSrchSubmit', '');
        return $frm;
    }

    public function fatActionCatchAll($action)
    {
        $this->_template->render(false, false, 'error-pages/404.php');
    }

    public function setupPoll()
    {
        $siteLangId = CommonHelper::getLangId();
        $pollId = FatApp::getPostedData('pollfeedback_polling_id', FatUtility::VAR_INT, 0);
        if ($pollId <= 0) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = Common::getPollForm($pollId, $siteLangId);
        if (!$post = $frm->getFormDataFromArray(FatApp::getPostedData())) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieWithError(Message::getHtml());
        }
        $pollFeedback = new PollFeedback();
        if ($pollFeedback->isPollAnsweredFromIP($pollId, $_SERVER['REMOTE_ADDR'])) {
            Message::addErrorMessage(Labels::getLabel('Msg_Poll_already_posted_from_this_IP', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post['pollfeedback_response_ip'] = $_SERVER['REMOTE_ADDR'];
        $post['pollfeedback_added_on'] = date('Y-m-d H:i:s');

        $pollFeedback->assignValues($post);
        if (!$pollFeedback->save()) {
            Message::addErrorMessage($pollFeedback->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('Msg_Poll_Feedback_Sent_Successfully', $siteLangId));
    }

    protected function getChangeEmailForm($passwordField = true)
    {
        $frm = new Form('changeEmailFrm');
        $newEmail = $frm->addEmailField(
            Labels::getLabel('LBL_NEW_EMAIL', $this->siteLangId),
            'new_email'
        );
        $newEmail->requirements()->setRequired();

        $conNewEmail = $frm->addEmailField(
            Labels::getLabel('LBL_CONFIRM_NEW_EMAIL', $this->siteLangId),
            'conf_new_email'
        );
        $conNewEmailReq = $conNewEmail->requirements();
        $conNewEmailReq->setRequired();
        $conNewEmailReq->setCompareWith('new_email', 'eq');

        if ($passwordField) {
            $curPwd = $frm->addPasswordField(Labels::getLabel('LBL_CURRENT_PASSWORD', $this->siteLangId), 'current_password');
            $curPwd->requirements()->setRequired();
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $this->siteLangId));
        return $frm;
    }

    protected function getOtpForm()
    {
        $frm = new Form('otpFrm');
        $frm->addHiddenField('', 'user_id');
        if (true === MOBILE_APP_API_CALL) {
            $frm->addRequiredField('', 'upv_otp');
        } else {
            $attr = ['maxlength' => 1, 'size' => 1, 'placeholder' => '*'];
            for ($i = 0; $i < User::OTP_LENGTH; $i++) {
                $frm->addTextBox('', 'upv_otp[' . $i . ']', '', $attr);
            }
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_VERIFY', $this->siteLangId));
        return $frm;
    }

    protected function userEmailVerifications($userObj, $data, $configureEmail = false)
    {
        if (!$configureEmail) {
            $verificationCode = $userObj->prepareUserVerificationCode($data['user_new_email']);
        } else {
            $verificationCode = $userObj->prepareUserVerificationCode($data['user_email']);
        }

        $link = UrlHelper::generateFullUrl('GuestUser', 'changeEmailVerification', array('verify' => $verificationCode));

        $email = new EmailHandler();
        $dataArr = array(
            'user_name' => $data['user_name'],
            'link' => $link,
            'user_new_email' => $data['user_email'],
            'user_phone_dcode' => ValidateElement::formatDialCode($data['user_phone_dcode']),
            'user_phone' => $data['user_phone'],
        );

        if (!$configureEmail) {
            $dataArr = array(
                'user_name' => $data['user_name'],
                'user_phone_dcode' => ValidateElement::formatDialCode($data['user_phone_dcode']),
                'user_phone' => $data['user_phone'],
                'link' => $link,
                'user_new_email' => $data['user_new_email'],
                'user_email' => $data['user_email'],
            );
            if (!$email->sendChangeEmailRequestNotification($this->siteLangId, $dataArr)) {
                return false;
            }
        }

        if (!$email->sendEmailVerificationLink($this->siteLangId, $dataArr)) {
            return false;
        }
        return true;
    }

    public function includeDateTimeFiles()
    {
        $this->_template->addJs(array('js/jquery-ui-timepicker-addon.js'), false);
    }

    public function includeProductPageJsCss()
    {
        $this->_template->addJs('js/masonry.pkgd.js');
        $this->_template->addJs('js/product-search.js');
        $this->_template->addJs('js/ion.rangeSlider.js');
        $this->_template->addJs('js/listing-functions.js');
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

    public function getAppTempUserId()
    {
        if (array_key_exists('temp_user_id', $this->app_user) && !empty($this->app_user["temp_user_id"])) {
            return $this->app_user["temp_user_id"];
        }

        if ($this->appToken && UserAuthentication::isUserLogged('', $this->appToken)) {
            $userId = UserAuthentication::getLoggedUserId();
            if ($userId > 0) {
                return $userId;
            }
        }

        $generatedTempId = substr(md5(rand(1, 99999) . microtime()), 0, UserAuthentication::TOKEN_LENGTH);
        return $this->app_user['temp_user_id'] = $generatedTempId;
    }

    public function tempTokenLogin()
    {
        $forTempTokenBasedGetActions = array('downloadDigitalFile', 'charge');
        if (!in_array($this->action, $forTempTokenBasedGetActions)) {
            return;
        }

        $get = FatApp::getQueryStringData();
        if (empty($get) || !array_key_exists('ttk', $get)) {
            return;
        }

        $ttk = ($get['ttk'] != '') ? $get['ttk'] : '';

        if (strlen($ttk) != UserAuthentication::TOKEN_LENGTH) {
            FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Temp_Token', CommonHelper::getLangId()));
        }

        $userId = 0;
        if (!empty($get) && array_key_exists('user_id', $get)) {
            $userId = FatUtility::int($get['user_id']);
        }

        $uObj = new User($userId);
        if (!$user_temp_token_data = $uObj->validateAPITempToken($ttk)) {
            FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Token_Data', CommonHelper::getLangId()));
        }

        if (!$user = $uObj->getUserInfo(array('credential_username', 'credential_password', 'user_id'), true, true)) {
            FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Request', CommonHelper::getLangId()));
        }

        $authentication = new UserAuthentication();
        if ($authentication->login($user['credential_username'], $user['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
            $uObj->deleteUserAPITempToken();
        }
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
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
    }

    protected function getPhoneNumberForm()
    {
        $frm = new Form('phoneNumberFrm');
        $frm->addHiddenField('', 'user_phone_dcode');
        $frm->addRequiredField(Labels::getLabel('LBL_PHONE_NUMBER', $this->siteLangId), 'user_phone', '', array('placeholder' => Labels::getLabel('LBL_PHONE_NUMBER', $this->siteLangId)));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_GET_OTP', $this->siteLangId));
        return $frm;
    }

    public function validateOtpApi($updateToDb = 0, $doLogin = true)
    {
        $updateToDb = FatUtility::int($updateToDb);
        $recoverPwd = FatApp::getPostedData('recoverPwd', FatUtility::VAR_INT, 0);
        $doLogin = 0 < $recoverPwd ? false : $doLogin;

        $otpFrm = $this->getOtpForm();
        $post = $otpFrm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            LibHelper::dieJsonError(current($otpFrm->getValidationErrors()));
        }
        if (true === MOBILE_APP_API_CALL) {
            if (User::OTP_LENGTH != strlen($post['upv_otp'])) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_INVALID_OTP', $this->siteLangId));
            }
            $otp = $post['upv_otp'];
        } else {
            if (!is_array($post['upv_otp']) || User::OTP_LENGTH != count($post['upv_otp'])) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_INVALID_OTP', $this->siteLangId));
            }
            $otp = implode("", $post['upv_otp']);
        }

        $userId = FatApp::getPostedData('user_id', FatUtility::VAR_INT, 0);
        $userId = 1 > $userId ?  UserAuthentication::getLoggedUserId(true) : $userId;

        $obj = new User($userId);
        $resp = $obj->verifyUserPhoneOtp($otp, ($doLogin && false === MOBILE_APP_API_CALL && !UserAuthentication::isUserLogged()), true);
        if (false == $resp) {
            LibHelper::dieJsonError($obj->getError());
        }

        $this->set('msg', Labels::getLabel('MSG_OTP_MATCHED.', $this->siteLangId));

        if (0 < $recoverPwd && true === MOBILE_APP_API_CALL) {
            $obj = new UserAuthentication();
            $record = $obj->getUserResetPwdToken($userId);
            $token = $record['uprr_token'];
            $this->set('data', ['token' => $token]);
            $this->_template->render();
        }

        if (0 < $updateToDb) {
            $userObj = clone $obj;
            $userObj->assignValues(['user_phone_dcode' => $resp['upv_phone_dcode'], 'user_phone' => $resp['upv_phone']]);
            if (!$userObj->save()) {
                LibHelper::dieJsonError($userObj->getError());
            }
            $this->set('msg', Labels::getLabel('MSG_UPDATED_SUCCESSFULLY', $this->siteLangId));
        }

        if (true === MOBILE_APP_API_CALL) {
            if (!UserAuthentication::isUserLogged()) {
                $uObj = new User($userId);
                if (!$token = $uObj->setMobileAppToken()) {
                    LibHelper::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                }

                $userInfo = $uObj->getUserInfo(array('user_name', 'user_id', 'user_phone_dcode', 'user_phone', 'credential_email'), true, true, true);
                $data = array_merge(['token' => $token], $userInfo);
                $this->set('data', $data);
            }

            $this->_template->render();
        }
    }

    public function accessLocation()
    {
        if (true === CommonHelper::isAppUser()) {
            /* Restrict to open location popup in case of app webview. */
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_APP_ACCESS', $this->siteLangId));
        }

        $this->set('frm', $this->getGoogleAutocompleteAddressForm());
        $this->_template->render(false, false, '_partial/access-location.php');
    }

    protected function getGoogleAutocompleteAddressForm()
    {
        $frm = new Form('googleAutocomplete');
        $frm->addTextBox('', 'location', '', array('autocomplete' => 'off'));
        return $frm;
    }


    /*
     * You can override this function in child class if that class required any external js library.
     */
    public function getExternalLibraries()
    {
        $json['libraries'] = [];
        FatUtility::dieJsonSuccess($json);
    }


    /**
     * getTransferBankForm
     *
     * @param  mixed $langId
     * @param  mixed $orderId
     * @return object
     */
    public function getTransferBankForm(int $langId, string $orderId = ''): object
    {
        $frm = new Form('frmPayment');
        $frm->addHiddenField('', 'opayment_order_id', $orderId);
        $frm->addTextBox(Labels::getLabel('LBL_PAYMENT_METHOD', $langId), 'opayment_method');
        $frm->addTextBox(Labels::getLabel('LBL_TXN_ID', $langId), 'opayment_gateway_txn_id');
        $frm->addTextBox(Labels::getLabel('LBL_AMOUNT', $langId), 'opayment_amount')->requirements()->setFloatPositive(true);
        $frm->addTextArea(Labels::getLabel('LBL_COMMENTS', $langId), 'opayment_comments', '');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_CONFIRM_ORDER', $langId));
        return $frm;
    }

    private function checkMaintenance()
    {
        if (FatApp::getConfig("CONF_MAINTENANCE", FatUtility::VAR_INT, 0) == applicationConstants::NO) {
            return true;
        }

        if (in_array($this->_controllerName, ['MaintenanceController'])) {
            return true;
        }

        if (in_array($this->_actionName, ['setLanguage', 'updateUserCookies'])) {
            return true;
        }

        LibHelper::exitWithError(Labels::getLabel('MSG_SITE_UNDER_MAINTENANCE', CommonHelper::getLangId()));
        FatApp::redirectUser(UrlHelper::generateUrl('maintenance'));
    }
}
