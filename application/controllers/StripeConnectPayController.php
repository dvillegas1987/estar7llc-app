<?php

class StripeConnectPayController extends PaymentController
{
    public const KEY_NAME = 'StripeConnect';
    public $stripeConnect;
    private $liveMode = '';
    private $paymentAmount = 0;
    private $sourceId = '';
    private $orderInfo = [];
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

        $this->stripeConnect = PluginHelper::callPlugin(self::KEY_NAME, [$this->siteLangId], $error, $this->siteLangId);
        if (false === $this->stripeConnect) {
            $this->setErrorAndRedirect($error);
        }
        $this->init();
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        if ('distribute' != $this->action && (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged())) {
            $this->userId = UserAuthentication::getLoggedUserId(true);
            if (1 > $this->userId) {
                $msg = Labels::getLabel('MSG_INVALID_USER', $this->siteLangId);
                $this->setErrorAndRedirect($msg);
            }
        }

        if (false === $this->stripeConnect->init($this->userId)) {
            $this->setErrorAndRedirect($this->stripeConnect->getError());
        }

        if (!empty($this->stripeConnect->getError())) {
            $this->setErrorAndRedirect($this->stripeConnect->getError());
        }

        $this->settings = $this->stripeConnect->getSettings();

        if (isset($this->settings['env']) && Plugin::ENV_PRODUCTION == $this->settings['env']) {
            $this->liveMode = "live_";
        }
    }

    /**
     * allowedCurrenciesArr
     *
     * @return array
     */
    protected function allowedCurrenciesArr(): array
    {
        return [
            'USD', 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'UYU', 'UZS', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'
        ];
    }

    protected function minChargeAmountCurrencies()
    {
        return [
            'USD' => 0.50, 'AED' => 2.00, 'AUD' => 0.50, 'BGN' => 1.00, 'BRL' => 0.50, 'CAD' => 0.50, 'CHF' => 0.50, 'CZK' => 15.00,
            'DKK' => 2.50, 'EUR' => 0.50, 'GBP' => 0.30, 'HKD' => 4.00, 'HUF' => 175.00, 'INR' => 0.50, 'JPY' => 50, 'MXN' => 10,
            'MYR' => 2, 'NOK' => 3.00, 'NZD' => 0.50, 'PLN' => 2.00, 'RON' => 2.00, 'SEK' => 3.00, 'SGD' => 0.50
        ];
    }

    protected function zeroDecimalCurrencies()
    {
        return [
            'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'
        ];
    }

    /**
     * getCardForm
     *
     * @return object
     */
    private function getCardForm(): object
    {
        $frm = new Form('frmCardForm');
        $frm->addRequiredField(Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $this->siteLangId), 'number');
        $frm->addRequiredField(Labels::getLabel('LBL_CARD_HOLDER_FULL_NAME', $this->siteLangId), 'name');
        $data['months'] = applicationConstants::getMonthsArr($this->siteLangId);
        $today = getdate();
        $data['year_expire'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
        }
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_MONTH', $this->siteLangId), 'exp_month', $data['months'], '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_YEAR', $this->siteLangId), 'exp_year', $data['year_expire'], '', array(), '');
        $frm->addPasswordField(Labels::getLabel('LBL_CVV_SECURITY_CODE', $this->siteLangId), 'cvc')->requirements()->setRequired();

        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
            $frm->addCheckBox(Labels::getLabel('LBL_SAVE_THIS_CARD_FOR_FASTER_CHECKOUT', $this->siteLangId), 'cc_save_card', '1');
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));

        return $frm;
    }

    /**
     * getSavedCardPaymentForm
     *
     * @return object
     */
    private function getSavedCardPaymentForm(): object
    {
        $frm = new Form('frmCardPaymentForm');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_PAY_NOW', $this->siteLangId));
        return $frm;
    }

    /**
     * checkCardType
     *
     * @return void
     */
    public function checkCardType()
    {
        $post = FatApp::getPostedData();
        $res = ValidateElement::ccNumber($post['cc']);
        echo json_encode($res);
        exit;
    }

    /**
     * addCardForm
     *
     * @param  mixed $orderId
     * @return void
     */
    public function addCardForm($orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }
        $frm = $this->getCardForm();

        $this->set('frm', $frm);
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('orderId', $orderId);
        $this->_template->render(false, false);
    }

    /**
     * removeCard
     *
     * @return void
     */
    public function removeCard()
    {
        $cardId = FatApp::getPostedData('cardId', FatUtility::VAR_STRING, '');
        if (empty($cardId)) {
            $this->setError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        if (false === $this->stripeConnect->removeCard(['cardId' => $cardId])) {
            $this->setErrorAndRedirect($this->stripeConnect->getError());
        }
        $msg = Labels::getLabel("MSG_REMOVED_SUCCESSFULLY", $this->siteLangId);
        FatUtility::dieJsonSuccess($msg);
    }

    /**
     * getOrderInfo
     *
     * @param  string $orderId
     * @return array
     */
    private function getOrderInfo(string $orderId): array
    {
        if (empty($this->orderInfo)) {
            $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
            $this->paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
            $this->orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        }
        return $this->orderInfo;
    }

    /**
     * charge
     *
     * @param  string $orderId
     * @return void
     */
    public function charge($orderId)
    {
        $this->orderId = $orderId;
        if (empty(trim($this->orderId))) {
            $msg = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($msg);
        }

        $this->orderInfo = $this->getOrderInfo($this->orderId);

        if (!$this->orderInfo['id']) {
            $msg = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($msg);
        }

        if ($this->orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER._ALREADY_PAID_OR_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg);
        }

        if (array_key_exists($this->systemCurrencyCode, $this->minChargeAmountCurrencies())) {
            $stripeMinAmount = $this->minChargeAmountCurrencies()[$this->systemCurrencyCode];
            if ($stripeMinAmount > $this->paymentAmount) {
                $this->error = CommonHelper::replaceStringData(Labels::getLabel('MSG_MINIMUM_STRIPE_CHARGE_AMOUNT_IS_{MIN-AMOUNT}', $this->siteLangId), ['{MIN-AMOUNT}' => $stripeMinAmount]);
            }
        }

        $confirmationRequired = false;
        $frm = $this->getSavedCardPaymentForm();
        $post = FatApp::getPostedData();
        if (isset($post['fIsAjax'])) {
            unset($post['fIsAjax'], $post['fOutMode']);
        }

        if (!empty($post)) {
            $saveCard = applicationConstants::NO;
            $cardId = FatApp::getPostedData('card_id', FatUtility::VAR_STRING, '');
            if (!empty($cardId)) {
                $this->sourceId = $cardId;
            } else {
                $cardFrm = $this->getCardForm();
                $cardData = $cardFrm->getFormDataFromArray($post);
                if (false === $cardData) {
                    $this->setErrorAndRedirect(current($cardFrm->getValidationErrors()));
                }
                $saveCard = FatApp::getPostedData('cc_save_card', FatUtility::VAR_INT, 0);
                unset($cardData['btn_submit'], $cardData['cc_save_card']);

                /* It will generate card temp token. */
                if (false === $this->stripeConnect->generateCardToken($cardData)) {
                    $this->setErrorAndRedirect($this->stripeConnect->getError());
                }
                $cardTokenResponse = $this->stripeConnect->getResponse();

                if (0 < $saveCard) {
                    /* Bind Card with customer. */
                    if (false === $this->stripeConnect->addCard(['source' => $cardTokenResponse->id])) {
                        $this->setErrorAndRedirect($this->stripeConnect->getError());
                    }
                    $cardTokenResponse = $this->stripeConnect->getResponse();
                } else {
                    $card = [
                        'token' => $cardTokenResponse->id
                    ];
                    /* Create method with temp card token if customer don't want to save card. */
                    if (false === $this->stripeConnect->addPaymentMethod($card)) {
                        $this->setErrorAndRedirect($this->stripeConnect->getError());
                    }
                    $cardTokenResponse = $this->stripeConnect->getResponse();
                }
                $this->sourceId = $cardTokenResponse->id;
            }

            if ((UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) && 0 < $saveCard && false === $this->stripeConnect->updateCustomerInfo(['default_source' => $this->sourceId])) {
                $this->setErrorAndRedirect($this->stripeConnect->getError());
            }

            $this->createPaymentIntent();
            $response = $this->stripeConnect->getResponse();
            $paymentIntendId = $response->id;
            $clientSecret = $response->client_secret;
            switch ($response->status) {
                case 'succeeded':
                    $successUrl = CommonHelper::generateFullUrl('custom', 'paymentSuccess', [$this->orderId]);
                    $successMsg = Labels::getLabel('LBL_PAYMENT_SUCCEEDED._WAITING_FOR_CONFIRMATION', $this->siteLangId);
                    if (FatUtility::isAjaxCall() || true === MOBILE_APP_API_CALL) {
                        $json['status'] = Plugin::RETURN_TRUE;
                        $json['msg'] = $successMsg;
                        $json['redirectUrl'] = $successUrl;
                        FatUtility::dieJsonSuccess($json);
                    }
                    Message::addMessage($successMsg);
                    FatApp::redirectUser($successUrl);
                    break;
                case 'requires_confirmation':
                    $this->set('paymentIntendId', $paymentIntendId);
                    $this->set('clientSecret', $clientSecret);
                    $confirmationRequired = true;
                    break;
                case 'requires_payment_method':
                case 'requires_action':
                case 'processing':
                case 'requires_capture':
                case 'canceled':
                    $msg = Labels::getLabel('MSG_UNABLE_TO_CHARGE_:_{STATUS}', $this->siteLangId);
                    $msg = CommonHelper::replaceStringData($msg, ['{STATUS}' => $response->status]);
                    $this->setErrorAndRedirect($msg);
                    break;
            }
        } elseif (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
            $requestParam = $this->stripeConnect->formatCustomerDataFromOrder($this->orderInfo);
            if (false === $this->stripeConnect->bindCustomer($requestParam)) {
                $this->setErrorAndRedirect($this->stripeConnect->getError());
            }
            $this->customerId = $this->stripeConnect->getCustomerId();
            $this->set('customerId', $this->customerId);
        }

        $savedCards = [];
        $defaultSource = "";
        if ((UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) && true === $this->stripeConnect->loadCustomer()) {
            $customerInfo = $this->stripeConnect->getResponse()->toArray();
            if (!empty($customerInfo)) {
                $savedCards = array_key_exists('sources', $customerInfo) ? $customerInfo['sources']['data'] : [];
                $defaultSource = array_key_exists('default_source', $customerInfo) ? $customerInfo['default_source'] : "";
            }
        }

        $this->set('defaultSource', $defaultSource);
        $this->set('savedCards', $savedCards);

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($this->orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('paymentAmount', $this->paymentAmount);
        $this->set('orderInfo', $this->orderInfo);
        $this->set('sourceId', $this->sourceId);

        if (true === MOBILE_APP_API_CALL) {
            $this->set('confirmationRequired', $confirmationRequired);
            $this->_template->render();
        }

        $this->set('liveMode', $this->liveMode);
        $this->set('settings', $this->settings);
        $this->set('orderId', $orderId);
        $this->set('frm', $frm);
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('exculdeMainHeaderDiv', true);

        if (true === $confirmationRequired || FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'stripe-connect-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }

        $this->_template->render(true, false);
    }

    /**
     * convertInPaisa
     *
     * @param  mixed $amount
     * @return void
     */
    private function convertInPaisa($amount)
    {
        if (in_array($this->systemCurrencyCode, $this->zeroDecimalCurrencies())) {
            return $amount;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
    }

    /**
     * createPaymentIntent
     *
     * @return void
     */
    private function createPaymentIntent()
    {
        if (empty($this->sourceId)) {
            $msg = Labels::getLabel('MSG_NO_SOURCE_PROVIDED', $this->siteLangId);
            $this->setErrorAndRedirect($msg);
        }

        $customerId = $this->stripeConnect->getCustomerId();
        $desc = Labels::getLabel('LBL_ORDER_#{order-id}_PLACED._SHIPPING_AND_TAX_CHARGES_INCLUDED', $this->siteLangId);
        $desc = CommonHelper::replaceStringData($desc, ['{order-id}' => $this->orderId]);
        $chargeData = [
            'amount' => $this->convertInPaisa($this->paymentAmount),
            'currency' => $this->systemCurrencyCode,
            'description' =>  $desc,
            'metadata' => [
                'order_id' => $this->orderId
            ],
            'statement_descriptor' => $this->orderId,
            'transfer_group' => $this->orderId,
            'payment_method' => $this->sourceId,
            'payment_method_types' => ['card'],
        ];

        if (!empty($customerId)) {
            $chargeData['customer'] = $customerId;
        }

        if (false === $this->stripeConnect->createPaymentIntent($chargeData)) {
            $this->setErrorAndRedirect($this->stripeConnect->getError());
        }
        return true;
    }

    /**
     * distribute
     *
     * @return void
     */
    public function distribute()
    {
        if (false === $this->stripeConnect->init()) {
            $error = [
                'msg' => $this->stripeConnect->getError()
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, time(), json_encode($error));
            CommonHelper::printArray($error, true);
        }

        $payloadStr = @file_get_contents('php://input');
        $payload = json_decode($payloadStr, true);

        if (empty($payload)) {
            $error = [
                'msg' => Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId),
                'response' => $payload,
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, time(), json_encode($error));
            CommonHelper::printArray($error, true);
        }
        
        $orderId = isset($payload['data']['object']['metadata']['order_id']) ? $payload['data']['object']['metadata']['order_id'] : '';
        $status = isset($payload['data']['object']['status']) ? $payload['data']['object']['status'] : Labels::getLabel("MSG_FAILURE", $this->siteLangId);
        if ($payload['type'] != "payment_intent.succeeded") {
            $msg = Labels::getLabel('MSG_UNABLE_TO_CHARGE_:_{STATUS}', $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{STATUS}' => $status]);
            $recordId = empty($orderId) ? time() : $orderId;
            
            $error = [
                'msg' => $msg,
                'response' => $payload,
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $recordId, json_encode($error));
            CommonHelper::printArray($error, true);
        }

        $paymentIntendId = isset($payload['data']['object']['id']) ? $payload['data']['object']['id'] : '';
        if (empty($orderId) || empty($paymentIntendId)) {
            $error = [
                'msg' => Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId),
                'response' => $payload,
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
            CommonHelper::printArray($error, true);
        }

        $this->orderId = $orderId;
        $this->orderInfo = $this->getOrderInfo($this->orderId);
        if ($this->orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $error = [
                'msg' => Labels::getLabel('MSG_INVALID_ORDER._ALREADY_PAID_OR_CANCELLED', $this->siteLangId),
                'response' => $payload,
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
            CommonHelper::printArray($error, true);
        }

        $chargeResponse = isset($payload['data']['object']['charges']['data']) ? current($payload['data']['object']['charges']['data']) : [];
        if (empty($chargeResponse)) {
            $error = [
                'msg' => Labels::getLabel('MSG_INVALID_ORDER_CHARGE', $this->siteLangId),
                'response' => $payload,
            ];
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
            CommonHelper::printArray($error, true);
        }

        $chargeId = $chargeResponse['id'];
        $message = $chargeResponse['status'];

        /* Recording Payment in DB */
        $orderPaymentObj = new OrderPayment($this->orderId, $this->siteLangId);

        $this->paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();


        if (false === $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $chargeId, $this->paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($chargeResponse), false, 0, Orders::ORDER_PAYMENT_PAID)) {
            $orderPaymentObj->addOrderPaymentComments($message);
        }

        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $orderObj = new Orders();
        $orderProducts = $orderObj->getChildOrders(array('order_id' => $orderInfo['id']), $orderInfo['order_type'], $orderInfo['order_language_id']);

        foreach ($orderProducts as $op) {
            $netSellerAmount = CommonHelper::orderProductAmount($op, 'NET_VENDOR_AMOUNT', false, User::USER_TYPE_SELLER);
            $discount = CommonHelper::orderProductAmount($op, 'DISCOUNT');
            $rewardPoint = CommonHelper::orderProductAmount($op, 'REWARDPOINT');
            $totalDiscount = abs($discount) + abs($rewardPoint);

            $firstTransferAmount = $netSellerAmount - $totalDiscount;
            $pendingTransferAmount = $totalDiscount;

            if (0 == $pendingTransferAmount) {
                $firstTransferAmount = $firstTransferAmount - $op['op_commission_charged'];
            } else {
                if ($op['op_commission_charged'] <= $pendingTransferAmount) {
                    $pendingTransferAmount = $pendingTransferAmount - $op['op_commission_charged'];
                } else {
                    $pendingTransferAmount = $op['op_commission_charged'] - $pendingTransferAmount;
                    $firstTransferAmount = $firstTransferAmount - $pendingTransferAmount;
                }
            }

            $accountId = User::getUserMeta($op['op_selprod_user_id'], 'stripe_account_id');
            // Credit sold product amount to seller wallet.
            $msg = 'MSG_PRODUCT_SOLD_#{invoice-no}.';
            if (0 < $pendingTransferAmount) {
                $msg .= "_DISCOUNT/REWARD_POINTS_INCLUSIVE.";
            }
            $comments = Labels::getLabel($msg, $this->siteLangId);
            $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $op['op_invoice_number']]);
            Transactions::creditWallet($op['op_selprod_user_id'], Transactions::TYPE_PRODUCT_SALE, $netSellerAmount, $this->siteLangId, $comments, $op['op_id']);
            
            $commComments = Labels::getLabel('MSG_COMMISSION_CHARGED._#{invoice-no}', $this->siteLangId);
            $commComments = CommonHelper::replaceStringData($commComments, ['{invoice-no}' => $op['op_invoice_number']]);
            Transactions::debitWallet($op['op_selprod_user_id'], Transactions::TYPE_ADMIN_COMMISSION, $op['op_commission_charged'], $this->siteLangId, $commComments, $op['op_id']);

            if (!empty($accountId)) {
                $charge = [
                    'amount' => $this->convertInPaisa($firstTransferAmount),
                    'currency' => $orderInfo['order_currency_code'],
                    'destination' => $accountId,
                    // 'transfer_group' => $op['op_invoice_number'],
                    'description' => $comments,
                    'metadata' => [
                        'op_id' => $op['op_id']
                    ],
                    'source_transaction' => $chargeId
                ];

                if (false === $this->stripeConnect->doTransfer($charge)) {
                    $error = [
                        'msg' => $this->stripeConnect->getError(),
                        'response' => $charge,
                    ];
                    TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
                    continue;
                }

                $resp = $this->stripeConnect->getResponse();

                if (empty($resp->id)) {
                    $error = [
                        'msg' => Labels::getLabel('MSG_UNABLE_TO_TRANFER', $this->siteLangId),
                        'response' => $resp,
                    ];
                    TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
                    continue;
                }

                // Debit sold product amount from seller wallet.
                $comments = $comments . ' ' . Labels::getLabel('MSG_TRANSFERED_TO_ACCOUNT_{account-id}.', $this->siteLangId);
                $comments = CommonHelper::replaceStringData($comments, ['{account-id}' => $accountId]);
                Transactions::debitWallet($op['op_selprod_user_id'], Transactions::TYPE_TRANSFER_TO_THIRD_PARTY_ACCOUNT, $firstTransferAmount, $this->siteLangId, $comments, $op['op_id'], $resp->id);
            }

            if (0 < $pendingTransferAmount) {
                // Credit sold product discount amount to seller wallet.
                $discountComments = Labels::getLabel('MSG_AMOUNT_CREDITED_FOR_DISCOUNT_APPLIED_ON_PRODUCT_SOLD_#{invoice-no}.', $this->siteLangId);
                $discountComments = CommonHelper::replaceStringData($discountComments, ['{invoice-no}' => $op['op_invoice_number']]);
                /*  Transactions::creditWallet($op['op_selprod_user_id'], Transactions::TYPE_PRODUCT_SALE, $discount, $this->siteLangId, $discountComments, $op['op_id']); */

                unset($charge['source_transaction']);
                $charge['amount'] = $this->convertInPaisa($pendingTransferAmount);
                $charge['description'] = $discountComments;
                $charge['metadata']['source_transaction'] = $chargeId;
                if (false === $this->stripeConnect->doTransfer($charge)) {
                    $error = [
                        'msg' => $this->stripeConnect->getError(),
                        'response' => $this->stripeConnect->getResponse(),
                    ];
                    TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
                    continue;
                }

                $resp = $this->stripeConnect->getResponse();
                if (empty($resp->id)) {
                    $error = [
                        'msg' => Labels::getLabel('MSG_UNABLE_TO_TRANFER_PENDING_AMOUNT', $this->siteLangId),
                        'response' => $resp,
                    ];
                    TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($error));
                    continue;
                }

                // Debit sold product discount amount from seller wallet.
                $comments = Labels::getLabel('MSG_AMOUNT_DEBITED_FOR_DISCOUNT_APPLIED_ON_PRODUCT_SOLD_#{invoice-no}._TRANSFERED_TO_ACCOUNT_{account-id}.', $this->siteLangId);
                $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $op['op_invoice_number'], '{account-id}' => $accountId]);
                Transactions::debitWallet($op['op_selprod_user_id'], Transactions::TYPE_TRANSFER_TO_THIRD_PARTY_ACCOUNT, $pendingTransferAmount, $this->siteLangId, $comments, $op['op_id'], $resp->id);
            }
        }
    }

    /**
     * setError
     *
     * @param  mixed $msg
     * @return void
     */
    private function setError(string $msg = "")
    {
        $msg = !empty($msg) ? $msg : $this->stripeConnect->getError();
        LibHelper::exitWithError($msg, true);
    }


    /**
     * getCustomer
     *
     * @return void
     */
    public function getCustomer()
    {
        if (empty($this->stripeConnect->getCustomerId())) {
            $this->setError(Labels::getLabel('MSG_INVALID_CUSTOMER', $this->siteLangId));
        }
        $this->stripeConnect->loadCustomer();
        $customerInfo = $this->stripeConnect->getResponse()->toArray();
        $this->set('customerInfo', $customerInfo);
        $this->_template->render();
    }

    /**
     * markCardAsDefault
     *
     * @return void
     */
    public function markCardAsDefault()
    {
        $source = FatApp::getPostedData('source', FatUtility::VAR_STRING, '');
        if (empty($source)) {
            $this->setError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        if (empty($this->stripeConnect->getCustomerId())) {
            $this->setError(Labels::getLabel('MSG_INVALID_CUSTOMER', $this->siteLangId));
        }

        $requestParam['default_source'] = $source;
        if (false === $this->stripeConnect->updateCustomerInfo($requestParam)) {
            $this->setError();
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_SUCCESSFULLY_UPDATED', $this->siteLangId));
    }

    /**
     * getExternalLibraries
     *
     * @return void
     */
    public function getExternalLibraries()
    {
        $json['libraries'] = ['https://js.stripe.com/v3/'];
        FatUtility::dieJsonSuccess($json);
    }
}
