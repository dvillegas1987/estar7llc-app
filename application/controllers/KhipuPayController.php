<?php

class KhipuPayController extends PaymentController
{
    public const KEY_NAME = "Khipu";
    public $initiatePayment;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return ['CLP'];
    }

    private function init(): void
    {
        if (false === $this->plugin->validateSettings($this->siteLangId)) {
            $this->setErrorAndRedirect($this->plugin->getError());
        }

        $this->settings = $this->plugin->getSettings();
    }

    public function charge($orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $payment_amount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (empty($orderInfo)) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $frm = $this->getPaymentForm($orderId);
        $postOrderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
        $processRequest = false;
        if (!empty($postOrderId) && $orderId = $postOrderId) {
            $receiver_id = $this->settings['receiver_id'];
            $subject = Labels::getLabel('MSG_YoKart_Payment', $this->siteLangId);
            $body = '';
            $return_url = UrlHelper::generateFullUrl('custom', 'paymentSuccess', array($orderId));
            $notify_url = UrlHelper::generateNoAuthUrl('KhipuPay', 'send');
            $cancel_url = CommonHelper::getPaymentCancelPageUrl();
            $custom = $orderId;
            $transaction_id = 'Order-' . $orderId;
            $picture_url = '';
            $payer_email = $orderInfo['customer_email'];
            $secret = $this->settings['secret_key'];
            $concatenated = "receiver_id=$receiver_id&subject=$subject&body=$body&amount=$payment_amount&return_url=$return_url&cancel_url=$cancel_url&custom=$custom&transaction_id=$transaction_id&picture_url=$picture_url&payer_email=$payer_email&secret=$secret";
            $hash = sha1($concatenated);
            $configuration = new Khipu\Configuration();
            $configuration->setReceiverId($this->settings['receiver_id']);
            $configuration->setSecret($this->settings['secret_key']);
            //$configuration-> setDebug (true);
            $client = new Khipu\ApiClient($configuration);
            $payments = new Khipu\Client\PaymentsApi($client);
            try {
                $this->paymentInitiated($orderId);
                $this->initiatePayment = $payments->paymentsPost(
                    FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->siteLangId), // Reason for purchase
                    "CLP", // Currency
                    ceil($payment_amount), // Amount
                    [
                        $transaction_id, // transaction ID in trade
                        $custom, // optional field greater long to send information to the URL notification
                        null, // Payment Description
                        null, // ID of the bank to pay
                        $return_url, // return URL
                        $cancel_url, // URL rejection
                        $picture_url, // URL Product Image
                        $notify_url, // URL notification
                        "1.3",  // notification version of the API
                        null, // Expiry Date
                        null, // Send the payment by email
                        null, // Name of payer
                        null, // Email payer
                        null, // Send email reminders
                        null, // E-mail of responsible payment
                        null, // Personal identifier of the payer, if used only you are paid with this
                        null // Commission for the integrator
                    ]
                );
                $frm = $this->getPaymentForm($orderId, true);
                $processRequest = true;
            } catch (exception $e) {
                $this->setErrorAndRedirect($e->getMessage(), FatUtility::isAjaxCall());
            }
        }

        $frm->fill(['orderId' => $orderId]);
        $this->set('frm', $frm);
        $this->set('processRequest', $processRequest);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('paymentAmount', $payment_amount);
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
    public function send()
    {
        $post = FatApp::getPostedData();
        $api_version = $post['api_version'];
        $notification_token = $post['notification_token'];
        try {
            if ($api_version == '1.3') {
                $configuration = new Khipu\Configuration();
                $configuration->setSecret($this->settings['secret_key']);
                $configuration->setReceiverId($this->settings['receiver_id']);
                $client = new Khipu\ApiClient($configuration);
                $payments = new Khipu\Client\PaymentsApi($client);
                $response = $payments->paymentsGet($notification_token);
                $orderId = $response->getCustom();
                $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
                /* Retrieve Payment to charge corresponding to your order */
                $order_payment_amount = $orderPaymentObj->getOrderPaymentGatewayAmount();

                if ($order_payment_amount > 0) {
                    /* Retrieve Primary Info corresponding to your order */
                    $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
                    $order_actual_paid = ceil($order_payment_amount);
                    $json = array();
                    if (!$response) {
                        throw new Exception(Labels::getLabel('MSG_EMPTY_GATEWAY_RESPONSE', $this->siteLangId));
                    }
                    if ($response->getReceiverId() == $this->settings['receiver_id']) {
                        if (strtolower($response->getStatus()) == 'done') {
                            if ($response->getAmount() == $order_actual_paid) {
                                // Make payment as complete and deliver the good or service
                                if (!$orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $response->getTransactionId(), $response->getAmount(), Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode($response))) {
                                }
                            } else {
                                TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));
                                $request = $response->__toString() . "\n\n KHIPU :: TOTAL PAID MISMATCH! " . $response->getAmount() . "\n\n";
                                $orderPaymentObj->addOrderPaymentComments($request);
                            }
                        }
                    } else {
                        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));
                        $request = $response->__toString() . "\n\n KHIPU :: RECEIVER MISMATCH! " . $response->getReceiverId() . "\n\n";
                        $orderPaymentObj->addOrderPaymentComments($request);
                    }
                } else {
                    $json['error'] = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
                }
            } else {
                // Use previous version of Notification API
            }
        } catch (OmiseNotFoundException $e) {
            $json['error'] = 'ERROR: ' . $e->getMessage();
        } catch (exception $e) {
            $json['error'] = 'ERROR: ' . $e->getMessage();
        }
        echo json_encode($json);
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
        $actionUrl = false === $processRequest ? UrlHelper::generateUrl('KhipuPay', 'charge', array($orderId)) : $this->initiatePayment->getPaymentUrl();
        $frm = new Form('frmPaymentForm', array('action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');
        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        }
        return $frm;
    }
}
