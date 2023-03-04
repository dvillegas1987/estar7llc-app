<?php
class ShippingZoneRatesController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewShippingManagement();
    }
    
    public function form($zoneId, $rateId = 0)
    {
        $this->objPrivilege->canEditShippingManagement();
        $rateId = FatUtility::int($rateId);
        $data = array();
        $frm = $this->getForm($zoneId, $rateId);
        if (0 < $rateId) {
            $data = ShippingRate::getAttributesById($rateId);
            if (empty($data)) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $data['is_condition'] = 0;
            if ($data['shiprate_condition_type'] > 0) {
                $data['is_condition'] = 1;
            }
            $frm->fill($data);
        }
        $this->set('languages', Language::getAllNames());
        $this->set('zoneId', $zoneId);
        $this->set('rateId', $rateId);
        $this->set('frm', $frm);
        $this->set('rateData', $data);
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditShippingManagement();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rateId = $post['shiprate_id'];
        $isCondition = $post['is_condition'];
        if ($isCondition < 1) {
            $post['shiprate_condition_type'] = 0;
            $post['shiprate_min_val'] = 0;
            $post['shiprate_max_val'] = 0;
        }
        unset($post['shiprate_id']);

        $srObj = new ShippingRate($rateId);
        $srObj->assignValues($post);

        if (!$srObj->save()) {
            Message::addErrorMessage($srObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $rateId = $srObj->getMainTableRecordId();
        $newTabLangId = 0;
        if ($rateId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = ShippingRate::getAttributesByLangId($langId, $rateId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $shipProfileId = ShippingProfileZone::getAttributesById($post['shiprate_shipprozone_id'], 'shipprozone_shipprofile_id');
        ShippingProfile::setDefaultRates($post['shiprate_shipprozone_id'], $shipProfileId);

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->set('zoneId', $post['shiprate_shipprozone_id']);
        $this->set('rateId', $rateId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($zoneId = 0, $rateId = 0, $langId = 0)
    {
        $this->objPrivilege->canEditShippingManagement();
        $zoneId = FatUtility::int($zoneId);
        $rateId = FatUtility::int($rateId);
        $langId = FatUtility::int($langId);

        if ($rateId == 0 || $langId == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($zoneId, $rateId, $langId);
        $langData = ShippingRate::getAttributesByLangId($langId, $rateId);
        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('zoneId', $zoneId);
        $this->set('rateId', $rateId);
        $this->set('langId', $langId);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditShippingManagement();
        $post = FatApp::getPostedData();
        $zoneId = $post['zone_id'];
        $rateId = $post['rate_id'];
        $langId = $post['lang_id'];

        if ($rateId == 0 || $langId == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($zoneId, $rateId, $langId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $data = array(
            'shipratelang_lang_id' => $langId,
            'shipratelang_shiprate_id' => $rateId,
            'shiprate_name' => $post['shiprate_name']
        );
        $srObj = new ShippingRate($rateId);
        if (!$srObj->updateLangData($langId, $data)) {
            Message::addErrorMessage($srObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $key => $langName) {
            if (!$row = ShippingRate::getAttributesByLangId($key, $rateId)) {
                $newTabLangId = $key;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('zoneId', $zoneId);
        $this->set('rateId', $rateId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function deleteRate($rateId)
    {
        $this->objPrivilege->canEditShippingManagement();

        $srch = ShippingRate::getSearchObject(0);
        $srch->joinTable(
            ShippingProfileZone::DB_TBL,
            'LEFT OUTER JOIN',
            'tspz.' . ShippingProfileZone::DB_TBL_PREFIX . 'id = srate.' . ShippingRate::DB_TBL_PREFIX . 'shipprozone_id',
            'tspz'
        );
        $srch->joinTable(
            ShippingRate::DB_TBL,
            'LEFT OUTER JOIN',
            'tsr.' . ShippingRate::DB_TBL_PREFIX . 'shipprozone_id = tspz.' . ShippingProfileZone::DB_TBL_PREFIX . 'id',
            'tsr'
        );
        $srch->addMultipleFields(['tsr.*']);
        $srch->addCondition('srate.shiprate_id', '=', $rateId);
        $rs = $srch->getResultSet();
        $rates = FatApp::getDb()->fetchAll($rs);
        
        if (is_array($rates) && !empty($rates)) {
            $canDelete = false;
            $withoutCondtionCount = 0;
            $conditional = false;
            foreach ($rates as $rate) {
                if ($rateId == $rate['shiprate_id'] && 0 != $rate['shiprate_condition_type']) {
                    $conditional = true;
                    break;
                }

                if (0 == $rate['shiprate_condition_type']) {
                    $withoutCondtionCount++;
                }
            }

            if (0 == $withoutCondtionCount || 1 < $withoutCondtionCount || true === $conditional) {
                $canDelete = true;
            }

            if (false === $canDelete) {
                $msg = Labels::getLabel('MSG_PLEASE_MAINTAIN_ATLEASE_ONE_SHIPPING_RATE_WITHOUT_CONDITION', $this->adminLangId);
                Message::addErrorMessage($msg);
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $sObj = new ShippingRate($rateId);
        if (!$sObj->deleteRecord(true)) {
            Message::addErrorMessage($sObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Rate_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getForm($zoneId = 0, $rateId = 0)
    {
        $conditionTypes = ShippingRate::getConditionTypes($this->adminLangId);
        $zoneId = FatUtility::int($zoneId);
        $rateId = FatUtility::int($rateId);
        $frm = new Form('frmShippingRates');
        $frm->addHiddenField('', 'shiprate_shipprozone_id', $zoneId);
        $frm->addHiddenField('', 'shiprate_id', $rateId);
        $cndFld = $frm->addHiddenField('', 'is_condition', 0);
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Rate_Name', $this->adminLangId), 'shiprate_identifier');
        
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Cost', $this->adminLangId), 'shiprate_cost');
        
        $fld = $frm->addRadioButtons('', 'shiprate_condition_type', $conditionTypes, '', array('class' => 'list-inline'));        
        
        $fldCndTypeUnReq = new FormFieldRequirement('shiprate_condition_type', Labels::getLabel('LBL_Condition_type', $this->adminLangId));
        $fldCndTypeUnReq->setRequired(false);

        $fldCndTypeReq = new FormFieldRequirement('shiprate_condition_type', Labels::getLabel('LBL_Condition_type', $this->adminLangId));
        $fldCndTypeReq->setRequired(true);
        
        $frm->addFloatField(Labels::getLabel('LBL_Minimum', $this->adminLangId), 'shiprate_min_val');
        
        $fldMinUnReq = new FormFieldRequirement('shiprate_min_val', Labels::getLabel('LBL_Minimum', $this->adminLangId));
        $fldMinUnReq->setRequired(false);

        $fldMinReq = new FormFieldRequirement('shiprate_min_val', Labels::getLabel('LBL_Minimum', $this->adminLangId));
        $fldMinReq->setRequired(true);
        $fldMinReq->setFloatPositive();
        $fldMinReq->setRange('0.001', '99999999');
        
        $frm->addFloatField(Labels::getLabel('LBL_Maximum', $this->adminLangId), 'shiprate_max_val');
        
        $fldMaxUnReq = new FormFieldRequirement('shiprate_max_val', Labels::getLabel('LBL_Maximum', $this->adminLangId));
        $fldMaxUnReq->setRequired(false);

        $fldMaxReq = new FormFieldRequirement('shiprate_max_val', Labels::getLabel('LBL_Maximum', $this->adminLangId));
        $fldMaxReq->setRequired(true);
        $fldMaxReq->setFloatPositive();
        $fldMaxReq->setRange('0.001', '99999999');
        $fldMaxReq->setCompareWith('shiprate_min_val', 'gt', '');
        
        $cndFld->requirements()->addOnChangerequirementUpdate(1, 'eq', 'shiprate_min_val', $fldMinReq);
        $cndFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'shiprate_min_val', $fldMinUnReq);
        
        $cndFld->requirements()->addOnChangerequirementUpdate(1, 'eq', 'shiprate_max_val', $fldMaxReq);
        $cndFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'shiprate_max_val', $fldMaxUnReq);
        
        $cndFld->requirements()->addOnChangerequirementUpdate(1, 'eq', 'shiprate_condition_type', $fldCndTypeReq);
        $cndFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'shiprate_condition_type', $fldCndTypeUnReq);
        
        $fld = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        /*
        $btn = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->adminLangId));
        $fld->attachField($btn);
         * 
         */
        return $frm;
    }
    
    private function getLangForm($zoneId = 0, $rateId = 0, $langId = 0)
    {
        $frm = new Form('frmRateLang');
        $frm->addHiddenField('', 'zone_id', $zoneId);
        $frm->addHiddenField('', 'rate_id', $rateId);
        $frm->addHiddenField('', 'lang_id', $langId);
        $frm->addRequiredField(Labels::getLabel('LBL_Rate_Name', $this->adminLangId), 'shiprate_name');
        $fld = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        /*
        $btn = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->adminLangId));
        $fld->attachField($btn);
         * 
         */
        return $frm;
    }
}
