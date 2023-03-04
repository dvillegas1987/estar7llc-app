<?php

/**
 * Mpesa - M-Pesa services in 10 countries: Albania, the Democratic Republic of Congo, Egypt, Ghana, India, Kenya, Lesotho, Mozambique, Romania and Tanzania. 
 */
class Mpesa extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;
    public const PRODUCTION_URL = 'https://api.safaricom.co.ke';
    public const SANDBOX_URL = 'https://sandbox.safaricom.co.ke';

    public $requiredKeys = [
        'shortcode',
        'passkey',
    ];
    private $env = Plugin::ENV_SANDBOX;
    private $shortcode = '';
    private $passkey = '';
    protected $response = '';

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();
        $this->requiredKeys();
    }

    /**
     * requiredKeys
     *
     * @return void
     */
    public function requiredKeys()
    {
        $this->env = FatUtility::int($this->getKey('env'));
        if (0 < $this->env) {
            $this->requiredKeys = [
                'live_shortcode',
                'live_passkey',
            ];
        }
    }

    /**
     * init
     *
     * @param  int $userId
     * @return bool
     */
    public function init(int $userId): bool
    {
        if (false == $this->validateSettings()) {
            return false;
        }

        if (false === $this->loadBaseCurrencyCode()) {
            return false;
        }

        if (0 < $userId) {
            if (false === $this->loadLoggedUserInfo($userId)) {
                return false;
            }
        }

        $this->shortcode = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_shortcode'] : $this->settings['shortcode'];
        $this->passkey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_passkey'] : $this->settings['passkey'];
        return true;
    }

    /**
     * getActionUrl
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        return Plugin::ENV_PRODUCTION == $this->env ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    /**
     * getResponse
     *
     * @return array
     */
    public function getResponse(): array
    {
        return (array)json_decode($this->response, true);
    }

    /**
     * STKPushSimulationUrl
     *
     * @return string
     */
    public function STKPushSimulationUrl(): string
    {
        return $this->getActionUrl() . '/mpesa/stkpush/v1/processrequest';
    }

    /**
     * STKPushQueryUrl
     *
     * @return string
     */
    public function STKPushQueryUrl(): string
    {
        return $this->getActionUrl() . '/mpesa/stkpushquery/v1/query';
    }

    /**
     * tokenUrl
     *
     * @return string
     */
    public function tokenUrl(): string
    {
        return $this->getActionUrl() . '/oauth/v1/generate?grant_type=client_credentials';
    }

    /**
     * callbackUrl
     *
     * @param  string $orderId
     * @return string
     */
    public function callbackUrl(string $orderId): string
    {
        return UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', "callback", [$orderId]);
    }

    /**
     * generateToken - This is used to generate tokens for the sandbox/live environment
     *
     * @return bool
     */
    public function generateToken(): bool
    {
        $credentials = base64_encode($this->settings["consumer_key"] . ':' . $this->settings["consumer_secret"]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->tokenUrl());
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if (!$this->response = curl_exec($curl)) {
            $this->error = curl_error($curl);
            if (empty($this->error)) {
				$this->error = Labels::getLabel('MSG_UNABLE_TO_GENERATE_TOKEN', $this->langId);
			}
            return false;
        }
        curl_close($curl);
        return true;
    }

    /**
     * getToken
     *
     * @return string
     */
    public function getToken(): string
    {
        $response = $this->getResponse();
        return !empty($response) && isset($response['access_token']) ? $response['access_token'] : '';
    }

    /**
     * STKPushSimulation - Use this function to initiate an STKPush Simulation
     * 
     * @param  float $amount - This is the Amount transacted normaly a numeric value. Money that customer pays to the Shorcode. Only whole numbers are supported.
     * @param  string $customerPhone - The phone number sending money. The parameter expected is a Valid Safaricom Mobile Number that is M-Pesa registered in the format 2547XXXXXXXX. The MSISDN sending the funds
     * @param  string $transactionDesc - This is any additional information/comment that can be sent along with the request from your system. Maximum of 13 Characters.
     * @return bool
     */

    public function STKPushSimulation(string $orderId, float $amount, string $customerPhone, string $transactionDesc): bool
    {
        if (false === $this->generateToken()) {
            return false;
        }

        $token = $this->getToken();

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);


        $postFields = array(
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $amount,
            'PartyA' => $customerPhone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $customerPhone,
            'CallBackURL' => $this->callbackUrl($orderId),
            'AccountReference' => $this->settings['account_reference'],
            'TransactionDesc' => $transactionDesc
        );
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->STKPushSimulationUrl());
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Authorization:Bearer ' . $token
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($curl, CURLOPT_HEADER, false);

        if (!$this->response = curl_exec($curl)) {
            $this->error = curl_error($curl);
            return false;
        }
        curl_close($curl);
        return true;
    }
    
    /**
     * STKPushQuery - Use this function to initiate an STKPush Status Query request(To cross verify the callback response).
     *
     * @param  string $checkoutRequestID | Checkout RequestID
     * @return bool
     */
    public function STKPushQuery(string $checkoutRequestID): bool
    {
        if (false === $this->generateToken()) {
            return false;
        }

        $token = $this->getToken();

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->STKPushQueryUrl());
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Authorization:Bearer ' . $token
        ]);


        $postFields = array(
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestID
        );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($curl, CURLOPT_HEADER, false);
        
        if (!$this->response = curl_exec($curl)) {
            $this->error = curl_error($curl);
            return false;
        }
        curl_close($curl);
        return true;
    }
}
