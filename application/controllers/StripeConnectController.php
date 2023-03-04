<?php

class StripeConnectController extends PaymentMethodBaseController
{
    public const KEY_NAME = 'StripeConnect';
    private $stripeConnect;

    /**
     * __construct
     *
     * @param  mixed $action
     * @return void
     */
    public function __construct(string $action)
    {
        parent::__construct($action);
        $this->init();
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        $error = '';
        $this->stripeConnect = PluginHelper::callPlugin(self::KEY_NAME, [$this->siteLangId], $error, $this->siteLangId);
        if (false === $this->stripeConnect) {
            FatUtility::dieJsonError($error);
        }

        if (1 > $this->userParentId) {
            $msg = Labels::getLabel('MSG_INVALID_USER', $this->siteLangId);
            FatUtility::dieJsonError($msg);
        }

        if (false === User::isSeller()) {
            $msg = Labels::getLabel('MSG_LOGGED_USED_MUST_BE_SELLER_TYPE', $this->siteLangId);
            FatUtility::dieJsonError($msg);
        }

        if (false === $this->stripeConnect->init($this->userParentId, true)) {
            $this->setError();
        }

        if (!empty($this->stripeConnect->getError())) {
            $this->setError();
        }
    }

    /**
     * setError
     *
     * @param  mixed $msg
     * @return void
     */
    private function setError(string $msg = "")
    {
        $msg = !empty($msg) ? $msg : $this->stripeConnect->getError();
        LibHelper::exitWithError($msg, true);
    }


    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $accountId = $this->stripeConnect->getAccountId();
        if (!empty($accountId)) {
            if (true === $this->stripeConnect->isUserAccountRejected()) {
                $this->setError();
            }
        }
        $requiredFields = $this->stripeConnect->getRequiredFields();
        $stripeUserData = [];
        if (empty($requiredFields) && !empty($accountId)) {
            if (false === $this->stripeConnect->loadRemoteUserInfo()) {
                $this->setError();
            }
            $stripeUserData = $this->stripeConnect->getResponse()->toArray();
        }
        
        /* This will return url only for ExpressAccount connected to admin account. */
        $this->stripeConnect->createLoginLink();

        /* Check if user account has errors. */
        $this->stripeConnect->checkUserAccountIsIncomplete();

