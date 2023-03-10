<?php

class Language extends MyAppModel
{
    public const DB_TBL = 'tbl_languages';
    public const DB_TBL_PREFIX = 'language_';

    public function __construct($langId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $langId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject($isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'l');

        if ($isActive == true) {
            $srch->addCondition('l.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getAllNames($assoc = true, $recordId = 0, $active = true, $deleted = false)
    {
        $cacheKey = $assoc . '-' . $recordId . '-' . $active . '-' . $deleted;
        $languageGetAllNames = FatCache::get('languageGetAllNames' .  $cacheKey, CONF_DEF_CACHE_TIME, '.txt');
        if ($languageGetAllNames) {
            return json_decode($languageGetAllNames, true);
        }

        $siteDefaultLang = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $srch = new SearchBase(static::DB_TBL);
        $srch->addOrder(static::tblFld('id'));
        if ($active === true) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($recordId > 0) {
            $srch->addCondition(static::tblFld('id'), '=', FatUtility::int($recordId));
        }

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if ($assoc) {
            $srch->addMultipleFields(array(static::tblFld('id'), static::tblFld('name')));
            $langData = FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        } else {
            $langData = FatApp::getDb()->fetchAll($srch->getResultSet(), static::tblFld('id'));
        }
        $defaultLangData = $langData[$siteDefaultLang];
        unset($langData[$siteDefaultLang]);
        $langData = [$siteDefaultLang => $defaultLangData] + $langData;

        FatCache::set('languageGetAllNames' . $cacheKey, FatUtility::convertToJson($langData), '.txt');
        return $langData;
    }

    public static function getAllCodesAssoc($withDefaultValue = false, $recordId = 0, $active = true, $deleted = false)
    {
        $cacheKey = $withDefaultValue . '-' . $recordId . '-' . $active . '-' . $deleted;
        $languageGetAllCodesAssoc = FatCache::get('languageGetAllCodesAssoc' .  $cacheKey, CONF_DEF_CACHE_TIME, '.txt');
        if ($languageGetAllCodesAssoc) {
            return json_decode($languageGetAllCodesAssoc, true);
        }

        $srch = new SearchBase(static::DB_TBL);
        $srch->addOrder(static::tblFld('id'));
        if ($active === true) {
            $srch->addCondition('language_active', '=', applicationConstants::ACTIVE);
        }

        if ($recordId > 0) {
            $srch->addCondition(static::tblFld('id'), '=', FatUtility::int($recordId));
        }

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array(static::tblFld('id'), 'UPPER(' . static::tblFld('code') . ')'));
        $row = FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        if ($withDefaultValue) {
            $row = array(0 => 'Universal') + $row;
        }

        FatCache::set('languageGetAllCodesAssoc' . $cacheKey, FatUtility::convertToJson($row), '.txt');
        return $row;
    }

    public static function getLayoutDirection($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }

        $getLayoutDirection = FatCache::get('getLayoutDirection' .  $langId, CONF_DEF_CACHE_TIME, '.txt');
        if ($getLayoutDirection) {
            return json_decode($getLayoutDirection, true);
        }

        $langData = self::getAttributesById($langId, array('language_layout_direction'));
        if (false != $langData) {
            FatCache::set('getLayoutDirection' . $langId, FatUtility::convertToJson($langData['language_layout_direction']), '.txt');
            return $langData['language_layout_direction'];
        }
    }
}
