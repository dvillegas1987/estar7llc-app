<?php

class LibHelper extends FatUtility
{
    public static function dieJsonError($message)
    {
        if (true === MOBILE_APP_API_CALL) {
            $message = strip_tags($message);
        }
        FatUtility::dieJsonError($message);
    }

    public static function dieWithError($message)
    {
        FatUtility::dieWithError($message);
    }

    public static function exitWithError($message, $json = false, $redirect = false)
    {
        if (true === MOBILE_APP_API_CALL) {
            $message = strip_tags($message);
            FatUtility::dieJsonError($message);
        }

        if (true === $json) {
            FatUtility::dieJsonError($message);
        }

        if (FatUtility::isAjaxCall() || $redirect === false) {
            FatUtility::dieWithError($message);
        }

        if (true === $redirect) {
            Message::addErrorMessage($message);
        }
    }

    public static function getCommonReplacementVarsArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = CommonHelper::getLangId();
        }
        return array(
            '{SITE_NAME}' => FatApp::getConfig("CONF_WEBSITE_NAME_$langId"),
            '{SITE_URL}' => UrlHelper::generateFullUrl(),
        );
    }

    /**
     * This function returns the maximum files size that can be uploaded
     * in PHP
     * @returns int File size in bytes
     **/
    public static function getMaximumFileUploadSize()
    {
        return min(static::convertPHPSizeToBytes(ini_get('post_max_size')), static::convertPHPSizeToBytes(ini_get('upload_max_filesize')));
    }

    /**
     * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
     *
     * @param string $sSize
     * @return integer The value in bytes
     */
    public static function convertPHPSizeToBytes($sSize)
    {
        $sSuffix = strtoupper(substr($sSize, -1));
        if (!in_array($sSuffix, array('P', 'T', 'G', 'M', 'K'))) {
            return (int) $sSize;
        }
        $iValue = substr($sSize, 0, -1);
        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'T':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'G':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'M':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'K':
                $iValue *= 1024;
                break;
        }
        return (int) $iValue;
    }

    public static function bytesToSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public static function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function emailAddressMasking(string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            list($first, $last) = explode('@', $email);
            $first = str_replace(substr($first, 1), str_repeat('*', strlen($first) - 2), $first) . substr($first, -1);
            $last = explode('.', $last);
            $last_domain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0']) - 2), $last['0']) . substr($last[0], -1);
            return $first . '@' . $last_domain . '.' . $last['1'];
        }
    }

    public static function phoneNumberMasking(string $phone): string
    {
        return substr($phone, 0, 4) . str_repeat('*',(strlen($phone) - 5)) . substr($phone, -1);
    }
}
