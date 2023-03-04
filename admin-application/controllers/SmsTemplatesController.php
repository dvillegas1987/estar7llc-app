<?php

class SmsTemplatesController extends AdminBaseController
{
    private $stplCode;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewSmsTemplate();
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        if (false === SmsArchive::canSendSms()) {
            $message = Labels::getLabel("MSG_NO_SMS_PLUGIN_CONFIGURED", $this->adminLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
    }

    public function index()
    {
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->set("canEdit", $this->objPrivilege->canEditSmsTemplate($this->admin_id, true));
        $this->_template->addJs('js/jscolor.js');
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmStplsSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getTemplateForm($stplCode = '', $lang_id = 0)
    {
        $this->objPrivilege->canViewSmsTemplate();
        $frm = new Form('frmEtplLang');
        $frm->addHiddenField('', 'stpl_code', $stplCode);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $this->adminLangId), 'stpl_name');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Body', $this->adminLangId), 'stpl_body');
        $fld->requirements()->setRequired(true);
        $frm->addHtml(Labels::getLabel('LBL_Replacement_Caption', $this->adminLangId), 'replacement_caption', '<h3>' . Labels::getLabel('LBL_Replacement_Vars', $this->adminLangId) . '</h3>');
        $frm->addHtml(Labels::getLabel('LBL_Replacement_Vars', $this->adminLangId), 'stpl_replacements', '');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_discard", Labels::getLabel('LBL_DISCARD', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || 1 > $data['page']) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = SmsTemplate::getSearchObject($this->adminLangId);
        $srch->addOrder(SmsTemplate::DB_TBL_PREFIX . 'code', 'ASC');
        $srch->addGroupBy(SmsTemplate::DB_TBL_PREFIX . 'code');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('stpl_code', 'like', '%' . $post['keyword'] . '%');
            $cond->attachCondition('stpl_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        }
        $rs = $srch->getResultSet();
        
        if (!$rs) {
            Message::addErrorMessage($srch->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('langId', $this->adminLangId);
        $this->set("canEdit", $this->objPrivilege->canEditSmsTemplate($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditSmsTemplate();
        $data = FatApp::getPostedData();
        $lang_id = $data['lang_id'];
        $frm = $this->getTemplateForm($data['stpl_code'], $lang_id);
        $post = $frm->getFormDataFromArray($data);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $stplCode = $post['stpl_code'];

        $data = [
            'stpl_lang_id' => $lang_id,
            'stpl_code' => $stplCode,
            'stpl_name' => $post['stpl_name'],
            'stpl_body' => $post['stpl_body'],
        ];

        $stplCode = $data['stpl_code'];
        $stplObj = new SmsTemplate($stplCode);

        if (!$stplObj->addUpdateData($data)) {
            Message::addErrorMessage($stplObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(SmsTemplate::DB_TBL);
            if (false === $updateLangDataobj->updateTranslatedData($stplCode)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function editTemplate($stplCode, $lang_id = 0, $autoFillLangData = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        
        if (empty($stplCode) || 1 > $lang_id) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        $tempFrm = $this->getTemplateForm($stplCode, $lang_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(SmsTemplate::DB_TBL);
            $translatedData = $updateLangDataobj->getTranslatedData($stplCode, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $tempData = current($translatedData);
        } else {
            $stplObj = new SmsTemplate($stplCode);
            $tempData = $stplObj->getTpl($stplCode, $lang_id);
        }

        if ($tempData) {
            $tempFrm->fill($tempData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('stplCode', $stplCode);
        $this->set('lang_id', $lang_id);
        $this->set('tempFrm', $tempFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    private function validateStatusRequest()
    {
        $this->objPrivilege->canEditSmsTemplate();
        $this->stplCode = FatApp::getPostedData('stplCode', FatUtility::VAR_STRING, '');
        if (empty($this->stplCode)) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_INVALID_REQUEST", $this->adminLangId));
        }
    }
    public function makeActive()
    {
        $this->validateStatusRequest();
        $obj = new SmsTemplate($this->stplCode);
        if (false == $obj->makeActive()) {
            FatUtility::dieJsonError($obj->getError());
        }
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function makeInActive()
    {
        $this->validateStatusRequest();
        $obj = new SmsTemplate($this->stplCode);
        if (false == $obj->makeInActive()) {
            FatUtility::dieJsonError($obj->getError());
        }
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditSmsTemplate();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $stplCodesArr = FatApp::getPostedData('stpl_codes');
        if (empty($stplCodesArr) || 0 > $status) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        foreach ($stplCodesArr as $stplCode) {
            $obj = new SmsTemplate($stplCode);
            switch ($status) {
                case applicationConstants::ACTIVE:
                    $obj->makeActive();
                    break;

                case applicationConstants::INACTIVE:
                    $obj->makeInActive();
                    break;
            }
        }
        FatUtility::dieJsonSuccess($this->str_update_record);
    }
}
