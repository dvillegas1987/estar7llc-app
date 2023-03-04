<?php
class AddressTest extends YkModelTest
{
    private $class = 'Address';

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
    { 
        self::truncateDbData();
        $obj = new self();
        $obj->insertUserData();
        $obj->insertAddressData();
    }  

    /**
     * tearDownAfterClass
     *
     * @return void
     */
    public static function tearDownAfterClass() :void
    {   
        self::truncateDbData();
    }    
    /**
     * truncateDbData
     *
     * @return void
     */
    public static function truncateDbData()
    {
        FatApp::getDb()->query("TRUNCATE TABLE ".Address::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".User::DB_TBL);
        FatApp::getDb()->query("TRUNCATE TABLE ".User::DB_TBL_CRED);
    }
    
    private function insertUserData()
    {
        $arr = [
            [
            'credential_user_id'=>1, 'credential_username'=>'vivek', 'credential_email'=>'vivek.kumar@fatbit.in', 'credential_password'=>'Welcome@123', 'credential_active'=>1, 'credential_verified'=>1
            ]
        ];
        $this->InsertDbData(User::DB_TBL_CRED, $arr);

        $arr = [
            [
            'user_id'=>1, 'user_name'=>'vivek', 'user_dial_code'=>'+91', 'user_phone'=>'9501496955', 'user_deleted'=>0,'user_id_buyer'=>1
            ]
        ];
        $this->InsertDbData(User::DB_TBL, $arr);
    }
    private function insertAddressData()
    {
        $arr = [
            [
                'addr_id'=> 1, 'addr_type'=> 1, 'addr_record_id'=>1,'addr_added_by'=> 0, 'addr_lang_id'=> 1, 'addr_title'=> 'AblySoft','addr_name'=>'Vivek', 'addr_address1'=> 'Plot no 268, JLPL industrial area, Sector 82', 'addr_address2'=> 'Mohali', 'addr_city'=>'Mohali', 'addr_state_id'=> '1294', 'addr_country_id'=> '99','addr_phone'=>'9843000000', 'addr_zip'=>'160055', 'addr_lat'=> '', 'addr_lng'=> '', 'addr_is_default'=> 1, 'addr_deleted'=> 0,
            ],
        ];            
        $this->InsertDbData(Address::DB_TBL, $arr);
    }

    /**
     * @test
     *
     * @dataProvider feedGetDefaultByRecordId
     * @param  int $type
     * @param  int $recordId
     * @param  int $langId
     * @return void
     */
    public function getDefaultByRecordId($expected, $type, $recordId, $langId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getDefaultByRecordId', [$type, $recordId, $langId]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result['addr_id']);
    }    
    /**
     * feedGetDefaultByRecordId
     *
     * @return array
    */
    public function feedGetDefaultByRecordId()
    {  
        return [
            [0, 'test', 1, 1],   //Invalid type, valid recordId, valid langId,
            [0, 1, 'test', 1],   //Valid type, Invalid recordId, valid langId
            [0, 1, 1, 'test'],   //Valid type, valid recordId, Invalid langId
            [1, 1, 1, 1],   //Valid type, valid recordId, valid langId
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedGetData
     * @param  int $type
     * @param  int $recordId
     * @param  int $isDefault
     * @param  bool $joinTimeSlots
     * @return void
     */
    public function getData($type, $recordId, $isDefault, $joinTimeSlots)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getData', [$type, $recordId, $isDefault, $joinTimeSlots]);
        $this->assertIsArray($result);
    }    
    /**
     * feedGetData
     *
     * @return array
    */
    public function feedGetData()
    {  
        return [
            ['test', 1, 0, false],   //Invalid type, valid recordId, valid isDefault, valid joinTimeSlots
            [1, 'test', 1, false],   //Valid type, Invalid recordId, valid isDefault, valid joinTimeSlots
            [1, 1, 'test', false],   //Valid type, valid recordId, Invalid isDefault, valid joinTimeSlots
            [1, 1, 0, false],       //Valid type, valid recordId, valid isDefault, valid joinTimeSlots
        ];
    } 

    /**
     * @test
     *
     * @dataProvider feedDeleteByRecordId
     * @param  int $type
     * @param  int $recordId
     * @return void
     */
    public function deleteByRecordId($expected, $type, $recordId)
    {
        $this->expectedReturnType(YkAppTest::TYPE_BOOL);
        $result = $this->execute($this->class, [], 'deleteByRecordId', [$type, $recordId]);
        $this->assertEquals($expected, $result);
    }    
    /**
     * feedDeleteByRecordId
     *
     * @return array
    */
    public function feedDeleteByRecordId()
    {  
        return [
            [false, 'test', 1],   //Invalid type, valid recordId
            [false, 1, 'test'],   //Valid type, Invalid recordId
            [false, 'test', 'test'],   //Invalid type, Invalid recordId
            [true, 1, 1],       //Valid type, valid recordId
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetGeoData
     * @param  mixed $lat
     * @param  mixed $long
     * @param  mixed $countryCode
     * @param  mixed $stateCode
     * @param  mixed $zipCode
     * @param  mixed $address
     * @return void
     */
    public function getGeoData($lat, $long, $countryCode, $stateCode, $zipCode, $address)
    {
        $this->expectedReturnType(YkAppTest::TYPE_ARRAY);
        $result = $this->execute($this->class, [], 'getGeoData', [$lat, $long, $countryCode, $stateCode, $zipCode, $address]);
        CommonHelper::printArray($result, 1);
        $this->assertIsArray($result);
    }    
    /**
     * feedGetGeoData
     *
     * @return array
    */
    public function feedGetGeoData()
    { 
        return [
            ['test', 'test', 0, 0, 0, ''],  //Return array, invalid lat, invalid long  
            ['test', '70.1', 0, 0, 0, ''],  //Return array, invalid lat, valid long        
            ['30.2', '70.1', 0, 0, 0, ''],  //Return array, valid lat, valid long       
        ];
    }

}