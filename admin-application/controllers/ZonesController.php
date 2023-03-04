<?php
class ZonesController extends AdminBaseController
{
    private $canEdit;
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewZones();
        $this->canEdit = $this->objPrivilege->canEditZones(AdminAuthentication::getLoggedAdminId(), true);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        $srch = Zone::getSearchObject(false, $this->adminLangId);
        $srch->addFld('zone.* , z_l.zone_name');
        $srch->addOrder('zone_name', 'ASC');
        if (!empty($post['keyword'])) {
            $condition = $srch->addCondition('zone.zone_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('z_l.zone_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($zoneId)
    {
        $this->objPrivilege->canEditZones();
        $zoneId =  FatUtility::int($zoneId);
        $frm = $this->getForm($zoneId);
        if (0 < $zoneId) {
            $data = Zone::getAttributesById($zoneId);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        $this->set('languages', Language::getAllNames());
        $this->set('zone_id', $zoneId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditZones();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $zoneId = $post['zone_id'];
        unset($post['zone_id']);

        $record = new Zone($zoneId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($zoneId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Zone::getAttributesByLangId($langId, $zoneId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $zoneId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->set('zoneId', $zoneId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($zoneId = 0, $langId = 0 ,$autoFillLangData = 0)
    {
        $zoneId = FatUtility::int($zoneId);
        $langId = FatUtility::int($langId);

        if ($zoneId == 0 || $langId == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($zoneId, $langId);        
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Countries::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($zoneId, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Zone::getAttributesByLangId($langId, $zoneId);
        }     
        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('zoneId', $zoneId);
        $this->set('lang_id', $langId);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditZones();
        $post = FatApp::getPostedData();

        $zoneId = $post['zone_id'];
        $lang_id = $post['lang_id'];

        if ($zoneId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($zoneId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['zone_id']);
        unset($post['lang_id']);

        $data = array(
            'zonelang_lang_id' => $lang_id,
            'zonelang_zone_id' => $zoneId,
            'zone_name'=>$post['zone_name']
        );

        $zoneObj = new Zone($zoneId);

        if (!$zoneObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($zoneObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Zone::getAttributesByLangId($langId, $zoneId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('zoneId', $zoneId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($zoneId = 0)
    {
        $zoneId = FatUtility::int($zoneId);
        $frm = new Form('frmZone');
        $frm->addHiddenField('', 'zone_id', $zoneId);
        $frm->addRequiredField(Labels::getLabel('LBL_Zone_Identifier', $this->adminLangId), 'zone_identifier');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'zone_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($zoneId = 0, $lang_id = 0)
    {
        $frm = new Form('frmZoneLang');
        $frm->addHiddenField('', 'zone_id', $zoneId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');        
        $frm->addRequiredField(Labels::getLabel('LBL_Zone_Name', $this->adminLangId), 'zone_name');
        
        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditZones();
        $zoneId = FatApp::getPostedData('zoneId', FatUtility::VAR_INT, 0);
        if (0 >= $zoneId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Zone::getAttributesById($zoneId, array('zone_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['zone_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateZoneStatus($zoneId, $status);
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditZones();
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $zoneIdsArr = FatUtility::int(FatApp::getPostedData('zone_ids'));
        if (empty($zoneIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        foreach ($zoneIdsArr as $zoneId) {
            if (1 > $zoneId) {
                continue;
            }
            $this->updateZoneStatus($zoneId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateZoneStatus($zoneId, $status)
    {
        $status = FatUtility::int($status);
        $zoneId = FatUtility::int($zoneId);
        if (1 > $zoneId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $zoneObj = new Zone($zoneId);
        if (!$zoneObj->changeStatus($status)) {
            Message::addErrorMessage($zoneObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
