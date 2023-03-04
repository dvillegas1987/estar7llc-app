<?php
class OrdersTest extends YkModelTest
{   
   
    /**
     * @dataProvider setDataSubsciptionStatusArr
     */
    public function testGetOrderSubscriptionStatusArr($langId)
    {
        $result = Orders::getOrderSubscriptionStatusArr($langId);                                           
        $this->assertIsArray($result);
    }
    
    public function setDataSubsciptionStatusArr()
    {
        return array(
            array('test'), // Invalid lang id
            array(1), // Valid lang id      
        ); 
    }
    
    /**
     * @dataProvider setDataChargesArr
     */
    public function testGetOrderProductChargesArr($opId)
    {
        $order = new Orders();
        $result = $order->getOrderProductChargesArr($opId);
        $this->assertIsArray($result);
    }
    
    public function setDataChargesArr()
    {
        return array(
            array('test'), // Invalid opid
            array(29), // Valid opid
        ); 
    }

    
    
}