<?php
class UserTest extends YkModelTest
{
    private $class = 'User';
    
    /**
     * @dataProvider dataSetCredentials
     */
    public function testSetLoginCredentials($userId, $username, $email, $password, $active, $verified, $expected)
    {
        /* $user = new User();
        $user->setMainTableRecordId($userId);
        $result = $user->setLoginCredentials($username, $email, $password, $active, $verified); */
        $result = $this->execute($this->class, [], 'canSellerUpgradeOrDowngradePlan', [$userId, $spPlanId, $langId]);
        $this->assertEquals($expected, $result);
    }
    
    public function dataSetCredentials()
    {
        return array(
            array('70000', 'dev70000', 'dev70000@dummyid.com', 'Test@123', null, null, true),//User details with inactive and unverified parameter
            array('70001', 'dev70001', 'dev70001@dummyid.com', 'Test@123', 1, null, true),// User details with active and unverified parameter
            array('70002', 'dev70002', 'dev70002@dummyid.com', 'Test@123', null, 1, true),// User details with inactive and verified parameter
            array('70003', 'dev70003', 'dev70003@dummyid.com', 'Test@123', 1, 1, true), // User details with active and verified parameter
            array('70004', 'dev70004', 'dev70003@dummyid.com', 'Test@123', 1, 1, true), // User with existing email id
            array('70000', 'test', 'test@dummyid.com', 'Test@123', null, null, true),//User with existing user id
            array('wrong', 'wrong', 'wrong@dummyid.com', 'Test@123', null, null, false),//User with invalid user id
            array('99999', 'testpassword', 'testpassword@dummyid.com', 'test', null, null, false),//User password less than 8 characters
        );
    }
    
    
    /**
     * @dataProvider setNotifyAdminRegistration
     */
    public function testNotifyAdminRegistration($data, $langId, $expected)
    {
        $user = new User();
        $result = $user->notifyAdminRegistration($data, $langId);
        $this->assertEquals($expected, $result);
    }
    
    public function setNotifyAdminRegistration()
    {
        return array(
            array(array('user_name' =>'cindy', 'user_username' =>'cindy', 'user_email' =>'cindy@dummyid.com', 'user_registered_initially_for' => 1), 1, true), //Valid parameters
        );
    }
    
    /**
     * @dataProvider setGuestWelcomeEmail
     */
    public function testGuestUserWelcomeEmail($data, $langId, $expected)
    {
        $user = new User();
        $result = $user->guestUserWelcomeEmail($data, $langId);
        $this->assertEquals($expected, $result);
    }
    
    public function setGuestWelcomeEmail()
    {
        return array(
            array(array('user_name' =>'cindy', 'user_email' =>'cindy@dummyid.com'), 1, true), //Valid parameters
        );
    }
    
    /**
     * @dataProvider loginPasswordData
     */
    public function testSetLoginPassword($userId, $password, $expected)
    {
        $user = new User();
        $user->setMainTableRecordId($userId);
        $result = $user->setLoginPassword($password);
        $this->assertEquals($expected, $result);
    }
    
    public function loginPasswordData()
    {
        return array(
            array('test', 'www@123', false), // Invalid user id
            array('70003', 'www@123', true), // User id exist
        );
    }
    
    /**
     * @dataProvider updateBankInfoData
     */
    public function testUpdateBankInfo($userId, $data, $expected)
    {
        $user = new User();
        $user->setMainTableRecordId($userId);
        $result = $user->updateBankInfo($data);
        $this->assertEquals($expected, $result);
    }
    
    public function updateBankInfoData()
    {
        return array(
            array(5, array('ub_bank_name' =>'PNB', 'ub_account_holder_name' =>'Sammy', 'ub_account_number' => '789789789'
            , 'ub_ifsc_swift_code' => 'PNB789', 'ub_bank_address' => 'mohali, punjab, india'), true), //Existing user id
            array('test', array('ub_bank_name' =>'PNB', 'ub_account_holder_name' =>'dev', 'ub_account_number' => '147147147'
            , 'ub_ifsc_swift_code' => 'SBI147', 'ub_bank_address' => 'chandigarh, india'), false), //Invalid user id
            array(999999, array('ub_bank_name' =>'KOTAK', 'ub_account_holder_name' =>'Tester', 'ub_account_number' => '321321321'
            , 'ub_ifsc_swift_code' => 'KBK321', 'ub_bank_address' => 'panchkula, haryana, india'), true), //User id does not exist
        );
    }
    
    /**
     * @dataProvider updateUserReturnAddressData
     */
    public function testUpdateUserReturnAddress($userId, $data, $expected)
    {
        $user = new User();
        $user->setMainTableRecordId($userId);
        $result = $user->updateUserReturnAddress($data);
        $this->assertEquals($expected, $result);
    }
    
    public function updateUserReturnAddressData()
    {
        return array(
            array(4, array('ura_state_id' =>'1250', 'ura_country_id' =>'50', 'ura_zip' => '1234', 'ura_phone_dcode' => '+91', 'ura_phone' => '9879879870'), true), //Existing user id
            array('test', array('ura_state_id' =>'1250', 'ura_country_id' =>'50', 'ura_zip' => '1234', 'ura_phone_dcode' => '+91', 'ura_phone' => '9879879870'), false), //Invalid user id
            array(999999, array('ura_state_id' =>'1180', 'ura_country_id' =>'80', 'ura_zip' => '8520'
            , 'ura_phone_dcode' => '+91', 'ura_phone' => '3213213210'), true), //User id does not exist
        );
    }
    
