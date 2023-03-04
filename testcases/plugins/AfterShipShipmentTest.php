<?php

class AfterShipShipmentTest extends YkPluginTest
{
    public const KEY_NAME = 'AfterShipShipment';
    public const PLUGIN_TYPE = Plugin::TYPE_SHIPMENT_TRACKING;

    private $keys = '';

    /**
     * init - Called before execution. Treet as a setter function.
     *
     * @return void
     */
    public function init()
    {
        if (!empty($this->keys)) {
            $this->classObj->settings = $this->keys;
        }
    }

    /**
     * inputFeeder
     *
     * @param  string $action
     * @return array
     */
    private function inputFeeder(string $dataProvider): array
    {
        return [
            [
                ('feedInit' == $dataProvider), 
                [
                    'plugin_active' => 1,
                    'api_key' => 'a603e21c-339c-4496-9c19-7da8d8457ab7'
                ],
            ], // Return TRUE. Everything is correct. Return False if plan expires.
            [
                false, 
                [
                    'plugin_active' => 0,
                    'api_key' => 'a603e21c-339c-4496-9c19-7da8d8457ab7'
                ],
            ], // Return FALSE. Plugin Inactive.
            [
                ('feedInit' == $dataProvider), 
                [
                    'plugin_active' => 1,
                    'api_key' => 'XXXx'
                ],
            ], // Return TRUE. Plugin Active but invalid api key. Doesn't validate key.
            [
                false, 
                [
                    'plugin_active' => 1,
                    'api_key' => ''
                ],
            ], // Return false. Plugin Active but empty api key.
            [
                false, 
                [
                    'plugin_active' => 0,
                    'api_key' => ''
                ],
            ], // Return false. Plugin Inactive and empty api key.
        ];
    }

    /**
     * feedInit
     *
     * @return array
     */
    public function feedInit(): array
    {
        return $this->inputFeeder(__FUNCTION__);
    }

    /**
     * @test
     *
     * @dataProvider feedInit
     * @param  mixed $feed
     * @return void
     */
    public function pluginInit($expected, $feed)
    {
        $this->keys = $feed;
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'init');
        $this->assertEquals($expected, $response);
    }

    /**
     * feedGetTrackingCouriers
     *
     * @return array
     */
    public function feedGetTrackingCouriers(): array
    {
        return $this->inputFeeder(__FUNCTION__);
    }

    /**
     * @test
     *
     * @dataProvider feedGetTrackingCouriers
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function getTrackingCouriers($expected, $feed)
    {
        $this->keys = $feed;
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'getTrackingCouriers');
        $this->assertEquals($expected, $response);
    }

    /**
     * feedCreateTracking
     *
     * @return array
     */
    public function feedCreateTracking(): array
    {
        return [
            [false, ['9876543210', 'dsv', 'O1611568233']], // Return False. If plan expires.
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedCreateTracking
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function createTracking($expected, $feed)
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'createTracking', $feed);
        $this->assertEquals($expected, $response);
    }

    /**
     * feedGetTrackingInfo
     *
     * @return array
     */
    public function feedGetTrackingInfo(): array
    {
        return [
            [false, ['5003756156', 'dsv']], // Return False. If plan expires.
            [false, ['0', 'dsv']], // Return False. If invalid values.
            [false, ['', 'dsv']], // Return False. If invalid values.
            [false, ['5003756156', '']], // Return False. If invalid values.
            [false, ['XX', 'XXX']] // Return False. If invalid values.
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetTrackingInfo
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function getTrackingInfo($expected, $feed)
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'getTrackingInfo', $feed);
        $this->assertEquals($expected, $response);
    }
}
