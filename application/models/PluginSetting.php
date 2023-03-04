<?php

class PluginSetting
{
    private $error;
    private $pluginId;
    private $pluginKey;
    private $langId;

    public const DB_TBL = 'tbl_plugin_settings';
    public const DB_TBL_PREFIX = 'pluginsetting_';
    
    public const TYPE_STRING = 1;
    public const TYPE_INT = 2;
    public const TYPE_FLOAT = 3;
    public const TYPE_BOOL = 4;

    public function __construct($id, $pluginKey = '')
    {
        $this->pluginId = empty($pluginKey) ? $id : Plugin::getAttributesByCode($pluginKey, 'plugin_id');
        $this->pluginKey = $pluginKey;
        $this->langId = CommonHelper::getLangId();
    }

    public function getError()
    {
        return $this->error;
    }

    private function delete(): bool
    {
        if (1 > $this->pluginId) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }
        $statement = [
            'smt' => static::DB_TBL_PREFIX . 'plugin_id = ?',
            'vals' => [
                $this->pluginId
            ]
        ];
        if (!FatApp::getDb()->deleteRecords(static::DB_TBL, $statement)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function get(int $langId = 0, string $column = '')
    {
        if (empty($this->pluginKey)) {
            $this->error = Labels::getLabel('MSG_PLUGIN_KEY_NOT_FOUND', $this->langId);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL, 'tps');
        $srch->addCondition('tps.' . static::DB_TBL_PREFIX . 'plugin_id', '=', $this->pluginId);
        $srch->addMultipleFields(array('tps.' . static::DB_TBL_PREFIX . 'key', 'tps.' . static::DB_TBL_PREFIX . 'value'));
        $rs = $srch->getResultSet();
        if (!$rs) {
            $this->error = $srch->getError();
            return false;
        }
        $row =  FatApp::getDb()->fetchAllAssoc($rs);

        $settingsData = Plugin::getAttributesByCode($this->pluginKey, '', $langId);
        if (0 < $langId) {
            $settingsData['plugin_name'] = !empty($settingsData['plugin_name']) ? $settingsData['plugin_name'] : $settingsData['plugin_identifier'];
        }
        $settings = array_merge($row, $settingsData);

        if (!empty($column) && is_string($column)) {
            return array_key_exists($column, $settings) ? $settings[$column] : '';
        }

        return $settings;
    }

    public function save(array $data): bool
    {
        if (empty($data) || !is_array($data)) {
            $this->error = Labels::getLabel('MSG_PLEASE_PROVIDE_DATA_TO_SAVE_SETTINGS', $this->langId);
            return false;
        }
        unset($data['keyName'], $data['btn_submit'], $data["plugin_id"]);

        if (!$this->delete()) {
            return false;
        }
        foreach ($data as $key => $val) {
            $updateData = [
                'pluginsetting_plugin_id' => $this->pluginId,
                'pluginsetting_key' => $key,
                'pluginsetting_value' => is_array($val) ? serialize($val) : $val,
            ];

            if (!FatApp::getDb()->insertFromArray(static::DB_TBL, $updateData, false, ['IGNORE'])) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        }
        return true;
    }

    public static function getForm($requirements, $langId)
    {
        $frm = new Form('frmPlugins');
        $frm->addHiddenField('', 'keyName');
        $frm->addHiddenField('', 'plugin_id');

        foreach ($requirements as $fieldName => $attributes) {
            $label = 'LBL_' . str_replace(' ', '_', strtoupper($attributes['label']));
            $label = Labels::getLabel($label, $langId);

            switch ($attributes['type']) {
                case static::TYPE_INT:
                    $fld = $frm->addIntegerField($label, $fieldName);
                    break;
                case static::TYPE_FLOAT:
                    $fld = $frm->addFloatField($label, $fieldName);
                    break;
                case static::TYPE_BOOL:
                    $yesNo = array_reverse(applicationConstants::getYesNoArr($langId));
                    $fld = $frm->addSelectBox($label, $fieldName, $yesNo, '', array(), '');
                    break;
                default:
                    $fld = $frm->addTextBox($label, $fieldName);
                    break;
            }
            if (true == $attributes['required']) {
                $fld->requirements()->setRequired(true);
            }
        }

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public static function addKeyFields($frm)
    {
        $frm->addHiddenField('', 'keyName');
        $frm->addHiddenField('', 'plugin_id');
        return $frm;
    }
}
