<?php

class PaypalPayController extends PaymentController
{
    public const KEY_NAME = "Paypal";
    private $externalLibUrl = '';
    private $userId = 0;

    /**
     * __construct
     *
     * @param  string $action
     * @return void
     */
    public function __construct(string $action)
    {
        parent::__construct($action);
        $this->init();
    }

    /**
     * allowedCurrenciesArr
     *
     * @return array
     */
    protected function allowedCurrenciesArr(): array
    {
        return [
            "AUD", "BRL", "CAD", "CZK", "DKK", "EUR", "HKD", "HUF", "INR", "ILS", "JPY", "MYR", "MXN", "TWD", "NZD", "NOK", "PHP", "PLN", "GBP", "RUB", "SGD", "SEK", "CHF", "THB", "USD"
        ];
    }

    /**
     * init
     *
     * @return void
     */
    private function init(): void
    {
        $userId = UserAuthentication::getLoggedUserId(true);
        if (false === $this->plugin->init($userId)) {
            $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
        }

        $this->settings = $this->plugin->getSettings();
        $this->clientId = 0 < $this->settings['env'] ? $this->settings['live_client_id'] : $this->settings['client_id'];
        $this->externalLibUrl = 'https://www.paypal.com/sdk/js?client-id=' . $this->clientId . '&currency=' . $this->systemCurrencyCode;
    }

    /**
     * charge
     *
     * @param  string $orderId
     * @return void
     */
    public function charge($orderId)
    {
        if ($orderId == '') {
            $msg = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }
        $this->set('orderInfo', $orderInfo);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('externalLibUrl', $this->externalLibUrl);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'paypal-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }
    
    /**
     * createOrder
     *
     * @param  string $orderId
     * @return string json
     */
    public function createOrder(string $orderId)
    {
        if (false === $this->plugin->createOrder($orderId)) {
            $error = $this->plugin->getError();
            $msg = is_array($error) && isset($error['error']) ? $error['error'] . ' : ' . $error['error_description'] : $error;
            $msg = is_array($msg) && isset($msg['message']) ? $msg['message'] : $msg;
            $this->setErrorAndRedirect($msg, true);
        }
        $this->paymentInitiated($orderId);
        $order = $this->plugin->getResponse();
        echo json_encode($order->result, JSON_PRETTY_PRINT);
    }

    /**
     * captureOrder
     *
     * @param  mixed $paypalOrderId
     * @return string json
     */
    public function captureOrder(string $paypalOrderId)
    {
        //=== Save order either by retrieving order from paypal OR the order we still have in session
        if (false === $this->plugin->captureOrder($paypalOrderId)) {
            $error = $this->plugin->getError();
            $msg = is_array($error) && isset($error['error']) ? $error['error'] . ' : ' . $error['error_description'] : $error;
            $this->setErrorAndRedirect($msg, true);
        }
        $order = $this->plugin->getResponse();
        echo json_encode($order->result, JSON_PRETTY_PRINT);
    }
    
    /**
     * callback
     *
     * @param  string $orderId
     * @return void
     */
    public function callback(string $orderId)
    {
        $post = FatApp::getPostedData();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        if ('COMPLETED' != $post['status']) {
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($post));
            $msg = Labels::getLabel("MSG_PAYMENT_FAILED_:_{STATUS}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{STATUS}' => $post['status']]);
            $orderPaymentObj->addOrderPaymentComments($msg);
            $this->setErrorAndRedirect($msg, true);
        }
        
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $paypalOrderId = $post['id'];
        $currencyCode = $orderInfo["order_currency_code"];
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        if (false === $this->plugin->validatePaymentRequest($paypalOrderId, $orderId, $currencyCode, $paymentAmount)) {
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($post));
            FatUtility::dieJsonError($this->plugin->getError());
        }

        /* Recording Payment in DB */
        $orderPaymentObj->addOrderPayment(self::KEY_NAME, $paypalOrderId, $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($post));
        /* End Recording Payment in DB */
        $json['redirecUrl'] = UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
        FatUtility::dieJsonSuccess($json);
    }

    /**
     * getExternalLibraries
     *
     * @return void
     */
    public function getExternalLibraries()
    {
        $json['libraries'] = [$this->externalLibUrl];
        FatUtility::dieJsonSuccess($json);
    }
}
