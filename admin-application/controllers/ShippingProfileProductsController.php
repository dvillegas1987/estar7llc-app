<?php
class ShippingProfileProductsController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewShippingManagement();
    }
    
    public function index($profileId)
    {
        $frm = $this->getForm($profileId);
        $this->set("frm", $frm);
        $this->_template->render(false, false);
    }
    
    public function search($profileId)
    {
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
      
        $srch = ShippingProfileProduct::getSearchObject();
        $srch->addCondition('shippro_shipprofile_id', '=', $profileId);
        $srch->addCondition('shippro_user_id', '=', 0);
        $srch->addCondition(Product::DB_TBL_PREFIX . 'type', '=', Product::PRODUCT_TYPE_PHYSICAL);
        $srch->addOrder('product_name', 'ASC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $profileData = ShippingProfile::getAttributesById($profileId);
        
        $this->set('productsData', $records);
        $this->set('profileData', $profileData);
        $this->set('profile_id', $profileId);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }
    
    public function autoComplete()
    {
        $post = FatApp::getPostedData();
        $shipProfileId = FatApp::getPostedData('shipProfileId', FatUtility::VAR_INT, 0);
        $srch = new ProductSearch($this->adminLangId);        
        $srch->addOrder('product_name');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('product_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        $srch->addCondition(Product::DB_TBL_PREFIX . 'type', '=', Product::PRODUCT_TYPE_PHYSICAL);
        $srch->addCondition(Product::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition(Product::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        
        $srch->addMultipleFields(array('product_id as id', 'product_name','product_identifier'));
        
        if(0 < $shipProfileId ){            
            $srch->joinTable(ShippingProfileProduct::DB_TBL, 'LEFT OUTER JOIN', 'p.product_id = sppro.shippro_product_id and sppro.shippro_user_id = '.applicationConstants::NO, 'sppro');
            $cnd = $srch->addCondition(ShippingProfileProduct::DB_TBL_PREFIX . 'shipprofile_id', '!=', $shipProfileId);
            
        }          
        $srch->addGroupBy('product_id');
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();

        $products = array();
        if ($rs) {
            $products = $db->fetchAll($rs, 'id');
        }
        $json = array();
        foreach ($products as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')),
                'product_identifier' => strip_tags(html_entity_decode($option['product_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditShippingManagement();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false == $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $prodType = Product::getAttributesById($post['shippro_product_id'], 'product_type');
        if (Product::PRODUCT_TYPE_DIGITAL == $prodType) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_DIGITAL_PRODUCTS_ARE_NOT_ALLOWED', $this->adminLangId));
        }

        $data = array(
            'shippro_user_id' => 0,
            'shippro_product_id' => $post['shippro_product_id'],
            'shippro_shipprofile_id' => $post['shippro_shipprofile_id']
        );
        
        $spObj = new ShippingProfileProduct();
        if (!$spObj->addProduct($data)) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function removeProduct($productId)
    {
        $this->objPrivilege->canEditShippingManagement();
        $defaultProfileId = ShippingProfile::getDefaultProfileId(0);
        /* [ REMOVE PRODUCT FROM CURRENT PROFILE AND ADD TO DEFAULT PROFILE */
        $data = array(
            'shippro_shipprofile_id' => $defaultProfileId,
            'shippro_product_id' => $productId
        );
        
        $spObj = new ShippingProfileProduct();
        if (!$spObj->addProduct($data)) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */
        
        $this->set('msg', Labels::getLabel('LBL_Product_Removed_from_current_profile.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getForm($profileId = 0)
    {
        $profileId = FatUtility::int($profileId);
        $frm = new Form('frmProfileProducts');
        $frm->addHiddenField('LBL_Product_Name', 'shippro_shipprofile_id', $profileId)->requirement->setRequired(true);
        $frm->addHiddenField(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'shippro_product_id', '')->requirements()->setRequired(true);
        $fld = $frm->addTextBox('', 'product_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
