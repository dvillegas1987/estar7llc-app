<?php
class ShippingPackagesController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        if (1 > FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
            $msg = Labels::getLabel('LBL_PLEASE_TURN_ON_PRODUCT_DIMENSION_SETTING_FIRST_GENERAL_SETTINGS_>_PRODUCT', $this->adminLangId);
            Message::addErrorMessage($msg);
            FatApp::redirectUser(UrlHelper::generateUrl('configurations'));
        }
        $this->objPrivilege->canViewShippingPackages();
    }
    
    public function index()
    {
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->set('canEdit', $this->objPrivilege->canViewShippingPackages(0, true));
        $this->_template->render();
    }
    
    public function search()
    {
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $post = $searchForm->getFormDataFromArray($data);
        $srch = ShippingPackage::getSearchObject();
        $srch->addOrder('shippack_name', 'ASC');
        if (!empty($post['keyword'])) {
            $srch->addCondition('spack.shippack_name', 'like', '%' . $post['keyword'] . '%');
        }
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
        $this->set('arr_listing', $records);
        $this->set('unitTypeArray', ShippingPackage::getUnitTypes($this->adminLangId));
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('canEdit', $this->objPrivilege->canViewShippingPackages(0, true));
        $this->_template->render(false, false);
    }
    
    public function form($packageId = 0)
    {
        $this->objPrivilege->canEditShippingPackages();
        $packageId = FatUtility::int($packageId);
        $data = array();
        $frm = $this->getForm();
        if (0 < $packageId) {
            $data = ShippingPackage::getAttributesById($packageId);
            if (empty($data)) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditShippingPackages();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $packageName = FatApp::getPostedData('shippack_name', FatUtility::VAR_STRING, '');
        $recordId = FatUtility::int(ShippingPackage::getPackageIdByName($packageName));
        if (0 < $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_THIS_PACKAGE_NAME_ALREDY_IN_USE.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $packageId = $post['shippack_id'];
        unset($post['shippack_id']);

        $spObj = new ShippingPackage($packageId);
        $spObj->assignValues($post);

        if (!$spObj->save()) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
    
    
    private function getForm()
    {
        $unitTypeArray = ShippingPackage::getUnitTypes($this->adminLangId);
        $frm = new Form('frmShippingPackages');
        $frm->addHiddenField('', 'shippack_id');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Package_Name', $this->adminLangId), 'shippack_name');
        $frm->addFloatField(Labels::getLabel('LBL_Length', $this->adminLangId), 'shippack_length');
        $frm->addFloatField(Labels::getLabel('LBL_Width', $this->adminLangId), 'shippack_width');
        $frm->addFloatField(Labels::getLabel('LBL_Height', $this->adminLangId), 'shippack_height');
        
        $frm->addSelectBox(Labels::getLabel('LBL_Unit', $this->adminLangId), 'shippack_units', $unitTypeArray, '', [], Labels::getLabel('LBL_Select', $this->adminLangId));
        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        
        return $frm;
    }
}
