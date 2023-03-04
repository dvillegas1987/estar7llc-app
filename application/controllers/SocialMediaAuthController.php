<?php

class SocialMediaAuthController extends PluginBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    protected function setErrorAndRedirect($message, $errRedirection = false)
    {
        if (false === $errRedirection || true === MOBILE_APP_API_CALL) {
            LibHelper::dieJsonError($message);
        }

        Message::addErrorMessage($message);
        FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'loginForm'));
    }

    protected function redirectToDashboard($preferredDashboard = 0, $referredRedirection = true)
    {
        $referredUrl = User::getPreferedDashbordRedirectUrl($preferredDashboard);
        $cartObj = new Cart();
        if ($cartObj->hasProducts()) {
            $referredUrl = UrlHelper::generateFullUrl('cart');
            if (true === $referredRedirection) {
                FatApp::redirectUser($referredUrl);
            }
        }
        if (isset($_SESSION['referer_page_url'])) {
            $referredUrl = $_SESSION['referer_page_url'];
            unset($_SESSION['referer_page_url']);
            if (true === $referredRedirection) {
                FatApp::redirectUser($referredUrl);
            }
        }
        if (false === $referredRedirection) {
            $message = Labels::getLabel('MSG_LOGGEDIN_SUCCESSFULLY', $this->siteLangId);
            $this->set('url', $referredUrl);
            $this->set('msg', $message);
            $this->_template->render(false, false, 'json-success.php');
        }
        FatApp::redirectUser($referredUrl);
    }

    protected function doLogin($email, $userName, $socialAccountID, $userType)
    {
        try {
            $keyName = get_called_class()::KEY_NAME;
        } catch (\Error $e) {
            $this->setErrorAndRedirect($e->getMessage(), (!MOBILE_APP_API_CALL));
        }
        $referralToken = FatApp::getPostedData('referralToken', FatUtility::VAR_STRING, '');

        $userObj = new User();
        $userInfo = $userObj->validateUser($email, $userName, $socialAccountID, $keyName, $userType, $referralToken);
        if (false === $userInfo) {
            $this->setErrorAndRedirect($userObj->getError(), (!MOBILE_APP_API_CALL));
        }

        if (true === MOBILE_APP_API_CALL) {
            $userId = $userInfo['user_id'];
            $userObj = new User($userId);
            if (!$token = $userObj->setMobileAppToken()) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }
            $this->set('token', $token);
            $this->set('userInfo', $userInfo);
            $this->_template->render(true, true, 'guest-user/login.php');
        }
        
        if (empty($userInfo['credential_email']) && empty($userInfo['user_phone'])) {
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

        return $userInfo;
    }

    public function getListing()
    {
        $socialLoginApis = Plugin::getDataByType(Plugin::TYPE_SOCIAL_LOGIN, $this->siteLangId);
        $this->set('data', ['socialLoginApis' => array_values($socialLoginApis)]);
        $this->_template->render();
    }

    public function getStatus()
    {
        $socialLoginApis = Plugin::getSocialLoginPluginsStatus($this->siteLangId);
        $this->set('data', ['status' => $socialLoginApis]);
        $this->_template->render();
    }
}
