<?php

/**
 * Paystack - API's reference https://paystack.com/docs/api/
 */
class PaystackPayController extends PaymentController
{
    public const KEY_NAME = "Paystack";
    private $userId = 0;
    private $authorize;

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
        return ['NGN', 'USD', 'GHS', 'ZAR'];
    }

    /**
     * init
     *
     * @return void
     */
    private function init(): void
    {
        $this->userId = UserAuthentication::getLoggedUserId(true);
        if (false === $this->plugin->init($this->userId)) {
            $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
        }
        $this->settings = $this->plugin->getSettings();        
        $this->secretKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_secret_key'] : $this->settings['secret_key'];
        $this->publicKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_public_key'] : $this->settings['public_key'];
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

        $frm = $this->getPaymentForm($orderId);
        $postOrderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
        $processRequest = false;
        if (!empty($postOrderId) && $orderId = $postOrderId) {
            if (false === $this->plugin->initiatePaymentRequest($orderId)) {
                $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
            }

            $this->authorize = json_decode($this->plugin->getResponse(), true);
            if (false === $this->authorize['status']) {
                $this->setErrorAndRedirect($this->authorize['message'], FatUtility::isAjaxCall());
            }
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_access_code'] = $this->authorize['data']['access_code'];
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_reference'] = $this->authorize['data']['reference'];
            $frm = $this->getPaymentForm($orderId, true);
            $processRequest = true;
        }

        $frm->fill(['orderId' => $orderId]);
        $this->set('frm', $frm);
        $this->set('processRequest', $processRequest);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'paystack-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    /**
     * callback
     *
     * @param  string $orderId
     * @return void
     */
    public function callback(string $orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId);
        $referenceId = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_reference'];
        if (!array_key_exists('reference', $_REQUEST)) {
            $this->logFailure($orderId);
        }

        if ($referenceId != $_REQUEST['reference']) {
            $this->logFailure($orderId);
        }

        if (false === $this->plugin->validatePaymentResponse($referenceId)) {
            $this->logFailure($orderId, $this->plugin->getError());
        }

        $response = $this->plugin->getResponse();
        $paymentResponse = json_decode($response, true);
        if (Plugin::RETURN_TRUE != $paymentResponse['status']) {
            $msg = $paymentResponse['message'];
            $this->logFailure($orderId, $msg);
        }

        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        if (false === $orderPaymentObj->addOrderPayment(self::KEY_NAME, $referenceId, $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), $response)) {
            $msg = $orderPaymentObj->getError();
            $this->logFailure($orderId, $msg);
        }

        /* Unset Session Element On Payment Success.  */
        unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_access_code']);
        unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_reference']);
        /* Unset Session Element On Payment Success.  */

        FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
    }

    /**
     * logFailure
     *
     * @param  string $orderId
     * @return void
     */
    private function logFailure(string $orderId, string $msg = '', array $response = [])
    {
        $response = !empty($response) ? $response : $_REQUEST;
        $orderPaymentObj = new OrderPayment($orderId);
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));

        if (empty($msg)) {
            $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $this->plugin->getError()]);
        }

        $orderPaymentObj->addOrderPaymentComments($msg);
        Message::addErrorMessage($msg);
        FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        die;
    }

    /**
     * getPaymentForm
     *
     * @param  string $orderId
     * @param  bool $processRequest
     * @return object
     */
    private function getPaymentForm(string $orderId, bool $processRequest = false): object
    {
        $actionUrl = false === $processRequest ? UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', [$orderId]) : $this->authorize['data']['authorization_url'];

        $frm = new Form('frmPaymentForm', array('action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');
        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        }
        return $frm;
    }

    /**
     * webhook - Not in use curretly, it will be used when customer don't get redirected after transaction, i.e for backend process
     *
     * @return void
     */
    public function webhook()
    {

        $response = @file_get_contents("php://input");

        $signature = (isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '');

        /* It is a good idea to log all events received. Add code *
         * here to log the signature and body to db or file       */

        if (!$signature) {
            // only a post with paystack signature header gets our attention
            exit();
        }

        // confirm the event's signature
        if ($signature !== hash_hmac('sha512', $response, $this->secretKey)) {
            // silently forget this ever happened
            exit();
        }

        http_response_code(200);

        $paymentResponse = json_decode($response, true);        
        $orderId = $paymentResponse['data']['metadata']['order_id'];
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if ($orderInfo) {
            if ('charge.success' != $paymentResponse['event']) {
                $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
                $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $paymentResponse['event']]);
                $this->logFailure($orderId, $msg, $paymentResponse);
            }

            $orderAmt = ($paymentResponse['data']['amount']) / 100;
            $totalPaidMatch = ((float)$orderAmt == (float)$paymentAmount);
            if (false === $totalPaidMatch) {
                $msg = Labels::getLabel('MSG_PAYMENT_MISMATCH', $this->siteLangId);
                $this->logFailure($orderId, $msg, $paymentResponse);
            }
            /* Unset Session Element On Payment Success.  */
            unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_access_code']);
            unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_reference']);
            /* Unset Session Element On Payment Success.  */
        }
        exit();
    }
}
