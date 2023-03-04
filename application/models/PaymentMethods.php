<?php

class PaymentMethods
{
    public const MOVE_TO_ADMIN_WALLET = 0;
    public const MOVE_TO_CUSTOMER_WALLET = 1;
    public const MOVE_TO_CUSTOMER_CARD = 2;

    public const REFUND_TYPE_RETURN = 1;
    public const REFUND_TYPE_CANCEL = 2;

    private $paymentPlugin;
    private $keyname = '';
    private $langId = '';
    private $canRefundToCard = false;
    private $resp = [];
    private $sellerId = '';
    private $opId = '';
    private $transferId = '';
    private $invoiceNumber = '';
    private $remoteTxnId = '';
    private $sellerTxnAmount = '';
    private $error = '';

    public function __construct()
    {
        $this->paymentPlugin = (object)[];
    }
    
    /**
     * getSearchObject
     *
     * @param  int $langId
     * @param  bool $isActive
     * @return object
     */
    public static function getSearchObject(int $langId = 0, bool $isActive = true): object
    {
        $srch = Plugin::getSearchObject($langId, $isActive);
        $cond = $srch->addCondition('plugin_type', '=', Plugin::TYPE_REGULAR_PAYMENT_METHOD);
        $cond->attachCondition('plugin_type', '=', Plugin::TYPE_SPLIT_PAYMENT_METHOD);
        return $srch;
    }
    
    /**
     * cashOnDeliveryIsActive
     *
     * @return bool
     */
    public function cashOnDeliveryIsActive(): bool
    {
        return (Plugin::isActive('CashOnDelivery') || Plugin::isActive('PayAtStore'));
    }

    /**
     * canUseWalletForPayment
     *
     * @return bool
     */
    public static function canUseWalletForPayment(): bool
    {
        $srch = Plugin::getSearchObject(0, true);
        $srch->addCondition(Plugin::DB_TBL_PREFIX . 'type', '=', Plugin::TYPE_SPLIT_PAYMENT_METHOD);
        $srch->addCondition(Plugin::DB_TBL_PREFIX . 'active', '=', Plugin::ACTIVE);
        $srch->setPageSize(1);
        $srch->getResultSet();
        return (1 > $srch->recordCount());
    }

    /**
     * canRefundToCard
     *
     * @param string $keyname
     * @param int $langId
     * @return bool
     */
    public function canRefundToCard(string $keyname, int $langId): bool
    {
        $this->keyname = $keyname;
        $this->langId = $langId;
        $this->paymentPlugin = PluginHelper::callPlugin($this->keyname, [$this->langId]);
        return $this->canRefundToCard = method_exists($this->paymentPlugin, 'initiateRefund');
    }

