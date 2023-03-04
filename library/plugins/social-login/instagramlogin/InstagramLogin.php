<?php

class InstagramLogin extends SocialMediaAuthBase
{
    public const KEY_NAME = __CLASS__;
    private const PRODUCTION_URL = 'https://api.instagram.com/oauth/';
    private const ACCESS_TOKEN_URL = self::PRODUCTION_URL . 'access_token';
    private const REQ_USER_PROFILE_URL = 'https://graph.instagram.com/me?fields=id,username&access_token=';

    public $requiredKeys = [
        'client_id',
        'client_secret'
    ];

    private $response = [];
    private $redirectUri = '';

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
        return true;
    }

    /**
     * getRedirectUri
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return preg_replace("/^http:/i", "https:", (!empty($this->redirectUri) ? $this->redirectUri : UrlHelper::generateFullUrl('','', [], CONF_WEBROOT_URL) . 'public/instalogin.php'));
    }

    /**
     * getRequestUri
     *
     * @return void
     */
    public function getRequestUri()
    {
        return self::PRODUCTION_URL . 'authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->settings['client_id'],
            'scope' => 'user_profile,user_media',
            'redirect_uri' => $this->getRedirectUri()
        ]);
    }
    
    /**
     * requestAccessToken
     *
     * @param  string $code
     * @return bool
     */
    public function requestAccessToken(string $code): bool
    {
        $curlPost = 'client_id=' . $this->settings['client_id'] . '&redirect_uri=' . $this->getRedirectUri() . '&client_secret=' . $this->settings['client_secret'] . '&code=' . $code . '&grant_type=authorization_code';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ACCESS_TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

        $this->response = json_decode(curl_exec($ch), true);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code != '200') {
            $this->error = Labels::getLabel('MSG_FAILED_TO_RETRIEVE_ACCESS_TOKEN', $this->langId);
            return false;
        }

        return true;
    }
    
    /**
     * requestUserProfileInfo
     *
     * @param  string $access_token
     * @return bool
     */
    public function requestUserProfileInfo(string $access_token): bool
    {
        $url = self::REQ_USER_PROFILE_URL . $access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $this->response = json_decode(curl_exec($ch), true);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (empty($this->response)) {
            $this->error = Labels::getLabel('MSG_FAILED_TO_RETRIEVE_USER_INFO', $this->langId);
            return false;
        }

        if (!empty($this->response['error'])) {
            $this->error = $this->response['error']['message'];
            return false;
        }
        return true;
    }
    
    /**
     * getResponse
     *
     * @return array
     */
    public function getResponse(): array
    {
        return (array) $this->response;
    }
}
