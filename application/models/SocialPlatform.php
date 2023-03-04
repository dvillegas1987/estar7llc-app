<?php

class SocialPlatform extends MyAppModel
{
    public const DB_TBL = 'tbl_social_platforms';
    public const DB_TBL_PREFIX = 'splatform_';

    public const DB_TBL_LANG = 'tbl_social_platforms_lang';
    public const DB_TBL_LANG_PREFIX = 'splatformlang_';

    public const ICON_CSS_FB_CLASS = 'facebook';
    public const ICON_CSS_TWITTER_CLASS = 'twitter';
    public const ICON_CSS_YOUTUBE_CLASS = 'youtube';
    public const ICON_CSS_INSTAGRAM_CLASS = 'instagram';
    public const ICON_CSS_GOOGLE_PLUS_CLASS = 'google';
    public const ICON_CSS_PINTEREST_CLASS = 'pinterest-p';
    public const ICON_CSS_LINKEDIN_CLASS = 'linkedin';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'sp');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'sp_l.' . static::DB_TBL_LANG_PREFIX . 'splatform_id = sp.' . static::tblFld('id') . ' AND
			sp_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'sp_l'
            );
        }

        if ($isActive == true) {
            $srch->addCondition('sp.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        return $srch;
    }

    public static function getIconArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return [
            static::ICON_CSS_FB_CLASS => Labels::getLabel('LBL_Facebook_Icon', $langId),
            static::ICON_CSS_TWITTER_CLASS => Labels::getLabel('LBL_Twitter_Icon', $langId),
            static::ICON_CSS_YOUTUBE_CLASS => Labels::getLabel('LBL_Youtube_Icon', $langId),
            static::ICON_CSS_INSTAGRAM_CLASS => Labels::getLabel('LBL_Instagram_Icon', $langId),
            static::ICON_CSS_GOOGLE_PLUS_CLASS => Labels::getLabel('LBL_Google_Icon', $langId),
            static::ICON_CSS_PINTEREST_CLASS => Labels::getLabel('LBL_Pinterest_Icon', $langId),
            static::ICON_CSS_LINKEDIN_CLASS => Labels::getLabel('LBL_LINKEDIN_ICON', $langId),
        ];
    }

    public static function getAvailableIconsArr($userId, $langId)
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        $iconsArr = static::getIconArr($langId);

        $srch = static::getSearchObject();
        $srch->addFld('splatform_icon_class');
        $srch->doNotCalculateRecords();
        $srch->addCondition('splatform_user_id', '=', $userId);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        foreach ($records as $row) {
            unset($iconsArr[$row['splatform_icon_class']]);
        }
        return $iconsArr;
    }
}
