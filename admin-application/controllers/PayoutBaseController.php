<?php

class PayoutBaseController extends PluginSettingController
{
    protected $envoirment;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->envoirment = FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false);
    }

    public function index()
    {
        $recordId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if (1 > $recordId) {
            $message = Labels::getLabel('LBL_INVALID_REQUEST', $this->adminLangId);
            LibHelper::dieJsonError($message);
        }

        $comment = FatApp::getPostedData('comment', FatUtility::VAR_STRING, '');

        $specifics = WithdrawalRequestsSearch::getWithDrawalSpecifics($recordId);
        try {
            $calledClass = get_called_class();
            $obj = new $calledClass(__FUNCTION__);
            $response = $obj->release($recordId, $specifics);
        } catch (\Error $e) {
            $message = 'ERR - ' . $e->getMessage();
            LibHelper::dieJsonError($message);
        }

        if (true !== $response['status']) {
            $message = Labels::getLabel('LBL_UNABLE_TO_PROCEED!_PLEASE_TRY_AGAIN', $this->adminLangId);
            LibHelper::dieJsonError($message);
        }

        $assignFields = array('withdrawal_status' => Transactions::WITHDRAWL_STATUS_PROCESSED, 'withdrawal_comments' => $comment);
        if (!FatApp::getDb()->updateFromArray(User::DB_TBL_USR_WITHDRAWAL_REQ, $assignFields, array('smt' => 'withdrawal_id=?', 'vals' => array($recordId)))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $oldTrxComment = Transactions::getAttributesById($recordId, 'utxn_comments');
        $rs = FatApp::getDb()->updateFromArray(
                Transactions::DB_TBL,
                array('utxn_comments' => $oldTrxComment . " (" . $comment . ")"),
                array('smt' => 'utxn_withdrawal_id=?', 'vals' => array($recordId))
        );

        $this->set('msg', Labels::getLabel('LBL_PAYOUT_REQUEST_SENT_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
