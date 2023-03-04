<?php

class SmsArchive extends MyAppModel
{
    public const DB_TBL = 'tbl_sms_archives';
    public const DB_TBL_PREFIX = 'smsarchive_';
    public $body = '';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public function toPhone($toNumber)
    {
        $this->toNumber = $toNumber;
    }

    public function setTemplate($langId, $tpl, $replacements = [])
    {
        $this->tpl = $tpl;
        $this->langId = FatUtility::int($langId);
        if (1 > $this->langId) {
            $this->langId = Commonhelper::getLangId();
        }

        if (false == static::canSendSms($tpl)) {
            $this->error = Labels::getLabel("MSG_UNABLE_TO_SEND_SMS", $this->langId);
            return false;
        }

        $tplData = SmsTemplate::getTpl($this->tpl, $this->langId);
        if (!$tplData) {
            $this->error = Labels::getLabel("MSG_TEMPLATE_NOT_FOUND", $this->langId);
            return false;
        }

        if (1 > $tplData['stpl_status']) {
            $this->error = Labels::getLabel("MSG_TEMPLATE_NOT_ACTIVE", $this->langId);
            return false;
        }

        $replacements = array_merge($replacements, LibHelper::getCommonReplacementVarsArr($langId));
        $this->body = CommonHelper::replaceStringData($tplData['stpl_body'], $replacements);
    }


    public function send()
    {
        $smsGateway = FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_SMS_NOTIFICATION, FatUtility::VAR_INT, 0);
        if (empty($smsGateway) || empty($this->toNumber) || empty($this->body) || 1 > $this->langId) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $pluginKey = Plugin::getAttributesById($smsGateway, 'plugin_code');

        $error = '';
        if (false === PluginHelper::includePlugin($pluginKey, Plugin::getDirectory(Plugin::TYPE_SMS_NOTIFICATION), $error, $this->langId)) {
            $this->error = $error;
            return false;
        }

        $smsGateway = new $pluginKey($this->langId);
        $response = $smsGateway->send($this->toNumber, $this->body);

        if (false == $response || false == $response['status']) {
            $this->error = isset($response['msg']) ? $response['msg'] : $smsGateway->getError();
            return false;
        }

        $dataToSave = [
            'smsarchive_to' => $this->toNumber,
            'smsarchive_tpl_name' => $this->tpl,
            'smsarchive_body' => $this->body,
            'smsarchive_sent_on' => date('Y-m-d H:i:s'),
            'smsarchive_response_id' => !empty($response['response_id']) ? $response['response_id'] : 0
        ];

        $this->assignValues($dataToSave);
        if (!$this->save()) {
            return false;
        }
        return true;
    }

    public static function updateStatus($messageId, $status, $response, &$error = '')
    {
        if (empty($messageId) || empty($status)) {
            $error = Labels::getLabel('MSG_INVALID_REQUEST', CommonHelper::getLangId());
            return false;
        }

        $db = FatApp::getDb();

        $dataToSave = [
            'smsarchive_status' => $status,
            'smsarchive_response' => json_encode($response),
        ];

        $where = ['smt' => 'smsarchive_response_id = ?', 'vals' => [$messageId]];
        if (!$db->updateFromArray(self::DB_TBL, $dataToSave, $where)) {
            $error = $db->getError();
            return false;
        }
        return true;
    }

    public static function canSendSms(string $tpl = ''): bool
    {
        $active = (new Plugin())->getDefaultPluginData(Plugin::TYPE_SMS_NOTIFICATION, 'plugin_active');
        $status = empty($tpl) ? 1 : SmsTemplate::getTpl($tpl, 0, 'stpl_status');
        return (false != $active && !empty($active) && 0 < $status);
    }
}
