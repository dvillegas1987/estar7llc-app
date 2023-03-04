<?php

class AbandonedCartController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewAbandonedCart();
    }
    
    public function index()
    {
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmAbandonedCartSearch');
        $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'user_name');
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Product', $this->adminLangId), 'seller_product');
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $this->adminLangId), 'readonly' => 'readonly' ));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $this->adminLangId), 'readonly' => 'readonly' ));
        $frm->addHiddenField('', 'abandonedcart_user_id');
        $frm->addHiddenField('', 'abandonedcart_selprod_id');
        $frm->addHiddenField('', 'abandonedcart_action');
        $frm->addHiddenField('', 'page', 1);
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
    
    public function search()
    {
        $frmSearch = $this->getSearchForm();
        $postedData = $frmSearch->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $userId = FatApp::getPostedData('abandonedcart_user_id', FatUtility::VAR_INT, 0);
        $selProdId = FatApp::getPostedData('abandonedcart_selprod_id', FatUtility::VAR_INT, 0);
        $action = FatApp::getPostedData('abandonedcart_action', FatUtility::VAR_INT, 0);
        $dateFrom = FatApp::getPostedData('date_from', null, '');
        $dateTo = FatApp::getPostedData('date_to', null, '');
        
        $abandonedCart = new AbandonedCart();
        $records = $abandonedCart->getAbandonedCartList($userId, $selProdId, $action, $dateFrom, $dateTo, $page);
        $this->set("records", $records);
        $this->set('page', $page);
        $this->set('pageSize', $abandonedCart->getPageSize());
        $this->set('recordCount', $abandonedCart->recordCount());
        $this->set('pageCount', $abandonedCart->pages());
        $this->set('postedData', $postedData);
        $totCartRecovered = 0;
        if($action == AbandonedCart::ACTION_PURCHASED){
            $cartRecovered = $abandonedCart->getCartRecoveredTotal($userId, $selProdId, $dateFrom, $dateTo); 
            $totCartRecovered = $cartRecovered['amount'];
        }        
        $this->set('totCartRecovered', $totCartRecovered); 
        $this->set('canEdit', $this->objPrivilege->canEditAbandonedCart(0, true));    
        $this->_template->render(false, false);
    }
    
    public function products()
    {
        $this->_template->render();
    }
    
    public function getProducts()
    {
        $postedData = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $abandonedCart = new AbandonedCart();
        $records = $abandonedCart->getAbandonedCartProducts($page);
        $this->set("records", $records);
        $this->set('page', $page);
        $this->set('pageSize', $abandonedCart->getPageSize());
        $this->set('recordCount', $abandonedCart->recordCount());
        $this->set('pageCount', $abandonedCart->pages());
        $this->set('postedData', $postedData);
        $this->_template->render(false, false);
    }
    
    public function discountNotification()
    {
        $abandonedcartId = FatApp::getPostedData('abandonedcartId', FatUtility::VAR_INT, 0);
        $couponId = FatApp::getPostedData('couponId', FatUtility::VAR_INT, 0);
        if ($abandonedcartId < 1 || $couponId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Email_Not_Sent_Invalid_Parameters', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $abandonedCart = new AbandonedCart($abandonedcartId);
        if (!$abandonedCart->sendDiscountEmail($couponId)) {
            Message::addErrorMessage($abandonedCart->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $abandonedCart->updateDiscountNotification();
        $this->set('msg', Labels::getLabel('MSG_Email_Sent_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function validateProductForNotification($productId)
    {
        $productId = FatUtility::int($productId);
        if ( $productId < 1 ) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $product = AbandonedCart::validateProductForNotification($productId);
        if(empty($product)){
            Message::addErrorMessage(Labels::getLabel('MSG_Product_is_either_deleted/disabled_or_out_of_stock', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->_template->render(false, false, 'json-success.php');
    }
    
}
