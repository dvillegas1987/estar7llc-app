<?php

class FacebookLoginTest extends YkPluginTest
{
    public const KEY_NAME = 'FacebookLogin';
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
     * @dataProvider feedVerifyAccessToken
     * @param  mixed $expected
     * @param  mixed $accessToken
     * @param  mixed $state
     * @return void
     */
    public function verifyAccessToken(bool $expected, $accessToken, $state = ''): void
    {
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'verifyAccessToken', [$accessToken, $state]);
        $this->assertEquals($expected, $response);
    }
        
    /**
     * feedVerifyAccessToken
     *
     * @return array
     */
    public function feedVerifyAccessToken(): array
    {
        // Returned false in case of invalid or missing Plugin Keys. Fail in case of opposite expectation.
        return [
            [false, '', ''], // Return False in case of all input empty.
            [false, 'abc', 'xyz'], // Return False in case of all input empty
            [false, 'EAAEViMZCbui8BAOgZB44rNUIWWbIRUtbJjzMI63nvW3iIcd1mAozgtfZCixUPMl6VC3YXA9ocjauZBxi5V6gFeijZBZABtaTY5Sy8Ym5ADZBfS70oG5cDaOa3X5HEDC5irEAPUnZCKfKklZAYmL2AUPnLuBT0TeQdsIDYl9r7kgGvAwZDZD'], // Return false in case invalid token
            [false, 'EAAEViMZCbui8BAOgZB44rNUIWWbIRUtbJjzMI63nvW3iIcd1mAozgtfZCixUPMl6VC3YXA9ocjauZBxi5V6gFeijZBZABtaTY5Sy8Ym5ADZBfS70oG5cDaOa3X5HEDC5irEAPUnZCKfKklZAYmL2AUPnLuBT0TeQdsIDYl9r7kgGvAwZDZD', 'ce5f965b037a2a71a316dd7cb2f94e2b'], // Return False in case of same access token but invalid.
        ];
    }
}
