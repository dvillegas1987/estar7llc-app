<?php

require_once dirname(__FILE__) . '/PaynowFunctions.php';

/**
 * Paynow - API's reference https://docs.paynow.pl/
 * Git - https://github.com/pay-now/paynow-php-sdk
 */

use Paynow\Client;
use Paynow\Environment;
use Paynow\Exception\PaynowException;

class Paynow extends PaymentMethodBase
{
    use PaynowFunctions;

    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'api_access_key',
        'signature_calculation_key',
    ];
    private $env = Plugin::ENV_SANDBOX;
    private $apiAccessKey = '';
    private $signatureKey = '';
    private $response = '';
    protected $client;
    protected $orderId = '';
    protected $requestBody = [];

    private const REQUEST_PAYMENT = 1;
    private const REQUEST_VALIDATE_PAYMENT = 2;

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
                'live_api_access_key',
                'live_signature_calculation_key',
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

        $this->apiAccessKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_api_access_key'] : $this->settings['api_access_key'];
        $this->signatureKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_signature_calculation_key'] : $this->settings['signature_calculation_key'];
        $clientEnv = Plugin::ENV_PRODUCTION == $this->settings['env'] ? Environment::PRODUCTION : Environment::SANDBOX;
        $this->client = new Client($this->apiAccessKey, $this->signatureKey, $clientEnv);
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
        return $this->doRequest(self::REQUEST_PAYMENT);
    }
    
    /**
     * formatPaymentAmount - The provided amount should be given in the smallest unit of payment currency (grosz in case of PLN).
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

        $returnUrl = UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', "paymentSuccess", [$this->orderId]); /* The URL that the buyer will be redirected to, after making payment. This URL overrides return_url value from PoS configuration. */

        $this->requestBody = [
            "amount" => $this->formatPaymentAmount($paymentAmount),
            "currency" => $this->systemCurrencyCode,
            "externalId" => $this->orderId,
            "description" => $this->orderId,
            "buyer" => [
                "email" => $customerEmail
            ],
            "continueUrl" => $returnUrl
        ];
        return true;
    }

    /**
     * validatePaymentResponse
     *
     * @param  string $paymentId
     * @return bool
     */
    public function validatePaymentResponse(string $paymentId): bool
    {
        return $this->doRequest(self::REQUEST_VALIDATE_PAYMENT, [$paymentId]);
    }

    /**
     * doRequest
     *
     * @param  mixed $requestType
     * @return mixed
     */
    public function doRequest(int $requestType, array $requestParam = [])
    {
        try {
            switch ($requestType) {
                case self::REQUEST_PAYMENT:
                    return $this->payment();
                    break;
                case self::REQUEST_VALIDATE_PAYMENT:
                    return $this->validatePaymentStatus($requestParam);
                    break;
            }
        } catch (PaynowException $e) {
            // catch errors
            $this->error = $e->getMessage();
        } catch (\UnexpectedValueException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $this->error = $e->getMessage();
        } catch (Error $e) {
            // Handle error
            $this->error = $e->getMessage();
        }
        return false;
    }
}
