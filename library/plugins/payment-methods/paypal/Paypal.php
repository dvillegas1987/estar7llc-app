<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

class Paypal extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'client_id',
        'secret_key',
    ];
    private $clientId = '';
    private $secretKey = '';
    private $resp;

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
        $envoirment = FatUtility::int($this->getKey('env'));
        if (0 < $envoirment) {
            $this->requiredKeys = [
                'live_client_id',
                'live_secret_key',
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


        $this->clientId = 0 < $this->settings['env'] ? $this->settings['live_client_id'] : $this->settings['client_id'];
        $this->secretKey = 0 < $this->settings['env'] ? $this->settings['live_secret_key'] : $this->settings['secret_key'];
        return true;
    }

    /**
     * environment - Setup Paypal Environment.
     *
     * @return object
     */
    public function environment(): object
    {
        return (0 < $this->settings['env'] ? new ProductionEnvironment($this->clientId, $this->secretKey) : new SandboxEnvironment($this->clientId, $this->secretKey));
    }

    /**
     * client - Setup Paypal Client.
     *
     * @return object
     */
    public function client(): object
    {
        return new PayPalHttpClient($this->environment());
    }

    /**
     * createOrder - Create papal order
     *
     * @param  mixed $orderId
     * @return bool
     */
    public function createOrder(string $orderId): bool
    {
        //=== Create New Order Request
        $request = new OrdersCreateRequest();
        $request->prefer("return=representation");

        //=== Create Request Body
        $request->body = $this->buildRequestBody($orderId);
        //=== Call PayPal to set up a transaction
        return $this->executeRequest($request);
    }

    /**
     * captureOrder
     *
     * @param  mixed $paypalOrderId
     * @return bool
     */
    public function captureOrder($paypalOrderId): bool
    {
        $request = new OrdersCaptureRequest($paypalOrderId);
        //=== Call PayPal to get the transaction details
        return $this->executeRequest($request);
    }

    /**
     * getResponse
     *
     * @return object
     */
    public function getResponse(): object
    {
        return $this->resp;
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
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        } else {
            $orderObj = new Orders();
            $orderAddresses = $orderObj->getOrderAddresses($orderId);
            $shippingAddress = isset($orderAddresses[Orders::SHIPPING_ADDRESS_TYPE]) ? $orderAddresses[Orders::SHIPPING_ADDRESS_TYPE] : [];
            $billingAddress = isset($orderAddresses[Orders::BILLING_ADDRESS_TYPE]) ? $orderAddresses[Orders::BILLING_ADDRESS_TYPE] : [];

            $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        }

        $request_body = $purchase_units = $pu_amount = [];

        //=== Prepare amount & break down of amount for order
        $pu_amount["currency_code"] = $this->systemCurrencyCode;
        $pu_amount["value"] =  number_format((float)$paymentAmount, 2, '.', '');
        $purchase_units["reference_id"] = $orderId;
        $purchase_units["amount"] = $pu_amount;
     
        if ($orderInfo['order_type'] == Orders::ORDER_PRODUCT && !empty($shippingAddress)) {
            $purchase_units["shipping"] = [
                "address_line_1" => $shippingAddress['oua_address1'],
                "address_line_2" => $shippingAddress['oua_address2'],
                "admin_area_1" => $shippingAddress['oua_state_code'],
                "admin_area_2" => $shippingAddress['oua_city'],
                "postal_code" => $shippingAddress['oua_zip'],
                "country_code" => $shippingAddress['oua_country_code']
            ];
        }

        $customerName = !isset($orderInfo['customer_name']) || empty($orderInfo['customer_name']) ? $this->userData['user_name'] : $orderInfo['customer_name'];
        $customerEmail = !isset($orderInfo['customer_email']) || empty($orderInfo['customer_email']) ? $this->userData['credential_email'] : $orderInfo['customer_email'];

        $request_body["intent"] = "CAPTURE";
        $request_body["payer"] = [
            "name" => [
                "given_name" => $customerName,
            ],
            "email_address" => $customerEmail
        ];


        if ($orderInfo['order_type'] == Orders::ORDER_PRODUCT && !empty($billingAddress)) {
            $request_body["payer"]['address'] = [
                "address_line_1" => $billingAddress['oua_address1'],
                "address_line_2" => $billingAddress['oua_address2'],
                "admin_area_1" => $billingAddress['oua_state_code'],
                "admin_area_2" => $billingAddress['oua_city'],
                "postal_code" => $billingAddress['oua_zip'],
                "country_code" => $billingAddress['oua_country_code']
            ];
        }

        $request_body["purchase_units"][] = $purchase_units;
        $request_body["application_context"] = [
            "cancel_url" => $cancelBtnUrl,
            "return_url" => UrlHelper::generateFullUrl(self::KEY_NAME, "callback", [$orderId])
        ];

        return $request_body;
    }
    
    /**
     * validatePaymentRequest
     *
     * @param  string $paypalOrderId
     * @param  string $orderId
     * @param  string $currencyCode
     * @param  float $totalAmount
     * @return bool
     */
    public function validatePaymentRequest(string $paypalOrderId, string $orderId, string $currencyCode, float $totalAmount): bool
    {
        if (!empty(Orders::isExistTransactionId($paypalOrderId))) {
            $this->error = Labels::getLabel('MSG_INVALID_TXN_REQUEST._THIS_TRANSACTION_ALREADY_PROCESSED', $this->langId);
            return false;
        }

        $request = new OrdersGetRequest($paypalOrderId);
        //=== Call PayPal to get the transaction details
        if (false === $this->executeRequest($request)) {
            return false;
        }
        $response = $this->getResponse();

        if (200 != $response->statusCode) {
            $this->error = Labels::getLabel('MSG_SOMETHING_WENT_WRONG._INVALID_RESPONSE.', $this->langId);
            return false;
        }

        $result = $response->result;
        if ('COMPLETED' != $result->status || 'CAPTURE' != $result->intent) {
            $this->error = Labels::getLabel('MSG_THIS_TXN_NOT_YET_CAPTURED/_COMPLETED', $this->langId);
            return false;
        }
        
        $purchaseUnit = isset($result->purchase_units) ? current($result->purchase_units) : [];
        $capturePayment = isset($purchaseUnit->payments->captures) ? current($purchaseUnit->payments->captures) : [];
        if (empty($capturePayment)) {
            $this->error = Labels::getLabel('MSG_SOMETHING_WENT_WRONG._INVALID_CAPTURE_RESPONSE.', $this->langId);
            return false;
        }

        if ($purchaseUnit->reference_id != $orderId) {
            $this->error = Labels::getLabel('MSG_INVALID_ORDER.', $this->langId);
            return false;
        }

        $paidCurrency = isset($capturePayment->amount->currency_code) ? $capturePayment->amount->currency_code : '';
        $paidAmount = isset($capturePayment->amount->value) ? $capturePayment->amount->value : [];
        $payeeEmail = $purchaseUnit->payee->email_address;

        if ($currencyCode != $paidCurrency) {
            $this->error = Labels::getLabel('MSG_INVALID_CURRENCY.', $this->langId);
            return false;
        }

        if ($totalAmount != $paidAmount) {
            $this->error = Labels::getLabel('MSG_INVALID_PAID_AMOUNT.', $this->langId);
            return false;
        }

        if ($this->settings['payee_email'] != $payeeEmail) {
            $this->error = Labels::getLabel('MSG_INVALID_MERCHANT.', $this->langId);
            return false;
        }

        return true;
    }


    /**
     * executeRequest
     *
     * @param  mixed $request
     * @return bool
     */
    private function executeRequest($request): bool
    {
        try {
            $client = $this->client();
            //=== Return a response to the client.
            $this->resp = $client->execute($request);
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $msg = LibHelper::isJson($e->getMessage()) ? json_decode($e->getMessage(), true) : $e->getMessage();
            $this->error = $msg;
            return false;
        } catch (\Error $e) {
            // Handle error
            $msg = LibHelper::isJson($e->getMessage()) ? json_decode($e->getMessage(), true) : $e->getMessage();
            $this->error = $msg;
            return false;
        } catch (HttpException $e) {
            $msg = LibHelper::isJson($e->getMessage()) ? json_decode($e->getMessage(), true) : $e->getMessage();
            $this->error = $msg;
            return false;
        }
        return true;
    }
}
