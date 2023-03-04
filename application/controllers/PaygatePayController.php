<?php
class PaygatePayController extends PaymentController
{
    public const KEY_NAME = "Paygate";
    private $userId = 0;
    private $payWeb3;

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

        $this->settings = $this->plugin->getSettings();
        $this->paygateId = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_paygate_id'] : $this->settings['paygate_id'];
        $this->encryptionKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_encryption_key'] : $this->settings['encryption_key'];
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
            if (false === $this->plugin->initiateRequest($orderId)) {
                $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
            }

            $this->payWeb3 = $this->plugin->getResponse();
            if (isset($this->payWeb3->lastError) && !empty($this->payWeb3->lastError)) {
                $this->setErrorAndRedirect($this->payWeb3->lastError, FatUtility::isAjaxCall());
            }

            if (isset($this->payWeb3->processRequest)) {
                $isValid = $this->payWeb3->validateChecksum($this->payWeb3->initiateResponse);
                if (false == $isValid) {
                    $this->setErrorAndRedirect(Labels::getLabel('MSG_INVALID_CHECKSUM', $this->siteLangId), FatUtility::isAjaxCall());
                }
                $frm = $this->getPaymentForm($orderId, true);
                $processRequest = true;
            }
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
            $json['html'] = $this->_template->render(false, false, 'paygate-pay/charge-ajax.php', true, false);
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
        $response = FatApp::getPostedData();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $log = [
                'msg' => $msg,
                'response' => $response,
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($log));
            $orderPaymentObj->addOrderPaymentComments($msg);
            Message::addErrorMessage($msg);
            FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        }

        if (false === $this->plugin->validateResponse($orderId, $response)) {
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));

            $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $this->plugin->getError()]);

            $orderPaymentObj->addOrderPaymentComments($msg);
            Message::addErrorMessage($msg);
            FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        }

        $this->queryResponse = $this->plugin->getResponse();
        if (false === $this->plugin->validateTxnStatus($this->queryResponse['TRANSACTION_STATUS'])) {
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($this->queryResponse));

            $desc = Labels::getLabel("MSG_PAYMENT_FAILED", $this->siteLangId);
            $desc = isset($this->queryResponse['RESULT_DESC']) ? $this->queryResponse['RESULT_DESC'] : $desc;

            $msg = Labels::getLabel("MSG_TXN_STATUS_{STATUS}._{DESC}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{STATUS}' => $this->plugin->getError(), '{DESC}' => $desc]);

            $orderPaymentObj->addOrderPaymentComments($msg);
            Message::addErrorMessage($msg);
            FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        }

        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        /* Recording Payment in DB */
        $orderPaymentObj->addOrderPayment(self::KEY_NAME, $this->queryResponse['TRANSACTION_ID'], $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($this->queryResponse));
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
        $actionUrl = false === $processRequest ? UrlHelper::generateUrl('PaygatePay', 'charge', array($orderId)) : $this->payWeb3::$process_url;
        $frm = new Form('frmPaymentForm', array('action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (true === $processRequest && isset($this->payWeb3->processRequest)) {
            /*
            * If the checksums match loop through the returned fields and create the redirect from
            */
            foreach ($this->payWeb3->processRequest as $name => $value) {
                $frm->addHiddenField('', $name, $value);
            }
        } else {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        }

        return $frm;
    }
}
