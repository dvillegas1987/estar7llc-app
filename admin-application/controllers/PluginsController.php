<?php

class PluginsController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->objPrivilege->canViewPlugins($this->admin_id);
    }

    public function index()
    {
        $this->canEdit = $this->objPrivilege->canEditPlugins($this->admin_id, true);
        $this->set("canEdit", $this->canEdit);
        $this->set("plugins", Plugin::getTypeArr($this->adminLangId));
        $this->set('activeTab', Plugin::TYPE_CURRENCY_CONVERTER);
        $this->set('includeEditor', true);
        $this->_template->render();
    }

    public function search($type)
    {
        $srch = Plugin::getSearchObject($this->adminLangId, false);
        $srch->joinTable(Configurations::DB_TBL, 'LEFT JOIN', "conf_val = plugin_id AND conf_name = 'CONF_DEFAULT_PLUGIN_" . $type . "'");
        $srch->addCondition('plugin_type', '=', $type);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder(Plugin::DB_TBL_PREFIX . 'active', 'DESC');
        $srch->addOrder(Plugin::DB_TBL_PREFIX . 'display_order', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $activeTaxPluginFound = false;
        if (Plugin::TYPE_TAX_SERVICES == $type) {
            array_walk($records, function($val) use(&$activeTaxPluginFound) {
                if (Plugin::ACTIVE == $val[Plugin::DB_TBL_PREFIX . 'active']) {
                    $activeTaxPluginFound = true;
                    return;
                }
            });
        }
        
        $this->canEdit = $this->objPrivilege->canEditPlugins($this->admin_id, true);
        $pluginTypes = Plugin::getTypeArr($this->adminLangId);
        
        $groupType = Plugin::getGroupType($type);
        $otherPluginTypes = '';
        if (!empty($groupType)) {
            foreach ($groupType as $pluginType) {
                if ($type == $pluginType) {
                    continue;
                }
                $srch = Plugin::getSearchObject(0, true);
                $srch->addCondition(Plugin::DB_TBL_PREFIX . 'type', '=', $pluginType);
                $srch->addCondition(Plugin::DB_TBL_PREFIX . 'active', '=', Plugin::ACTIVE);
                $srch->setPageSize(1);
                $srch->getResultSet();
                if (0 < $srch->recordCount()) {
                    $otherPluginTypes .= $pluginTypes[$pluginType] . ', ';
                }
            }
            $otherPluginTypes = rtrim($otherPluginTypes, ', ');
        }

        $this->set("canEdit", $this->canEdit);
        $this->set("activeTaxPluginFound", $activeTaxPluginFound);
        $this->set("type", $type);
        $this->set("pluginTypes", $pluginTypes);
        $this->set("otherPluginTypes", $otherPluginTypes);
        $this->set("arr_listing", $records);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function form($pluginType, $pluginId)
    {
        $pluginId = FatUtility::int($pluginId);
        $frm = $this->getForm($pluginType, $pluginId);
        $identifier = '';
        if (0 < $pluginId) {
            $data = Plugin::getAttributesById($pluginId, ['plugin_id', 'plugin_identifier', 'plugin_active']);
            if ($data === false) {
                FatUtility::dieJsonError($this->str_invalid_request);
            }

            if (in_array($pluginType, Plugin::HAVING_KINGPIN)) {
                $defaultCurrConvAPI = FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . $pluginType, FatUtility::VAR_INT, 0);
                if (!empty($defaultCurrConvAPI)) {
                    $data['CONF_DEFAULT_PLUGIN_' . $pluginType] = $defaultCurrConvAPI;
                }
            }
            $identifier = $data['plugin_identifier'];
            $frm->fill($data);
        }

        $this->set('identifier', $identifier);
        $this->set('languages', Language::getAllNames());
        $this->set('pluginId', $pluginId);
        $this->set('type', $pluginType);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditPlugins();
        $post = FatApp::getPostedData();
        $pluginId = $post['plugin_id'];
        $pluginType = $post['plugin_type'];
        $frm = $this->getForm($pluginType, $pluginId);
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['plugin_id'], $post['plugin_type']);
        
        if (1 > $pluginId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        if (0 < $pluginId) {
            $pluginId = Plugin::getAttributesById($pluginId, 'plugin_id');
            if ($pluginId === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
        }

        $record = new Plugin($pluginId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($pluginId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Plugin::getAttributesByLangId($langId, $pluginId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $pluginId = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        
        if (in_array($pluginType, Plugin::HAVING_KINGPIN)) {
            $defaultCurrConvAPI = FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . $pluginType, FatUtility::VAR_INT, 0);
            if (!empty($post['CONF_DEFAULT_PLUGIN_' . $pluginType]) || empty($defaultCurrConvAPI)) {
                $confVal = empty($defaultCurrConvAPI) ? $pluginId : $post['CONF_DEFAULT_PLUGIN_' . $pluginType];
                $confRecord = new Configurations();
                if (!$confRecord->update(['CONF_DEFAULT_PLUGIN_' . $pluginType => $confVal])) {
                    Message::addErrorMessage($confRecord->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('pluginId', $pluginId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($pluginId = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $pluginId = FatUtility::int($pluginId);
        $lang_id = FatUtility::int($lang_id);

        if ($pluginId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($pluginId, $lang_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Plugin::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($pluginId, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Plugin::getAttributesByLangId($lang_id, $pluginId);
        }
        if ($langData) {
            $langFrm->fill($langData);
        }

        $pluginDetail = Plugin::getAttributesById($pluginId, ['plugin_type', 'plugin_identifier']);

        if (!in_array($pluginDetail['plugin_type'], Plugin::HAVING_DESCRIPTION)) {
            $langFrm->removeField($langFrm->getField('plugin_description'));
        }

        $this->set('languages', Language::getAllNames());
        $this->set('type', $pluginDetail['plugin_type']);
        $this->set('identifier', $pluginDetail['plugin_identifier']);
        $this->set('pluginId', $pluginId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditPlugins();
        $post = FatApp::getPostedData();

        $pluginId = $post['plugin_id'];
        $lang_id = $post['lang_id'];

        if ($pluginId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($pluginId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['plugin_id']);
        unset($post['lang_id']);

        $data = array(
        'pluginlang_lang_id' => $lang_id,
        'pluginlang_plugin_id' => $pluginId,
        'plugin_name' => $post['plugin_name'],
        'plugin_description' => FatApp::getPostedData('plugin_description', FatUtility::VAR_STRING, ''),
        );

        $pluginObj = new Plugin($pluginId);

        if (!$pluginObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($pluginObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Plugin::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($pluginId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Plugin::getAttributesByLangId($langId, $pluginId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('pluginId', $pluginId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadIcon($plugin_id)
    {
        $this->objPrivilege->canEditPlugins();

        $plugin_id = FatUtility::int($plugin_id);

        if (1 > $plugin_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_PLUGIN_LOGO, $plugin_id, 0, $_FILES['file']['name'], -1, $unique_record = true);
        if (!$res) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('pluginId', $plugin_id);
        $this->set('file', $_FILES['file']['name']);
        $this->set('msg', $_FILES['file']['name'] . ' ' . Labels::getLabel('LBL_File_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditPlugins();

        $post = FatApp::getPostedData();

        if (!empty($post)) {
            $pluginObj = new Plugin();
            if (!$pluginObj->updateOrder($post['plugin'])) {
                Message::addErrorMessage($pluginObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            $this->set('msg', Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditPlugins();
        $pluginId = FatApp::getPostedData('pluginId', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        if (0 >= $pluginId) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $data = Plugin::getAttributesById($pluginId, array('plugin_id', 'plugin_active', 'plugin_type'));

        if ($data == false) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }
        
        if (false == Plugin::updateStatus($data['plugin_type'], $status, $pluginId, $error)) {
            FatUtility::dieJsonError($error);
        }

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatusByType()
    {
        $this->objPrivilege->canEditPlugins();
        $pluginId = FatApp::getPostedData('pluginId', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        if (0 >= $pluginId) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $data = Plugin::getAttributesById($pluginId, array('plugin_id', 'plugin_active', 'plugin_type'));

        if ($data == false) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        if (false == Plugin::updateStatus($data['plugin_type'], $status, $pluginId, $error)) {
            FatUtility::dieJsonError($error);
        }

        if (Plugin::ACTIVE == $status) {
            $groupType = Plugin::getGroupType($data['plugin_type']);
            $eiherPluginTypes = array_values(array_diff($groupType, [$data['plugin_type']]));
            
            foreach ($eiherPluginTypes as $pluginType) {
                if (false == Plugin::updateStatus($pluginType, Plugin::INACTIVE, null, $error)) {
                    FatUtility::dieJsonError($error);
                }
            }
        }
        
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($pluginType, $pluginId = 0)
    {
        $pluginId = FatUtility::int($pluginId);

        $frm = new Form('frmPlugin');
        $frm->addHiddenField('', 'plugin_id', $pluginId);
        $frm->addHiddenField('', 'plugin_type', $pluginType);
        $frm->addRequiredField(Labels::getLabel('LBL_Plugin_Identifier', $this->adminLangId), 'plugin_identifier');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'plugin_active', $activeInactiveArr, '', array(), '');
        
        if (in_array($pluginType, Plugin::HAVING_KINGPIN)) {
            $frm->addCheckBox(Labels::getLabel('LBL_MARK_AS_DEFAULT', $this->adminLangId), 'CONF_DEFAULT_PLUGIN_' . $pluginType, $pluginId, array(), false, 0);
        }

        if (in_array($pluginType, Plugin::HAVING_SEPARATE_ICON)) {
            $fld = $frm->addButton(
                'Icon',
                'plugin_icon',
                Labels::getLabel('LBL_Upload_File', $this->adminLangId),
                array('class'=>'uploadFile-Js','id'=>'plugin_icon','data-plugin_id' => $pluginId)
            );
            $fld->htmlAfterField = '<span id="plugin_icon"></span>';
            if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_PLUGIN_LOGO, $pluginId)) {
                $uploadedTime = AttachedFile::setTimeParam($attachment['afile_updated_at']);
                $fld->htmlAfterField .= '<div class="uploaded--image">
                <img src="'.UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'plugin', array($pluginId), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').'"></div>';
            }
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($pluginId = 0, $lang_id = 0)
    {
        $frm = new Form('frmPluginLang');
        $frm->addHiddenField('', 'plugin_id', $pluginId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Plugin_Name', $this->adminLangId), 'plugin_name');
        $frm->addHtmlEditor(Labels::getLabel('LBL_EXTRA_INFO', $this->adminLangId), 'plugin_description');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditPlugins();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $pluginType = FatApp::getPostedData('plugin_type', FatUtility::VAR_INT, 0);
        $pluginIdsArr = FatUtility::int(FatApp::getPostedData('plugin_ids'));
        if (empty($pluginIdsArr) || -1 == $status || 1 > $pluginType) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
        }
        $error = '';
        foreach ($pluginIdsArr as $pluginId) {
            if (1 > $pluginId) {
                continue;
            }
            Plugin::updateStatus($pluginType, $status, $pluginId, $error);
        }
        $msg = !empty($error) ? $error : $this->str_update_record;
        $this->set('msg', $msg);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeBulkStatusByType()
    {
        $this->objPrivilege->canEditPlugins();
        $pluginGroupType = FatApp::getPostedData('plugin_type', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);

        $pluginIdsArr = FatUtility::int(FatApp::getPostedData('plugin_ids'));
        if (empty($pluginIdsArr) || -1 == $status || 1 > $pluginGroupType) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
        }

        foreach ($pluginIdsArr as $pluginId) {
            if (1 > $pluginId) {
                continue;
            }
            Plugin::updateStatus($pluginGroupType, $status, $pluginId);
        }

        if (Plugin::ACTIVE == $status) {
            $groupType = Plugin::getGroupType($pluginGroupType);
            $eiherPluginTypes = array_values(array_diff($groupType, [$pluginGroupType]));
            
            foreach ($eiherPluginTypes as $pluginType) {
                if (false == Plugin::updateStatus($pluginType, Plugin::INACTIVE, null, $error)) {
                    FatUtility::dieJsonError($error);
                }
            }
        }
        
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }
}
