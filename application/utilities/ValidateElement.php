<?php

class ValidateElement extends FatUtility
{
    public const PHONE_NO_FORMAT = '';
    public const PHONE_NO_LENGTH = 15;
    public const PHONE_REGEX = '^[0-9]{1,15}$';
    public const ZIP_REGEX = '^[a-zA-Z0-9]+$';
    public const CITY_NAME_REGEX = '^([^0-9]*)$';
    public const PASSWORD_REGEX = '^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%-_]{8,15}$';
    public const USERNAME_REGEX = '^[a-zA-Z0-9]{3,30}$';
    public const FATBIT_USERNAME_REGEX = '^[a-zA-Z][a-zA-Z_\.0-9]{3,19}$';
    public const VISA_REGEX = '^4';
    public const MASTER_REGEX = '^5[1-5]';
    public const AMEX_REGEX = '^3[47]';
    public const DINERS_CLUB_REGEX = '^3(?:0[0-5]|[68])';
    public const DISCOVER_REGEX = '^6(?:011|5)';
    public const JCB_REGEX = '^(?:2131|1800|35\d{3})';
    public const TIME_REGEX = '^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$';
    public const URL_REGEX = '^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$';
    public const PHONE_FORMATS = [
        '98XXXXXX89',
    ];


    public static function phone($string = '')
    {
        if (strlen($string) < 10) {
            return false;
        }

        if (!preg_match('/' . static::PHONE_REGEX . '/', $string)) {
            return false;
        }
        return true;
    }
    
    public static function password($string = '')
    {
        if (strlen($string) < 1) {
            return false;
        }

        if (!preg_match('/' . static::PASSWORD_REGEX . '/', $string)) {
            return false;
        }
        return true;
    }

    public static function username($string = '')
    {
        if (strlen($string) < 3) {
            return false;
        }
        if (!preg_match('/' . static::USERNAME_REGEX . '/', $string)) {
            return false;
        }
        return true;
    }

    public static function fatbitUsername($string = '')
    {
        if (strlen($string) < 3) {
            return false;
        }
        if (!preg_match('/' . static::FATBIT_USERNAME_REGEX . '/', $string)) {
            return false;
        }
        return true;
    }

    public static function ccNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', ($cardNumber));
        $len = strlen($cardNumber);
        $result = array();
        if ($len > 16) {
            $result['card_type'] = 'Invalid';
            return $result;
        }
        switch ($cardNumber) {
            case 0:
                $result['card_type'] = '';
                break;
            case (preg_match('/' . static::VISA_REGEX . '/', $cardNumber) >= 1):
                $result['card_type'] = 'VISA';
                break;
            case (preg_match('/' . static::MASTER_REGEX . '/', $cardNumber) >= 1):
                $result['card_type'] = 'MASTER';
                break;
            case (preg_match('/' . static::AMEX_REGEX . '/', $cardNumber) >= 1):
                $result['card_type'] = 'AMEX';
                break;
            case (preg_match('/' . static::DINERS_CLUB_REGEX . '/', $cardNumber) >= 1):
                $result['card_type'] = 'DINERS_CLUB';
                break;
            case (preg_match('/' . static::DISCOVER_REGEX . '/', $cardNumber) >= 1):
                $result['card_type'] = 'DISCOVER';
                break;
            case (preg_match('/' . static::JCB_REGEX . '/', $cardNumber) >= 1):
                $result['card_type'] = 'JCB';
                break;
            default:
                $result['card_type'] = '';
                break;
        }
        return $result;
    }

    public static function formatDialCode($dialCode)
    {
        return (false !== strpos($dialCode, '-') ? (explode('-', $dialCode))[0] : $dialCode);
    }
}
