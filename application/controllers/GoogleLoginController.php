<?php

class GoogleLoginController extends SocialMediaAuthController
{
    public const KEY_NAME = 'GoogleLogin';

    public function __construct($action)
    {
        parent::__construct($action);

        $error = '';
        $this->google = PluginHelper::callPlugin(self::KEY_NAME, [$this->siteLangId], $error, $this->siteLangId);
        if (false === $this->google) {
            $this->setErrorAndRedirect($error, true);
        }

        if (false === $this->google->init()) {
            $this->setErrorAndRedirect($this->google->getError(), true);
        }
    }

    public function index()
    {
        $get = FatApp::getQueryStringData();
        $userType = FatApp::getPostedData('type', FatUtility::VAR_INT, User::USER_TYPE_BUYER);
        $accessToken = FatApp::getPostedData('accessToken', FatUtility::VAR_STRING, '');
        
        if (true === MOBILE_APP_API_CALL && empty($accessToken)) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            $this->setErrorAndRedirect($message, true);
        }
        
        if (!empty($accessToken) || isset($get['code'])) {
            if (empty($accessToken)) {
                $this->google->authenticate($get['code']);
                $accessToken = $this->google->getAccessToken();
                if (empty($accessToken)) {
                    $message = Labels::getLabel('MSG_UNABLE_TO_ACCESS_THIS_ACCOUNT', $this->siteLangId);
                    $this->setErrorAndRedirect($message, true);
                }
            }
            $this->google->setAccessToken($accessToken);
            $this->google->loadClientData();

            $user = $this->google->getClientData();

            $userGoogleEmail = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
            $userGoogleId = $user['id'];
            $userGoogleName = $user['name'];

            if ($user['name'] == '') {
                $exp = explode("@", $user['email']);
                $userGoogleName = substr($exp[0], 0, 80);
            }
            
            $userInfo = $this->doLogin($userGoogleEmail, $userGoogleName, $userGoogleId, $userType);
            $this->redirectToDashboard($userInfo['user_preferred_dashboard']);
        }
        FatApp::redirectUser($this->google->getAuthUrl());
    }
}
