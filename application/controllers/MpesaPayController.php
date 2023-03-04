<?php

/**
 * MpesaPayController - M-Pesa services in 10 countries: Albania, the Democratic Republic of Congo, Egypt, Ghana, India, Kenya, Lesotho, Mozambique, Romania and Tanzania. 
 */
class MpesaPayController extends PaymentController
{
    public const KEY_NAME = "Mpesa";
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
            "ALL", "CDF", "EGP", "GHS", "INR", "KES", "LSL", "ZAR", "MZN", "RON", "TZS"
        ];
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
        $this->clientId = 0 < $this->settings['env'] ? $this->settings['live_shortcode'] : $this->settings['shortcode'];
        $this->secretKey = 0 < $this->settings['env'] ? $this->settings['live_passkey'] : $this->settings['passkey'];
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

        $customerPhone =  '';
        if (0 < $this->userId) {
            $userObj = new User($this->userId);
            $userData = $userObj->getUserInfo(['user_phone']);
            $customerPhone = array_key_exists('user_phone', $userData) ? $userData["user_phone"] : '';
        }

        $frm = $this->getPaymentForm($orderId);
        if (!empty($customerPhone)) {
            $frm->fill(['customerPhone' => $customerPhone]);
        }
        $this->set('frm', $frm);

        $phoneNumber = FatApp::getPostedData('customerPhone', FatUtility::VAR_INT, 0);
        if (0 < $phoneNumber) {
            $this->paymentInitiated($orderId);
            if (false === $this->plugin->STKPushSimulation($orderId, $paymentAmount, $phoneNumber, $orderId)) {
                $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
            }

            $response = $this->plugin->getResponse();
            if (array_key_exists('errorMessage', $response)) {
                $this->setErrorAndRedirect($response['errorMessage'], FatUtility::isAjaxCall());
            }

            if (array_key_exists('ResponseCode', $response)) {
                if (0 < $response['ResponseCode']) {
                    $this->setErrorAndRedirect($response['ResponseDescription'], FatUtility::isAjaxCall());
                }

                $msg = Labels::getLabel('MSG_WAITING_FOR_CONFIRMATION', $this->siteLangId);
                $json['msg'] = $response['ResponseDescription'] . ' ' . $msg;
                $json['redirect'] = UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
            } else {
                $msg = Labels::getLabel('LBL_SOMETHING_WENT_WRONG', $this->siteLangId);
                $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
            }
        }

        $this->set('exculdeMainHeaderDiv', true);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'mpesa-pay/charge-ajax.php', true, false);
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
        $json = file_get_contents('php://input');
        $post = json_decode($json, true);
        $orderPaymentObj = new OrderPayment($orderId);
        $msg = "";
        if (json_last_error() == JSON_ERROR_NONE) {
            $callbackResponse = array_key_exists('callback_response', $post) ? $post['callback_response'] : $post;
            $stkCallback = isset($callbackResponse['Body']['stkCallback']) ? $callbackResponse['Body']['stkCallback'] : [];
            $checkoutRequestID = isset($stkCallback['CheckoutRequestID']) ? $stkCallback['CheckoutRequestID'] : '';
            $error = empty($checkoutRequestID);
            if (false === $error && isset($stkCallback['ResultCode']) && 0 == $stkCallback['ResultCode']) {
                if (false === $this->plugin->STKPushQuery($checkoutRequestID)) {
                    $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
                }

                $response = $this->plugin->getResponse();
                /**
                 * 0 Success (for C2B).
                 * 00000000	Success (For APIs that are not C2B).
                 * 1 or any other number	Rejecting the transaction.
                 */
                if (array_key_exists('ResponseCode', $response) && 1 != $response['ResponseCode']) {
                    $error = ($response['ResultCode'] != $stkCallback['ResultCode']);
                    $json = false == $error ? $json : json_encode(array_merge(['callback_response' => $post], ['verification_response' => $response]));
                } else {
                    $error = true;
                    $json = json_encode(array_merge(['callback_response' => $post], ['verification_response' => $response]));
                }

                if (false === $error) {
                    $callbackMetadata = $stkCallback['CallbackMetadata'];
                    $payment_amount = 0;
                    $txnId = '';
                    foreach ($callbackMetadata['Item'] as $orderTxn) {
                        if ('amount' == strtolower($orderTxn['Name'])) {
                            $payment_amount = $orderTxn['Value'];
                        }

                        if ('mpesareceiptnumber' == strtolower($orderTxn['Name'])) {
                            $txnId = $orderTxn['Value'];
                        }

                        if (!empty($payment_amount) && !empty($txnId)) {
                            break;
                        }
                    }
                    $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $txnId, $payment_amount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), $json);
                    return;
                }
            } else { 
                $msg = array_key_exists('ResultDesc', $stkCallback) ? $stkCallback['ResultDesc'] : $this->getResultCodeName($stkCallback['ResultCode']);
            }
        }
        $msg = Labels::getLabel("MSG_PAYMENT_FAILED.", $this->siteLangId) . (!empty($msg) ? ' ' . $msg : '');
        $resp = ['msg' => $msg, 'resp' => $post];
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($resp));
        $orderPaymentObj->addOrderPaymentComments($msg);
        return;
    }

    /**
     * getPaymentForm
     *
     * @param  mixed $orderId
     * @return object
     */
    private function getPaymentForm($orderId): object
    {
        $frm = new Form('frmPaymentForm', array('id' => 'frmPaymentForm', 'action' => UrlHelper::generateUrl('MpesaPay', 'charge', array($orderId)), 'class' => "form form--normal"));
        $frm->addRequiredField(Labels::getLabel('LBL_PHONE_NUMBER', $this->siteLangId), 'customerPhone');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_REQUEST', $this->siteLangId));

        return $frm;
    }

    /**
     * getResultCodeName
     *
     * @param  int $code
     * @return object
     */
    private function getResultCodeName(int $code): string
    {
        $arr = [
            '0' => Labels::getLabel('MSG_SUCCESS', $this->siteLangId),
            '1' => Labels::getLabel('MSG_INSUFFICIENT_FUNDS', $this->siteLangId),
            '2' => Labels::getLabel('MSG_LESS_THAN_MINIMUM_TRANSACTION_VALUE', $this->siteLangId),
            '3' => Labels::getLabel('MSG_MORE_THAN_MAXIMUM_TRANSACTION_VALUE', $this->siteLangId),
            '4' => Labels::getLabel('MSG_WOULD_EXCEED_DAILY_TRANSFER_LIMIT', $this->siteLangId),
            '5' => Labels::getLabel('MSG_WOULD_EXCEED_MINIMUM_BALANCE', $this->siteLangId),
            '6' => Labels::getLabel('MSG_UNRESOLVED_PRIMARY_PARTY', $this->siteLangId),
            '7' => Labels::getLabel('MSG_UNRESOLVED_RECEIVER_PARTY', $this->siteLangId),
            '8' => Labels::getLabel('MSG_WOULD_EXCEED_MAXIUMUM_BALANCE', $this->siteLangId),
            '11' => Labels::getLabel('MSG_DEBIT_ACCOUNT_INVALID', $this->siteLangId),
            '12' => Labels::getLabel('MSG_CREDIT_ACCOUNT_INVALIUD', $this->siteLangId),
            '13' => Labels::getLabel('MSG_UNRESOLVED_DEBIT_ACCOUNT', $this->siteLangId),
            '14' => Labels::getLabel('MSG_UNRESOLVED_CREDIT_ACCOUNT', $this->siteLangId),
            '15' => Labels::getLabel('MSG_DUPLICATE_DETECTED', $this->siteLangId),
            '17' => Labels::getLabel('MSG_INTERNAL_FAILURE', $this->siteLangId),
            '20' => Labels::getLabel('MSG_UNRESOLVED_INITIATOR', $this->siteLangId),
            '26' => Labels::getLabel('MSG_TRAFFIC_BLOCKING_CONDITION_IN_PLACE', $this->siteLangId),
        ];
        return array_key_exists($code, $arr) ? $arr[$code] : '';
    }
}
