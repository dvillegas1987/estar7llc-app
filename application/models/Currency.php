<?php

class Currency extends MyAppModel
{
    public const DB_TBL = 'tbl_currency';
    public const DB_TBL_PREFIX = 'currency_';

    public const DB_TBL_LANG = 'tbl_currency_lang';
    public const DB_TBL_LANG_PREFIX = 'currencylang_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'curr');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'curr_l.' . static::DB_TBL_LANG_PREFIX . 'currency_id = curr.' . static::tblFld('id') . ' and
			curr_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'curr_l'
            );
        }

        if ($isActive) {
            $srch->addCondition('curr.currency_active', '=', 1);
        }

        return $srch;
    }

    public static function getListingObj($langId, $attr = null)
    {
        $srch = self::getSearchObject($langId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
                'IFNULL(curr_l.currency_name,curr.currency_code) as currency_name'
            )
        );

        return $srch;
    }

    public static function getCurrencyAssoc($langId)
    {
        $currencyGetCurrencyAssoc = FatCache::get('currencyGetCurrencyAssoc' .  $langId, CONF_DEF_CACHE_TIME, '.txt');
        if ($currencyGetCurrencyAssoc) {
            return json_decode($currencyGetCurrencyAssoc, true);
        }

        $langId = FatUtility::int($langId);
        $srch = self::getListingObj($langId, array('currency_id', 'currency_code'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAllAssoc($rs);

        if (!is_array($row)) {
            return false;
        }
        FatCache::set('currencyGetCurrencyAssoc' . $langId, FatUtility::convertToJson($row), '.txt');
        return $row;
    }

    public static function getCurrencyNameWithCode($langId)
    {
        $langId = FatUtility::int($langId);
        $srch = self::getSearchObject($langId);
        $srch->addMultipleFields(
            array(
                'currency_id',
                'CONCAT(IFNULL(curr_l.currency_name,curr.currency_code)," (",currency_code ,")") as currency_name_code'
            )
        );
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetchAllAssoc($rs, 'currency_id');
        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    public static function getDefault()
    {
        return Currency::getAttributesById(FatApp::getConfig("CONF_CURRENCY", FatUtility::VAR_INT, 1));
    }

    public static function getDefaultCurrencyCode()
    {
        $baseCurrency = static::getDefault();
        if (empty($baseCurrency)) {
            return false;
        }
        return strtoupper($baseCurrency['currency_code']);
    }

    public function getCurrencyConverterApi()
    {
        $defaultCurrConvAPI = FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . PLUGIN::TYPE_CURRENCY_CONVERTER, FatUtility::VAR_INT, 0);
        if (empty($defaultCurrConvAPI)) {
            $this->error = Labels::getLabel('MSG_DEFAULT_CURRENCY_CONVERTER_NOT_DEFINED', CommonHelper::getLangId());
            return false;
        } elseif (1 > Plugin::getAttributesById($defaultCurrConvAPI, 'plugin_active')) {
            $this->error = Labels::getLabel('MSG_DEFAULT_CURRENCY_CONVERTER_API_ACTIVE', CommonHelper::getLangId());
            return false;
        }

        return Plugin::getAttributesById($defaultCurrConvAPI, 'plugin_code');
    }

    public function updatePricingRates($currenciesData)
    {
        $currencyObj = new TableRecord(static::DB_TBL);
        foreach ($currenciesData as $currencyCode => $rate) {
            $data['currency_date_modified'] = date('Y-m-d H:i:s');
            $data['currency_value'] = $rate;

            $currencyObj->assignValues($data);
            if (!$currencyObj->update(['smt' => 'currency_code=?', 'vals' => [$currencyCode]])) {
                $this->error = $currencyObj->getError();
                return false;
            }
        }
        return true;
    }
}
