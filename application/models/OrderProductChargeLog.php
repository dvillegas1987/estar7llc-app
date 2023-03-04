<?php

class OrderProductChargeLog extends MyAppModel
{
    public const DB_TBL = 'tbl_order_prod_charges_logs';
    public const DB_TBL_PREFIX = 'opchargelog_';

    public const DB_TBL_LANG = 'tbl_order_prod_charges_logs_lang';
    public const DB_TBL_LANG_PREFIX = 'opchargeloglang_';
    private $opId;

    public function __construct(int $opId, int $id = 0)
    {
        $this->opId = FatUtility::int($opId);
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    /**
    * getSearchObject
    *
    * @param  int $langId
    * @return object
    */
    public static function getSearchObject($langId = 0):object
    {
        $srch = new SearchBase(static::DB_TBL, 'opcl');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'opcl_l.opchargeloglang_opchargelog_id = opchargelog_id
			AND opcl_l.opchargeloglang_lang_id = ' . $langId,
                'opcl_l'
            );
        }

        return $srch;
    }

    /**
    * getData
    *
    * @param  int $langId
    * @return array
    */
    public function getData($langId = 0):array
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject($langId);
        $srch->addCondition('opchargelog_op_id', '=', $this->opId);
        $srch->addMultipleFields(array('opchargelog_name as name', 'opchargelog_value as value', 'opchargelog_percentvalue as percentageValue', 'opchargelog_is_percent as inPercentage'));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $records = FatApp::getDb()->fetchAll($srch->getResultSet(), 'name');
        return $records;
    }
}
