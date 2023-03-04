<?php

class EmailTemplatesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('langForm', 'search', 'setup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewEmailTemplates($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditEmailTemplates($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        $this->set("includeEditor", true);
    }

    public function index()
    {
        $this->objPrivilege->canViewEmailTemplates();
        $frmSearch = $this->getSearchForm();
        $this->_template->addCss('css/cropper.css');
        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js', 'js/jscolor.js'));
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $this->objPrivilege->canViewEmailTemplates();
        $frm = new Form('frmEtplsSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewEmailTemplates();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = EmailTemplates::getSearchObject();
        $srch->addOrder(EmailTemplates::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addGroupBy(EmailTemplates::DB_TBL_PREFIX . 'code');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('etpl_code', 'like', '%' . $post['keyword'] . '%', 'AND');
            $cond->attachCondition('etpl_name', 'like', '%' . $post['keyword'] . '%', 'OR');
            $cond->attachCondition('etpl_subject', 'like', '%' . $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('langId', $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function sendTestMail()
    {
        $to = FatApp::getConfig("CONF_SITE_OWNER_EMAIL");
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 1);
        $tpl = FatApp::getPostedData('etpl_code', FatUtility::VAR_STRING, '');

        if (empty($tpl)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_INVALID_TEMPLATE', $this->adminLangId));
        }

        if (!EmailHandler::sendMailTpl($to, $tpl, $langId)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_MAIL_NOT_SENT', $this->adminLangId));
        }

        $this->set('msg', Labels::getLabel('LBL_Mail_Sent_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function testEmailTemplate($tpl)
    {
        $to = FatApp::getConfig("CONF_SITE_OWNER_EMAIL");
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 1);
        if (!EmailHandler::sendMailTpl($to, $tpl, $langId)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_MAIL_NOT_SENT', $this->adminLangId));
        }

        $this->set('msg', Labels::getLabel('LBL_MAIL_SENT', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditEmailTemplates();
        $data = FatApp::getPostedData();
        $lang_id = $data['lang_id'];
        $frm = $this->getLangForm($data['etpl_code'], $lang_id);
        $post = $frm->getFormDataFromArray($data);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $etplCode = $post['etpl_code'];

        $data = [
            'etpl_lang_id' => $lang_id,
            'etpl_code' => $etplCode,
            'etpl_name' => $post['etpl_name'],
            'etpl_subject' => $post['etpl_subject'],
            'etpl_body' => $post['etpl_body'],
        ];

        $etplCode = $data['etpl_code'];
        $etplObj = new EmailTemplates($etplCode);
        /*
        $record =  $etplObj->getEtpl($etplCode, $lang_id);
        if($record == false){
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError( Message::getHtml() );
        } */

        if (!$etplObj->addUpdateData($data)) {
            Message::addErrorMessage($etplObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(EmailTemplates::DB_TBL);
            if (false === $updateLangDataobj->updateTranslatedData($etplCode)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getLangForm($etplCode = '', $lang_id = 0)
    {
        $this->objPrivilege->canViewEmailTemplates();
        $frm = new Form('frmEtplLang');
        $frm->addHiddenField('', 'etpl_code', $etplCode);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $this->adminLangId), 'etpl_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Subject', $this->adminLangId), 'etpl_subject');
        $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Body', $this->adminLangId), 'etpl_body');
        $fld->requirements()->setRequired(true);
        $frm->addHtml(Labels::getLabel('LBL_Replacement_Caption', $this->adminLangId), 'replacement_caption', '<h3>' . Labels::getLabel('LBL_Replacement_Vars', $this->adminLangId) . '</h3>');
        $frm->addHtml(Labels::getLabel('LBL_Replacement_Vars', $this->adminLangId), 'etpl_replacements', '');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        $fldTestEmail = $frm->addButton("", "test_email", Labels::getLabel('LBL_SEND_TEST_EMAIL', $this->adminLangId));
        $fld_submit->attachField($fldTestEmail);
        return $frm;
    }

    public function langForm($etplCode = '', $lang_id = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canViewEmailTemplates();

        $lang_id = FatUtility::int($lang_id);

        if ($etplCode == '' || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($etplCode, $lang_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(EmailTemplates::DB_TBL);
            $translatedData = $updateLangDataobj->getTranslatedData($etplCode, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $etplObj = new EmailTemplates($etplCode);
            $langData = $etplObj->getEtpl($etplCode, $lang_id);
        }

        if ($langData) {
            $langFrm->fill($langData);
        }
        if (is_array($langData) && array_key_exists('etpl_replacements', $langData) && $langData['etpl_replacements'] == '') {
            $etplData = $etplObj->getEtpl($etplCode);
            $langFrm->getField('etpl_replacements')->value = $etplData['etpl_replacements'];
        }
        $this->set('languages', Language::getAllNames());
        $this->set('etplCode', $etplCode);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditEmailTemplates();
        $etplCode = FatApp::getPostedData('etplCode', FatUtility::VAR_STRING, '');
        if ($etplCode == '') {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $etplObj = new EmailTemplates($etplCode);
        $records = $etplObj->getEtpl($etplCode);

        if ($records == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $status = ($records['etpl_status'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateEmailTplStatus($etplCode, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditEmailTemplates();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $etplCodesArr = FatApp::getPostedData('etpl_codes');
        if (empty($etplCodesArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($etplCodesArr as $etplCode) {
            if (empty($etplCode)) {
                continue;
            }

            $this->updateEmailTplStatus($etplCode, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateEmailTplStatus($etplCode, $status)
    {
        $status = FatUtility::int($status);
        if (empty($etplCode) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $etplObj = new EmailTemplates($etplCode);
        if (!$etplObj->activateEmailTemplate($status, $etplCode)) {
            Message::addErrorMessage($etplObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function settingsForm($lang_id = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canViewEmailTemplates();

        $lang_id = FatUtility::int($lang_id);

        if ($lang_id == 0) {
            $lang_id = $this->adminLangId;
        }

        $settingFrm = $this->getSettingsForm($lang_id);
        $emailLogo = AttachedFile::getAttachment(AttachedFile::FILETYPE_EMAIL_LOGO, 0, 0, $lang_id);
        $this->set('logoImage', $emailLogo);
        $this->set('languages', Language::getAllNames());
        $this->set('lang_id', $lang_id);
        $this->set('settingFrm', $settingFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setupSettings()
    {
        $this->objPrivilege->canEditEmailTemplates();
        $data = FatApp::getPostedData();
        $lang_id = $data['lang_id'];
        $frm = $this->getSettingsForm($lang_id);
        $post = $frm->getFormDataFromArray($data);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $record = new Configurations();
        if (!$record->update($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSettingsForm($lang_id = 0)
    {
        $this->objPrivilege->canViewEmailTemplates();

        $frm = new Form('frmEtplSettingsForm');

        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Header_Background_color', $this->adminLangId), 'CONF_EMAIL_TEMPLATE_COLOR_CODE' . $lang_id, FatApp::getConfig('CONF_EMAIL_TEMPLATE_COLOR_CODE' . $lang_id, FatUtility::VAR_STRING, ''));
        $fld->addFieldTagAttribute('class', 'jscolor');

        $ratioArr = AttachedFile::getRatioTypeArray($this->adminLangId);
        //$frm->addSelectBox(Labels::getLabel('LBL_Logo_Ratio', $this->adminLangId), 'CONF_EMAIL_TEMPLATE_LOGO_RATIO', $ratioArr, AttachedFile::RATIO_TYPE_SQUARE, array(), '');
        $frm->addRadioButtons(Labels::getLabel('LBL_Logo_Ratio', $this->adminLangId), 'CONF_EMAIL_TEMPLATE_LOGO_RATIO', $ratioArr, AttachedFile::RATIO_TYPE_SQUARE);
        $frm->addHiddenField('', 'file_type', AttachedFile::FILETYPE_EMAIL_LOGO);
        $frm->addHiddenField('', 'logo_min_width');
        $frm->addHiddenField('', 'logo_min_height');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->adminLangId), 'email_logo', array('accept' => 'image/*', 'data-frm' => 'frmEtplSettingsForm'));
        $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Footer_Content', $this->adminLangId), 'CONF_EMAIL_TEMPLATE_FOOTER_HTML' . $lang_id, FatApp::getConfig('CONF_EMAIL_TEMPLATE_FOOTER_HTML' . $lang_id, FatUtility::VAR_STRING, ''));
        $fld->requirements()->setRequired(true);


        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function uploadLogo()
    {
        $this->objPrivilege->canEditShops();
        $post = FatApp::getPostedData();
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $aspectRatio = FatApp::getPostedData('ratio_type', FatUtility::VAR_INT, 0);

        if (!$file_type) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_EMAIL_LOGO);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], $file_type, 0, 0, $_FILES['cropped_image']['name'], -1, true, $lang_id, '', 0, $aspectRatio)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('lang_id', $lang_id);
        $this->set('msg', Labels::getLabel('LBL_File_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeEmailLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_EMAIL_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
