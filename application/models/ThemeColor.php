<?php

class ThemeColor extends MyAppModel
{
    public const DB_TBL = 'tbl_theme';
    public const DB_TBL_COLORS = 'tbl_theme_colors';
    public const DB_TBL_PREFIX = 'theme_';
    public const DB_TBL_COLORS_PREFIX = 'tcolor_';

    public const TYPE_BRAND = 1;
    public const TYPE_BRAND_INVERSE = 2;
    public const TYPE_BODY = 3;
    public const TYPE_SECONDARY = 4;
    public const TYPE_SECONDARY_INVERSE = 5;
    public const TYPE_PRIMARY = 6;
    public const TYPE_PRIMARY_INVERSE = 7;
    public const TYPE_BORDER = 8;
    public const TYPE_BORDER_DARK = 9;
    public const TYPE_BORDER_LIGHT = 10;
    public const TYPE_FONT = 11;
    public const TYPE_FONT_SECONDARY = 12;
    public const TYPE_GREY = 13;
    public const TYPE_GREY_LIGHT = 14;
    public const TYPE_THIRD = 15;
    public const TYPE_THIRD_INVERSE = 16;


    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getTypeArr($langId, $excludeEditable = true) {
        $langId = FatUtility::int($langId);
        $arr = [
            static::TYPE_BRAND => Labels::getLabel('LBL_Brand_Color', $langId),
            static::TYPE_BRAND_INVERSE => Labels::getLabel('LBL_Brand_Inverse_Color', $langId),
            static::TYPE_BODY => Labels::getLabel('LBL_Body_Color', $langId),
            static::TYPE_SECONDARY => Labels::getLabel('LBL_Secondary_Color', $langId),
            static::TYPE_SECONDARY_INVERSE => Labels::getLabel('LBL_Secondary_Inverse_Color', $langId),
        ];

        if (false == $excludeEditable) {
            $arr1 = [
                static::TYPE_PRIMARY => Labels::getLabel('LBL_Primary_Color', $langId),
                static::TYPE_PRIMARY_INVERSE => Labels::getLabel('LBL_Primary_Inverse_Color', $langId),
                static::TYPE_BORDER => Labels::getLabel('LBL_Border_Color', $langId),
                static::TYPE_BORDER_DARK => Labels::getLabel('LBL_Border_Dark_Color', $langId),
                static::TYPE_BORDER_LIGHT => Labels::getLabel('LBL_Brand_Light_Color', $langId),
                static::TYPE_FONT => Labels::getLabel('LBL_Font_Color', $langId),
                static::TYPE_FONT_SECONDARY => Labels::getLabel('LBL_Font_Secondary_Color', $langId),
                static::TYPE_GREY => Labels::getLabel('LBL_Grey_Color', $langId),
                static::TYPE_GREY_LIGHT => Labels::getLabel('LBL_Grey_Light_Color', $langId),
                static::TYPE_THIRD => Labels::getLabel('LBL_Third_Color', $langId),
                static::TYPE_THIRD_INVERSE => Labels::getLabel('LBL_Third_Inverse_Color', $langId),
            ];

            $arr = array_merge($arr, $arr1);
        }
        return $arr;
    }

    public static function getSearchObject($joinColors = false)
    {
        $srch = new SearchBase(static::DB_TBL, 't');

        if ($joinColors) {
            $srch->joinTable(
                static::DB_TBL_COLORS,
                'LEFT OUTER JOIN',
                'tcolor_theme_id = theme_id'
            );
        }
        return $srch;
    }

    public static function getById($themeId, $langId, $excludeEditable = false, $assoc = false)
    {
        $srch = static::getSearchObject(true);
        $srch->addMultipleFields(array('tcolor_key', 'tcolor_value'));
        $srch->doNotCalculateRecords();
        $srch->addCondition('theme_id', '=', $themeId);
        if (true == $excludeEditable) {
            $colorkeys = array_keys(static::getTypeArr($langId, true));
            $srch->addCondition('tcolor_key', 'IN', $colorkeys);
        }
        $srch->addOrder('tcolor_display_order', 'asc');

        $rs = $srch->getResultSet();        
        if (true == $assoc) {
            return FatApp::getDb()->fetchAllAssoc($rs);
        }

        return FatApp::getDb()->fetchAll($rs);
    }
}