    /**
    * @dataProvider addSupplierRequest
    */
    public function testAddSupplierRequestData($data, $langId, $expected)
    {
        $user = new User();
        $result = $user->addSupplierRequestData($data, $langId);
        $this->assertEquals($expected, $result);
    }
    
    public function addSupplierRequest()
    {
        $reference_number = '79-'.time();
        return array(
            array(array('user_id' =>'test', 'reference' =>$reference_number, 'fieldIdsArr' => array('3','1','2')), 1, false), //Invalid user id
            array(array('user_id' =>'79', 'reference' =>$reference_number, 'fieldIdsArr' => array('3','1','2')), 1, true), //Valid user id
        );
    }
    
    /**
     * @dataProvider activateSupplierData
     */
    public function testActivateSupplier($userId, $activateAdveracc, $expected)
    {
        $user = new User();
        $user->setMainTableRecordId($userId);
        $result = $user->activateSupplier($activateAdveracc);
        $this->assertEquals($expected, $result);
    }
    
    public function activateSupplierData()
    {
        return array(
            array('test', 0, false), //Invalid user id
            array(79, 'test', true), //Invalid is_advertiser
            array(79, 0, true), //Valid user id with is_advertiser to 0
            array(79, 1, true), //Valid user id with is_advertiser to 1
        );
    }
    
    /**
    * @dataProvider dataSaveUser
    */
    public function testSaveUserData($data, $expected)
    {
        $user = new User();
        $result = $user->saveUserData($data);
        $this->assertEquals($expected, $result);
    }
    
    public function dataSaveUser()
    {
        $isSupplier = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1))  ? 0 : 1;
        $isAdvertiser = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0 : 1;
        $userActive = FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1) ? 0: 1;
        $userVerify = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1) ? 0 : 1;
        $random = mt_rand();
        
        return array(
            array(array('user_name' => 'Cindy', 'user_username' => 'Cindy', 'user_email' => 'testdevtest@dummyid.com', 'user_password' => 'Test@123', 'user_newsletter_signup' => 0, 'user_is_buyer' => User::USER_TYPE_BUYER, 'user_preferred_dashboard' => User::USER_BUYER_DASHBOARD, 'user_registered_initially_for' => User::USER_TYPE_BUYER, 'user_is_supplier' => $isSupplier,'user_is_advertiser' => $isAdvertiser, 'user_active' => $userActive, 'user_verify' => $userVerify), false),
            //username already exist
            
            array(array('user_name' => 'Cindy', 'user_username' => 'testdevtest', 'user_email' => 'Cindy@dummyid.com', 'user_password' => 'Test@123', 'user_newsletter_signup' => 0, 'user_is_buyer' => User::USER_TYPE_BUYER, 'user_preferred_dashboard' => User::USER_BUYER_DASHBOARD, 'user_registered_initially_for' => User::USER_TYPE_BUYER, 'user_is_supplier' => $isSupplier,'user_is_advertiser' => $isAdvertiser, 'user_active' => $userActive, 'user_verify' => $userVerify), false),
            //user email already exist
            
            array(array('user_name' => 'pok'.$random, 'user_username' => 'pok'.$random, 'user_email' => 'pok'.$random.'@dummyid.com', 'user_password' => 'wrong','user_newsletter_signup' => 0, 'user_is_buyer' => User::USER_TYPE_BUYER, 'user_preferred_dashboard' => User::USER_BUYER_DASHBOARD, 'user_registered_initially_for' => User::USER_TYPE_BUYER, 'user_is_supplier' => $isSupplier,'user_is_advertiser' => $isAdvertiser, 'user_active' => $userActive, 'user_verify' => $userVerify), false),
            //New user with invalid password
             
            array(array('user_name' => 'pok'.$random, 'user_username' => 'pok'.$random, 'user_email' => 'pok'.$random, 'user_password' => 'Test@123','user_newsletter_signup' => 0, 'user_is_buyer' => User::USER_TYPE_BUYER, 'user_preferred_dashboard' => User::USER_BUYER_DASHBOARD, 'user_registered_initially_for' => User::USER_TYPE_BUYER, 'user_is_supplier' => $isSupplier,'user_is_advertiser' => $isAdvertiser, 'user_active' => $userActive, 'user_verify' => $userVerify), false),
            //New user with invalid email

            array(array('user_name' => 'pok'.$random, 'user_username' => 'pok'.$random, 'user_email' => 'pok'.$random.'@dummyid.com', 'user_password' => 'Test@123','user_newsletter_signup' => 0, 'user_is_buyer' => User::USER_TYPE_BUYER, 'user_preferred_dashboard' => User::USER_BUYER_DASHBOARD, 'user_registered_initially_for' => User::USER_TYPE_BUYER, 'user_is_supplier' => $isSupplier,'user_is_advertiser' => $isAdvertiser, 'user_active' => $userActive, 'user_verify' => $userVerify), true),
            //New User with valid data
        );
    }
}
