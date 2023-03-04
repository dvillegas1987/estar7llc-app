<?php

class SmsTemplate extends MyAppModel
{
    public const DB_TBL = 'tbl_sms_templates';
    public const DB_TBL_PREFIX = 'stpl_';

    public const LOGIN = 'LOGIN';
    public const COD_OTP_VERIFICATION = 'COD_OTP_VERIFICATION';

    private $stplCode;

    public function __construct($stplCode = '')
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'code', $stplCode);
        $this->stplCode = $stplCode;
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        $srch = new SearchBase(static::DB_TBL);
        $srch->addOrder(static::DB_TBL_PREFIX . 'name', 'ASC');
        $srch->addMultipleFields(
            [
                static::DB_TBL_PREFIX . 'code',
                static::DB_TBL_PREFIX . 'lang_id',
                static::DB_TBL_PREFIX . 'name',
                static::DB_TBL_PREFIX . 'body',
                static::DB_TBL_PREFIX . 'replacements',
                static::DB_TBL_PREFIX . 'status',
            ]
        );
        if ($langId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'lang_id', '=', $langId);
        }
        return $srch;
    }

    public static function getTpl($stpl_code, $langId = 0, $attr = '')
    {
        if (empty($stpl_code)) {
            return false;
        }

        $db = FatApp::getDb();

        $srch = static::getSearchObject($langId);
        $srch->addCondition(static::DB_TBL_PREFIX . 'code', 'LIKE', $stpl_code);
        if ($langId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'lang_id', '=', $langId);
        }
        if (!empty($attr)) {
            $cols = is_string($attr) ? [$attr] : $attr;
            $srch->addMultipleFields($cols);
        }

        $srch->addOrder(static::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addGroupby(static::DB_TBL_PREFIX . 'code');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$rs) {
            return false;
        }
        $data = $db->fetch($rs);
        if (!empty($attr) && is_string($attr)) {
            return isset($data[$attr]) ? $data[$attr] : '';
        }
        return $data;
    }

    private function formatData($data)
    {
        $langId = 0 < FatUtility::int($data['stpl_lang_id']) ? $data['stpl_lang_id'] : 0;
        if (1 > $langId) {
            $this->error = Labels::getLabel('MSG_INVALID_LANGUAGE', CommonHelper::getLangId());
            return false;
        }
        
        if (empty($data['stpl_body'])) {
            $this->error = Labels::getLabel('MSG_MESSAGE_BODY_IS_REQUIRED', CommonHelper::getLangId());
            return false;
        }

        return [
            self::DB_TBL_PREFIX . 'code' => !empty($data['stpl_code']) ? $data['stpl_code'] : '',
            self::DB_TBL_PREFIX . 'lang_id' => $langId,
            self::DB_TBL_PREFIX . 'name' => !empty($data['stpl_name']) ? $data['stpl_name'] : '',
            self::DB_TBL_PREFIX . 'body' => $data['stpl_body'],
        ];
    }
    public function addUpdateData($data)
    {
        $assignValues = $this->formatData($data);
        if (false === $assignValues) {
            return false;
        }

        if (!FatApp::getDb()->insertFromArray(static::DB_TBL, $assignValues, false, [], $assignValues)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    private function updateStatus($status)
    {
        if (empty($this->stplCode)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST', CommonHelper::getLangId());
            return false;
        }

        $db = FatApp::getDb();
        $updateData = [
            static::DB_TBL_PREFIX . 'status' => $status
        ];
        $condition = [
            'smt' => static::DB_TBL_PREFIX . 'code = ?',
            'vals' => [
                $this->stplCode
            ]
        ];
        if (!$db->updateFromArray(static::DB_TBL, $updateData, $condition)) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function makeActive()
    {
        return $this->updateStatus(applicationConstants::ACTIVE);
    }

    public function makeInActive()
    {
        return $this->updateStatus(applicationConstants::INACTIVE);
    }
}
