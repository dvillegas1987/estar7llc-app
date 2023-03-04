<?php

class TaxController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewTax($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditTax($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewTax();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmTaxSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewTax();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = Tax::getSearchObject($this->adminLangId, false);
        $srch->addCondition('taxcat_deleted', '=', 0);

        $activatedTaxServiceId = Tax::getActivatedServiceId();
        $srch->addCondition('taxcat_plugin_id', '=', $activatedTaxServiceId);

        $srch->addFld('t.*');

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('t.taxcat_identifier', 'like', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('t_l.taxcat_name', 'like', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('t.taxcat_code', 'like', '%' . $post['keyword'] . '%', 'OR');
        }

        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(array("t_l.taxcat_name"));
        $srch->addOrder('taxcat_active', 'DESC');
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
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set('activatedTaxServiceId', $activatedTaxServiceId);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditTax();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $activatedTaxServiceId = Tax::getActivatedServiceId();
        /*if (!$activatedTaxServiceId) {
            if (Tax::validatePostOptions($this->adminLangId) == false) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Tax_Option_Rate', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }*/

        $taxcat_id = $post['taxcat_id'];
        unset($post['taxcat_id']);

        $record = new Tax($taxcat_id);
        if (!$record->addUpdateData($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($taxcat_id == 0) {
            $taxcat_id = $record->getMainTableRecordId();
        }

        /*if (!$activatedTaxServiceId) {
            $taxvalOptions = array();
            $taxStructure = new TaxStructure(FatApp::getConfig('CONF_TAX_STRUCTURE', FatUtility::VAR_FLOAT, 0));
            $options = $taxStructure->getOptions($this->adminLangId);
            foreach ($options as $optionVal) {
                $taxvalOptions[$optionVal['taxstro_id']] = $post[$optionVal['taxstro_id']];
            }
        }*/

        $newTabLangId = 0;
        if ($taxcat_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Tax::getAttributesByLangId($langId, $taxcat_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $taxcat_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('taxcatId', $taxcat_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditTax();
        $post = FatApp::getPostedData();

        $taxcat_id = $post['taxcat_id'];
        $lang_id = $post['lang_id'];

        if ($taxcat_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($taxcat_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['taxcat_id']);
        unset($post['lang_id']);

        $data = array(
            'taxcatlang_taxcat_id' => $taxcat_id,
            'taxcatlang_lang_id' => $lang_id,
            'taxcat_name' => $post['taxcat_name'],
        );

        $taxObj = new Tax($taxcat_id);
        if (!$taxObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($taxObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Tax::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($taxcat_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Tax::getAttributesByLangId($langId, $taxcat_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('taxcatId', $taxcat_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($taxcat_id = 0)
    {
        $this->objPrivilege->canEditTax();

        $taxcat_id = FatUtility::int($taxcat_id);
        $frm = $this->getForm($taxcat_id);

        if (0 < $taxcat_id) {
            $taxObj = new Tax($taxcat_id);
            $srch = $taxObj->getSearchObject($this->adminLangId, false);

            $srch->addCondition('taxcat_id', '=', $taxcat_id);
            $srch->addMultipleFields(array('t.*', 't_l.taxcat_name'));

            $rs = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($rs);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('taxcat_id', $taxcat_id);
        $this->set('frmTax', $frm);
        $this->_template->render(false, false);
    }

    public function langForm($taxcat_id = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canEditTax();

        $taxcat_id = FatUtility::int($taxcat_id);
        $lang_id = FatUtility::int($lang_id);

        if ($taxcat_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $taxLangFrm = $this->getLangForm($taxcat_id, $lang_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Tax::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($taxcat_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Tax::getAttributesByLangId($lang_id, $taxcat_id);
        }

        if ($langData) {
            $taxLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('taxcat_id', $taxcat_id);
        $this->set('taxcat_lang_id', $lang_id);
        $this->set('taxLangFrm', $taxLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditTax();

        $taxCatId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if (1 > $taxCatId) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $this->markAsDeleted($taxCatId);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditTax();
        $taxcatIdsArr = FatUtility::int(FatApp::getPostedData('taxcat_ids'));

        if (empty($taxcatIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($taxcatIdsArr as $taxcat_id) {
            if (1 > $taxcat_id) {
                continue;
            }
            $this->markAsDeleted($taxcat_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($taxcat_id)
    {
        $taxcat_id = FatUtility::int($taxcat_id);
        if (1 > $taxcat_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $taxtObj = new Tax($taxcat_id);
        if (!$taxtObj->canRecordMarkDelete($taxcat_id)) {
            $msg = Labels::getLabel('MSG_PLEASE_UNLINK_ALL_THE_PRODUCTS_FIRST', $this->adminLangId);
            Message::addErrorMessage($msg);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $taxtObj->assignValues(array(Tax::tblFld('deleted') => 1));
        if (!$taxtObj->save()) {
            Message::addErrorMessage($taxtObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$this->deleteGroupData($taxcat_id)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_delete_group_old_data', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditTax();
        $taxcatId = FatApp::getPostedData('taxcatId', FatUtility::VAR_INT, 0);
        if (0 >= $taxcatId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Tax::getAttributesById($taxcatId, array('taxcat_id', 'taxcat_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['taxcat_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateTaxStatus($taxcatId, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditTax();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $taxcatIdsArr = FatUtility::int(FatApp::getPostedData('taxcat_ids'));
        if (empty($taxcatIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($taxcatIdsArr as $taxcatId) {
            if (1 > $taxcatId) {
                continue;
            }

            $this->updateTaxStatus($taxcatId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateTaxStatus($taxcatId, $status)
    {
        $status = FatUtility::int($status);
        $taxcatId = FatUtility::int($taxcatId);
        if (1 > $taxcatId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new Tax($taxcatId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getLangForm($taxcat_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmTaxLang');
        $frm->addHiddenField('', 'taxcat_id', $taxcat_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Tax_Category_Name', $this->adminLangId), 'taxcat_name');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getForm($taxcat_id = 0)
    {
        $this->objPrivilege->canEditTax();
        $taxcat_id = FatUtility::int($taxcat_id);

        $frm = new Form('frmTax');
        $frm->addHiddenField('', 'taxcat_id', $taxcat_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Tax_Category_Identifier', $this->adminLangId), 'taxcat_identifier');

        $activatedTaxServiceId = Tax::getActivatedServiceId();

        if ($activatedTaxServiceId) {
            $frm->addHiddenField('', 'taxcat_plugin_id', $activatedTaxServiceId)->requirements()->setRequired();
        }

        if ($activatedTaxServiceId || FatApp::getConfig('CONF_TAX_CATEGORIES_CODE', FatUtility::VAR_INT, 1)) {
            $frm->addRequiredField(Labels::getLabel('LBL_Tax_Code', $this->adminLangId), 'taxcat_code');
        }

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'taxcat_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function autoCompleteTaxCategories()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewTax();
        $srch = Tax::getSearchObject($this->adminLangId, true);
        $srch->addCondition('taxcat_deleted', '=', 0);
        $activatedTaxServiceId = Tax::getActivatedServiceId();

        $srch->addFld('taxcat_id');
        if ($activatedTaxServiceId) {
            $srch->addFld('concat(IFNULL(taxcat_name,taxcat_identifier), " (",taxcat_code,")")as taxcat_name');
        } else {
            $srch->addFld('IFNULL(taxcat_name,taxcat_identifier)as taxcat_name');
        }
        $srch->addCondition('taxcat_plugin_id', '=', $activatedTaxServiceId);

        if (!empty($post['keyword'])) {
            $srch->addCondition('taxcat_name', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('taxcat_identifier', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('taxcat_code', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $taxCategories = $db->fetchAll($rs, 'taxcat_id');
        $json = array();
        $defaultStringLength = applicationConstants::DEFAULT_STRING_LENGTH;
        foreach ($taxCategories as $key => $taxCategory) {
            $taxCatName = strip_tags(html_entity_decode($taxCategory['taxcat_name'], ENT_QUOTES, 'UTF-8'));
            $taxCatName1 = substr($taxCatName, 0, $defaultStringLength);
            if ($defaultStringLength < strlen($taxCatName)) {
                $taxCatName1 .= '...';
            }
            $json[] = array(
                'id' => $key,
                'name' => $taxCatName1
            );
        }
        die(json_encode($json));
    }

    public function ruleForm($taxCatId = 0)
    {
        $this->objPrivilege->canEditTax();
        $taxCatId = FatUtility::int($taxCatId);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        $frm = TaxRule::getRuleForm($this->adminLangId);
        $data = [];
        $rulesData = [];
        $ruleLocations = [];

        if ($taxCatId == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $data = Tax::getAttributesById($taxCatId);
        $frm->fill($data);

        $taxObj = new TaxRule();
        $rulesData = $taxObj->getRules($taxCatId, $this->adminLangId);
        if (!empty($rulesData)) {
            $ruleLocations = $taxObj->getLocations($taxCatId);
        }

        unset($languages[$siteDefaultLangId]);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);

        $this->set('taxCategory', $data['taxcat_identifier']);
        $this->set('frm', $frm);
        $this->set('rules', $rulesData);
        $this->set('ruleLocations', $ruleLocations);
        $this->_template->render();
    }

    public function addRuleForm($index = 0)
    {
        $this->objPrivilege->canEditTax();
        $index = FatUtility::int($index);
        $frm = TaxRule::getRuleForm($this->adminLangId);
        $languages = Language::getAllNames();
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        unset($languages[$siteDefaultLangId]);
        $this->set('otherLanguages', $languages);
        $this->set('frm', $frm);
        $this->set('index', $index);
        $this->_template->render(false, false);
    }

    public function setupTaxRule()
    {
        $this->objPrivilege->canEditTax();
        $data = FatApp::getPostedData();
        if (empty($data)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (isset($data['rules']) && !empty($data['rules'])) {
            $rulesData = $data['rules'];
            if (!$this->checkForValidStateTypes($rulesData)) {
                Message::addErrorMessage(Labels::getLabel('LBL_All_States_And_Include_can\'t_be_used_together_or_All_state_can\'t_be_used_more_then_once_in_a_group_for_same_country', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            
            if (!$this->checkForValidCombinationStates($rulesData)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Combination_of_Country,_Type,_State', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }            
            
        }

        $taxCatId = $data['taxcat_id'];
        unset($data['taxcat_id']);

        /* [ DELETE OLD DATA FROM GROUP */
        if (!$this->deleteGroupData($taxCatId)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_delete_old_tax_settings', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        if (isset($data['rules']) && !empty($data['rules'])) {
            $rules = $data['rules'];
            foreach ($rules as $rule) {
                $combinedTaxes = (isset($rule['combinedTaxDetails'])) ? $rule['combinedTaxDetails'] : [];

                if (!empty($combinedTaxes)) {
                    $totalCombinedTax = 0;
                    array_walk($combinedTaxes, function ($value) use (&$totalCombinedTax) {
                        $totalCombinedTax += $value['taxruledet_rate'];
                    });
                    if ($totalCombinedTax != $rule['taxrule_rate']) {
                        Message::addErrorMessage(Labels::getLabel('LBL_INVALID_COMBINED_TAX_COMBINATION', $this->adminLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }

                $ruleId = 0;
                $isCombined = FatUtility::int($rule['taxrule_is_combined']);
                $states = (isset($rule['states'])) ? $rule['states'] : [];
                unset($rule['taxrule_id']);
                $taxRuleObj = new TaxRule($ruleId);
                $rule['taxrule_taxcat_id'] = $taxCatId;
                $rule['taxrule_name'] = $rule['taxrule_name'][$this->adminLangId];
                $taxRuleObj->assignValues($rule);
                if (!$taxRuleObj->save()) {
                    Message::addErrorMessage($taxRuleObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }

                $ruleId = $taxRuleObj->getMainTableRecordId();
                /* [ update location data */
                if (!empty($states)) {
                    if (!$this->updateLocationData($states, $taxCatId, $ruleId, $rule)) {
                        Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_Update_Location_Data', $this->adminLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
                /* ] */

                /* [ UPDATE COMBINED TAX DETAILS */
                if (!empty($combinedTaxes)) {
                    if (!$this->updateCombinedData($combinedTaxes, $ruleId, $this->adminLangId)) {
                        Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_Update_Combined_Tax_Data', $this->adminLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
                /* ] */
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->set('taxCatId', $taxCatId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function checkForValidStateTypes($rulesData = [])
    {
        if (!empty($rulesData)) {
            $types = [];
            $countries = [];
            $currentkey = 0;
            foreach ($rulesData as $key => $rule) {
                if ($currentkey = array_search($rule['country_id'], $countries) !== false  && (($rule['type'] == TaxRule::TYPE_ALL_STATES && $types[$currentkey] == TaxRule::TYPE_ALL_STATES) || ($rule['type'] == TaxRule::TYPE_ALL_STATES && $types[$currentkey] == TaxRule::TYPE_INCLUDE_STATES) || ($rule['type'] == TaxRule::TYPE_INCLUDE_STATES && $types[$currentkey] == TaxRule::TYPE_ALL_STATES))) {
                    return false;
                } else {
                    if ($rule['type'] != TaxRule::TYPE_EXCLUDE_STATES) {
                        $countries[] = $rule['country_id'];
                        $types[] = $rule['type'];
                    } else {
                        unset($rulesData[$key]);
                    }
                }
            }
        }
        return true;
    }
    
    private function checkForValidCombinationStates($rulesData = [])
    {   
        $combinations = [];
        if (!empty($rulesData)) {
                foreach ($rulesData as $key => $rule) {
                    $countryId = $rule['country_id'];
                    $type = $rule['type'];
                    foreach ($rule['states'] as $state) {
                        $combinationKey = [$countryId ."-".$type."-".$state];
                        if(in_array($combinationKey ,$combinations)){
                            return false;
                        }
                        $combinations[] = $combinationKey;
                    }
            }
        }
        return true;
    }

    private function deleteGroupData($taxCatId)
    {
        $taxRuleObj = new TaxRule();
        if (!$taxRuleObj->deleteRules($taxCatId)) {
            Message::addErrorMessage($taxRuleObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $locObj = new TaxRuleLocation();
        if (!$locObj->deleteLocations($taxCatId)) {
            return false;
        }
        return true;
    }

    private function updateLocationData($states, $taxCatId, $ruleId, $rule)
    {
        $locObj = new TaxRuleLocation();
        foreach ($states as $state) {
            $isUnique = 1;
            if ($rule['type'] == TaxRule::TYPE_EXCLUDE_STATES) {
                $isUnique = null;
            }

            $data = array(
                'taxruleloc_taxcat_id' => $taxCatId,
                'taxruleloc_taxrule_id' => $ruleId,
                'taxruleloc_country_id' => $rule['country_id'],
                'taxruleloc_state_id' => $state,
                'taxruleloc_type' => $rule['type'],
                'taxruleloc_unique' => $isUnique
            );
            if (!$locObj->updateLocations($data)) {
                return false;
            }
        }
        return true;
    }

    private function updateCombinedData($combinedTaxes, $ruleId)
    {
        if (!empty($combinedTaxes)) {
            foreach ($combinedTaxes as $combinedTax) {
                $comTaxId = 0;
                $combinedTax['taxruledet_taxrule_id'] = $ruleId;
                unset($combinedTax['taxruledet_id']);
                $taxRuleComObj = new TaxRuleCombined($comTaxId);
                $taxRuleComObj->assignValues($combinedTax);
                if (!$taxRuleComObj->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getCombinedTaxes($taxStrId, $ruleId = 0)
    {
        $taxStrId = FatUtility::int($taxStrId);
        $ruleId = FatUtility::int($ruleId);

        $taxStructure = new TaxStructure($taxStrId);
        $combTaxes =  $taxStructure->getCombinedTaxesByParent($this->adminLangId, $ruleId);

        $this->set('taxStrId', $taxStrId);
        $this->set('combTaxes', $combTaxes);
        $this->_template->render(false, false);
    }
}
