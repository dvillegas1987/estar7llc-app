<?php

class AddressesController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);
        //$this->set('bodyClass','is--dashboard');
    }

    public function setUpAddress()
    {
        $frm = $this->getUserAddressForm($this->siteLangId);
        $post = FatApp::getPostedData();

        $markAsDefault = (!empty($post['isDefault']) && 0 < FatUtility::int($post['isDefault']) ? true : false);

        if (empty($post)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $addr_state_id = FatApp::getPostedData('addr_state_id', FatUtility::VAR_INT, 0);
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post['addr_state_id'] = $addr_state_id;

        $addr_id = FatApp::getPostedData('addr_id', FatUtility::VAR_INT, 0);
        unset($post['addr_id']);
        
        $post['addr_phone_dcode'] = FatApp::getPostedData('addr_phone_dcode', FatUtility::VAR_STRING, '');

        $addressObj = new Address($addr_id);

        $data_to_be_save = $post;
        $data_to_be_save['addr_record_id'] = UserAuthentication::getLoggedUserId();
        $data_to_be_save['addr_type'] = Address::TYPE_USER;
        $data_to_be_save['addr_lang_id'] = $this->siteLangId;
        $addressObj->assignValues($data_to_be_save, true);
        if (!$addressObj->save()) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($addressObj->getError());
            }
            Message::addErrorMessage($addressObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        if (0 <= $addr_id) {
            $addr_id = $addressObj->getMainTableRecordId();
        }

        if (true === $markAsDefault) {
            $this->markAsDefault($addr_id);
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $this->set('data', array('addr_id' => $addr_id));
            $this->_template->render();
        }
        $this->set('addr_id', $addr_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setDefault()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $addr_id = FatUtility::int($post['id']);
        $this->markAsDefault($addr_id);

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDefault($addr_id)
    {
        if (1 > $addr_id) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $address = new Address($addr_id);
        $addressDetail = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());

        if (empty($addressDetail)) {
            $message = Labels::getLabel('MSG_Invalid_request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $updateArray = array('addr_is_default' => 0);
        $whr = array('smt' => 'addr_type = ? and addr_record_id = ?', 'vals' => array(Address::TYPE_USER, UserAuthentication::getLoggedUserId()));

        if (!FatApp::getDb()->updateFromArray(Address::DB_TBL, $updateArray, $whr)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $addressObj = new Address($addr_id);
        $data = array(
            'addr_is_default' => 1,
            'addr_type' => Address::TYPE_USER,
            'addr_record_id' => UserAuthentication::getLoggedUserId(),
        );

        $addressObj->assignValues($data, true);
        if (!$addressObj->save()) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($addressObj->getError());
            }
            Message::addErrorMessage($addressObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function deleteRecord()
    {
        $addrId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        $type = FatApp::getPostedData('type', FatUtility::VAR_STRING, '');
        if (1 > $addrId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($type == Address::TYPE_SHOP_PICKUP) {
            $userId = $this->userParentId;
            $shopDetails = Shop::getAttributesByUserId($userId, null, false);
            if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
                Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $recordId = $shopDetails['shop_id'];
        } else {
            $userId = UserAuthentication::getLoggedUserId();
            $userDefaultAddress = Address::getDefaultByRecordId(Address::TYPE_USER, $userId);
            if ($userDefaultAddress['addr_id'] == $addrId) {
                $message = Labels::getLabel('MSG_Select_another_address', $this->siteLangId);
                FatUtility::dieJsonError($message);
            }
            $recordId = $userId;
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(Address::DB_TBL, array('smt' => 'addr_record_id = ? AND addr_id = ?', 'vals' => array($recordId, $addrId)))) {
            LibHelper::dieJsonError($db->getError());
        }
        $msg = Labels::getLabel('MSG_Removed_Successfully', $this->siteLangId);
        if (true === MOBILE_APP_API_CALL) {
            $this->set('msg', $msg);
            $this->_template->render();
        }
        FatUtility::dieJsonSuccess($msg);
    }

    public function getPickupAddresses()
    {
        $pickUpBy = FatApp::getPostedData('pickUpBy', FatUtility::VAR_INT, -1);
        $recordId = FatApp::getPostedData('recordId', FatUtility::VAR_INT, -1);
        $addrId = FatApp::getPostedData('addrId', FatUtility::VAR_INT, 0);
        $slotId = FatApp::getPostedData('slotId', FatUtility::VAR_INT, 0);
        $slotDate = FatApp::getPostedData('slotDate', FatUtility::VAR_STRING, '');
        if ($pickUpBy < 0 || $recordId < 0) {
            $msg = Labels::getLabel('LBL_Invalid_request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($msg);
            }
            Message::addErrorMessage($msg);
            FatUtility::dieWithError(Message::getHtml());
        }

        $type = ($pickUpBy == 0) ? Address::TYPE_ADMIN_PICKUP : Address::TYPE_SHOP_PICKUP;
        $address = new Address();
        $addresses = $address->getData($type, $recordId, 0, true);
        $this->set('addresses', $addresses);
        $this->set('pickUpBy', $pickUpBy);
        $this->set('addrId', $addrId);
        $this->set('slotId', $slotId);
        $this->set('slotDate', $slotDate);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->addJs(array('js/jquery.datetimepicker.js'));
        $this->_template->render(false, false);
    }

    public function getTimeSlotsByAddressAndDate(int $addressId = 0, string $selectedDate = '', int $pickUpBy = -1, bool $return = false)
    {
        $addressId = FatApp::getPostedData('addressId', FatUtility::VAR_INT, $addressId);
        $selectedDate = FatApp::getPostedData('selectedDate', FatUtility::VAR_STRING, $selectedDate);
        $pickUpBy = FatApp::getPostedData('pickUpBy', FatUtility::VAR_INT, $pickUpBy);
        $selectedSlot = FatApp::getPostedData('selectedSlot', FatUtility::VAR_INT, 0);
        if ($addressId < 1 || empty($selectedDate)) {
            $message = Labels::getLabel('LBL_Invalid_request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $addressArr = Address::getAttributesById($addressId,['addr_record_id','addr_type']);
        if(!$addressArr){
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            LibHelper::dieJsonError(Message::getHtml());
        }

        $day = date('w', strtotime($selectedDate));
        $timeSlot = new TimeSlot();
        $timeSlots = $timeSlot->timeSlotsByAddrIdAndDay($addressId, $day);

        array_walk($timeSlots, function (&$value, $key) {
            if (isset($value['tslot_from_time'])) {
                $value['tslot_from_time'] = date("H:i", strtotime($value['tslot_from_time']));
            }
            if (isset($value['tslot_to_time'])) {
                $value['tslot_to_time'] = date("H:i", strtotime($value['tslot_to_time']));
            }
        });        
        
        $pickupInterval = FatApp::getConfig('CONF_TIME_SLOT_ADDITION', FatUtility::VAR_INT, 2);
        if($addressArr['addr_type'] == Address::TYPE_SHOP_PICKUP){ 
            $pickupInterval = ShopSpecifics::getAttributesById($addressArr['addr_record_id'],'shop_pickup_interval');                                        
        }
        
        $this->set('pickupInterval', $pickupInterval);
        $this->set('timeSlots', $timeSlots);
        $this->set('selectedDate', $selectedDate);
        $this->set('pickUpBy', $pickUpBy);
        $this->set('selectedSlot', $selectedSlot);
        if (false === $return) {
            if (true === MOBILE_APP_API_CALL) {
                $this->_template->render();
            }
            $this->_template->render(false, false, 'addresses/time-slots.php');
        }
    }

    public function slotDaysByAddr(int $addrId, int $pickUpBy = -1)
    {
        if (1 > $addrId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            LibHelper::dieJsonError(Message::getHtml());
        }

        $timeSlot = new TimeSlot();
        $slotData = $timeSlot->timeSlotsByAddrId($addrId);
        $slotDays = [];
        foreach ($slotData as $data) {
            if (!in_array($data['tslot_day'], $slotDays)) {
                $slotDays[] = $data['tslot_day'];
            }
        }

        $activeDate = '';
        if (!empty($slotDays)) {
            $daysArr = TimeSlot::getDaysArr($this->siteLangId);
            $currentDay = date('w', strtotime(date('Y-m-d')));            
            if (in_array($currentDay, $slotDays)) {                
                $addressArr = Address::getAttributesById($addrId,['addr_record_id','addr_type']);
                if(!$addressArr){
                    $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    LibHelper::dieJsonError(Message::getHtml());
                }
                $pickupInterval = FatApp::getConfig('CONF_TIME_SLOT_ADDITION', FatUtility::VAR_INT, 2);
                if($addressArr['addr_type'] == Address::TYPE_SHOP_PICKUP){ 
                    $pickupInterval = ShopSpecifics::getAttributesById($addressArr['addr_record_id'],'shop_pickup_interval');                                        
                }
                
                $displayTime = date("H:i:s", strtotime('+' . $pickupInterval . ' hour'));
             
                $currentDateSlots = $timeSlot->timeSlotsByAddrIdAndDay($addrId, $currentDay);
                foreach ($currentDateSlots as $data) {
                    if (strtotime($data['tslot_from_time']) > strtotime($displayTime)) {
                        $activeDate = date('Y-m-d');
                        break;
                    }
                }
                if (empty($activeDate)) {
                    $index = array_search($currentDay, $slotDays);
                    if ($index < count($slotDays) - 1) {
                        $next = $slotDays[$index + 1];
                        $activeDate = date('Y-m-d', strtotime('+' . ($next - $currentDay) . ' days'));
                    } else {
                        $activeDate = date('Y-m-d', strtotime('+' . (count($daysArr) - $currentDay) . ' days'));
                    }
                }
            }

            if (!in_array($currentDay, $slotDays)) {
                foreach ($slotDays as $slotDay) {
                    if ($slotDay > $currentDay) {
                        $activeDate = date('Y-m-d', strtotime('+' . ($slotDay - $currentDay) . ' days'));
                        break;
                    }
                }
                if (empty($activeDate)) {
                    $needToAddDays = count($daysArr) - $currentDay + min($slotDays);
                    $activeDate = date('Y-m-d', strtotime('+' . $needToAddDays . ' days'));
                }
            }
        }
        $this->set('slotDays', $slotDays);
        $this->set('activeDate', $activeDate);
        if (true === MOBILE_APP_API_CALL) {
            $this->getTimeSlotsByAddressAndDate($addrId, $activeDate, $pickUpBy, true);
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }
}
