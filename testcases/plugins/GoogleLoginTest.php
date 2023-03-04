<?php

class GoogleLoginTest extends YkPluginTest
{
    public const KEY_NAME = 'GoogleLogin';
    public const PLUGIN_TYPE = Plugin::TYPE_SOCIAL_LOGIN;

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        if (false === $this->classObj->init()) {
            $this->error = $this->classObj->getError();
            return false;
        }
        return true;
    }
    
    /**
     * @test
     *
     * @dataProvider feedAuthenticate
     * @param  bool $expected
     * @param  mixed $code
     * @return void
     */
    public function authenticate($expected, $code)
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'authenticate', [$code]);
        $this->assertEquals($expected, $response);
    }
        
    /**
     * feedAuthenticate
     *
     * @return array
     */
    public function feedAuthenticate(): array
    {
        // Returned false in case of invalid or missing Plugin Keys. Fail in case of opposite expectation.
        return [
            [false, ''], // Return False in case of empty input.
            [false, 'abc'], // Return False in case of wrong input.
            [false, '4/1AGMhN5-Wob96JggkuwJhSCEW9tXH8ngw4G4JilTT4YWeAHaV0C4noApoBcjyclkanShIw5MoPeBrppRhBP5jME'], // Return false in case of expired $code
            [false, 123], // Return false in case of invalid value type.
        ];
    }

    /**
     * @test
     *
     * @dataProvider feedSetAccessToken
     * @param  bool $expected
     * @param  mixed $accessToken
     * @return void
     */
    public function setAccessToken($expected, $accessToken)
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'setAccessToken', [$accessToken]);
        $this->assertEquals($expected, $response);
    }
        
    /**
     * feedSetAccessToken
     *
     * @return array
     */
    public function feedSetAccessToken()
    {
        // Returned false in case of invalid or missing Plugin Keys. Fail in case of opposite expectation.
        return [
            [false, ''], // Return False in case of empty input.
            [true, 'abc'], // Return true either access_Token is wrong.
            [true, 'ya29.a0AfH6SMCnHrEFgUqi2G4P1GG5q1p-cXlIh7AwNyHODTDTtJu47hnl_IXdJIiKrut9hV5MYUZQQSzqTNyWItZUOejLYLSJEkhqgcyOfptidJCnz6Lcg0ufCfDrBoCTHIPKMXlaAz9AkRIIkLlipbS9gyoM_RkOR2xjbhk'], // Return True in case valid accessToken
            [false, 1234], // Return false in case invalid value type.
        ];
    }
}
