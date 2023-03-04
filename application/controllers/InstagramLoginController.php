<?php

class InstagramLoginController extends SocialMediaAuthController
{
    public const KEY_NAME = 'InstagramLogin';
    public $settings = [];

    public function __construct($action)
    {
        parent::__construct($action);
        
        $error = '';
        $this->insta = PluginHelper::callPlugin(self::KEY_NAME, [$this->siteLangId], $error, $this->siteLangId);
        if (false === $this->insta) {
            $this->setErrorAndRedirect($error, true);
        }

        if (false === $this->insta->init()) {
            $this->setErrorAndRedirect($this->insta->getError(), true);
        }
        
        $this->settings = $this->insta->getSettings();
    }
    
    public function index()
    {
        $get = FatApp::getQueryStringData();
        $userType = FatApp::getPostedData('type', FatUtility::VAR_INT, User::USER_TYPE_BUYER);
        $accessToken = FatApp::getPostedData('accessToken', FatUtility::VAR_STRING, '');
        
        if (empty($accessToken)) {
            if (isset($get['code'])) {
                if (isset($get['code'])) {
                    if (false == $this->insta->requestAccessToken($get['code'])) {
                        $this->setErrorAndRedirect($this->insta->getError(), true);
                    }
                    $accessToken = $this->insta->getResponse()['access_token'];
                }
            }
        }

        if (!empty($accessToken)) {
            if (false == $this->insta->requestUserProfileInfo($accessToken)) {
                $this->setErrorAndRedirect($this->insta->getError(), true);
            }
            $userInfo = $this->insta->getResponse();
            $instagramId = $userInfo['id'];
            $userName = $userInfo['username'];
            $userName = $userName . $instagramId;
            if (empty($instagramId)) {
                $msg = Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId);
                $this->setErrorAndRedirect($msg, true);
            }

            $userInfo = $this->doLogin('', $userName, $instagramId, $userType);
            $this->redirectToDashboard($userInfo['user_preferred_dashboard']);
        }
        FatApp::redirectUser($this->insta->getRequestUri());
    }
}