    /**
     * moveRefundLocationsArr
     *
     * @param type $langId
     * @return array
     */
    public static function moveRefundLocationsArr($langId = 0): array
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return [
            self::MOVE_TO_ADMIN_WALLET => Labels::getLabel('LBL_DO_NOT_TRANSFER', $langId),
            self::MOVE_TO_CUSTOMER_WALLET => Labels::getLabel('LBL_TRANSFER_TO_CUSTOMER_WALLET', $langId),
            self::MOVE_TO_CUSTOMER_CARD => Labels::getLabel('LBL_TRANSFER_TO_PAYMENT_SOURCE', $langId),
        ];
    }
    
    /**
     * convertInPaisa
     *
     * @param  mixed $amount
     * @return void
     */
    private function convertInPaisa($amount): int
    {
        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
    }

    /**
     * initiateRefund
     *
     * @param array $requestRow
     * @return mixed
     */
    public function initiateRefund(array $requestRow, int $refundType = self::REFUND_TYPE_RETURN): bool
    {
        if (false == $this->canRefundToCard) {
            $msg = Labels::getLabel('MSG_THIS_{PAYMENT-METHOD}_PAYMENT_METHOD_IS_NOT_ABLE_TO_REFUND_IN_CARD', $this->langId);
            $this->error = CommonHelper::replaceStringData($msg, ['{PAYMENT-METHOD}' => $this->keyname]);
            return false;
        }

        switch ($refundType) {
            case self::REFUND_TYPE_RETURN:
                $this->opId = $requestRow['orrequest_op_id'];
                break;
            
            case self::REFUND_TYPE_CANCEL:
                $this->opId = $requestRow['op_id'];
                break;
            
            default:
                $this->error = Labels::getLabel('MSG_INVALID_REFUND_TYPE', $this->langId);
                return false;
                break;
        }

        $orderObj = new Orders();
        $childOrderInfo = $orderObj->getOrderProductsByOpId($this->opId, $this->langId);
        $payments = $orderObj->getOrderPayments(["order_id" => $childOrderInfo['op_order_id']]);

        $this->sellerId = $childOrderInfo['op_selprod_user_id'];
        $this->invoiceNumber = $childOrderInfo['op_invoice_number'];

        $txnId = "";
        array_walk($payments, function ($value, $key) use (&$txnId) {
            if ($this->keyname == $value['opayment_method']) {
                $txnId = $value['opayment_gateway_txn_id'];
                return;
            }
        });

        $this->txnAmount = CommonHelper::orderProductAmount($childOrderInfo, 'NETAMOUNT');
        switch ($refundType) {
            case self::REFUND_TYPE_RETURN:
                if ($childOrderInfo['op_qty'] != $requestRow['orrequest_qty']) {
                    $this->txnAmount = ($this->txnAmount / $childOrderInfo['op_qty']) * $requestRow['orrequest_qty'];
                }
               
                $amountToBePaidToSeller = CommonHelper::orderProductAmount($childOrderInfo, 'NETAMOUNT', false, User::USER_TYPE_SELLER);

                $deductableSellerAmount = (($amountToBePaidToSeller - $childOrderInfo['op_commission_charged']) / $childOrderInfo['op_qty']) * $requestRow['orrequest_qty'];

                break;
        }

        switch ($this->keyname) {
            case 'StripeConnect':
                $requestParam = [
                    'amount' => $this->convertInPaisa($this->txnAmount),
                    'charge' => $txnId,
                    'metadata' => [
                       'orderInvoice' => $this->invoiceNumber
                    ]
                ];
                if (false === $this->paymentPlugin->init(true)) {
                    $this->error = $this->paymentPlugin->getError();
                    return false;
                }

                $respStatus = $this->paymentPlugin->initiateRefund($requestParam);
                if (false == $respStatus) {
                    $this->error = $this->paymentPlugin->getError();
                    return false;
                }
                
                $txnData = $this->getTransferTxnData();
                $transferAmtArr = [ ];
                if (!empty($txnData)) {
                    foreach ($txnData as $txn) {
                        if (empty($txn['utxn_gateway_txn_id'])) {
                            continue;
                        }
                        
                        $transferAmtArr[$txn['utxn_gateway_txn_id']] = $txn['utxn_debit'];
                        if (self::REFUND_TYPE_RETURN == $refundType) {
                            if ($txn['utxn_debit'] >= $deductableSellerAmount) {
                                $transferAmtArr[$txn['utxn_gateway_txn_id']] = $deductableSellerAmount;
                                $deductableSellerAmount = 0;
                            } else {
                                $deductableSellerAmount = $deductableSellerAmount - $transferAmtArr[$txn]['utxn_gateway_txn_id'];
                            }
                        }
                    }
                }

                if (!empty($transferAmtArr)) {
                    foreach ($transferAmtArr as $transferId => $txnAmt) {
                        $this->transferId = $transferId;
                        $this->sellerTxnAmount = $txnAmt;

                        $this->refundFromWallet();

                        $comments = Labels::getLabel('MSG_REFUND_INITIATE_-', $this->langId) .  $txnData[$transferId]['utxn_comments'];
                        $requestParam = [
                            'transferId' => $this->transferId,
                            'data' => [
                                'amount' => $this->convertInPaisa($this->sellerTxnAmount), // In Paisa
                                'description' => $comments,
                                'metadata' => [
                                    'op_id' => $this->opId
                                ],
                            ],
                        ];
                        $respStatus = $this->paymentPlugin->revertTransfer($requestParam);
                        if (false == $respStatus) {
                            $this->error = $this->paymentPlugin->getError();
                            return false;
                        }

                        //To get response object
                        $this->resp = $this->paymentPlugin->getResponse();
                        if (!empty($this->resp->id)) {
                            $this->remoteTxnId = $this->resp->id;
                            // Credit to wallet if successfully refund from remote account
                            $this->returnRefundAmount($comments);
                        }
                    }
                }

            break;
        }
        return true;
    }
    
    /**
     * getTxnAmount - Return txn amount used while refund
     *
     * @return void
     */
    public function getTxnAmount()
    {
        return $this->txnAmount;
    }

    /**
     * getSellerTxnAmount - Return selller txn amount used while refund
     *
     * @return void
     */
    public function getSellerTxnAmount()
    {
        return $this->sellerTxnAmount;
    }

    /**
     * getTransferTxnData
     *
     * @return void
     */
    public function getTransferTxnData(int $sellerId = 0, int $opId = 0)
    {
        $sellerId = 0 < $sellerId ? $sellerId : $this->sellerId;
        $opId = 0 < $opId ? $opId : $this->opId;

        $db = FatApp::getDb();
        $srch = Transactions::getUserTransactionsObj($sellerId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('utxn.utxn_type', '=', Transactions::TYPE_TRANSFER_TO_THIRD_PARTY_ACCOUNT);
        $srch->addCondition('utxn.utxn_op_id', '=', $opId);
        $srch->addOrder('utxn_debit', 'DESC');
        $rs = $srch->getResultSet();
        $records = $db->fetchAll($rs, 'utxn_gateway_txn_id');
        if (!$records) {
            $this->error = $db->getError();
            return false;
        }
        return $records;
    }
    
    /**
     * refundFromWallet - Refund transferred amount to seller
     *
     * @return bool
     */
    private function refundFromWallet()
    {
        $comments = Labels::getLabel('MSG_REFUND_INITIATE_REGARDING_#{invoice-no}', $this->langId);
        $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $this->invoiceNumber]);
        Transactions::debitWallet($this->sellerId, Transactions::TYPE_ORDER_REFUND, $this->sellerTxnAmount, $this->langId, $comments, $this->opId);
        return true;
    }

    /**
     * returnRefundAmount - Return Refund amount if debited from seller remote account.
     *
     * @param  strig $comments
     * @return bool
     */
    private function returnRefundAmount(string $comments): bool
    {
        if (empty($this->remoteTxnId)) {
            $this->error = Labels::getLabel('MSG_NO_REMOTE_TXN_ID_FOUND', $this->langId);
            return false;
        }
        
        Transactions::creditWallet($this->sellerId, Transactions::TYPE_ORDER_REFUND, $this->sellerTxnAmount, $this->langId, $comments, $this->opId, $this->remoteTxnId);
        return true;
    }

    /**
     * getResponse
     *
     * @return object
     */
    public function getResponse(): object
    {
        return empty($this->resp) ? (object) array() : $this->resp;
    }
    
    /**
     * getError
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
