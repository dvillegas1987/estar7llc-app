<?php

class WithdrawalRequestsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewWithdrawRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditWithdrawRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewWithdrawRequests();
        $data = FatApp::getPostedData();
        $frmSearch = $this->getSearchForm($this->adminLangId);
        if ($data) {
            $data['withdrawal_id'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewWithdrawRequests();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm($this->adminLangId);
        $data = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $post = $searchForm->getFormDataFromArray($data);
        $srch = new WithdrawalRequestsSearch();
        $srch->joinUsers(true);
        $srch->joinForUserBalance();
        $srch->addOrder('withdrawal_id', 'DESC');
        $srch->joinTable(User::DB_TBL_USR_WITHDRAWAL_REQ_SPEC, 'LEFT JOIN', User::DB_TBL_USR_WITHDRAWAL_REQ_SPEC_PREFIX . 'withdrawal_id = tuwr.withdrawal_id');
        $srch->addMultipleFields(
            array(
                'tuwr.*', 'GROUP_CONCAT(CONCAT(`uwrs_key`, ":", `uwrs_value`)) as payout_detail', 'user_name', 'credential_email as user_email', 'credential_username as user_username',
                'user_balance', 'user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate'
            )
        );

        if (isset($post['keyword']) && $post['keyword']) {
            $cond = $srch->addCondition('credential_username', 'like', '%' . $post['keyword'] . '%');
            $cond->attachCondition('user_name', 'like', '%' . $post['keyword'] . '%', 'OR');
            $cond->attachCondition('credential_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        }

        if (isset($post['minprice']) && $post['minprice'] > 0) {
            $srch->addCondition('tuwr.withdrawal_amount', '>=', $post['minprice']);
        }
        if (isset($post['withdrawal_id']) && $post['withdrawal_id'] > 0) {
            $srch->addCondition('tuwr.withdrawal_id', '=', $post['withdrawal_id']);
        }

        if (isset($post['maxprice']) && $post['maxprice'] > 0) {
            $srch->addCondition('tuwr.withdrawal_amount', '<=', $post['maxprice']);
        }

        if (isset($post['status']) && $post['status'] >= 0) {
            $srch->addCondition('tuwr.withdrawal_status', '=', $post['status']);
        }

        if (isset($post['date_from']) && $post['date_from']) {
            $srch->addCondition('tuwr.withdrawal_request_date', '>=', $post['date_from'] . ' 00:00:00');
        }

        if (isset($post['date_to']) && $post['date_to']) {
            $srch->addCondition('tuwr.withdrawal_request_date', '<=', $post['date_to'] . ' 00:00:00');
        }

        $type = FatApp::getPostedData('type', FatUtility::VAR_INT, 0);
        if ($type > 0) {
            if ($type == User::USER_TYPE_SELLER) {
                $srch->addCondition('user_is_supplier', '=', applicationConstants::YES);
            }
            if ($type == User::USER_TYPE_BUYER) {
                $srch->addCondition('user_is_buyer', '=', applicationConstants::YES);
            }

            if ($type == User::USER_TYPE_ADVERTISER) {
                $srch->addCondition('user_is_advertiser', '=', applicationConstants::YES);
            }

            if ($type == User::USER_TYPE_AFFILIATE) {
                $srch->addCondition('user_is_affiliate', '=', applicationConstants::YES);
            }
        }

        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addGroupBy('tuwr.withdrawal_id');
        $rs = $srch->getResultSet();

        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('statusArr', Transactions::getWithdrawlStatusArr($this->adminLangId));
        $this->_template->render(false, false);
    }
    
    public function setupUpdateStatus()
    {
        $this->objPrivilege->canEditWithdrawRequests();
        $withdrawalId = FatApp::getPostedData('withdrawal_id', FatUtility::VAR_INT, 0);

        $frm = $this->getUpdateStatusForm($withdrawalId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false == $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedStatusUpdateArr = array(Transactions::WITHDRAWL_STATUS_APPROVED, Transactions::WITHDRAWL_STATUS_DECLINED);

        $srch = new WithdrawalRequestsSearch();
        $srch->addCondition('withdrawal_id', '=', $withdrawalId);
        $srch->addCondition('withdrawal_status', '=', Transactions::WITHDRAWL_STATUS_PENDING);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!$row || !in_array($post['withdrawal_status'], $allowedStatusUpdateArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $comment = $post['withdrawal_comments'];
        $assignFields = array('withdrawal_status' => $post['withdrawal_status'], 'withdrawal_comments' => $comment);
        if (!FatApp::getDb()->updateFromArray(User::DB_TBL_USR_WITHDRAWAL_REQ, $assignFields, array('smt' => 'withdrawal_id=?', 'vals' => array($withdrawalId)))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendWithdrawRequestNotification($withdrawalId, $this->adminLangId, "U")) {
            Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $assignFields = array('utxn_status' => Transactions::STATUS_COMPLETED);
        if ($post['withdrawal_status'] == Transactions::WITHDRAWL_STATUS_APPROVED) {
            $oldTrxComment = Transactions::getAttributesById($withdrawalId, 'utxn_comments');
            $assignFields['utxn_comments'] = $oldTrxComment . " (" . $comment . ")";
        }

        FatApp::getDb()->updateFromArray(
                Transactions::DB_TBL,
                $assignFields,
                array('smt' => 'utxn_withdrawal_id=?', 'vals' => array($withdrawalId))
        );

        if ($post['withdrawal_status'] == Transactions::WITHDRAWL_STATUS_DECLINED) {
            $transObj = new Transactions();
            $txnDetail = $transObj->getAttributesBywithdrawlId($withdrawalId);
            $formattedRequestValue = '#' . str_pad($withdrawalId, 6, '0', STR_PAD_LEFT);

            $txnArray["utxn_user_id"] = $txnDetail["utxn_user_id"];
            $txnArray["utxn_credit"] = $txnDetail["utxn_debit"];
            $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
            $txnArray["utxn_withdrawal_id"] = $txnDetail["utxn_withdrawal_id"];
            $txnArray["utxn_type"] = Transactions::TYPE_MONEY_WITHDRAWL_REFUND;
            $txnArray["utxn_comments"] = sprintf(Labels::getLabel('MSG_Withdrawal_Request_Declined_Amount_Refunded', $this->adminLangId), $formattedRequestValue);
            if (!empty($comment)) {
                $txnArray["utxn_comments"] = $txnArray["utxn_comments"] . "( " . $comment . " )";
            }

            if ($txnId = $transObj->addTransaction($txnArray)) {
                $emailNotificationObj->sendTxnNotification($txnId, $this->adminLangId);
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Status_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewComment($withdrawalId)
    {
        $this->objPrivilege->canEditWithdrawRequests();
        $srch = new WithdrawalRequestsSearch();
        $srch->addCondition('withdrawal_id', '=', $withdrawalId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();        
        $row = FatApp::getDb()->fetch($rs);        
        if (!$row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }  
        $this->set('comment', $row['withdrawal_comments']);
        $this->_template->render(false, false);
    }
    
    public function updateStatusForm($withdrawalId)
    {
        $this->objPrivilege->canEditWithdrawRequests();
        $allowedStatusUpdateArr = array(Transactions::WITHDRAWL_STATUS_APPROVED, Transactions::WITHDRAWL_STATUS_DECLINED);

        $srch = new WithdrawalRequestsSearch();
        $srch->addCondition('withdrawal_id', '=', $withdrawalId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();        
        $row = FatApp::getDb()->fetch($rs);
        
        if (1 > $withdrawalId || !$row ||  $row['withdrawal_status'] != Transactions::WITHDRAWL_STATUS_PENDING) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('frm', $this->getUpdateStatusForm($withdrawalId));
        $this->set('withdrawal_payment_method', $row['withdrawal_payment_method']);
        $this->_template->render(false, false);
    }
    
    private function getSearchForm($langId)
    {
        $frm = new Form('frmReqSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_From', $this->adminLangId) . ' [' . $this->siteDefaultCurrencyCode . ']', 'minprice')->requirements()->setFloatPositive(true);
        $frm->addTextBox(Labels::getLabel('LBL_To', $this->adminLangId) . ' [' . $this->siteDefaultCurrencyCode . ']', 'maxprice')->requirements()->setFloatPositive(true);

        $statusArr = Transactions::getWithdrawlStatusArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'status', array('-1' => 'Does not matter') + $statusArr, '', array(), '');

        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly', 'class' => 'field--calender'));

        $arr_options2 = array('-1' => Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + User::getUserTypesArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_User_Type', $this->adminLangId), 'type', $arr_options2, -1, array(), '');
        $frm->addHiddenField('', 'withdrawal_id', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearTagSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
        
    private function getUpdateStatusForm($withdrawalId)
    {
        $frm = new Form('frmUpdateStatus');
        $statusArr = array(
            Transactions::WITHDRAWL_STATUS_PENDING => Labels::getLabel('LBL_Withdrawal_Request_Pending', $this->adminLangId),
            Transactions::WITHDRAWL_STATUS_APPROVED => Labels::getLabel('LBL_Withdrawal_Request_Approved', $this->adminLangId),
            Transactions::WITHDRAWL_STATUS_DECLINED => Labels::getLabel('LBL_Withdrawal_Request_Declined', $this->adminLangId),
        );
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'withdrawal_status', $statusArr, '', array(), '');
        $frm->addTextarea(Labels::getLabel('LBL_Comment', $this->adminLangId), 'withdrawal_comments');
        $fld = $frm->addHiddenField('', 'withdrawal_id', $withdrawalId);
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}
