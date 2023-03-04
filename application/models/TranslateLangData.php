<?php
/*
    Microsoft Translator Text API 3.0
*/
require CONF_INSTALLATION_PATH . 'library/TranslateApi.php';

class TranslateLangData
{
    private $fromLangId;
    private $tbl;
    private $langTranslateFields;
    private $langTablePrimaryFields;
    private $error;
    private $translateObj;
    private $toLangQueryString;
    private $ignoreVariables = [];

    public function __construct($tbl)
    {
        if (empty($tbl)) {
            trigger_error(Labels::getLabel('MSG_SOMETHING_WENT_WRONG._PLEASE_TRY_AGAIN', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $this->tbl = $tbl;
        $this->langTranslateFields = $this->getLangTranslateFields();
        $this->langTablePrimaryFields = $this->getLangTablePrimaryFields();
    }

    private function getLangToCovert($toLangId, $fromLangId)
    {
        $this->fromLangId = (0 < $fromLangId) ? $fromLangId : FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);

        $translateFromlang = strtolower(Language::getAttributesById($this->fromLangId, 'language_code'));
        $this->translateObj = new TranslateApi($translateFromlang);

        if (0 < $toLangId) {
            $languages = [Language::getAttributesById($toLangId, array('language_id', 'language_code'))];
        } else {
            $languages = Language::getAllNames(false);
            unset($languages[$this->fromLangId]);
        }

        $this->toLangQueryString = strtolower("&to=" . implode("&to=", array_column($languages, 'language_code')));
        $langArr = array_change_key_case(array_flip(array_column($languages, 'language_code', 'language_id')));
        if (empty($langArr)) {
            $this->error = Labels::getLabel('MSG_NO_SECONDARY_LANGUAGE(S)_DEFINED', CommonHelper::getLangId());
            return false;
        }
        return $langArr;
    }

    private function formatTranslatedData($data, $langCount)
    {
        $formatedData = [];

        $data = array_column($data, 'translations');
        for ($i = 0; $i < $langCount; $i++) {
            $convertedData = array_column($data, $i);
            $targetLang = $convertedData[0]['to'];
            $convertedText = array_column($convertedData, 'text');
            $formatedData[$targetLang] = $convertedText;
        }
        return $formatedData;
    }

    public function directTranslate($data, $toLangId = 0, $fromLangId = 0)
    {
        if (empty($data) || 1 > count($data)) {
            $this->error = Labels::getLabel('MSG_PLEASE_PROVIDE_DATA_IN_DEFAULT_LANGUAGE_TO_TRANSLATE', CommonHelper::getLangId());
            return false;
        }

        $langArr = $this->getLangToCovert($toLangId, $fromLangId);
        $dataToUpdate = $this->getDataToTranslate($data);
        if (false === $dataToUpdate) {
            return false;
        }
        $response = $this->translateObj->translateData($this->toLangQueryString, $dataToUpdate);
        if (false === $response) {
            $this->error = $this->translateObj->getError();
            return false;
        }

        if (isset($response['error'])) {
            $this->error = $response['error']['message'];
            return false;
        }

        $convertedLangsData = $this->formatTranslatedData($response, count($langArr));

        $translatedDataToUpdate = [];
        foreach ($convertedLangsData as $lang => $langData) {
            $lang = strtolower($lang);
            $dataToupdate = array_combine(array_keys($data), $langData);
            $langRecordData = [];
            if (array_key_exists('langIdCol', $this->langTablePrimaryFields)) {
                $langRecordData = [
                    $this->langTablePrimaryFields['langIdCol'] => $langArr[$lang],
                ];
            }
            
            if (0 < count($this->ignoreVariables)) {
                $replacements = preg_filter('/$/', '</span>', preg_filter('/^/', "<span translate='no'>", $this->ignoreVariables));
                $replacements = array_combine($this->ignoreVariables, $replacements);
                foreach ($dataToupdate as &$value) {
                    foreach ($replacements as $key => $val) {
                        $value = str_replace($val, $key, $value);
                        $value = preg_replace('/<qt(?:\s[^>]*)?>([^<]+)<\/qt>/i', '"\1"', $value);
                    }
                }
            }

            $translatedDataToUpdate[$langArr[$lang]] = array_merge($langRecordData, $dataToupdate);
        }
        return $translatedDataToUpdate;
    }

    public function getTranslatedData($recordId, $toLangId = 0, $fromLangId = 0)
    {
        $langArr = $this->getLangToCovert($toLangId, $fromLangId);
        if (!$recordData = $this->getRecordData($recordId)) {
            return false;
        }

        if (empty($recordData)) {
            $this->error = Labels::getLabel('MSG_PLEASE_PROVIDE_DATA_IN_DEFAULT_LANGUAGE_TO_TRANSLATE', CommonHelper::getLangId());
            return false;
        }

        $dataToUpdate = $this->getDataToTranslate($recordData);
        if (false === $dataToUpdate) {
            return false;
        }

        $response = $this->translateObj->translateData($this->toLangQueryString, $dataToUpdate);
        if (false === $response) {
            $this->error = $this->translateObj->getError();
            return false;
        }

        if (!empty($response['error'])) {
            $this->error = $response['error']['message'];
            return false;
        }

        $convertedLangsData = $this->formatTranslatedData($response, count($langArr));

        $translatedDataToUpdate = [];
        foreach ($convertedLangsData as $lang => $langData) {
            $lang = strtolower($lang);
            $langRecordData = [
                $this->langTablePrimaryFields['recordIdCol'] => $recordId,
                $this->langTablePrimaryFields['langIdCol'] => $langArr[$lang],
            ];
            $dataToupdate = array_combine(array_keys($recordData), $langData);

            if (0 < count($this->ignoreVariables)) {
                $replacements = preg_filter('/$/', '</span>', preg_filter('/^/', "<span translate='no'>", $this->ignoreVariables));
                $replacements = array_combine($this->ignoreVariables, $replacements);
                foreach ($dataToupdate as &$value) {
                    foreach ($replacements as $key => $val) {
                        $value = str_replace($val, $key, $value);
                        $value = preg_replace('/<qt(?:\s[^>]*)?>([^<]+)<\/qt>/i', '"\1"', $value);
                    }
                }
            }
            $translatedDataToUpdate[$langArr[$lang]] = array_merge($langRecordData, $dataToupdate);
        }
        return $translatedDataToUpdate;
    }

    public function updateTranslatedData($recordId, $fromLangId = 0, $toLangId = 0)
    {
        $data = $this->getTranslatedData($recordId, $toLangId, $fromLangId);
        if (false === $data || empty($data) || 1 > count($data)) {
            return false;
        }

        foreach ($data as $translatedData) {
            if (!FatApp::getDB()->insertFromArray($this->tbl, $translatedData, false, array(), $translatedData)) {
                $this->error = Labels::getLabel('MSG_UNABLE_TO_UPDATE_DATA', CommonHelper::getLangId());
                return false;
            }
        }
    }

    private function getDataToTranslate($dataToUpdate)
    {
        $inputData = [];
        if (count($dataToUpdate) == count($dataToUpdate, COUNT_RECURSIVE)) {
            foreach ($dataToUpdate as $value) {
                preg_match_all("/{([^:}]*):?([^}]*)}/", $value, $matches);
                $this->ignoreVariables = array_unique(array_merge($this->ignoreVariables, (array)current($matches)));
                $replacements = preg_filter('/$/', '</span>', preg_filter('/^/', "<span translate='no'>", $this->ignoreVariables));
                $replacements = array_combine($this->ignoreVariables, $replacements);
                $value = preg_replace('/"([^"]+)"/', '"<qt>\1</qt>"', $value);
                foreach ($replacements as $key => $val) {
                    $value = str_replace($key, $val, $value);
                }
                $inputData[] = ['Text' => $value];
            }
        }
        if (empty($inputData)) {
            $this->error = Labels::getLabel('MSG_INVALID_DATA_FORMAT', CommonHelper::getLangId());
            return false;
        }
        return $inputData;
    }

    private function getRecordData($recordId)
    {
        $srch = new SearchBase($this->tbl, 'tld');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields($this->langTranslateFields);
        $srch->addCondition('tld.' . $this->langTablePrimaryFields['langIdCol'], '=', $this->fromLangId);
        $srch->addCondition('tld.' . $this->langTablePrimaryFields['recordIdCol'], '=', $recordId);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        if (false === $rs) {
            $this->error = Labels::getLabel('MSG_SOMETHING_WENT_WRONG._PLEASE_TRY_AGAIN', CommonHelper::getLangId());
            return false;
        }
        if (!$data = FatApp::getDb()->fetch($rs)) {
            $this->error = Labels::getLabel('MSG_COULD_NOT_FETCH_RESULTS', CommonHelper::getLangId());
            return false;
        }
        return array_filter($data);
    }


    private function getLangTranslateFields()
    {
        $qry = $this->getTableSchemaQry($this->tbl);
        $qry = FatApp::getDb()->query($qry . '
           AND COLUMN_KEY != "PRI"');

        return array_keys(FatApp::getDb()->fetchAll($qry, 'COLUMN_NAME'));
    }

    private function getLangTablePrimaryFields()
    {
        $qry = $this->getTableSchemaQry($this->tbl);
        $qry = FatApp::getDb()->query($qry . '
           AND COLUMN_KEY = "PRI"');

        $result = array_keys(FatApp::getDb()->fetchAll($qry, 'COLUMN_NAME'));
        $primaryCols = [];
        foreach ($result as $column) {
            if (0 < strpos($column, '_lang_id')) {
                $primaryCols['langIdCol'] = $column;
            } else {
                $primaryCols['recordIdCol'] = $column;
            }
        }
        return $primaryCols;
    }

    private function getTableSchemaQry()
    {
        return 'SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = "' . CONF_DB_NAME . '"
           AND TABLE_NAME = "' . $this->tbl . '"';
    }

    public function getError()
    {
        return $this->error;
    }
}
