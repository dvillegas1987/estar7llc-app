<?php


/**
 * Paynow - API's reference https://docs.paynow.pl/
 * Git - https://github.com/pay-now/paynow-php-sdk
 */
class PaynowPayController extends PaymentController
{
    public const KEY_NAME = "Paynow";
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
        return ['PLN'];
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

            $this->authorize = $this->plugin->getResponse();
            if ("ERROR" == $this->authorize->getStatus()) {
                $msg = Labels::getLabel("MSG_UNABLE_TO_INITIALIZE_PAYMENT_REQUEST._PAYMENT_CANNOT_BE_COMPLETED.", $this->siteLangId);
                $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
            }
            
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_paymentId'] = $this->authorize->getPaymentId();
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
            $json['html'] = $this->_template->render(false, false, 'paynow-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    /**
     * paymentSuccess
     *
     * @param  string $orderId
     * @return void
     */
    public function paymentSuccess(string $orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId);
        $paymentId = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_paymentId'];
        if (!array_key_exists('paymentStatus', $_REQUEST) || !array_key_exists('paymentStatus', $_REQUEST)) {
            $this->logFailure($orderId);
        }

        if ('CONFIRMED' != $_REQUEST['paymentStatus'] || $paymentId != $_REQUEST['paymentId']) {
            $this->logFailure($orderId);
        }

        if (false === $this->plugin->validatePaymentResponse($paymentId)) {
            $this->logFailure($orderId, $this->plugin->getError());
        }
        
        $paymentStatus = $this->plugin->getResponse();
        if ('CONFIRMED' != $paymentStatus->getStatus()) {
            $this->logFailure($orderId);
        }

        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        /* Recording Payment in DB */
        if (false === $orderPaymentObj->addOrderPayment(self::KEY_NAME, $paymentId, $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($_REQUEST))) {
            $msg = $orderPaymentObj->getError();
            $this->logFailure($orderId, $msg);
        }
        /* End Recording Payment in DB */

        /* Unset Session Element On Payment Success.  */
        unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME][self::KEY_NAME . '_paymentId']);
        /* Unset Session Element On Payment Success.  */

        FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
    }
    
    /**
     * logFailure
     *
     * @param  string $orderId
     * @return void
     */
    private function logFailure($orderId, $msg = '')
    {
        $orderPaymentObj = new OrderPayment($orderId);
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($_REQUEST));

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
        $actionUrl = false === $processRequest ? UrlHelper::generateUrl('PaynowPay', 'charge', array($orderId)) : $this->authorize->getRedirectUrl();
        $frm = new Form('frmPaymentForm', array('action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');
        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        }
        return $frm;
    }
}
