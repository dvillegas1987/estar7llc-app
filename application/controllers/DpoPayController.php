<?php
class DpoPayController extends PaymentController
{
    public const KEY_NAME = "Dpo";
    private $userId = 0;
    private $orderPaymentObj;

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
        return ["*"];
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
            $this->paymentInitiated($orderId);
            if (false === $this->plugin->initiateRequest($orderId)) {
                $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
            }

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
            $json['html'] = $this->_template->render(false, false, 'dpo-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }
    
    /**
     * paymentFailedAndRedirect
     *
     * @param  mixed $orderId
     * @param  mixed $msg
     * @param  mixed $response
     * @return void
     */
    public function paymentFailedAndRedirect(string $orderId, string $msg, array $response)
    {
        $log = [
            'msg' => $msg,
            'response' => $response,
        ];
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($log));
        $this->orderPaymentObj->addOrderPaymentComments($msg);
        Message::addErrorMessage($msg);
        FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
    }

    /**
     * callback
     *
     * @param  string $orderId
     * @return void
     */
    public function callback(string $orderId)
    {
        $response = FatApp::getQueryStringData();

        $this->orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        if ($response['PnrID'] != $response['CompanyRef'] || $orderId != $response['CompanyRef']) {
            $msg = Labels::getLabel('LBL_INVALID_PAYMENT.', $this->siteLangId);
            $this->paymentFailedAndRedirect($orderId, $msg, $response);
        }

        $orderInfo = $this->orderPaymentObj->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->paymentFailedAndRedirect($orderId, $msg, $response);
        }

        if (false === $this->plugin->validateResponse($response)) {
            $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $this->plugin->getError()]);
            $this->paymentFailedAndRedirect($orderId, $msg, $response);
        }

        $paymentAmount = $this->orderPaymentObj->getOrderPaymentGatewayAmount();
        $queryResponse = $this->plugin->getResponse();
        $logResponse = [
            'callbackResponse' => $response,
            'queryResponse' => $queryResponse,
        ];

        if ($paymentAmount != $queryResponse->TransactionFinalAmount || $this->systemCurrencyCode != $queryResponse->TransactionFinalCurrency) {
            $msg = Labels::getLabel('LBL_PAYMENT_MISMATCHED.', $this->langId);
            $this->paymentFailedAndRedirect($orderId, $msg, $logResponse);
        }

        /* Recording Payment in DB */
        $this->orderPaymentObj->addOrderPayment(self::KEY_NAME, $response['TransID'], $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($logResponse));
        /* End Recording Payment in DB */
        FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
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
        $actionUrl = false === $processRequest ? UrlHelper::generateUrl('DpoPay', 'charge', array($orderId)) : $this->plugin->getPaymenUrl();
        $frm = new Form('frmPaymentForm', array('action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        }

        return $frm;
    }
}
