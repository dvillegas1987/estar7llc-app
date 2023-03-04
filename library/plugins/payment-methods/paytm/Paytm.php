<?php

/*
 * https://developer.paytm.com/docs/show-payment-page/?ref=payments
 */
require_once 'PaytmChecksum.php';
use Curl\Curl;

class Paytm extends PaymentMethodBase
{

    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'merchant_id',
        'merchant_key',
        'merchant_website',
    ];
    private $merchant_id = '';
    private $merchant_key = '';
    private $merchant_website = '';
    private $apiUrl = '';
    private $response = '';

    private const TEST_URL = 'https://securegw-stage.paytm.in/theia/api/v1/';
    private const LIVE_URL = 'https://securegw.paytm.in/theia/api/v1/';
    private const TEST_PAYMENT_URL = 'https://securegw-stage.paytm.in/v3/order/status';
    private const LIVE_PAYMENT_URL = 'https://securegw.paytm.in/v3/order/status';
    private const REQUEST_TOKEN = 1;
    private const REQUEST_VERIFY_PAYMENT = 2;

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

    public function init(): bool
    {
        if (false == $this->validateSettings()) {
            return false;
        }

        if (false === $this->loadBaseCurrencyCode()) {
            return false;
        }

        $this->merchant_id = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_merchant_id'] : $this->settings['merchant_id'];
        $this->merchant_key = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_merchant_key'] : $this->settings['merchant_key'];
        $this->merchant_website = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_merchant_website'] : $this->settings['merchant_website'];
        $this->apiUrl = Plugin::ENV_PRODUCTION == $this->settings['env'] ? self::LIVE_URL : self::TEST_URL;
        return true;
    }

    public function requiredKeys()
    {
        $this->env = FatUtility::int($this->getKey('env'));
        if (0 < $this->env) {
            $this->requiredKeys = [
                'live_merchant_id',
                'live_merchant_key',
                'live_merchant_website',
            ];
        }
    }

    public function getPaymentToken($orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $paytmParams = [
            "body" => [
                "requestType" => "Payment",
                "mid" => $this->merchant_id,
                "websiteName" => $this->merchant_website,
                "orderId" => $orderInfo['id'],
                "callbackUrl" => UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', 'callback'),
                "txnAmount" => array(
                    "value" => $paymentAmount,
                    "currency" => $orderInfo['order_currency_code'],
                ),
                "userInfo" => array(
                    "custId" => $orderInfo['customer_id'],
                ),
            ],
        ];

        $paytmParams["head"]["signature"] = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $this->merchant_key);

        if (!$this->doRequest(self::REQUEST_TOKEN, $paytmParams)) {
            return false;
        }

        if (isset($this->resp['body']['resultInfo']['resultStatus']) && $this->resp['body']['resultInfo']['resultStatus'] == "F") {
            $this->error = $this->resp['body']['resultInfo']['resultMsg'];
            return false;
        }

        return $this->resp['body']['txnToken'];
    }

    public function verifySignature($post)
    {
        if (!isset($post['CHECKSUMHASH'])) {
            return false;
        }
        return PaytmChecksum::verifySignature($post, $this->merchant_key, $post['CHECKSUMHASH']);
    }

    public function verifyPayment($orderId)
    {
        $paytmParams = [
            "body" => [
                "mid" => $this->merchant_id,
                "orderId" => $orderId,
            ],
        ];

        $paytmParams["head"] = array(
            "signature" => PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $this->merchant_key)
        );

        if (!$this->doRequest(self::REQUEST_VERIFY_PAYMENT, $paytmParams)) {
            return false;
        }

        if (!isset($this->resp['body']['resultInfo']['resultCode']) || $this->resp['body']['resultInfo']['resultCode'] != "01") {
            $this->error = $this->resp['body']['resultInfo']['resultMsg'] ?? Labels::getLabel("ERR_PAYMENT_FAILED", $this->langId);
            return false;
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        if ($paymentAmount != $this->resp['body']['txnAmount']) {
            $this->error = Labels::getLabel("ERR_INVALID_TRANSACTION_AMOUNT", $this->langId);
            return false;
        }

        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $this->error = Labels::getLabel("MSG_INVALID_ORDER_PAID_CANCELLED", $this->langId);
            return false;
        }

        return true;
    }

    private function doRequest(int $requestType, $requestParam = []): bool
    {
        try {
            $curl = new Curl();
            $curl->setHeader('Content-Type', 'application/json');
            switch ($requestType) {
                case self::REQUEST_TOKEN:
                    $url = $this->getApiUrl() . 'initiateTransaction?mid=' . $this->merchant_id . '&orderId=' . $requestParam['body']['orderId'];
                    $curl->post($url, json_encode($requestParam, JSON_UNESCAPED_SLASHES));
                    break;
                case self::REQUEST_VERIFY_PAYMENT:
                    $url = Plugin::ENV_PRODUCTION == $this->settings['env'] ? self::LIVE_PAYMENT_URL : self::TEST_PAYMENT_URL;
                    $curl->post($url, json_encode($requestParam, JSON_UNESCAPED_SLASHES));
                    break;
            }
            if ($curl->error) {
                echo $this->error = $curl->errorCode . ' : ' . $curl->errorMessage;
                return false;
            }

            $this->resp = json_decode(json_encode($curl->getResponse()), true);
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        } catch (Error $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function getMerchantId()
    {
        return $this->merchant_id;
    }

    public function getMerchantKey()
    {
        return $this->merchant_key;
    }

    public function getMerchantWebsite()
    {
        return $this->merchant_website;
    }

}