        $this->set('isSubUser', (0 < (int) $this->userInfo['user_parent']));
        $this->set('userAccountErrors', $this->stripeConnect->getError());
        $this->set('loginUrl', $this->stripeConnect->getLoginUrl());
        $this->set('accountId', $accountId);
        $this->set('userAccountIsValid', $this->stripeConnect->userAccountIsValid());
        $this->set('initialFormSubmitted', empty($this->stripeConnect->initialFormSubmitted()));
        $this->set('requiredFields', $requiredFields);
        $this->set('keyName', self::KEY_NAME);
        $this->set('pluginName', $this->getPluginData()['plugin_name']);
        $this->set('stripeAccountType', $this->stripeConnect->getAccountType());
        $this->set('stripeUserData', $stripeUserData);
        $json['status'] = 1;
        $json['html'] = $this->_template->render(false, false, 'stripe-connect/index.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        if (false === $this->stripeConnect->register()) {
            $this->setError();
        }
        $msg = Labels::getLabel('MSG_SETUP_SUCCESSFULLY', $this->siteLangId);
        FatUtility::dieJsonSuccess($msg);
    }

    /**
     * completeAccount()
     *
     * @return void
     */
    public function completeAccount()
    {
        if (false === $this->stripeConnect->requestAccountLinks()) {
            $this->setError();
        }
        $resp = $this->stripeConnect->getResponse()->toArray();
        $json = [
            'msg' => Labels::getLabel('MSG_REDIRECTING...', $this->siteLangId),
            'link' => $resp['url']
        ];
        FatUtility::dieJsonSuccess($json);
    }

    /**
     * requiredFieldsForm
     *
     * @return void
     */
    public function requiredFieldsForm()
    {
        $businessType = FatApp::getPostedData('businessType', FatUtility::VAR_STRING, 'individual');
        $businessType = 'undefined' == $businessType ? 'individual' : $businessType;

        $frm = $this->getRequiredFieldsForm($businessType);
        if (false === $frm) {
            FatUtility::dieJsonSuccess($this->msg);
        }

        $initialFieldsValue = $this->stripeConnect->initialFieldsValue();
        $frm->fill($initialFieldsValue);

        $stateCode = isset($initialFieldsValue['business_profile']['support_address']['state']) ? $initialFieldsValue['business_profile']['support_address']['state'] : '';
        $errors = $this->stripeConnect->getErrorWhileUpdate();

        $this->set('errors', $errors);
        $this->set('frm', $frm);
        $this->set('stateCode', $stateCode);
        $this->set('keyName', self::KEY_NAME);
        $this->set('termAndConditionsUrl', $this->stripeConnect::TERMS_AND_SERVICES_URI);
        $json['status'] = 0;
        $json['html'] = $this->_template->render(false, false, 'stripe-connect/required-fields-form.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    /**
     * validateResponse
     *
     * @param  mixed $resp
     * @return void
     */
    private function validateResponse($resp)
    {
        if (false === $resp) {
            Message::addErrorMessage($this->stripeConnect->getError());
            FatApp::redirectUser(UrlHelper::generateUrl('seller', 'shop', [self::KEY_NAME]));
        }
        return true;
    }

    /**
     * setupRequiredFields
     *
     * @return void
     */
    public function setupRequiredFields()
    {
        $post = array_filter(FatApp::getPostedData());
        $redirect = true;
        if (isset($post['fIsAjax'])) {
            $redirect = false;
            unset($post['fOutMode'], $post['fIsAjax']);
        }

        if (array_key_exists('merchantCatCode', $post)) {
            unset($post['merchantCatCode']);
        }

        if (array_key_exists('verification', $_FILES) && !empty($this->stripeConnect->getRelationshipPersonId())) {
            foreach ($_FILES['verification']['tmp_name']['document'] as $side => $filePath) {
                $resp = $this->stripeConnect->uploadVerificationFile($filePath);
                $this->validateResponse($resp);
                $resp = $this->stripeConnect->updatePersonVerificationDocument($side);

                $this->validateResponse($resp);
            }
        }

        if (array_key_exists('individual', $_FILES) && array_key_exists('tmp_name', $_FILES['individual']) && is_array($_FILES['individual']['tmp_name']) && isset($_FILES['individual']['tmp_name']['verification'])) {
            foreach ($_FILES['individual']['tmp_name']['verification'] as $fieldName => $fieldData) {
                foreach ($fieldData as $side => $filePath) {
                    if (empty($filePath)) {
                        continue;
                    }

                    $resp = $this->stripeConnect->uploadVerificationFile($filePath);
                    $this->validateResponse($resp);

                    $requestParam = [
                        'individual' => [
                            'verification' => [
                                $fieldName => [
                                    $side => $this->stripeConnect->getResponse()
                                ]
                            ]
                        ]
                    ];
                    $resp = $this->stripeConnect->updateRequiredFields($requestParam);
                    $this->validateResponse($resp);
                }
            }
        }

        if (false === $this->stripeConnect->updateRequiredFields($post)) {
            $msg = $this->stripeConnect->getError();
            if (true === $redirect) {
                Message::addErrorMessage($msg);
                FatApp::redirectUser(UrlHelper::generateUrl('seller', 'shop', [self::KEY_NAME]));
            }
            FatUtility::dieJsonError($msg);
        }
        $msg = Labels::getLabel('MSG_SUCCESS', $this->siteLangId);
        if (true === $redirect) {
            Message::addMessage($msg);
            FatApp::redirectUser(UrlHelper::generateUrl('seller', 'shop', [self::KEY_NAME]));
        }

        FatUtility::dieJsonSuccess($msg);
    }

    /**
     * getRequiredFieldsForm
     *
     * @return object
     */
    private function getRequiredFieldsForm(string $businessType = 'individual')
    {
        $fieldsData = $this->stripeConnect->getRequiredFields();
        if (empty($fieldsData)) {
            $this->msg = Labels::getLabel('MSG_SUCCESSFULLY_SUBMITTED_TO_REVIEW', $this->siteLangId);
            return false;
        }

        $userObj = new User($this->userParentId);
        $userEmail = current($userObj->getUserInfo('credential_email'));

        $frm = new Form('frm' . self::KEY_NAME);
        $stateFldClass = '';
        $j = 0;
        foreach ($fieldsData as $field => $labelData) {
            $labelStr = $labelData;
            $htmlAfterField = "";
            $required = true;
            if (is_array($labelData)) {
                $labelStr = $labelData['title'];
                $htmlAfterField = $labelData['description'];
                $required = $labelData['required'];
            }

            $name = $label = $field;
            $labelParts = [];
            if (false !== strpos($field, ".")) {
                $labelParts = explode(".", $field);
                $label = implode(" ", $labelParts);
                $name = $labelParts[0];
                foreach ($labelParts as $i => $nameVal) {
                    if (0 == $i) {
                        continue;
                    }
                    $name .= '[' . $nameVal . ']';
                }
            }

            if (false !== strpos($label, 'person_')) {
                $personId = $this->getUserMeta('stripe_person_id');
                $label = str_replace($personId, "Person", $label);
            }

            if ('business_type' === $field) {
                $options = [
                    'individual' => Labels::getLabel('LBL_INDIVIDUAL', $this->siteLangId),
                    'company' => Labels::getLabel('LBL_COMPANY', $this->siteLangId),
                    'non_profit' => Labels::getLabel('LBL_NON_PROFIT', $this->siteLangId),
                    'government_entity' => Labels::getLabel('LBL_GOVERNMENT_ENTITY_(_US_ONLY_)', $this->siteLangId)
                ];
                $fld = $frm->addSelectBox($labelStr, 'business_type', $options, $businessType, [], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif (in_array(end($labelParts), $this->stripeConnect->boolParams)) {
                $options = [
                    0 => Labels::getLabel('LBL_NO', $this->siteLangId),
                    1 => Labels::getLabel('LBL_YES', $this->siteLangId)
                ];
                $fld = $frm->addSelectBox($labelStr, $name, $options, '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif (false !== strpos($field, 'verification.document') || false !== strpos($field, 'verification.additional_document')) {
                /* if (empty($this->stripeConnect->getRelationshipPersonId())) {
                    continue;
                } */
                if (false !== strpos($field, 'verification.additional_document')) {
                    $lbl = Labels::getLabel("LBL_INDIVIDUAL_ADDITIONAL_IDENTIFYING_DOCUMENT", $this->siteLangId);
                } else {
                    $lbl = Labels::getLabel("LBL_IDENTIFYING_DOCUMENT,_EITHER_A_PASSPORT_OR_LOCAL_ID_CARD", $this->siteLangId);
                }

                $lblFront = $lbl . ' (' . Labels::getLabel("LBL_FRONT", $this->siteLangId) . ')';
                $lblBack = $lbl . ' (' . Labels::getLabel("LBL_BACK", $this->siteLangId) . ')';
                $htmlAfterField .= Labels::getLabel("LBL_THE_UPLOADED_FILE_NEEDS_TO_BE_A_COLOR_IMAGE_(SMALLER_THAN_8,000PX_BY_8,000px),_IN_JPG,_PNG,_OR_PDF_FORMAT,_AND_LESS_THAN_10_MB_IN_SIZE.", $this->siteLangId);

                $fld = $frm->addFileUpload($lblFront, $name . '[front]');
                $fld2 = $frm->addFileUpload($lblBack, $name . '[back]');
                $fld2->requirement->setRequired(true);
                $fld2->htmlAfterField = '<p class="note">' . $htmlAfterField . '</p>';

                $frm->addFormTagAttribute('enctype', 'multipart/form-data');
            } elseif (false !== strpos($field, 'state')) {
                $country = '';
                if (empty($stateFldClass)) {
                    if (false === $this->stripeConnect->loadRemoteUserInfo()) {
                        $this->setError();
                    }
                    $stripeUserData = $this->stripeConnect->getResponse()->toArray();
                    for ($i = 0; $i < count($labelParts); $i++) {
                        if ($labelParts[$i] == 'state') {
                            $country = $country['country'];
                        } else {
                            $country = empty($country) ? $stripeUserData[$labelParts[$i]] : $country[$labelParts[$i]];
                        }
                    }
                }

                $fld = $frm->addSelectBox($labelStr, $name, [], '', ['class' => (empty($stateFldClass) ? 'state' : $stateFldClass), 'disabled' => 'disabled', 'data-country' => $country], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif (false !== strpos($field, 'month')) {
                $months = [];
                for ($i = 1; $i <= 12; $i++) {
                    $months[$i] = $i;
                }
                $fld = $frm->addSelectBox($labelStr, $name, $months, '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif (false !== strpos($field, 'day')) {
                $days = [];
                for ($i = 1; $i <= 31; $i++) {
                    $days[$i] = $i;
                }
                $fld = $frm->addSelectBox($labelStr, $name, $days, '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif (false !== strpos($field, 'year')) {
                $years = [];
                for ($i = 1900; $i <= (date('Y') - 13); $i++) {
                    $years[$i] = $i;
                }
                $fld = $frm->addSelectBox($labelStr, $name, $years, '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif (false !== strpos($field, 'country')) {
                $stateFldClass = md5($name);
                $countryObj = new Countries();
                $countriesArr = $countryObj->getCountriesArr($this->siteLangId, true, 'country_code');
                $fld = $frm->addSelectBox($labelStr, $name, $countriesArr, '', ['class' => 'country', 'data-statefield' => $stateFldClass], Labels::getLabel('LBL_Select', $this->siteLangId));
            } elseif ('tos_acceptance' == $field) {
                $fld = $frm->addCheckBox('', 'tos_acceptance', 1);
            } elseif (false !== strpos($field, 'mcc')) {
                $frm->addHiddenField('', $name, '', ['class' => 'mccValue-js' . $j]);
                $placeholder = Labels::getLabel('LBL_SEARCH...', $this->siteLangId);
                $fld = $frm->addSelectBox($labelStr, 'merchantCatCode', [], '', ['class' => 'mcc--js', 'data-valfld' => 'mccValue-js' . $j, 'placeholder' => $placeholder], $placeholder);
            } elseif (false !== strpos($field, 'phone')) {
                $fld = $frm->addTextBox($labelStr, $name, '', ['class' => 'phone-js onlyFlag--js']);
            } elseif (false !== strpos($field, 'email')) {
                $fld = $frm->addTextBox($labelStr, $name, $userEmail);
            } else {
                $fld = $frm->addTextBox($labelStr, $name);
            }
            $fld->requirement->setRequired($required);
            if (!empty($htmlAfterField)) {
                $fld->htmlAfterField = '<p class="note">' . $htmlAfterField . '</p>';
            }
            $j++;
        }

        if (0 < $j) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $this->siteLangId));
            $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearForm();'));
        }

        return $frm;
    }

    /**
     * deleteAccount
     *
     * @return void
     */
    public function deleteAccount()
    {
        if (false === $this->stripeConnect->deleteAccount()) {
            $this->setError();
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_DELETED_SUCCESSFULLY', $this->siteLangId));
    }

    /**
     * getMerchantCategory
     *
     * @return void
     */
    public function getMerchantCategory()
    {
        $data = $this->stripeConnect->getMerchantCategory();
        CommonHelper::jsonEncodeUnicode($data, true);
    }

    /**
     * unlinkAccount
     *
     * @return void
     */
    public function unlinkAccount()
    {
        if (false === $this->stripeConnect->unlinkAccount()) {
            $this->setError();
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_UNLINKED_SUCCESSFULLY', $this->siteLangId));
    }
}
