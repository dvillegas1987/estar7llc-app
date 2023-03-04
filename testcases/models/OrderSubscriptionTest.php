<?php
class OrderSubscriptionTest extends YkModelTest
{   
   
    /**
     * @dataProvider setDataFreeSubscription
     */
    public function testCanUserBuyFreeSubscription($langId, $userId, $expected)
    {
        $result = OrderSubscription::canUserBuyFreeSubscription($langId, $userId);                                           
        $this->assertEquals($expected, $result);
    }
    
    public function setDataFreeSubscription()
    {
        return array(
            array('test', 80, false), // Invalid langid and userid having subscription already
            array('test', 79, true), // Invalid langid and userid do not have subscription
            array(1, 80, false), // Valid langid and userid having subscription already
            array(1, 79, true), // Valid langid and userid do not have subscription
            array('test', 'test', false) // Invalid langid and userid
        ); 
    } 

    /**
     * @dataProvider dataForActivePlan
     */
    public function testGetUserCurrentActivePlanDetails($langId, $userId, $expected)
    {
        $result = OrderSubscription::getUserCurrentActivePlanDetails($langId, $userId);                                           
        $this->$expected($result);
    }
    
    public function dataForActivePlan()
    {
        return array(
            array('test', 80, 'assertIsArray'), // Invalid langid and userid having subscription already
            array('test', 79, 'assertNull'), // Invalid langid and userid do not have subscription
            array(1, 80, 'assertIsArray'), // Valid langid and userid having subscription already
            array(1, 79, 'assertNull'), // Valid langid and userid do not have subscription
            array('test', 'test', 'assertNull') // Invalid langid and userid
        );
    }
        

    
}