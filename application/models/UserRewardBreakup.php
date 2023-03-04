<?php
class UserRewardBreakup extends MyAppModel
{
    public const DB_TBL = 'tbl_user_reward_point_breakup';
    public const DB_TBL_PREFIX = 'urpbreakup_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'urpb');
        return $srch;
    }

    public static function rewardPointBalance($userId = 0, $orderId = '')
    {
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            return 0;
        }
        $db = FatApp::getDb();
        $totalBalance = 0;

        $srch = new UserRewardSearch();
        $srch->joinUserRewardBreakup();
        $srch->addCondition('urp.urp_user_id', '=', $userId);
        $srch->addCondition('urpb.urpbreakup_used', '=', 0);
        $cond = $srch->addCondition('urpb.urpbreakup_expiry', '>=', date('Y-m-d'), 'AND');
        $cond->attachCondition('urpb.urpbreakup_expiry', '=', '0000-00-00', 'OR');
        $srch->addMultipleFields(array('sum(urpbreakup_points) as balance'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if ($row != false) {
            $totalBalance = $totalBalance + FatUtility::int($row['balance']);
        }

        $srch = new OrderProductSearch();
        $srch->joinorders();
        $srch->joinPaymentMethod();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_reward_point_used', '>', 0);
        $cnd = $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PENDING);
        $cnd->attachCondition('plugin_code', '=', 'CashOnDelivery');
        $srch->addCondition('op.op_status_id', '=', FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 2 hours'));
        //$srch->addDirectCondition('DATE(o.order_date_added) = DATE(NOW())');
        $srch->addCondition('o.order_date_added', '>=', $date);
        $srch->addMultipleFields(array('order_reward_point_used'));
        if ($orderId != '') {
            $srch->addCondition('order_id', '!=', $orderId);
        }
        $srch->addGroupBy('order_id');
        $srch->addCondition('order_user_id', '=', $userId);
        
        $rs = $db->query('SELECT SUM(order_reward_point_used) as usedRewards from (' . $srch->getQuery() . ') as temp');
        $row = $db->fetch($rs);

        if ($row == false || $totalBalance < $row['usedRewards']) {
            return 0;
        }

        $totalBalance = $totalBalance - FatUtility::int($row['usedRewards']);
        return floor($totalBalance);
    }
}
