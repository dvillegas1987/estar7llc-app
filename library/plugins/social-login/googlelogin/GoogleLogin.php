<?php

class GoogleLogin extends SocialMediaAuthBase
{
    public const KEY_NAME = __CLASS__;

    private $client;
    private $redirectUri = '';
    private $clientData = [];

    public $requiredKeys = [
        'client_id',
        'client_secret',
        'developer_key'
    ];

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = FatUtility::int($langId);
        if (1 > $this->langId) {
            $this->langId = CommonHelper::getLangId();
        }
    }

    /**
     * init
     *
     * @return void
     */
    public function init(): bool
    {
        if (false == $this->validateSettings($this->langId)) {
            return false;
        }
        
        $this->redirectUri = $this->getRedirectUri();

        $this->client = new Google_Client();
        $this->client->setApplicationName(FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->langId));
        $this->client->setScopes(['email']);
        $this->client->setClientId($this->settings['client_id']);
        $this->client->setClientSecret($this->settings['client_secret']);
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->setDeveloperKey($this->settings['developer_key']);

        return true;
    }
    
    /**
     * getRedirectUri
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return !empty($this->redirectUri) ? $this->redirectUri : UrlHelper::generateFullUrl(self::KEY_NAME, 'index', [], '', false);
    }
    
    /**
     * getResponse
     *
     * @return object
     */
    public function getResponse(): object
    {
        return empty($this->response) ? (object) array() : $this->response;
    }
    
    /**
     * authenticate
     *
     * @param string $code
     * @return bool
     */
    public function authenticate(string $code): bool
    {
        if (empty($code)) {
            $this->error = Labels::getLabel('MSG_BAD_REQUEST', $this->langId);
            return false;
        }

        $resp = $this->client->authenticate($code);
        if (isset($resp['error']) && isset($resp['error_description'])) {
            $this->error = $resp['error'] . ' - ' . $resp['error_description'];
            return false;
        }
        return true;
    }

    /**
     * getAccessToken
     *
     * @return bool
     */
    public function getAccessToken(): string
    {
        $accessToken = $this->client->getAccessToken();
        return null == $accessToken || !isset($accessToken['access_token']) ? '' : $accessToken['access_token'];
    }

    /**
     * setAccessToken
     *
     * @return bool
     */
    public function setAccessToken(string $accessToken): bool
    {
        if (empty($accessToken)) {
            $this->error = Labels::getLabel('MSG_BAD_REQUEST', $this->langId);
            return false;
        }
        $resp = $this->client->setAccessToken($accessToken);
        return true;
    }

    /**
     * loadClientData
     *
     * @return bool
     */
    public function loadClientData(): bool
    {
        $this->oauth2 = new Google_Service_Oauth2($this->client);
        $this->clientData = $this->oauth2->userinfo->get();
        return true;
    }

    /**
     * getClientData
     *
     * @return array
     */
    public function getClientData(): array
    {
        return (array) $this->clientData;
    }

    /**
     * getAuthUrl
     *
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * isAccessTokenExpired
     *
     * @return bool
     */
    public function isAccessTokenExpired(): bool
    {
        return $this->client->isAccessTokenExpired();
    }

    /**
     * refreshAccessToken
     *
     * @return string
     */
    public function refreshAccessToken(): string
    {
        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        return $this->getAccessToken();
    }
}
