<?php

class BannerLocation extends MyAppModel
{
    public const DB_TBL = 'tbl_banner_locations';
    public const DB_TBL_PREFIX = 'blocation_';

    public const DB_TBL_LANG = 'tbl_banner_locations_lang';

    public const DB_DIMENSIONS_TBL = 'tbl_banner_location_dimensions';
    public const DB_DIMENSIONS_TBL_PREFIX = 'bldimensions_';

    public const HOME_PAGE_BANNER_LAYOUT_1 = 1;
    public const HOME_PAGE_BANNER_LAYOUT_2 = 2;
    public const PRODUCT_DETAIL_PAGE_BANNER = 3;
    public const HOME_PAGE_MOBILE_BANNER = 4;

    public const HOME_PAGE_LAYOUTS = [
        self::HOME_PAGE_BANNER_LAYOUT_1,
        self::HOME_PAGE_BANNER_LAYOUT_2,
        self::HOME_PAGE_MOBILE_BANNER
    ];

    public const MOBILE_API_BANNER_PAGESIZE = 1;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true, $deviceType = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'bl');
        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'blocationlang_blocation_id = blocation_id AND blocationlang_lang_id = ' . $langId,
                'bl_l'
            );
        }

        if ($isActive) {
            $srch->addCondition('blocation_active', '=', applicationConstants::ACTIVE);
        }    

        $deviceType = FatUtility::int($deviceType);
        if (1 > $deviceType) {
            $deviceType = applicationConstants::SCREEN_DESKTOP;
        }

        $srch->joinTable(BannerLocation::DB_DIMENSIONS_TBL, 'LEFT OUTER JOIN', 'bldim.bldimension_blocation_id = bl.blocation_id AND bldim.bldimension_device_type = ' . $deviceType, 'bldim');

        return $srch;
    }

    public static function getDimensions($bannerLocationId, $deviceType)
    {
        $srch = new BannerSearch(0, false);
        $srch->joinLocations();
        $srch->joinLocationDimension($deviceType);
        $srch->addMultipleFields(array('blocation_banner_width', 'blocation_banner_height'));
        $srch->addCondition('bldimension_blocation_id', '=', $bannerLocationId);
        $srch->addCondition('bldimension_device_type', '=', $deviceType);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        return $bannerDimensions = FatApp::getDb()->fetch($rs);
    }

    public static function getPromotionalBanners($collectionId, $langId, $pageSize = 0)
    {
        $collectionId = FatUtility::int($collectionId);
        $langId = FatUtility::int($langId);
        $db = FatApp::getDb();

        $bannerSrch = Banner::getBannerLocationSrchObj(true);
        $bannerSrch->addCondition('blocation_collection_id', '=', $collectionId);
        $rs = $bannerSrch->getResultSet();
        $bannerLocation = $db->fetch($rs);

        if (empty($bannerLocation)) {
            return [];
        }

        $banners = $bannerLocation;
        $bsrch = new BannerSearch($langId, true);
        $bsrch->joinPromotions($langId, true, true, true);
        $bsrch->joinLocations(true);
        $bsrch->addPromotionTypeCondition();
        $bsrch->joinActiveUser();
        $bsrch->joinUserWallet();     
        $bsrch->addSkipExpiredPromotionAndBannerCondition();
        $bsrch->joinBudget();
        $bsrch->addMultipleFields(array('banner_id', 'banner_blocation_id', 'banner_type', 'banner_record_id', 'banner_url', 'banner_target', 'banner_title', 'promotion_id', 'daily_cost', 'weekly_cost', 'monthly_cost', 'total_cost', 'banner_updated_on'));
        $bsrch->doNotCalculateRecords();
        $bsrch->joinAttachedFile();
        $bsrch->addCondition('banner_blocation_id', '=', $bannerLocation['blocation_id']);

        $srch = new SearchBase('(' . $bsrch->getQuery() . ') as t');
        $srch->doNotCalculateRecords();
        $srch->addDirectCondition(
            '((CASE
				WHEN promotion_duration=' . Promotion::DAILY . ' THEN promotion_budget > COALESCE(daily_cost,0)
				WHEN promotion_duration=' . Promotion::WEEKLY . ' THEN promotion_budget > COALESCE(weekly_cost,0)
				WHEN promotion_duration=' . Promotion::MONTHLY . ' THEN promotion_budget > COALESCE(monthly_cost,0)
				WHEN promotion_duration=' . Promotion::DURATION_NOT_AVAILABALE . ' THEN promotion_budget = -1
			  END ) )'
        );
        $srch->addMultipleFields(array('banner_id', 'banner_blocation_id', 'banner_type', 'banner_record_id', 'banner_url', 'banner_target', 'banner_title', 'promotion_id', 'userBalance', 'daily_cost', 'weekly_cost', 'monthly_cost', 'total_cost', 'promotion_budget', 'promotion_duration', 'banner_updated_on'));
        if ($pageSize == 0) {
            $pageSize = $bannerLocation['blocation_banner_count'];
        }
        $srch->setPageSize($pageSize);
        $srch->addOrder('', 'rand()');
        $rs = $srch->getResultSet();

        if (true === MOBILE_APP_API_CALL) {
            $bannerListing = $db->fetchAll($rs);
        } else {
            $bannerListing = $db->fetchAll($rs, 'banner_id');
        }
        $banners['banners'] = $bannerListing;
        return $banners;
    }

    public static function getBannerLocationArr($langId)
    {
        $srch = BannerLocation::getSearchObject($langId);
        $srch->joinTable(Collections::DB_TBL, 'INNER JOIN', 'blocation_id = home_section_id AND home_section_type=' . Collections::COLLECTION_TYPE_BANNER);
        $srch->addMultipleFields(array(
            'blocation_id',
            'blocation_promotion_cost',
            'ifnull(blocation_name,blocation_identifier) as blocation_name'
        ));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'blocation_id');
        $locationArr = array();
        if (!empty($row)) {
            foreach ($row as $key => $val) {
                $locationArr[$key] = $val['blocation_name'] . ' ( ' . CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']) . ' )';
            }
        }
    }

    public static function getDataByCollectionId($collectionId, $fetch_attr = null)
    {
        $srch = static::getSearchObject();
        if (null != $fetch_attr) {
            if (is_array($fetch_attr)) {
                $srch->addMultipleFields($fetch_attr);
            } elseif (is_string($fetch_attr)) {
                $srch->addFld($fetch_attr);
            }
        }
        $srch->addCondition('blocation_collection_id', '=', $collectionId);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            return [];
        }
        return $row;
    }

    /**
     * saveLangData
     *
     * @param  int $langId
     * @param  string $collectionName
     * @return bool
     */
    public function saveLangData(int $langId, string $collectionName): bool
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $data = array(
            'blocationlang_blocation_id' => $this->mainTableRecordId,
            'blocationlang_lang_id' => $langId,
            'blocation_name' => $collectionName,
        );

        if (!$this->updateLangData($langId, $data)) {
            $this->error = $this->getError();
            return false;
        }
        return true;
    }

    /**
     * saveTranslatedLangData
     *
     * @param  int $langId
     * @return bool
     */
    public function saveTranslatedLangData(int $langId): bool
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $translateLangobj = new TranslateLangData(static::DB_TBL_LANG);
        if (false === $translateLangobj->updateTranslatedData($this->mainTableRecordId, 0, $langId)) {
            $this->error = $translateLangobj->getError();
            return false;
        }
        return true;
    }
}
