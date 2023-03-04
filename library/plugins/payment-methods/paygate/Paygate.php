<?php
require_once('paygate.payweb3.php');
/**
 * Paygate - API's reference https://docs.paygate.co.za/. DPO South Africa.
 */
class Paygate extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'paygate_id',
        'encryption_key',
    ];
    private $env = Plugin::ENV_SANDBOX;
    private $paygateId = '';
    private $encryptionKey = '';
    private $payWeb3;
    private $response = '';

    private const TXN_STATUS_NOT_DONE = 0;
    private const TXN_STATUS_APPROVED = 1;
    private const TXN_STATUS_DECLINED = 2;
    private const TXN_STATUS_CANCELLED = 3;
    private const TXN_STATUS_USER_CANCELLED = 4;
    private const TXN_STATUS_RECEIVED_BY_PAYGATE = 5;
    private const TXN_STATUS_SETTLEMENT_VOIDED = 7;

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
                'live_paygate_id',
                'live_encryption_key',
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

        $this->paygateId = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_paygate_id'] : $this->settings['paygate_id'];
        $this->encryptionKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_encryption_key'] : $this->settings['encryption_key'];
        $this->payWeb3 = new PayGate_PayWeb3();
        $this->payWeb3->setEncryptionKey($this->encryptionKey);
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
     * initiateRequest
     *
     * @param  string $orderId
     * @param  string $payMethod
     * @return bool
     */
    public function initiateRequest(string $orderId): bool
    {
        /*
        * Set the array of fields to be posted to PayGate
        */
        //=== Create Request Body
        $body = $this->buildRequestBody($orderId);
        $this->payWeb3->setInitiateRequest($body);

        /*
        * Do the curl post to PayGate
        */
        if (false === $this->payWeb3->doInitiate()) {
            $lastError = isset($this->payWeb3->lastError) ? $this->payWeb3->lastError : Labels::getLabel('MSG_API_ERROR', $this->langId);
            $msg = Labels::getLabel('MSG_{LAST-ERROR}_:_SOMETHING_WENT_WRONG!!', $this->langId);
            $this->error = CommonHelper::replaceStringData($msg, ['{LAST-ERROR}' => $lastError]);
            return false;
        }
        $this->response = $this->payWeb3;
        return true;
    }

    /**
     * buildRequestBody
     *
     * @param  string $orderId
     * @return array
     */
    private function buildRequestBody(string $orderId): array
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $countryAlpha3Code = $this->userData['country_code_alpha3'];
        } else {
            $orderObj = new Orders();
            $orderAddresses = $orderObj->getOrderAddresses($orderId);
            $billingAddress = isset($orderAddresses[Orders::BILLING_ADDRESS_TYPE]) ? $orderAddresses[Orders::BILLING_ADDRESS_TYPE] : [];
            $countryAlpha3Code = $billingAddress['oua_country_code_alpha3'];
        }

        $langData = current(Language::getAllNames(false, $this->langId));
        $locale = strtolower($langData['language_code'] . '-' . $langData['language_country_code']);

        $customerEmail = !isset($orderInfo['customer_email']) || empty($orderInfo['customer_email']) ? $this->userData['credential_email'] : $orderInfo['customer_email'];

        return [
            'PAYGATE_ID'        => filter_var($this->paygateId, FILTER_SANITIZE_STRING),
            'REFERENCE'         => filter_var($orderId, FILTER_SANITIZE_STRING),
            'AMOUNT'            => filter_var($paymentAmount * 100, FILTER_SANITIZE_NUMBER_INT),
            'CURRENCY'          => filter_var($this->systemCurrencyCode, FILTER_SANITIZE_STRING),
            'RETURN_URL'        => filter_var(UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', "callback", [$orderId]), FILTER_SANITIZE_URL),
            'TRANSACTION_DATE'  => filter_var(date('Y-m-d H:i:s'), FILTER_SANITIZE_STRING),
            'LOCALE'            => filter_var($locale, FILTER_SANITIZE_STRING),
            'COUNTRY'           => filter_var($countryAlpha3Code, FILTER_SANITIZE_STRING),
            'EMAIL'             => filter_var($customerEmail, FILTER_SANITIZE_EMAIL),
        ];
    }

    /**
     * validateResponse
     *
     * @param  string $orderId
     * @param  array $response
     * @return bool
     */
    public function validateResponse(string $orderId, array $response): bool
    {
        $payRequestId = isset($response['PAY_REQUEST_ID']) ? $response['PAY_REQUEST_ID'] : '';
        $transactionStatus = isset($response['TRANSACTION_STATUS']) ? $response['TRANSACTION_STATUS'] : '';
        $checksum = isset($response['CHECKSUM']) ? $response['CHECKSUM'] : '';
        if (empty($payRequestId) || empty($transactionStatus) || empty($checksum)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $checksumData = array(
            'PAYGATE_ID'         => $this->paygateId,
            'PAY_REQUEST_ID'     => $payRequestId,
            'TRANSACTION_STATUS' => $transactionStatus,
            'REFERENCE'          => $orderId,
            'CHECKSUM'           => $checksum
        );

        /*
        * Check that the checksum returned matches the checksum we generate
        */
        if (false === $this->payWeb3->validateChecksum($checksumData)) {
            $this->error = Labels::getLabel('MSG_INVALID_CHECKSUM', $this->langId);
            return false;
        }

        $queryData = array(
            'PAYGATE_ID'     => $this->paygateId,
            'PAY_REQUEST_ID' => $payRequestId,
            'REFERENCE'      => $orderId
        );
        /*
         * Set the array of fields to be posted to PayGate
         */
        $this->payWeb3->setQueryRequest($queryData);

        /*
         * Do the curl post to PayGate
         */
        if (false === $this->payWeb3->doQuery()) {
            $this->error = Labels::getLabel('MSG_REQUEST_QUERY_MISMATCH', $this->langId);
            return false;
        }

        if (isset($this->payWeb3->lastError) && !empty($this->payWeb3->lastError)) {
            $this->error = $this->payWeb3->lastError;
        }

        if (!isset($this->payWeb3->queryResponse)) {
            $this->error = Labels::getLabel('MSG_NO_QUERY_RESPONSE', $this->langId);
            return false;
        }

        $this->response = $this->payWeb3->queryResponse;
        return true;
    }
    
    /**
     * transactionStatues
     *
     * @return array
     */
    public static function transactionStatues(): array
    {
        return [
            self::TXN_STATUS_NOT_DONE => "Not Done",
            self::TXN_STATUS_APPROVED => "Approved",
            self::TXN_STATUS_DECLINED => "Declined",
            self::TXN_STATUS_CANCELLED => "Cancelled",
            self::TXN_STATUS_USER_CANCELLED => "User Cancelled",
            self::TXN_STATUS_RECEIVED_BY_PAYGATE => "Received by PayGate",
            self::TXN_STATUS_SETTLEMENT_VOIDED => "Settlement Voided",
        ];
    }
    
    /**
     * validateTxnStatus
     *
     * @param  int $status
     * @return bool
     */
    public function validateTxnStatus(int $status): bool
    {
        if (self::TXN_STATUS_APPROVED == $status) {
            return true;
        }
        $this->error = self::transactionStatues()[$status];
        return false;
    }
}
