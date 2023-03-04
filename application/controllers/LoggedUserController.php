<?php

class LoggedUserController extends MyAppController
{
    public $userParentId = 0;
    public $userId = 0;
    public $userInfo = [];

    public function __construct($action)
    {
        parent::__construct($action);

        UserAuthentication::checkLogin();
        $this->userId = UserAuthentication::getLoggedUserId();
        $userObj = new User($this->userId);
        $this->userInfo = $userObj->getUserInfo(array(), false, false);

        if (false == $this->userInfo || (!UserAuthentication::isGuestUserLogged() && $this->userInfo['credential_active'] != applicationConstants::ACTIVE)) {
            if (FatUtility::isAjaxCall()) {
                Message::addErrorMessage(Labels::getLabel('MSG_Session_seems_to_be_expired', CommonHelper::getLangId()));
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'logout'));
        }

        if (0 < $this->userInfo['user_parent']) {
            $parentUser = new User($this->userInfo['user_parent']);
            $parentUserInfo = $parentUser->getUserInfo(array(), true, true);
            if (false == $parentUserInfo || $parentUserInfo['credential_active'] != applicationConstants::ACTIVE) {
                if (FatUtility::isAjaxCall()) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Session_seems_to_be_expired', CommonHelper::getLangId()));
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'logout'));
            }
        }

        if (!isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $userPreferedDashboardType = ($this->userInfo['user_preferred_dashboard']) ? $this->userInfo['user_preferred_dashboard'] : $this->userInfo['user_registered_initially_for'];

            switch ($userPreferedDashboardType) {
                case User::USER_TYPE_BUYER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';
                    break;
                case User::USER_TYPE_SELLER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
                    break;
                case User::USER_TYPE_AFFILIATE:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';
                    break;
                case User::USER_TYPE_ADVERTISER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';
                    break;
            }
        }

        if ((!UserAuthentication::isGuestUserLogged() && $this->userInfo['credential_verified'] != 1) && !($_SESSION[User::ADMIN_SESSION_ELEMENT_NAME] && $_SESSION[User::ADMIN_SESSION_ELEMENT_NAME] > 0)) {
            FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'logout'));
        }

        if ($this->userId < 1) {
            FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'logout'));
        }

        /* Thease actions are used while configuring Phone from "Configure Email/Phone Page". */
        $allowedActions = ['getotp', 'resendotp', 'validateotp'];
        $addPhoneValidaion = true;
        if (true == SmsArchive::canSendSms()) {
            $addPhoneValidaion = empty($this->userInfo['user_phone']) ? true : false;
        }
        if (!in_array(strtolower($action), $allowedActions) && empty($this->userInfo['user_phone']) && empty($this->userInfo['credential_email'])) {
            if (true == SmsArchive::canSendSms()) {
                $message = Labels::getLabel('MSG_PLEASE_CONFIGURE_YOUR_EMAIL_OR_PHONE', $this->siteLangId);
            } else {
                $message = Labels::getLabel('MSG_PLEASE_CONFIGURE_YOUR_EMAIL', $this->siteLangId);
            }

            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'configureEmail'));
        }

        $this->userParentId = (0 < $this->userInfo['user_parent']) ? $this->userInfo['user_parent'] : UserAuthentication::getLoggedUserId();

        $this->initCommonValues();
    }

    private function initCommonValues()
    {
        $this->set('isUserDashboard', true);
        $this->userPrivilege = UserPrivilege::getInstance();
        $this->set('userPrivilege', $this->userPrivilege);
    }

    protected function getOrderCancellationRequestsSearchForm($langId)
    {
        $frm = new Form('frmOrderCancellationRequest');
        $frm->addTextBox('', 'op_invoice_number');
        $frm->addSelectBox('', 'ocrequest_status', array('-1' => Labels::getLabel('LBL_Status_Does_Not_Matter', $langId)) + OrderCancelRequest::getRequestStatusArr($langId), '', array(), '');
        $frm->addDateField('', 'ocrequest_date_from', '', array('readonly' => 'readonly'));
        $frm->addDateField('', 'ocrequest_date_to', '', array('readonly' => 'readonly'));

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearOrderCancelRequestSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    protected function getOrderReturnRequestsSearchForm($langId)
    {
        $frm = new Form('frmOrderReturnRequest');
        $frm->addTextBox('', 'keyword');
        $frm->addSelectBox('', 'orrequest_status', array('-1' => Labels::getLabel('LBL_Status_Does_Not_Matter', $langId)) + OrderReturnRequest::getRequestStatusArr($langId), '', array(), '');
        $returnRquestArray = OrderReturnRequest::getRequestTypeArr($langId);
        if (count($returnRquestArray) > applicationConstants::YES) {
            $frm->addSelectBox('', 'orrequest_type', array('-1' => Labels::getLabel('LBL_Request_Type_Does_Not_Matter', $langId)) + $returnRquestArray, '', array(), '');
        } else {
            $frm->addHiddenField('', 'orrequest_type', '-1');
        }
        $frm->addDateField('', 'orrequest_date_from', '', array('readonly' => 'readonly'));
        $frm->addDateField('', 'orrequest_date_to', '', array('readonly' => 'readonly'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearOrderReturnRequestSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    protected function getOrderReturnRequestMessageSearchForm($langId)
    {
        $frm = new Form('frmOrderReturnRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orrequest_id');
        return $frm;
    }

    protected function getOrderReturnRequestMessageForm($langId)
    {
        $frm = new Form('frmOrderReturnRequestMessge');
        $frm->setRequiredStarPosition('');
        $fld = $frm->addTextArea('', 'orrmsg_msg');
        $fld->requirements()->setRequired();
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_Message_is_mandatory', $langId));
        $frm->addHiddenField('', 'orrmsg_orrequest_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }
}
