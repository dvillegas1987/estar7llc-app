<?php

class FacebookLoginController extends SocialMediaAuthController
{
    public const KEY_NAME = 'FacebookLogin';

    public function __construct($action)
    {
        parent::__construct($action);

        $error = '';
        $this->fb = PluginHelper::callPlugin(self::KEY_NAME, [$this->siteLangId], $error, $this->siteLangId);
        if (false === $this->fb) {
            $this->setErrorAndRedirect($error, true);
        }

        if (false === $this->fb->init()) {
            $this->setErrorAndRedirect($this->fb->getError(), true);
        }
    }
    
    public function index()
    {
        $get = FatApp::getQueryStringData();
        $userType = FatApp::getPostedData('type', FatUtility::VAR_INT, User::USER_TYPE_BUYER);
        $accessToken = FatApp::getPostedData('accessToken', FatUtility::VAR_STRING, '');

        if (empty($accessToken)) {
            if (!empty($get['code']) && false == $this->fb->loadAccessToken()) {
                $this->setErrorAndRedirect($this->fb->getError(), true);
            } else {
                $accessToken = $this->fb->getAccessToken();
            }
        }

        if (!empty($accessToken)) {
            $state = isset($get['state']) ? $get['state'] : '';

            if (false === $this->fb->verifyAccessToken($accessToken, $state)) {
                $this->setErrorAndRedirect($this->fb->getError(), true);
            }
            $resp = $this->fb->getResponse();
            $fbId = $resp->getId();
            $fbEmail = $resp->getEmail();
            $username = $fbEmail ?? $fbId;

            $userInfo = $this->doLogin($fbEmail, $username, $fbId, $userType);
            $this->redirectToDashboard($userInfo['user_preferred_dashboard']);
        }
        FatApp::redirectUser($this->fb->getRequestUri());
    }
}
