<?php

class MpesaTest extends YkPluginTest
{
    public const KEY_NAME = 'Mpesa';
    public const PLUGIN_TYPE = Plugin::TYPE_REGULAR_PAYMENT_METHOD;

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        $userId = UserAuthentication::getLoggedUserId(true);
        if (false === $this->classObj->init($userId)) {
            $this->error = $this->classObj->getError();
            return false;
        }
        return true;
    }

    /**
     * @test
     *
     * @dataProvider feedCallbackUrl
     * @param  mixed $orderId
     * @return void
     */
    public function callbackUrl($orderId)
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'callbackUrl', [$orderId]);
        $this->assertIsString($response);
    }

    /**
     * feedCallbackUrl
     *
     * @return array
     */
    public function feedCallbackUrl(): array
    {
        return [
            [''], // Return url string in case of all input empty.
            ['abc'], // Return url string in case of invalid order id
            [123], // Return url string if numeric variable passed
            ['O1607946732'], // Return url string if correct order id passed
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGenerateToken
     * @return void
     */
    public function generateToken(): void
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'generateToken');
        $this->assertIsBool($response);
    }

    /**
     * feedGenerateToken
     *
     * @return array
     */
    public function feedGenerateToken(): array
    {
        return [
            [], // Return bool(true/false) if token generated or not.
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedGetToken
     * @return void
     */
    public function getToken(): void
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'getToken');
        $this->assertIsString($response);
    }

    /**
     * feedGetToken
     *
     * @return array
     */
    public function feedGetToken(): array
    {
        return [
            [], // Return string type if response set or not.
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedSTKPushSimulation
     * @param  mixed $orderId
     * @param  mixed $amount
     * @param  mixed $customerPhone
     * @param  mixed $transactionDesc
     * @return void
     */
    public function sTKPushSimulation($expected, $orderId, $amount, $customerPhone, $transactionDesc): void
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'STKPushSimulation', [$orderId, $amount, $customerPhone, $transactionDesc]);
        $this->assertEquals($expected, $response);
    }

    /**
     * feedSTKPushSimulation
     *
     * @return array
     */
    public function feedSTKPushSimulation(): array
    {
        return [
            [false, 'O1607946732', '80', '918053250813', 'test'], // Return false if all invalid values are passed
            [false, 'O1607946732', '-80', 918053250813, 'test'], // Return false if all invalid values are passed
            [false, 'O1607946732', -80, 'abc', 'test'], // Return false if all invalid values are passed
            [false, 'O1607946732', '0', '918053250813', 'test'], // Return false if all invalid values are passed
            [false, 'O1607946732', '80', '254708374149', 'test'], // Return false if all values are correct. But dependent upon live dynamic created data.
        ];
    }
    
    /**
     * @test
     *
     * @dataProvider feedSTKPushQuery
     * @param  mixed $checkoutRequestID
     * @return void
     */
    public function sTKPushQuery($checkoutRequestID): void
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'STKPushQuery', [$checkoutRequestID]);
        $this->assertIsBool($response);
    }

    /**
     * feedSTKPushQuery
     *
     * @return array
     */
    public function feedSTKPushQuery(): array
    {
        return [
            ['O1607946732'], // Return false if all invalid values are passed
        ];
    }
}
