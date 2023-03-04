<?php

class PickupAddressesController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewPickupAddresses();
    }

    public function index()
    {
        $this->set('canEdit', $this->objPrivilege->canEditPickupAddresses($this->admin_id, true));
        $this->_template->render();
    }

    public function search()
    {
        $address = new Address(0, $this->adminLangId);
        $addresses = $address->getData(Address::TYPE_ADMIN_PICKUP, 0);
        $this->set('arr_listing', $addresses);
        $this->set('canEdit', $this->objPrivilege->canEditPickupAddresses($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function form($addressId = 0, $langId = 0)
    {
        $this->objPrivilege->canEditPickupAddresses();
        $stateId = 0;
        $slotData = [];
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $addressId = FatUtility::int($addressId);

        $frm = $this->getForm($addressId, $langId);
        $availability = TimeSlot::DAY_INDIVIDUAL_DAYS;
        if (0 < $addressId) {
            $address = new Address($addressId, $langId);
            $data = $address->getData(Address::TYPE_ADMIN_PICKUP, 0);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $stateId = $data['addr_state_id'];

            $timeSlot = new TimeSlot();
            $timeSlots = $timeSlot->timeSlotsByAddrId($addressId);            
            $timeSlotsRow = current($timeSlots);
            $availability = isset($timeSlotsRow['tslot_availability']) ? $timeSlotsRow['tslot_availability'] : 0;            
            $data['tslot_availability'] = $availability;
            $frm->fill($data);

            if (!empty($timeSlots)) {
                foreach ($timeSlots as $key => $slot) {
                    $slotData['tslot_day'][$slot['tslot_day']] = $slot['tslot_day'];
                    $slotData['tslot_from_time'][$slot['tslot_day']][] = $slot['tslot_from_time'];
                    $slotData['tslot_to_time'][$slot['tslot_day']][] = $slot['tslot_to_time'];
                }
            }
        }
        
        $this->set('availability', $availability);
        $this->set('addressId', $addressId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('langId', $langId);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('slotData', $slotData);
        $this->_template->render(false, false);
    }

    private function getForm($addressId = 0, $langId)
    {
        $addressId = FatUtility::int($addressId);
        $frm = new Form('frmAddress');
        $frm->addHiddenField('', 'addr_id', $addressId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $langId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Label', $langId), 'addr_title');
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $langId), 'addr_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Address_Line1', $langId), 'addr_address1');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Line2', $langId), 'addr_address2');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Country', $langId), 'addr_country_id', $countriesArr, '', array(), Labels::getLabel('LBL_Select', $this->adminLangId))->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $langId), 'addr_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId))->requirement->setRequired(true);
        $frm->addRequiredField(Labels::getLabel('LBL_City', $langId), 'addr_city');

        $zipFld = $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $langId), 'addr_zip');
        /* $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $langId)); */

        $frm->addHiddenField('', 'addr_phone_dcode');
        $phnFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $langId), 'addr_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $langId));

        $slotTimingsTypeArr = TimeSlot::getSlotTypeArr($this->adminLangId);
        $frm->addRadioButtons(Labels::getLabel('LBL_Slot_Timings', $this->adminLangId), 'tslot_availability', $slotTimingsTypeArr, TimeSlot::DAY_INDIVIDUAL_DAYS);

        $daysArr = TimeSlot::getDaysArr($this->adminLangId);
        for ($i = 0; $i < count($daysArr); $i++) {
            $frm->addCheckBox($daysArr[$i], 'tslot_day[' . $i . ']', $i, array(), false);
            $frm->addSelectBox(Labels::getLabel('LBL_From', $this->adminLangId), 'tslot_from_time[' . $i . '][]', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
            $frm->addSelectBox(Labels::getLabel('LBL_To', $this->adminLangId), 'tslot_to_time[' . $i . '][]', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
            $frm->addButton('', 'btn_add_row[' . $i . ']', '+');
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function setup()
    {
        $this->objPrivilege->canEditPickupAddresses();
        $post = FatApp::getPostedData();
        $availability = FatApp::getPostedData('tslot_availability', FatUtility::VAR_INT, 1);
        $addrStateId = FatUtility::int($post['addr_state_id']);
        $slotDays = isset($post['tslot_day']) ? $post['tslot_day'] : array();
        $slotFromTime = $post['tslot_from_time'];
        $slotToTime = $post['tslot_to_time'];

        $frm = $this->getForm($post['addr_id'], $post['lang_id']);
        $postedData = $frm->getFormDataFromArray($post);
        if (false === $postedData) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }       
        if($availability == TimeSlot::DAY_ALL_DAYS  && !isset($slotFromTime[TimeSlot::DAY_SUNDAY])){           
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $addressId = $post['addr_id'];
        unset($post['addr_id']);

        $address = new Address($addressId);
        $data = $post;
        $data['addr_state_id'] = $addrStateId;
        $data['addr_lang_id'] = $post['lang_id'];
        $data['addr_type'] = Address::TYPE_ADMIN_PICKUP;
        $address->assignValues($data);
        if (!$address->save()) {
            Message::addErrorMessage($address->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $updatedAddressId = $address->getMainTableRecordId();
        if (!FatApp::getDb()->deleteRecords(TimeSlot::DB_TBL, array('smt' => 'tslot_type = ? and tslot_record_id = ?', 'vals' => array(Address::TYPE_ADMIN_PICKUP, $updatedAddressId)))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!empty($slotDays) && $availability == TimeSlot::DAY_INDIVIDUAL_DAYS) {
            foreach ($slotDays as $day) {
                foreach ($slotFromTime[$day] as $key => $fromTime) {
                    if (!empty($fromTime) && !empty($slotToTime[$day][$key])) {
                        $slotData['tslot_type'] = Address::TYPE_ADMIN_PICKUP;
                        $slotData['tslot_availability'] = $availability;
                        $slotData['tslot_record_id'] = $updatedAddressId;
                        $slotData['tslot_day'] = $day;
                        $slotData['tslot_from_time'] = $fromTime;
                        $slotData['tslot_to_time'] = $post['tslot_to_time'][$day][$key];
                        $timeSlot = new TimeSlot();
                        $timeSlot->assignValues($slotData);
                        if (!$timeSlot->save()) {
                            Message::addErrorMessage($timeSlot->getError());
                            FatUtility::dieJsonError(Message::getHtml());
                        }
                    }
                }
            }
        }        
        if (!empty($slotDays) && $availability == TimeSlot::DAY_ALL_DAYS) {
            $daysArr = TimeSlot::getDaysArr($this->adminLangId);
            foreach ($daysArr as $day => $label) {
                foreach ($slotFromTime[TimeSlot::DAY_SUNDAY] as $key => $fromTime) {
                    if (!empty($fromTime) && !empty($slotToTime[TimeSlot::DAY_SUNDAY][$key])) {
                        $slotData['tslot_type'] = Address::TYPE_ADMIN_PICKUP;
                        $slotData['tslot_availability'] = $availability;
                        $slotData['tslot_record_id'] = $updatedAddressId;
                        $slotData['tslot_day'] = $day;
                        $slotData['tslot_from_time'] = $fromTime;
                        $slotData['tslot_to_time'] = $post['tslot_to_time'][TimeSlot::DAY_SUNDAY][$key];
                        $timeSlot = new TimeSlot();
                        $timeSlot->assignValues($slotData);
                        if (!$timeSlot->save()) {
                            Message::addErrorMessage($timeSlot->getError());
                            FatUtility::dieJsonError(Message::getHtml());
                        }
                    }
                }
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditPickupAddresses();
        $addressId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($addressId < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }
        if (!FatApp::getDb()->deleteRecords(Address::DB_TBL, array('smt' => 'addr_type = ? AND addr_id = ?', 'vals' => array(Address::TYPE_ADMIN_PICKUP, $addressId)))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }
}
