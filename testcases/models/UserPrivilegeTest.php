<?php
class UserPrivilegeTest extends YkModelTest
{
    private $class = 'UserPrivilege';

    /**
     * @dataProvider dataCanSellerUpgradeOrDowngradePlan
     */
    public function testCanSellerUpgradeOrDowngradePlan($expected, $userId, $spPlanId, $langId)
    {
        $result = $this->execute($this->class, [], 'canSellerUpgradeOrDowngradePlan', [$userId, $spPlanId, $langId]);
        $this->assertEquals($expected, $result);
    }
    
    public function dataCanSellerUpgradeOrDowngradePlan()
    {
        return array(
            array(false, 'test', 'test', $this->langId), //Invalid UserId and planId
            array(false, 'test', 99, $this->langId), //Invalid UserId but valid planId
            array(false, 4, 'test', $this->langId), //Invalid plan id but Valid user id          
            array(false, 4, 'test', $this->langId), //Invalid plan id but Valid user id
            array(false, '4', 'test', $this->langId), //Invalid plan id but Valid user id
            array(false, '4', '7', $this->langId), //Valid user id and plan id
            array(false, 4, 7, '1'), //Valid user id and plan id but wrong langId
            array(true, 4, 7, $this->langId), //Valid user id and plan id
        );
    }
}
