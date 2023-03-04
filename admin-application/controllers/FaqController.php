<?php

class FaqController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord', 'form', 'langForm', 'search', 'setup', 'langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewFaq($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditFaq($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }
    
    public function index($faqcat_id = 0)
    {
        $this->objPrivilege->canViewFaq();
        $faqcat_id = FatUtility::int($faqcat_id);
        if (1 > $faqcat_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatApp::redirectUser(UrlHelper::generateUrl('FaqCategories'));
        }
        
        $srchFrm = $this->getSearchForm();
        $data = array(
        'faqcat_id' => $faqcat_id
        );
        $srchFrm->fill($data);
        
        $faqCatData = FaqCategory::getAttributesById($faqcat_id);

        $faqCatObj = new FaqCategory();
        $categoryStructure = $faqCatObj->getCategoryStructure();

        $this->set("srchFrm", $srchFrm);
        $this->set("faqCatData", $faqCatData);
        $this->set("faqcat_id", $faqcat_id);
        $this->set("categoryStructure", $categoryStructure);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewFaq();
        
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $faqcat_id = $post['faqcat_id'];
        
        $srch = Faq::getSearchObject($this->adminLangId);
        $srch->addCondition('faq_faqcat_id', '=', $faqcat_id);
        if (!empty($post['keyword'])) {
            $condition = $srch->addCondition('f.faq_identifier', 'like', '%' . $post['keyword'] . '%');
            $condition->attachCondition('f_l.faq_title', 'like', '%' . $post['keyword'] . '%', 'OR');
        }
        
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        //$srch->setPageSize($pagesize);
        $srch->addOrder('faq_active', 'DESC');
        $srch->addOrder('faq_display_order', 'asc');
    
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
        $this->_template->render(false, false);
    }
    
    public function form($faqcat_id = 0, $faq_id = 0)
    {
        $this->objPrivilege->canEditFaq();
        
        $faqcat_id = FatUtility::int($faqcat_id);
        $faq_id = FatUtility::int($faq_id);
        
        $faqFrm = $this->getForm();
        $faqFrm->fill(array('faqcat_id' => $faqcat_id, 'faq_id' => $faq_id));

        if (0 < $faq_id) {
            $srch = Faq::getSearchObject($this->adminLangId);
            $srch->addCondition('faq_faqcat_id', '=', $faqcat_id);
            $srch->addCondition('faq_id', '=', $faq_id);
            $rs = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($rs);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $faqFrm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('faqcat_id', $faqcat_id);
        $this->set('faq_id', $faq_id);
        $this->set('faqFrm', $faqFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditFaq();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $faqcat_id = FatUtility::int($post['faqcat_id']);
        $faq_id = FatUtility::int($post['faq_id']);
        unset($post['faqcat_id']);
        unset($post['faq_id']);
        
        $record = new Faq($faq_id);
        
        if ($faq_id == 0) {
            $display_order = $record->getMaxOrder();
            $post['faq_display_order'] = $display_order;
        }
        
        $post['faq_faqcat_id'] = $faqcat_id;
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($faq_id > 0) {
            $faqId = $faq_id;
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Faq::getAttributesByLangId($langId, $faqId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $faqId = $record->getMainTableRecordId();
            $newTabLangId = $this->adminLangId;
        }
        
        $this->set('msg', Labels::getLabel('LBL_Category_Setup_Successful', $this->adminLangId));
        $this->set('faqId', $faqId);
        $this->set('catId', $faqcat_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditFaq();
        $post = FatApp::getPostedData();

        $faqcat_id = $post['faqcat_id'];
        $faq_id = $post['faq_id'];
        $lang_id = $post['lang_id'];

        if ($faqcat_id == 0 || $faq_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['faqcat_id']);
        unset($post['faq_id']);
        unset($post['lang_id']);
        $data = array(
        'faqlang_lang_id' => $lang_id,
        'faqlang_faq_id' => $faq_id,
        'faq_title' => $post['faq_title'],
        'faq_content' => $post['faq_content'],
        );

        $faqObj = new Faq($faq_id);
        if (!$faqObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($faqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Faq::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($faq_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Faq::getAttributesByLangId($langId, $faq_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
                
        $this->set('msg', $this->str_setup_successful);
        $this->set('catId', $faqcat_id);
        $this->set('faqId', $faq_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($faqcat_id = 0, $faq_id = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canViewFaq();
        
        $faqcat_id = FatUtility::int($faqcat_id);
        $faq_id = FatUtility::int($faq_id);
        $lang_id = FatUtility::int($lang_id);
        
        if ($faqcat_id == 0 || $faq_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $faqLangFrm = $this->getLangForm($lang_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Faq::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($faq_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Faq::getAttributesByLangId($lang_id, $faq_id);
        }
        
        $langData['faq_id'] = $faq_id;
        $langData['faqcat_id'] = $faqcat_id;
        $langData['lang_id'] = $lang_id;
        
        if ($langData) {
            $faqLangFrm->fill($langData);
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('faqcat_id', $faqcat_id);
        $this->set('faq_id', $faq_id);
        $this->set('faq_lang_id', $lang_id);
        $this->set('faqLangFrm', $faqLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }
    
    public function updateOrder()
    {
        $this->objPrivilege->canEditFaq();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $faqObj = new Faq();
            if (!$faqObj->updateOrder($post['faqs'])) {
                Message::addErrorMessage($faqObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
        }
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditFaq();
        
        $faq_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($faq_id < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $res = Faq::getAttributesById($faq_id, array('faq_id'));
        if ($res == false) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $faqObj = new Faq($faq_id);
        $faqObj->assignValues(array(Faq::tblFld('deleted') => 1));
        if (!$faqObj->save()) {
            Message::addErrorMessage($faqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addHiddenField('', 'faqcat_id');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
    
    private function getForm()
    {
        $frm = new Form('frmFaq');
        $frm->addHiddenField('', 'faqcat_id', 0);
        $frm->addHiddenField('', 'faq_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_FAQ_Identifier', $this->adminLangId), 'faq_identifier');
                
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'faq_active', $activeInactiveArr, '', array(), '');
        /* $frm->addCheckBox(Labels::getLabel('LBL_Featured',$this->adminLangId), 'faq_featured', 1, array(),false,0); */
                
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));

        return $frm;
    }
    
    private function getLangForm($lang_id = 0)
    {
        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $lang_id = 1 > $lang_id ? $siteLangId : $lang_id;

        $frm = new Form('frmFaqLang');
        $frm->addHiddenField('', 'faqcat_id');
        $frm->addHiddenField('', 'faq_id');

        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->adminLangId), 'faq_title');
        $frm->addTextArea(Labels::getLabel('LBL_Content', $this->adminLangId), 'faq_content');
        
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function autoComplete()
    {
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE');
        $post = FatApp::getPostedData();

        $srch = Faq::getSearchObject($this->adminLangId, true);
        $srch->addMultipleFields(array('faq_id, IFNULL(faq_title, faq_identifier) as faq_title'));
        
        $srch->joinTable(FaqCategory::DB_TBL, 'LEFT OUTER JOIN', 'fc.faqcat_id = f.faq_faqcat_id', 'fc');
        $srch->addCondition('fc.faqcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('fc.faqcat_active', '=', applicationConstants::ACTIVE);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('faq_title', 'LIKE', '%' . $post['keyword'] . '%');
            $cond->attachCondition('faq_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        $collectionId = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $alreadyAdded = Collections::getRecords($collectionId);
        if (!empty($alreadyAdded) && 0 < count($alreadyAdded)) {
            $srch->addCondition('faq_id', 'NOT IN', array_keys($alreadyAdded));
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $posts = $db->fetchAll($rs, 'faq_id');
        $json = array();
        foreach ($posts as $key => $post) {
            $json[] = array(
            'id' => $key,
            'name' => strip_tags(html_entity_decode($post['faq_title'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }
}
