<?php

class TaxStructureController extends AdminBaseController
{
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewTax();
    }

    public function index()
    {
        $this->set("canEdit", $this->objPrivilege->canEditTax($this->admin_id, true));
        $this->_template->render();
    }

    public function search()
    {
        $srch = TaxStructure::getSearchObject($this->adminLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('taxstr_parent', '=', 0);
        $srch->addOrder('taxstr_id', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("listing", $records);

        $this->canEdit = $this->objPrivilege->canEditTax($this->admin_id, true);
        $this->set("canEdit", $this->canEdit);
        $this->_template->render(false, false);
    }

    public function form($taxStrId = 0)
    {
        $this->objPrivilege->canEditTax();
		$taxStrId = FatUtility::int($taxStrId);
		
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
		$languages = Language::getAllNames();
       
        $frm = TaxStructure::getForm($this->adminLangId, $taxStrId);
        $taxStrData = [];
        $combinedTaxes = [];
        if (0 < $taxStrId) {
			$taxStrData = TaxStructure::getAttributesById($taxStrId);
            foreach ($languages as $langId => $data) {
                $taxStructure = new TaxStructure();
                $taxStrLangData = $taxStructure->getAttributesByLangId($langId, $taxStrId);
                if (!empty($taxStrLangData)) {
                    $taxStrData['taxstr_name'][$langId] = $taxStrLangData['taxstr_name'];
                }
                if ($taxStrData['taxstr_is_combined']) {
                    $combinedTaxes = $taxStructure->getCombinedTaxes($taxStrId);
                }
            }
			
            if ($taxStrData === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($taxStrData);
        }
		/* CommonHelper::printArray($combinedTaxes); die; */
		$langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        $this->set('combinedTaxes', $combinedTaxes);
        $this->set('taxStrData', $taxStrData);
        $this->set('otherLangData', $langData);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('languages', Language::getAllNames());
        $this->set('taxStrId', $taxStrId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditTax();

        $frm = TaxStructure::getForm($this->adminLangId);
        $post = FatApp::getPostedData();
		
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $taxStrId = $post['taxstr_id'];
        unset($post['taxstr_id']);
		
        $record = new TaxStructure($taxStrId);
        if (!$record->addUpdateData($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }
	
	public function translatedData()
    {
        $taxstrName = FatApp::getPostedData('taxstrName', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $data['taxstr_name'] = $taxstrName;
        $taxStructure = new TaxStructure();
        $translatedData = $taxStructure->getTranslatedData($data, $toLangId);
        if (!$translatedData) {
            Message::addErrorMessage($taxStructure->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('taxstrName', $translatedData[$toLangId]['taxstr_name']);
        $this->_template->render(false, false, 'json-success.php');
    }

}
