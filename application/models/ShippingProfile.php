<?php
class ShippingProfile extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_profile';
    const DB_TBL_PREFIX = 'shipprofile_';
    
    public const DB_TBL_LANG = 'tbl_shipping_profile_lang';
    public const DB_TBL_LANG_PREFIX = 'shipprofilelang_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isActive = false)
    {
        $srch = new SearchBase(static::DB_TBL, 'sprofile');
        
        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'sprofile_l.' . static::DB_TBL_LANG_PREFIX . static::tblFld('id') . ' = sprofile.' . static::tblFld('id') . ' and
			sprofile_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'sprofile_l'
            );
        }
        if ($isActive == true) {
            $srch->addCondition('sprofile.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function  getProfileArr($langId, $userId, $assoc = true, $isActive = false, $default = false)
    {
        $srch = self::getSearchObject($langId, $isActive);
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $srch->addCondition('shipprofile_user_id', '=', 0);
        } else {
            $srch->addCondition('shipprofile_user_id', '=', $userId);
        }
        $srch->addMultipleFields(array('shipprofile_id', 'IFNULL(shipprofile_name, shipprofile_identifier) as shipprofile_name'));
        $srch->addOrder('shipprofile_default', 'DESC');
        $srch->addOrder('shipprofile_id', 'ASC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if (true == $default) {
            $srch->addCondition('shipprofile_default', '=', applicationConstants::YES);
        }

        if ($assoc) {
            return FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        } else {
            return FatApp::getDb()->fetchAll($srch->getResultSet(), static::tblFld('id'));
        }
    }

    public static function getShipProfileIdByName($langId, $profileName, $userId = 0)
    {
        $srch = self::getSearchObject($langId);
        $cnd = $srch->addCondition('shipprofile_name', '=', trim($profileName));
        //$cnd->attachCondition('shipprofile_identifier', '=', trim($profileName));
        $srch->addCondition('shipprofile_user_id', '=', $userId);
        $srch->addFld('shipprofile_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return $row['shipprofile_id'];
        }
        return 0;
    }

    public static function setDefaultShipProfile($userId)
    {
        /* [ CREATE DEFAULT SHIPPING PROFILE */
        $dataToInsert = array(
            'shipprofile_user_id' => $userId,
            'shipprofile_identifier' => Labels::getLabel('LBL_ORDER_LEVEL_SHIPPING', CommonHelper::getLangId()),
            'shipprofile_active' => 1,
            'shipprofile_default' => 1
        );

        $shippingProfile = new ShippingProfile();
        $shippingProfile->assignValues($dataToInsert);

        if (!$shippingProfile->save()) {
            Message::addErrorMessage($shippingProfile->getError());
        }
        return $shippingProfile->getMainTableRecordId();
    }

    public static function setDefaultZone($userId, $shippingProfileId)
    {
        $zoneData = [
            'shipzone_user_id' => $userId,
            'shipzone_active' => applicationConstants::ACTIVE,
            'shipzone_name' => Labels::getLabel('LBL_Standard', CommonHelper::getLangId()) . '-' . $shippingProfileId
        ];
        $shippingZone = new ShippingZone();
        $shippingZone->assignValues($zoneData);
        if (!$shippingZone->save()) {
            Message::addErrorMessage($shippingZone->getError());
        }
        $shipZoneId = $shippingZone->getMainTableRecordId();

        if ($shipZoneId) {
            $location = [
                'shiploc_zone_id' => -1,
                'shiploc_country_id' => -1,
                'shiploc_state_id' => -1,
                'shiploc_shipzone_id' => $shipZoneId,
            ];
            $shippingZone->updateLocations($location);
        }

        $shipProZoneId = 0;
        if ($shippingProfileId && $shipZoneId) {
            $data = array(
                'shipprozone_shipprofile_id' => $shippingProfileId,
                'shipprozone_shipzone_id' => $shipZoneId
            );
            $shippingProfileZone = new ShippingProfileZone();
            $shippingProfileZone->assignValues($data);
            if (!$shippingProfileZone->save($data)) {
                Message::addErrorMessage($shippingProfileZone->getError());
            }
            $shipProZoneId = $shippingProfileZone->getMainTableRecordId();
        }

        return $shipProZoneId;
    }
    
    public static function getShippingZoneArr(int $userId): array
    {
        $shippingZoneSrch = ShippingZone::getSearchObject();
        $shippingZoneSrch->addCondition('shipzone_user_id', '=', $userId);
        $rs = $shippingZoneSrch->getResultSet();
        return (array) FatApp::getDb()->fetchAll($rs);
    }

    public static function setDefaultRates($shipProZoneId, $shippingProfileId)
    {
        $shipProZoneId = FatUtility::int($shipProZoneId);
        $shippingProfileId = FatUtility::int($shippingProfileId);

        $srSrch = ShippingRate::getSearchObject(CommonHelper::getLangId());
        $srSrch->addCondition('shiprate_condition_type', '=', 0);
        $srSrch->addCondition('shiprate_shipprozone_id', '=', $shipProZoneId);
        $rs = $srSrch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return $row['shiprate_id'];
        }

        $rates = [
            'shiprate_shipprozone_id' => $shipProZoneId,
            'shiprate_identifier' => Labels::getLabel('LBL_Standard', CommonHelper::getLangId()) . '-' . $shippingProfileId,
            'shiprate_condition_type' => 0,
            'shiprate_min_val' => 0,
            'shiprate_max_val' => 0,
        ];

        $shippingRate = new ShippingRate();
        $shippingRate->assignValues($rates);
        if (!$shippingRate->save()) {
            Message::addErrorMessage($shippingRate->getError());
        }

        return $shippingRate->getMainTableRecordId();
    }

    public static function getDefaultProfileId($userId, $shippingProfileId = 0)
    {
        $shippingProfileId = FatUtility::int($shippingProfileId);

        $srch = self::getSearchObject();
        $srch->addCondition('shipprofile_user_id', '=', $userId);
        $srch->addCondition('shipprofile_default', '=', 1);
        $srch->addFld('shipprofile_id');
        if (0 < $shippingProfileId) {
            $srch->addCondition('shipprofile_id', '=', $shippingProfileId);
        }
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $createDefaultShipProfile = false;
        if (empty($row)) {
            $createDefaultShipProfile = true;
        }

        $createDefaultRates = false;
        $createDefaultShipZone = false;
        if (true == $createDefaultShipProfile) {
            $shippingProfileId = self::setDefaultShipProfile($userId);
            $createDefaultShipZone = true;
            $createDefaultRates = true;
        }

        $shipProZoneId = 0;
        if (0 < $shippingProfileId && true == $createDefaultShipZone) {
            $shipProZoneId = self::setDefaultZone($userId, $shippingProfileId);
        }

        if (true == $createDefaultRates && 0 < $shipProZoneId && 0 < $shippingProfileId) {
            self::setDefaultRates($shipProZoneId, $shippingProfileId);
        }
        
        if (0 < $shippingProfileId && true == $createDefaultShipProfile) {
            $srch = new ProductSearch(CommonHelper::getLangId(), null, null, false, false);
            $srch->joinProductShippedBySeller($userId);
            if (User::canAddCustomProduct()) {
                $srch->addDirectCondition('((product_seller_id = 0 AND product_added_by_admin_id = ' . applicationConstants::YES . ' and psbs.psbs_user_id = ' . $userId . ') OR product_seller_id = ' . $userId . ')');
            } else {
                $cnd = $srch->addCondition('psbs.psbs_user_id', '=', $userId);
                $cnd->attachCondition('product_added_by_admin_id', '=', applicationConstants::YES, 'AND');
            }

            $srch->addCondition('product_type', '=', Product::PRODUCT_TYPE_PHYSICAL);
            $srch->addCondition('product_deleted', '=', applicationConstants::NO);
            if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
                $is_custom_or_catalog = FatApp::getPostedData('type', FatUtility::VAR_INT, -1);
                if ($is_custom_or_catalog > -1) {
                    if ($is_custom_or_catalog > 0) {
                        $srch->addCondition('product_seller_id', '>', 0);
                    } else {
                        $srch->addCondition('product_seller_id', '=', 0);
                    }
                }
            }

            if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                $shipProfileArr = ShippingProfile::getProfileArr($siteDefaultLangId, 0, true, true, true);
                $shippingProfileId = array_key_first($shipProfileArr);
                $userId = 0;
            }

            $srch->addMultipleFields(array($userId . ' as user_id', $shippingProfileId . ' as shipprofile_id', 'product_id'));
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addGroupBy('product_id');
            $tmpQry = $srch->getQuery();

            $qry = "INSERT INTO " . ShippingProfileProduct::DB_TBL . " (shippro_user_id, shippro_shipprofile_id, shippro_product_id) SELECT * FROM (" . $tmpQry . ") AS t ON DUPLICATE KEY UPDATE shippro_user_id = t.user_id, shippro_shipprofile_id = t.shipprofile_id, shippro_product_id = t.product_id";

            FatApp::getDb()->query($qry);
            return $shippingProfileId;
        }
        return $row['shipprofile_id'];
    }
}
