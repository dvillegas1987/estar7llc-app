<?php

class FixerCurrencyConverterTest extends YkPluginTest
{
    public const KEY_NAME = 'FixerCurrencyConverter';
    public const PLUGIN_TYPE = Plugin::TYPE_CURRENCY_CONVERTER;

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        $this->classObj->systemCurrencyCode = 'EUR';
    }

    /**
     * @test
     *
     * @dataProvider feedGetRates
     * @param  mixed $toCurrencies
     * @return void
     */
    public function getRates($expected, $toCurrencies)
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'getRates', [$toCurrencies]);
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
     * feedGetRates
     *
     * @return array
     */
    public function feedGetRates(): array
    {
        return [
            [1, ['USD', 'INR']], // Correct Values. Return array . Expected status 1(TRUE)
            [1, []], // No Value. Return all converted currencies array with by default base currency EUR . Expected status 1(TRU)
            [0, 'test'],   // Invalid Value. Return array . Expected status 0(FALSE)
        ];
    }
}
