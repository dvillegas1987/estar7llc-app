<?php
class UserAuthenticationTest extends YkModelTest
{
    private const CLASSNAME = 'UserAuthentication';

    /**
     * @dataProvider dataGuestLogin
     */
    public function testGuestLogin($userEmail, $name, $ip, $expected)
    {
        $result = $this->execute(self::CLASSNAME, [], 'guestLogin', [$userEmail, $name, $ip]);       
        $this->assertEquals($expected, $result);
    }
    
    public function dataGuestLogin()
    {
        return array(
            array('dev@dummyid.com', 'Dev', $_SERVER['REMOTE_ADDR'], false), // Existing user with verified and active account
            array('nonemail95021@gmail.com', 'kh', $_SERVER['REMOTE_ADDR'], false), // Existing User With Seller Account
            array('Electronicmart@dummyid.com', 'John', $_SERVER['REMOTE_ADDR'], true), // Deleted User
            array('Electronicmart@dummyid.com', 'John', $_SERVER['REMOTE_ADDR'], true), // Existing user with unverified account
            array('kanwar@dummyid.com', 'kanwar', $_SERVER['REMOTE_ADDR'], true), // Existing user with inactive account
            array('newuser'.rand().'@dummyid.com', 'newuser'.rand(), $_SERVER['REMOTE_ADDR'], true), // New User
        );
    }
    
    public function testLogFailedAttempt()
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->logFailedAttempt($_SERVER['REMOTE_ADDR'], 'developer@dummyid.com');
        $this->assertTrue(true);
    }
    
    public function testClearFailedAttempt()
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->clearFailedAttempt($_SERVER['REMOTE_ADDR'], 'developer@dummyid.com');
        $this->assertTrue(true);
    }
    
    /**
     * @dataProvider getLoginData
     */
    public function testLogin($userName, $password, $ip, $encryptPassword, $isAdmin, $tempUserId, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->login($userName, $password, $ip, $encryptPassword, $isAdmin, $tempUserId);
        $this->assertEquals($expected, $result);
    }
    
    public function getLoginData()
    {
        return array(
            array('wrong@dummyid.com', 'Cindy@123', $_SERVER['REMOTE_ADDR'], true, false, 0, false), // User With Wrong Email
            array('Cindy@dummyid.com', 'invalidpass', $_SERVER['REMOTE_ADDR'], true, false, 0, false), // User With Wrong Password
            array('wrong@dummyid.com', 'Wrong@123', $_SERVER['REMOTE_ADDR'], true, false, 0, false), // User With Wrong Email & Password
            array('demo@gmail.com', 'Demo@123', $_SERVER['REMOTE_ADDR'], true, false, 0, false), // User With Unverified account
            array('kanwar@dummyid.com', 'Kanwar@123', $_SERVER['REMOTE_ADDR'], true, false, 0, false), // User account deleted
            array('testshop@dummyid.com', 'Test@123', $_SERVER['REMOTE_ADDR'], true, false, 0, false), // User With deactivated account
            array('Cindy@dummyid.com', 'Cindy@123', $_SERVER['REMOTE_ADDR'], true, false, 0, true), // User With Valid Information
        );
    }
    
    /**
     * @dataProvider chkBruteForceAttempt
     */
    public function testBruteForceAttempt($ip, $username, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->isBruteForceAttempt($ip, $username);
        $this->assertEquals($expected, $result);
    }
    
    public function chkBruteForceAttempt()
    {
        return array(
            array($_SERVER['REMOTE_ADDR'], 'kanwar@dummyid.com', true), // User with existing ip and username
            array('192.168.0.25', 'dev101@dummyid.com', false), // User ip does not exist and username exist
            array($_SERVER['REMOTE_ADDR'], 'notexist@dummyid.com', true), // User ip exist and username does not exist
            array('192.168.0.25', 'notexist@dummyid.com', false), // User ip and username does not exist
        );
    }
    
    
    /**
     * @dataProvider getUserData
     */
    public function testGetUserByEmailOrUserName($username, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->getUserByEmailOrUserName($username);
        if (is_array($expected)) {
            $this->assertIsArray($result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }
    
    public function getUserData()
    {
        return array(
            array('wrong@dummyid.com', false), // User With Invalid Email
            array('wrong', false), // User With Invalid UserName
            array('Cindy@dummyid.com', array()), // User With Valid Email
            array('Cindy', array()), // User With Valid UserName
        );
    }
    
    /**
     * @dataProvider userPwdResetRequest
     */
    public function testCheckUserPwdResetRequest($userId, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->checkUserPwdResetRequest($userId);
        $this->assertEquals($expected, $result);
    }
    
    public function userPwdResetRequest()
    {
        return array(
            array('wrong', false), // User with wrong userid
            array(1, true), // User already request for reset password
            array(999999999, false), // User do not have previous request for reset password
        );
    }

    /**
     * @dataProvider addPwdRequest
     */
    public function testAddPasswordResetRequest($userData, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->addPasswordResetRequest($userData);
        $this->assertEquals($expected, $result);
    }
    
    public function addPwdRequest()
    {
        $token = UserAuthentication::encryptPassword(FatUtility::getRandomString(20));
        return array(
            array(array('user_id' => 'test', 'token' => $token), false), // User with invalid userid
            array(array('user_id' => 1, 'token' => 'token545'), false), // User with invalid token
            array(array('user_id' => 'test', 'token' => 'token545'), false), // User with invalid userid and token
            array(array('user_id' => 1, 'token' => $token), false), // User already have reset password request
            array(array('user_id' => 2, 'token' => $token), true), // User with valid data
        );
    }
    
    public function testDeleteOldPasswordResetRequest()
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->deleteOldPasswordResetRequest();
        $this->assertEquals(true, $result);
    }
    
    /**
     * @dataProvider chkResetLink
     */
    public function testCheckResetLink($userId, $token, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->checkResetLink($userId, $token);
        $this->assertEquals($expected, $result);
    }
    
    public function chkResetLink()
    {
        return array(
            array('test', '91c561f2b259e093f870675c5a011ee3', false), // User with invalid userid
            array(1, 'token545', false), // User with invalid token less than 20 characters
            array('test', 'token545', false), // User with invalid userid and token
            array(2, '91c561f2b259e093f870675c5a011ee3', false), // User do not request for reset password earlier
            array(1, '91c561f2b259e093f870675c5a011ee3', true), // User with valid data
        );
    }
    
    /**
     * @dataProvider resetUserPassword
     */
    public function testResetUserPassword($userId, $password, $expected)
    {
        $userAuth = new UserAuthentication();
        $result = $userAuth->resetUserPassword($userId, $password);
        $this->assertEquals($expected, $result);
    }
    
    public function resetUserPassword()
    {
        return array(
            array('test', 'Login@123', false), // User with invalid userid
            array(4, 'Login@123', true), // User with valid userid
        );
    }
}
