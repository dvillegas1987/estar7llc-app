<?php
class SellerPackagePlansTest extends YkModelTest
{   
   
    /**
     * @dataProvider setvisiblePackages
     */
    public function testGetSellerVisiblePackagePlans( $packageId)
    {
        $result = SellerPackagePlans::getSellerVisiblePackagePlans($packageId);         
        $this->assertIsArray($result);
    }
    
    public function setvisiblePackages()
    {
        return array(
            array('test'), // Invalid package id
            array(4), // Valid package id
            array(0), // Get All plans
        ); 
    }

    /**
     * @dataProvider setDataCheapPlan
     */
    public function testGetCheapestPlanByPackageId( $packageId, $expected)
    {
        $result = SellerPackagePlans::getCheapestPlanByPackageId($packageId);
        if(is_array($expected)){
            $this->assertIsArray($result);
        }else{
            $this->assertEquals($expected, $result);
        }        
    }
    
    public function setDataCheapPlan()
    {
        return array(
            array('test', false), // Invalid package id
            array(9999, array()), // Package id does not exist
            array(4, array()), // Valid package id
        );
    }

    
}

