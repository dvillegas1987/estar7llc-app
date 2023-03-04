<?php

/**
 * Paystack - API's reference https://paystack.com/docs/api/
 */
class Paystack extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'secret_key',
        'public_key',
    ];
    private $env = Plugin::ENV_SANDBOX;
    private $secretKey = '';
    private $publicKey = '';
    private $response = '';

    private const INITIALIZE_URL = "https://api.paystack.co/transaction/initialize/";
    private const VERIFY_URL = "https://api.paystack.co/transaction/verify/";

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
                'live_secret_key',
                'live_public_key',
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

        $this->secretKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_secret_key'] : $this->settings['secret_key'];
        $this->publicKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_public_key'] : $this->settings['public_key'];
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
     * getRequestBody
     *
     * @return array
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * initiatePaymentRequest
     *
     * @param  string $orderId
     * @return bool
     */
    public function initiatePaymentRequest(string $orderId): bool
    {
        $this->orderId = $orderId;
        /*
        * Set the array of fields to be posted to PayGate
        */
        //=== Create Request Body
        $this->buildRequestBody();
        return $this->initializePayment();
    }

    /**
     * initializePayment
     *
     * @return bool
     */
    private function initializePayment(): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::INITIALIZE_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->getRequestBody()));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            "Authorization: Bearer " . $this->secretKey,
            "Content-Type: application/json",
            "cache-control: no-cache"
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!$this->response = curl_exec($ch)) {
            $this->error = curl_error($ch);
            return false;
        }
        return true;
    }

    /**
     * formatPaymentAmount - The provided amount should be given in the smallest unit of payment currency.
     *
     * @param  float $amount
     * @return int
     */
    private function formatPaymentAmount(float $amount): int
    {
        return $amount * 100;
    }

    /**
     * buildRequestBody
     *
     * @return bool
     */
    private function buildRequestBody(): bool
    {
        $orderPaymentObj = new OrderPayment($this->orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $customerEmail = !isset($orderInfo['customer_email']) || empty($orderInfo['customer_email']) ? $this->userData['credential_email'] : $orderInfo['customer_email'];

        $callbackUrl = UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', "callback", [$this->orderId]); /* The URL that the buyer will be redirected to, after making payment. This URL overrides return_url value from PoS configuration. */

        $this->requestBody = [
            'email' => $customerEmail,
            'amount' => $this->formatPaymentAmount($paymentAmount),
            'currency' => $this->systemCurrencyCode,
            'metadata' => array('order_id' => $this->orderId),
            'callback_url' => $callbackUrl,
            'webhook_url' => CommonHelper::generateFullUrl(self::KEY_NAME . 'Pay', 'webhook')
        ];
        return true;
    }

    /**
     * validatePaymentResponse
     *
     * @param  string $referenceId
     * @return bool
     */
    public function validatePaymentResponse(string $referenceId): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::VERIFY_URL . rawurlencode($referenceId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            "accept: application/json",
            "authorization: Bearer " . $this->secretKey,
            "cache-control: no-cache"
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!$this->response = curl_exec($ch)) {
            $this->error = curl_error($ch);
            return false;
        }
        return true;
    }
}
