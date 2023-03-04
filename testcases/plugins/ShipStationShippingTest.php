<?php

class ShipStationShippingTest extends YkPluginTest
{
    public const KEY_NAME = 'ShipStationShipping';
    public const PLUGIN_TYPE = Plugin::TYPE_SHIPPING_SERVICES;

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

        $name = 'Kanwar';
        $stt1 = 'Plot no 268, JLPL industrial area, Sector 82';
        $stt2 = 'Mohali';
        $city = 'mohali';
        $state = 'Punjab';
        $zip = '160055';
        $countryCode = 'IN';
        $phone = '6867456';
        $this->classObj->setAddress($name, $stt1, $stt2, $city, $state, $zip, $countryCode, $phone);
        $this->classObj->setWeight('352.74', 'ounces');
        $this->classObj->setDimensions('25.00', '2.00', '20.00', 'Inch');
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
                true,
                [
                    'plugin_active' => 1,
                    'api_key' => '366da0dfeea246d0926798bc10ac60c8',
                    'api_secret_key' => '60e27c9440d44c92a9387f7cdfebb773'
                ],
            ], // Return TRUE. Everything is correct. Return False if plan expires.
            [
                false,
                [
                    'plugin_active' => 0,
                    'api_key' => '366da0dfeea246d0926798bc10ac60c8',
                    'api_secret_key' => '60e27c9440d44c92a9387f7cdfebb773'
                ],
            ], // Return FALSE. Plugin Inactive.
            [
                ('feedInit' == $dataProvider),
                [
                    'plugin_active' => 1,
                    'api_key' => 'XXX',
                    'api_secret_key' => 'XXX'
                ],
            ], // Return TRUE. Plugin Active but invalid api key. Doesn't validate key.
            [
                false,
                [
                    'plugin_active' => 1,
                    'api_key' => '',
                    'api_secret_key' => ''
                ],
            ], // Return false. Plugin Active but empty api key.
            [
                false,
                [
                    'plugin_active' => 0,
                    'api_key' => '',
                    'api_secret_key' => ''
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
     * @param  mixed $expected
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
     * executeAssertionOnArray
     *
     * @param  string $function
     * @param  mixed $expected
     * @param  array $functionParams
     * @return void
     */
    private function executeAssertionOnArray($function, $expected, $functionParams = [])
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], $function, $functionParams);

        $this->assertIsArray($response);
        if (false === $expected) {
            $this->assertCount(0, $response);
        } else {
            $this->assertGreaterThan(0, count($response));
        }
        $status = (!empty($response) && 0 < count($response));
        $this->assertEquals($expected, $status);
    }

    /**
     * feedGetCarriers
     *
     * @return array
     */
    public function feedGetCarriers(): array
    {
        return $this->inputFeeder(__FUNCTION__);
    }

    /**
     * @test
     *
     * @dataProvider feedGetCarriers
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function getCarriers($expected, $feed)
    {
        $this->keys = $feed;
        $this->executeAssertionOnArray(__FUNCTION__, $expected);
    }

    /**
     * feedGetRates
     *
     * @return array
     */
    public function feedGetRates(): array
    {
        return [
            [true, ['stamps_com', '141003']],
            [false, ['', '141003']],
            [false, ['stamps_com', '']],
            [false, ['', '']],
            [false, ['']],
            [false, []],
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetRates
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function getRates($expected, $feed)
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $this->executeAssertionOnArray(__FUNCTION__, $expected, $feed);
    }

    /**
     * feedAddOrder
     *
     * @return void
     */
    public function feedAddOrder(): array
    {
        return [
            [true, [260]],
            [false, ['as']],
            [false, [0]],
            [false, ['']],
            [false, [false]]
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedAddOrder
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function addOrder($expected, $feed)
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], __FUNCTION__, $feed);
        $this->assertEquals($expected, $response);
    }

    /**
     * feedBindLabel
     *
     * @return array
     */
    public function feedBindLabel(): array
    {
        $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'addOrder', [260]);
        $classObj = $this->getClassObject();
        $response = $classObj->getResponse();
        $classObj->unsetResponse();

        return [
            [true, [
                        [
                        'orderId' => $response['orderId'],
                        'carrierCode' => $response['carrierCode'],
                        'serviceCode' => $response['serviceCode'],
                        'confirmation' => $response['confirmation'],
                        'shipDate' => date('Y-m-d', strtotime('+7 day')),
                        'weight' => $response['weight'],
                        'dimensions' => $response['dimensions']
                    ]
                ]
            ], [false , []]
        ];
    }

    /**
     * @test
     *
     * @param  mixed $expected
     * @param  mixed $feed
     * @return void
     */
    public function bindLabel()
    {
        $inputFeeder = $this->feedBindLabel();
        foreach ($inputFeeder as $feed) {
            $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], __FUNCTION__, $feed[1]);
            $this->assertEquals($feed[0], $response);
        }
    }
}
