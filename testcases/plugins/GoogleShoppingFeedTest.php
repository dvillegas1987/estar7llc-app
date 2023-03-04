<?php

class GoogleShoppingFeedTest extends YkPluginTest
{
    public const KEY_NAME = 'GoogleShoppingFeed';
    public const PLUGIN_TYPE = Plugin::TYPE_ADVERTISEMENT_FEED;

    /**
     * @test
     *
     * @dataProvider feedAgeGroup
     * @param  mixed $langId
     * @return void
     */
    public function ageGroup($langId)
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'ageGroup', [$langId]);
        $this->assertIsArray($response);
    }
        
    /**
     * feedAgeGroup
     *
     * @return array
     */
    public function feedAgeGroup(): array
    {
        return [
            [1], // Return array with values
            [2], // Return array with values if values set for language id 2
            [0],   // Return array with values,
            ['a'],   // Return array with values,
        ];
    }
    
    /**
     * @test
     *
     * @dataProvider feedGetProductCategory
     * @param  mixed $userId
     * @param  mixed $keyword
     * @param  mixed $returnFullArray
     * @return void
     */
    public function getProductCategory($userId, $keyword, $returnFullArray)
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID, $userId], 'getProductCategory', [$keyword, $returnFullArray]);
        $this->assertIsArray($response);
    }
    
    /**
     * feedGetProductCategory
     *
     * @return array
     */
    public function feedGetProductCategory(): array
    {
        return [
            [4, 12, true], // Return empty array if invalid type values passed. Return invalid argument type error by actual method.
            [4, 123, false], // Return empty array if invalid type values passed. Return invalid argument type error by actual method.
            [4, '', false], // Return empty array.
            [0, 'phone', false],    // Return array. Invalid user.
            [4, 'case', false], // Return array. Valid user.
            [4, 'phone', true], // Return array. Valid user.
        ];
    }
    
    /**
     * @test
     *
     * @dataProvider feedPublishBatch
     * @param  mixed $expected
     * @param  mixed $userId
     * @param  mixed $data
     * @return void
     */
    public function publishBatch($expected, $userId, $data)
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID, $userId], 'publishBatch', [$data]);

        $status = 0;
        if (!empty($response)) {
            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('msg', $response);
            $this->assertArrayHasKey('data', $response);       
            $status = $response['status'];
        }

        $this->assertEquals($expected, $status);
    }
    
    /**
     * feedPublishBatch
     *
     * @return array
     */
    public function feedPublishBatch(): array
    {
        return [
            [0, 4, []], // Return 0. No Data
            [0, 4, ['abc']], // Return 0. Invalid Data Format.
            [0, 4, ['data' => []]], // Return 0. Missing Data.
            [0, 4, 123], // Return 0. Invalid 3rd param type expected array but passed int.
            [0, 4, 'abc'], // Return 0. Invalid 3rd param type expected array but passed string.
            [1, 4, [   
                    'batchId' => 1,
                    'currency_code' => 'USD',
                    'data' => [
                                [
                                    'selprod_id' => '180',
                                    'selprod_title' => 'Apple iPhone 12',
                                    'selprod_stock' => 'in stock',
                                    'selprod_condition' => 'New',
                                    'selprod_price' => '550.00',
                                    'selprod_available_from' => '2020-12-23 00:00:00',
                                    'product_id' => '76',
                                    'product_description' => '
                                        6.1-inch Super Retina XDR display                                            
                                        Ceramic Shield, tougher than any smartphone glass    
                                        A14 Bionic chip, the fastest chip ever in a smartphone 
                                        Advanced dual-camera system with 12MP Ultra Wide and Wide cameras; Night mode, Deep Fusion, Smart HDR 3, 4K Dolby Vision HDR recording
                                        12MP TrueDepth front camera with Night mode, 4K Dolby Vision HDR recording
                                        Industry-leading IP68 water resistance                                            
                                        Supports MagSafe accessories for easy attach and faster wireless charging
                                        iOS with redesigned widgets on the Home screen, all-new App Library, App Clips and more',
                                    'product_upc' => '',
                                    'language_code' => 'EN',
                                    'country_code' => 'US',
                                    'brand_name' => 'Apple',
                                    'abprod_item_group_identifier' => 'APPLE76',
                                    'adsbatch_expired_on' => '2021-01-22 00:00:00',
                                    'abprod_cat_id' => '7',
                                    'optionsData' => [
                                        [
                                            [
                                                'optionvalue_identifier' => '256 GB',
                                                'option_is_color' => '0',
                                                'option_name' => 'Storage',
                                            ]
                                        ],
                                        [
                                            [
                                                'optionvalue_identifier' => 'Gold',
                                                'option_is_color' => '1',
                                                'option_name' => 'Color',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                ]
            ], // Return 1. Success
            [0, 4, [   
                'batchId' => 1,
                'currency_code' => '',
                'data' => [
                            [
                                'selprod_id' => '180',
                                'selprod_title' => 'Apple iPhone 12',
                                'selprod_stock' => 'in stock',
                                'selprod_condition' => 'New',
                                'selprod_price' => '550.00',
                                'selprod_available_from' => '2020-12-23 00:00:00',
                                'product_id' => '76',
                                'product_description' => '
                                    6.1-inch Super Retina XDR display                                            
                                    Ceramic Shield, tougher than any smartphone glass    
                                    A14 Bionic chip, the fastest chip ever in a smartphone 
                                    Advanced dual-camera system with 12MP Ultra Wide and Wide cameras; Night mode, Deep Fusion, Smart HDR 3, 4K Dolby Vision HDR recording
                                    12MP TrueDepth front camera with Night mode, 4K Dolby Vision HDR recording
                                    Industry-leading IP68 water resistance                                            
                                    Supports MagSafe accessories for easy attach and faster wireless charging
                                    iOS with redesigned widgets on the Home screen, all-new App Library, App Clips and more',
                                'product_upc' => '',
                                'language_code' => 'EN',
                                'country_code' => 'US',
                                'brand_name' => 'Apple',
                                'abprod_item_group_identifier' => 'APPLE76',
                                'adsbatch_expired_on' => '2021-01-22 00:00:00',
                                'abprod_cat_id' => '7',
                                'optionsData' => [
                                    [
                                        [
                                            'optionvalue_identifier' => '256 GB',
                                            'option_is_color' => '0',
                                            'option_name' => 'Storage',
                                        ]
                                    ],
                                    [
                                        [
                                            'optionvalue_identifier' => 'Gold',
                                            'option_is_color' => '1',
                                            'option_name' => 'Color',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                ]
            ], // Return 0 if empty currency code
            [0, 4, [   
                'batchId' => 1,
                'currency_code' => 'USD',
                'data' => [
                            [
                                'selprod_id' => '180',
                                'selprod_title' => 'Apple iPhone 12',
                                'selprod_stock' => 'in stock',
                                'selprod_condition' => 'New',
                                'selprod_price' => '550.00',
                                'selprod_available_from' => '2020-12-23 00:00:00',
                                'product_id' => '76',
                                'product_description' => '
                                    6.1-inch Super Retina XDR display                                            
                                    Ceramic Shield, tougher than any smartphone glass    
                                    A14 Bionic chip, the fastest chip ever in a smartphone 
                                    Advanced dual-camera system with 12MP Ultra Wide and Wide cameras; Night mode, Deep Fusion, Smart HDR 3, 4K Dolby Vision HDR recording
                                    12MP TrueDepth front camera with Night mode, 4K Dolby Vision HDR recording
                                    Industry-leading IP68 water resistance                                            
                                    Supports MagSafe accessories for easy attach and faster wireless charging
                                    iOS with redesigned widgets on the Home screen, all-new App Library, App Clips and more',
                                'product_upc' => '',
                                'language_code' => 'EN',
                                'country_code' => 'XX',
                                'brand_name' => 'Apple',
                                'abprod_item_group_identifier' => 'APPLE76',
                                'adsbatch_expired_on' => '2021-01-22 00:00:00',
                                'abprod_cat_id' => '7',
                                'optionsData' => [
                                    [
                                        [
                                            'optionvalue_identifier' => '256 GB',
                                            'option_is_color' => '0',
                                            'option_name' => 'Storage',
                                        ]
                                    ],
                                    [
                                        [
                                            'optionvalue_identifier' => 'Gold',
                                            'option_is_color' => '1',
                                            'option_name' => 'Color',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                ]
            ], // Return 0 if invalid country code : XX
        ];
    }
}
