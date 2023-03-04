<?php

/**
 * Dpo - API's Documentation https://directpayonline.atlassian.net/wiki/spaces/API/overview.
 */
class Dpo extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

    public const SANDBOX_URL = 'https://secure1.sandbox.directpay.online/API/v6/';
    public const PRODUCTION_URL = 'https://secure.3gdirectpay.com/API/v6/';

    public const SANDBOX_PAY_URL = 'https://secure1.sandbox.directpay.online/payv2.php';
    public const PRODUCTION_PAY_URL = 'https://secure.3gdirectpay.com/payv2.php';

    public $requiredKeys = [
        'company_token',
        'service_type',
    ];
    private $env = Plugin::ENV_SANDBOX;
    private $companyToken = '';
    private $serviceType = '';
    private $actionUrl = '';
    private $actionPayUrl = '';
    private $response = '';
    private $tokenResponse = [];

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
                'live_company_token',
                'live_service_type',
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

        $this->companyToken = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_company_token'] : $this->settings['company_token'];
        $this->serviceType = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_service_type'] : $this->settings['service_type'];
        $this->actionUrl = Plugin::ENV_PRODUCTION == $this->settings['env'] ? self::PRODUCTION_URL : self::SANDBOX_URL;
        $this->actionPayUrl = Plugin::ENV_PRODUCTION == $this->settings['env'] ? self::PRODUCTION_PAY_URL : self::SANDBOX_PAY_URL;
        return true;
    }

    /**
     * getResponse
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * getCancelUrl
     *
     * @return string
     */
    public function getCancelUrl(): string
    {
        return CommonHelper::getPaymentCancelPageUrl();
    }

    /**
     * getReturnUrl
     *
     * @param  mixed $orderId
     * @return string
     */
    public function getReturnUrl(string $orderId): string
    {
        return UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', "callback", [$orderId]);
    }

    /**
     * getPaymenUrl
     *
     * @return string
     */
    public function getPaymenUrl(): string
    {
        return $this->actionPayUrl . "?ID=" . $this->getResponse()->TransToken[0];
    }

    /**
     * getTxnRequestBody
     *
     * @param  string $orderId
     * @return string
     */
    private function getTxnRequestBody(string $orderId): string
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $txn = '<Transaction>
                    <PaymentAmount>' . $paymentAmount . '</PaymentAmount>
                    <PaymentCurrency>' . $this->systemCurrencyCode . '</PaymentCurrency>
                    <CompanyRef>' . $orderId . '</CompanyRef>
                    <RedirectURL>' . $this->getReturnUrl($orderId) . '</RedirectURL>
                    <BackURL>' . $this->getCancelUrl() . '</BackURL>';
        if (0 < count(array_filter($this->userData))) {
            $txn .= '<customerFirstName>' . $this->userData['user_name'] . '</customerFirstName>
                            <customerPhone>' . $this->userData['addr_phone'] . '</customerPhone>
                            <customerEmail>' . $this->userData['credential_email'] . '</customerEmail>
                            <customerAddress>' . $this->userData['addr_address1'] . '</customerAddress>
                            <customerCity>' . $this->userData['addr_city'] . '</customerCity>
                            <customerZip>' . $this->userData['addr_zip'] . '</customerZip>
                            <customerCountry>' . $this->userData['country_code'] . '</customerCountry>';
        }
        $txn .= '</Transaction>';

        return $txn;
    }

    /**
     * getServiceRequestBody
     *
     * @param  string $orderId
     * @return string
     */
    private function getServiceRequestBody(string $orderId): string
    {
        $websiteName = FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->langId, FatUtility::VAR_STRING, '');
        $description = !empty($websiteName) ? $websiteName . ' : #' . $orderId : '#' . $orderId;
        $description .= ' ' . Labels::getLabel('LBL_ORDER_PAYMENT', $this->langId);
        return '<Services>
                    <Service>
                        <ServiceType>' . $this->serviceType . '</ServiceType>
                        <ServiceDescription>' . $description . '</ServiceDescription>
                        <ServiceDate>' . date('Y/m/d H:i') . '</ServiceDate>
                        <ServiceRef>' . $orderId . '</ServiceRef>
                    </Service>
                </Services>';
    }

    /**
     * buildRequestBody
     *
     * @param  string $orderId
     * @return string
     */
    private function buildRequestBody(string $orderId): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
                    <API3G>
                        <CompanyToken>' . $this->companyToken . '</CompanyToken>
                        <Request>createToken</Request>'
            . $this->getTxnRequestBody($orderId)
            . $this->getServiceRequestBody($orderId) .
            '</API3G>';
    }

    /**
     * initiateRequest
     *
     * @param  string $orderId
     * @param  string $payMethod
     * @return bool
     */
    public function initiateRequest(string $orderId): bool
    {
        /*
        * Create Request Body
        */
        $body = $this->buildRequestBody($orderId);
        
        /*
        * Do the curl post to Dpo
        */
        if (false === $this->doRequest($body)) {
            return false;
        }

        /*
        * Validate Xml Response
        */
        if (false === $this->isValidXml($this->getResponse())) {
            return false;
        }

        /*
        * Convert Xml to Array
        */
        return $this->formatResponse();
    }

    /**
     * isValidXml
     *
     * Check if XML is valid and correct
     */
    private function isValidXml($xml)
    {
        $doc = @simplexml_load_string($xml);
        if (false == $doc) {
            $this->error = CommonHelper::stripAllTags($xml);
            return false; // this is not valid
        }
        return true; // this is valid
    }

    /**
     * formatResponse - Convert the XML result into array
     *
     * @return bool
     */
    private function formatResponse(): bool
    {
        $xml = new SimpleXMLElement($this->getResponse());

        if ($xml->Result[0] != '000') {
            $msg = Labels::getLabel('LBL_PAYMENT_ERROR_CODE:_{ERROR_CODE},_{ERROR_DESCRIPTION}.', $this->langId);
            $this->error = CommonHelper::replaceStringData($msg, ['{ERROR_CODE}' => $xml->Result[0], '{ERROR_DESCRIPTION}' => $xml->ResultExplanation[0]]);
            return false;
        }

        $this->response = $xml;
        return true;
    }

    /**
     * validateResponse - Verify DPO Group response    
     *
     * @param  array $response
     * @return bool
     */
    public function validateResponse(array $response): bool
    {
        $transactionToken  = $response['TransactionToken'];

        // Get verify token response from DPO Group
        if (false === $this->verifytoken($transactionToken)) {
            return false;
        }

        $tokenResponse = $this->getResponse();
        // Check selected order status workflow
        if ($tokenResponse->Result[0] != '000') {
            $errorCode = $tokenResponse->Result[0];
            $errorDesc = $tokenResponse->ResultExplanation[0];
            $msg = Labels::getLabel('LBL_PAYMENT_ERROR_CODE:_{ERROR_CODE},_{ERROR_DESCRIPTION}.', $this->langId);
            $this->error = CommonHelper::replaceStringData($msg, ['{ERROR_CODE}' => $errorCode, '{ERROR_DESCRIPTION}' => $errorDesc]);
            return false;
        }

        $this->response = $tokenResponse;
        return true;
    }

    /**
     * verifytoken - VerifyToken response from DPO Group
     *
     * @param  mixed $transactionToken
     * @return void
     */
    private function verifytoken(string $transactionToken): bool
    {
        $inputXml = '<?xml version="1.0" encoding="utf-8"?>
                        <API3G>
                            <CompanyToken>' . $this->companyToken . '</CompanyToken>
                            <Request>verifyToken</Request>
                            <TransactionToken>' . $transactionToken . '</TransactionToken>
                        </API3G>';

        if (false === $this->doRequest($inputXml)) {
            return false;
        }

        // Convert the XML result into array
        $this->response = new SimpleXMLElement($this->getResponse());
        return true;
    }

    /**
     * doRequest
     *
     * @param  string $inputXml
     * @return bool
     */
    private function doRequest(string $inputXml): bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->actionUrl);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $inputXml);

        if (!$this->response = curl_exec($ch)) {
            $this->error = curl_error($ch);
            if (empty($this->error)) {
                $this->error = Labels::getLabel('MSG_VERIFICATION_ERROR:_UNABLE_TO_CONNECT_TO_THE_PAYMENT_GATEWAY.', $this->langId);
            }
            return false;
        }
        return true;
    }
}
