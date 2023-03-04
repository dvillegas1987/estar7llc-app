<?php
class EmailHandlerTest extends YkModelTest
{
   
    /**
     * @dataProvider sendPasswordLinkEmail
     */
    public function testSendForgotPasswordLinkEmail($langId, $data, $expected)
    {
        $email = new EmailHandler();
        $result = $email->sendForgotPasswordLinkEmail($langId, $data);
        $this->assertEquals($expected, $result);
    }
    
    public function sendPasswordLinkEmail()
    {
        return array(
            array(1, array('user_name' =>'cindy', 'credential_email' =>'cindy@dummyid.com', 'link' =>'test'), true), //Valid data
            array(1, array('user_name' =>'wrong', 'credential_email' =>'wrong@dummyid.com', 'link' =>'wrong'), true), //Invalid data I
        );
    }
    
    /**
     * @dataProvider setFailedLoginAttempt
     */
    public function testFailedLoginAttempt($langId, $data, $expected)
    {
        $email = new EmailHandler();
        $result = $email->failedLoginAttempt($langId, $data);
        $this->assertEquals($expected, $result);
    }
    
    public function setFailedLoginAttempt()
    {
        return array(
            array(1, array('user_name' =>'cindy', 'credential_email' =>'cindy@dummyid.com'), true), //Valid data
        );
    }
}
