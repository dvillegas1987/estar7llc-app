<?php

class Tax extends MyAppModel
{
    public const DB_TBL = 'tbl_tax_categories';
    public const DB_TBL_PREFIX = 'taxcat_';

    public const DB_TBL_LANG = 'tbl_tax_categories_lang';
    public const DB_TBL_LANG_PREFIX = 'taxcatlang_';

    public const DB_TBL_PRODUCT_TO_TAX = 'tbl_product_to_tax';
    public const DB_TBL_PRODUCT_TO_TAX_PREFIX = 'ptt_';

    private const TAX_RATE_CACHE_KEY_NAME = "taxRateCache_";

    private $db;

    public const TYPE_PERCENTAGE = 1;
    public const TYPE_FIXED = 0;

    public const TAX_TYPE_COMBINED = 1;

    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    /**
     * getFieldTypeArr
     *
     * @param  int $langId
     * @return array
     */
    public static function getFieldTypeArr(int $langId): array
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr = array(
            static::TYPE_PERCENTAGE => Labels::getLabel('LBL_PERCENTAGE', $langId),
            static::TYPE_FIXED => Labels::getLabel('LBL_FIXED', $langId),
        );
        return $arr;
    }

    /**
     * getSearchObject
     *
     * @return object
     */
    public static function getSearchObject(int $langId = 0, bool $isActive = true): object
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 't');

        if ($isActive == true) {
            $srch->addCondition('t.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                't_l.' . static::DB_TBL_LANG_PREFIX . 'taxcat_id = t.' . static::tblFld('id') . ' and
            t_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                't_l'
            );
        }
        return $srch;
    }

    /**
     * getSaleTaxCatArr
     *
     * @param  int $langId
     * @param  bool $isActive
     * @return array
     */
    public static function getSaleTaxCatArr(int $langId, bool $isActive = true): array
    {
        $langId = FatUtility::int($langId);

        $srch = static::getSearchObject($langId, $isActive);
        $srch->addCondition('taxcat_deleted', '=', 0);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $activatedTaxServiceId = Tax::getActivatedServiceId();

        $srch->addFld('taxcat_id');
        if (0 < $activatedTaxServiceId) {
            $srch->addFld('concat(IFNULL(taxcat_name, taxcat_identifier), " (",taxcat_code,")")as taxcat_name');
        } else {
            $srch->addFld('IFNULL(taxcat_name,taxcat_identifier)as taxcat_name');
        }
        $srch->addCondition('taxcat_plugin_id', '=', $activatedTaxServiceId);

        $res = $srch->getResultSet();
        if (!$res) {
            return array();
        }
        return FatApp::getDb()->fetchAllAssoc($res);
    }

    /**
     * getTaxCatObjByProductId
     *
     * @param  int $productId
     * @param  int $langId
     * @return object
     */
    public static function getTaxCatObjByProductId(int $productId, int $langId = 0): object
    {
        $srch = static::getSearchObject($langId);
        $srch->joinTable(
            static::DB_TBL_PRODUCT_TO_TAX,
            'LEFT OUTER JOIN',
            'ptt.ptt_taxcat_id = t.taxcat_id',
            'ptt'
        );

        $srch->addCondition('taxcat_deleted', '=', 0);
        $srch->addCondition('ptt_product_id', '=', FatUtility::int($productId));
        return $srch;
    }

    /**
     * addUpdateProductTaxCat
     *
     * @param  array $data
     * @return bool
     */
    public function addUpdateProductTaxCat(array $data): bool
    {
        if (0 >= Fatutility::int($data['ptt_product_id']) || 0 >= Fatutility::int($data['ptt_taxcat_id'])) {
            return false;
        }
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_PRODUCT_TO_TAX, $data, false, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    /**
     * addUpdateData
     *
     * @param  array $data
     * @return bool
     */
    public function addUpdateData(array $data): bool
    {
        unset($data['taxcat_id']);
        $assignValues = array(
            'taxcat_identifier' => $data['taxcat_identifier'],
            'taxcat_active' => $data['taxcat_active'],
            'taxcat_deleted' => 0,
            'taxcat_last_updated' => date('Y-m-d H:i:s'),
            'taxcat_code' => array_key_exists('taxcat_code', $data) ? $data['taxcat_code'] : '',
            'taxcat_plugin_id' => array_key_exists('taxcat_plugin_id', $data) ? $data['taxcat_plugin_id'] : 0,
        );

        if ($this->mainTableRecordId > 0) {
            $assignValues['taxcat_id'] = $this->mainTableRecordId;
        }

        $record = new TableRecord(self::DB_TBL);
        $record->assignValues($assignValues);

        if (!$record->addNew(array(), $assignValues)) {
            $this->error = $record->getError();
            return false;
        }

        $this->mainTableRecordId = $record->getId();
        return true;
    }

    /**
     * canRecordMarkDelete
     *
     * @param  int $id
     * @return bool
     */
    public function canRecordMarkDelete(int $id): bool
    {
        $srch = $this->getSearchObject(0, false);
        $srch->addCondition('t.' . static::DB_TBL_PREFIX . 'id', '=', $id);
        $srch->joinTable(static::DB_TBL_PRODUCT_TO_TAX, 'INNER JOIN', 'tpt.ptt_taxcat_id = t.taxcat_id', 'tpt');
        $srch->addFld('t.' . static::DB_TBL_PREFIX . 'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        return empty($row);
    }

    /**
     * getTaxRates
     *
     * @param  int $productId
     * @param  int $userId
     * @param  int $langId
     * @param  int $userCountry
     * @param  int $userState
     * @return array
     */
    public function getTaxRates(int $productId, int $userId, int $langId, int $userCountry = 0, int $userState = 0): array
    {
        $productId = Fatutility::int($productId);
        $userId = Fatutility::int($userId);
        $langId = Fatutility::int($langId);
        $activatedTaxServiceId = static::getActivatedServiceId();
        $taxRates = array();
        $srch = self::getTaxCatObjByProductId($productId, $langId);
        $srch->addCondition('ptt_product_id', '=', $productId, 'AND');
        if (0 == $activatedTaxServiceId) {
            $srch->joinTable(TaxRuleLocation::DB_TBL, 'LEFT JOIN', 'taxLoc.taxruleloc_taxcat_id = ptt_taxcat_id', 'taxLoc');
            $srch->joinTable(TaxRule::DB_TBL, 'LEFT JOIN', 'taxRule.taxrule_id = taxLoc.taxruleloc_taxrule_id', 'taxRule');

            if ($userCountry > 0 && $userState <= 0) {
                $cond = $srch->addCondition('taxruleloc_country_id', '=', $userCountry, 'AND');
                $cond->attachCondition('taxruleloc_country_id', '=', -1, 'OR');
            }
            if ($userState > 0) {
                $srch->addDirectCondition('(taxruleloc_country_id = -1 or (taxruleloc_country_id = ' . $userCountry . ' and ((taxruleloc_type = ' . TaxRule::TYPE_INCLUDE_STATES . ' AND taxruleloc_state_id = ' . $userState . ') OR (taxruleloc_type = ' . TaxRule::TYPE_ALL_STATES . ' AND taxruleloc_state_id = -1) OR (taxruleloc_type = ' . TaxRule::TYPE_EXCLUDE_STATES . ' AND taxruleloc_state_id != ' . $userState . ' and (select count(*) from ' . TaxRuleLocation::DB_TBL . ' where taxruleloc_type = ' . TaxRule::TYPE_EXCLUDE_STATES . ' and taxruleloc_state_id = ' . $userState . ' and taxruleloc_taxcat_id = ptt.ptt_taxcat_id) = 0))))', 'AND');
            }
            $srch->addMultipleFields(array('*', '(CASE WHEN taxruleloc_type = ' . TaxRule::TYPE_ALL_STATES . ' and taxruleloc_country_id = -1 THEN 99 WHEN taxruleloc_type = ' . TaxRule::TYPE_ALL_STATES . ' and taxruleloc_country_id = ' . $userCountry . ' THEN 98 ELSE taxruleloc_type END) AS displayOrder'));
            $srch->addGroupBy('taxrule_id');
            $srch->addOrder('displayOrder', 'ASC');
        }
        //echo $srch->getQuery();
        $srch->setPageSize(1);
        $res = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($res);
        if (!is_array($row)) {
            return array();
        }
        //CommonHelper::printArray($row);
        return $row;
    }

    /**
     * formatAddress
     *
     * @param  array $address
     * @param  string $type
     * @return array
     */
    private function formatAddress(array $address, string $type = ''): array
    {
        $postalCode = '';
        $line1 = '';
        $line2 = '';
        $city = '';
        $state = '';
        $stateCode = '';
        $country = '';
        $countryCode = '';

        switch (strtolower($type)) {
            case 'order':
                $postalCode = array_key_exists('oua_zip', $address) ? $address['oua_zip'] : $postalCode;
                $line1 = array_key_exists('oua_address1', $address) ? $address['oua_address1'] : $line1;
                $line2 = array_key_exists('oua_address2', $address) ? $address['oua_address2'] : $line2;
                $city = array_key_exists('oua_city', $address) ? $address['oua_city'] : $city;
                $state = array_key_exists('oua_state', $address) ? $address['oua_state'] : $state;
                $country = array_key_exists('oua_country', $address) ? $address['oua_country'] : $country;
                $stateCode = array_key_exists('oua_state_code', $address) ? $address['oua_state_code'] : $stateCode;
                $countryCode = array_key_exists('oua_country_code', $address) ? $address['oua_country_code'] : $countryCode;
                break;
            case 'shop':
                $postalCode = array_key_exists('shop_postalcode', $address) ? $address['shop_postalcode'] : $postalCode;
                $line1 = array_key_exists('shop_address_line_1', $address) ? $address['shop_address_line_1'] : $line1;
                $line2 = array_key_exists('shop_address_line_2', $address) ? $address['shop_address_line_2'] : $line2;
                $city = array_key_exists('shop_city', $address) ? $address['shop_city'] : $city;
                $state = array_key_exists('state_name', $address) ? $address['state_name'] : $state;
                $stateCode = array_key_exists('state_code', $address) ? $address['state_code'] : $stateCode;
                $country = array_key_exists('country', $address) ? $address['country'] : $country;
                $countryCode = array_key_exists('country_code', $address) ? $address['country_code'] : $countryCode;
                break;
            default:
                $postalCode = array_key_exists('addr_zip', $address) ? $address['addr_zip'] : $postalCode;
                $line1 = array_key_exists('addr_address1', $address) ? $address['addr_address1'] : $line1;
                $line2 = array_key_exists('addr_address2', $address) ? $address['addr_address2'] : $line2;
                $city = array_key_exists('city', $address) ? $address['city'] : $city;
                $city = array_key_exists('addr_city', $address) ? $address['addr_city'] : $city;
                $state = array_key_exists('state', $address) ? $address['state'] : $state;
                $state = array_key_exists('state_name', $address) ? $address['state_name'] : $state;
                $stateCode = array_key_exists('state_code', $address) ? $address['state_code'] : $stateCode;
                $country = array_key_exists('country', $address) ? $address['country'] : $country;
                $country = array_key_exists('country_name', $address) ? $address['country_name'] : $country;
                $countryCode = array_key_exists('country_code', $address) ? $address['country_code'] : $countryCode;
                break;
        }

        return [
            'line1' => $line1,
            'line2' => $line2,
            'city' => $city,
            'state' => empty($state) ? $stateCode : $state,
            'stateCode' => empty($stateCode) ? $state : $stateCode,
            'postalCode' => $postalCode,
            'country' => isset($country) ? $country : $countryCode,
            'countryCode' => $countryCode,
        ];
    }

    /**
     * calculateTaxRates
     *
     * @param int $productId
     * @param float $prodPrice
     * @param int $sellerId
     * @param int $langId
     * @param int $qty
     * @param int $type
     * @param array $extraInfo
     * @param bool $useCache
     * @return array
     */
    public function calculateTaxRates(int $productId, float $prodPrice, int $sellerId, int $langId, int $qty = 1, array $extraInfo = array(), bool $useCache = false): array
    {
        $tax = 0;
        $defaultTaxName = Labels::getLabel('LBL_Tax', $langId);

        $activatedTaxServiceId = static::getActivatedServiceId();

        $shipFromStateId = 0;
        $shipToStateId = 0;
        $shipToCountryId = 0;

        if (isset($extraInfo['shippingAddress']['addr_country_id'])) {
            $shipToCountryId = FatUtility::int($extraInfo['shippingAddress']['addr_country_id']);
        }

        if (isset($extraInfo['shippingAddress']['addr_state_id'])) {
            $shipToStateId = FatUtility::int($extraInfo['shippingAddress']['addr_state_id']);
        }

        if (array_key_exists('shippingAddress', $extraInfo)) {
            $shopInfo = Shop::getAttributesByUserId($sellerId, array('shop_state_id', 'shop_id', 'shop_identifier'));
            $shipFromStateId = $shopInfo['shop_state_id'];
        }

        $taxCategoryRow = $this->getTaxRates($productId, $sellerId, $langId, $shipToCountryId, $shipToStateId);

        if (empty($taxCategoryRow)) {
            $message = Labels::getLabel('MSG_INVALID_TAX_CATEGORY', $langId);
            if (isset($shopInfo['shop_identifier'])) {
                $message .= '(' . $shopInfo['shop_identifier'] . ')';
                $message .= '( Product Id-' . $productId . ')';
            }
            SystemLog::set($message);
            $status = (!CONF_DEVELOPMENT_MODE) ? true : false;
            return $data = [
                'status' => $status,
                'msg' => $message,
                'tax' => $tax,
                'rate' => 0,
                'optionsSum' => $tax,
                'taxCode' => '',
                'options' => []
            ];
        }
        $taxCatName = !empty($taxCategoryRow['taxcat_name']) ? $taxCategoryRow['taxcat_name'] : $taxCategoryRow['taxcat_identifier'];
        $taxCatCode = !empty($taxCategoryRow['taxcat_code']) ? $taxCategoryRow['taxcat_code'] : $taxCatName;
        $taxCategoryRow['taxcat_code'] = $taxCatCode;

        $arr  = [
            'productId' => $productId,
            'prodPrice' => $prodPrice,
            'sellerId' => $sellerId,
            'langId' =>   $langId,
            'qty' => $qty,
            'shipFromStateId' => $shipFromStateId,
            'shipToStateId' => $shipToStateId,
            'extraInfo' => $extraInfo,
            'taxCategoryRow' => $taxCategoryRow
        ];
        $cacheKey = self::TAX_RATE_CACHE_KEY_NAME . md5(json_encode($arr));

        global $taxRatesArr;
        if (0 < $activatedTaxServiceId && !empty($extraInfo) && $extraInfo['shippingAddress'] != '') {
            if (true == $useCache) {
                $rates = FatCache::get('taxCharges' . $cacheKey, CONF_API_REQ_CACHE_TIME, '.txt');
                if ($rates) {
                    return unserialize($rates);
                }
            }

            if (isset($taxRatesArr[$cacheKey]['values'])) {
                return $taxRatesArr[$cacheKey]['values'];
            }

            $pluginKey = Plugin::getAttributesById($activatedTaxServiceId, 'plugin_code');

            $error = '';
            if (false === PluginHelper::includePlugin($pluginKey, Plugin::getDirectory(Plugin::TYPE_TAX_SERVICES), $error, $langId)) {
                SystemLog::set($error);
                $status = (!CONF_DEVELOPMENT_MODE) ? true : false;
                return $data = [
                    'status' => $status,
                    'msg' => $error,
                    'tax' => $tax,
                    'rate' => 0,
                    'optionsSum' => $tax,
                    'taxCode' => $taxCategoryRow['taxcat_code'],
                    'options' => []
                ];
            }

            $toAddress = $this->formatAddress($extraInfo['shippingAddress']);

            if ($extraInfo['shippedBySeller']) {
                /* @todo check to get with seller_address */
                $fields = array('shop_postalcode', 'shop_address_line_1', 'shop_address_line_2', 'shop_city', 'state_name', 'state_code', 'country_code');
                $address = Shop::getShopAddress($shopInfo['shop_id'], true, $langId, $fields);
                $fromAddress = $this->formatAddress($address, 'shop');
            } else {
                $fromAddress = Admin::getAddress($langId);
                $shipFromStateId = FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 0);
            }
            $toAddress = $this->formatAddress($extraInfo['shippingAddress']);

            $itemsArr = [];
            $item = [
                'amount' => $prodPrice,
                'quantity' => $qty,
                'itemCode' => $productId,
                'taxCode' => $taxCategoryRow['taxcat_code'],
            ];
            array_push($itemsArr, $item);

            $shippingItems = [];
            $shippingItem = [
                'amount' => $extraInfo['shippingCost'],
                'quantity' => 1,
                'itemCode' => 'S-' . $productId,
                /*'taxCode' => $taxCategoryRow['taxcat_code'],*/
                'taxCode' => '',

            ];
            array_push($shippingItems, $shippingItem);

            $taxApi = new $pluginKey($langId, $fromAddress, $toAddress);
            if (false === $taxApi->init()) {
                SystemLog::set($taxApi->getError());
                $status = (!CONF_DEVELOPMENT_MODE) ? true : false;
                return $data = [
                    'status' => $status,
                    'msg' => $taxApi->getError(),
                    'tax' => $tax,
                    'rate' => 0,
                    'taxCode' => $taxCategoryRow['taxcat_code'],
                    'options' => []
                ];
            }

            $buyerId = FatUtility::int($extraInfo['buyerId']);
            $taxRates = $taxApi->getRates($itemsArr, $shippingItems, $buyerId);

            if (false == $taxRates['status']) {
                SystemLog::set($taxRates['msg'] . '( Product Id-' . $productId . ')');
                $status = (!CONF_DEVELOPMENT_MODE) ? true : false;
                $data = [
                    'status' => $status,
                    'msg' => $taxRates['msg'],
                    'tax' => $tax,
                    'rate' => 0,
                    'optionsSum' => $tax,
                    'taxCode' => $taxCategoryRow['taxcat_code'],
                    'options' => []
                ];
                $taxRatesArr[$cacheKey]['values'] = $data;
                FatCache::set('taxCharges' . $cacheKey, serialize($data), '.txt');
                return  $data;
            }

            $data = [
                'status' => true,
                'tax' => 0,
                'optionsSum' => 0,
                'rate' => 0,
                'taxCode' => $taxCategoryRow['taxcat_code'],
                'options' => []
            ];

            foreach ($taxRates['data'] as $code => $rate) {
                $data['tax'] = $data['tax'] + $rate['tax'];
                $data['optionsSum'] = $data['optionsSum'] + $rate['tax'];
                $data['rate'] = $data['rate'] + $rate['rate'];
                foreach ($rate['taxDetails'] as $name => $val) {
                    $data['options'][$name]['name'] = $val['name'];
                    $data['options'][$name]['percentageValue'] = isset($val['rate']) ? $val['rate'] : 0;
                    $data['options'][$name]['inPercentage'] = TAX::TYPE_PERCENTAGE;
                    if (isset($data['options'][$name]['value'])) {
                        $data['options'][$name]['value'] = $data['options'][$name]['value'] + $val['value'];
                    } else {
                        $data['options'][$name]['value'] = $val['value'];
                    }
                }
            }
            $taxRatesArr[$cacheKey]['values'] = $data;
            FatCache::set('taxCharges' . $cacheKey, serialize($data), '.txt');

            return $data;
        }

        if (0 < $activatedTaxServiceId) {
            SystemLog::set(Labels::getLabel('MSG_INVALID_TAX_CATEGORY', $langId) . '( Product Id-' . $productId . ')');
            $status = (!CONF_DEVELOPMENT_MODE) ? true : false;

            return $data = [
                'status' => $status,
                'msg' => Labels::getLabel('MSG_INVALID_TAX_CATEGORY', $langId),
                'tax' => $tax,
                'rate' => 0,
                'optionsSum' => $tax,
                'taxCode' => '',
                'options' => []
            ];
        }
        $tax = round((($prodPrice * $qty) * $taxCategoryRow['taxrule_rate']) / 100, 2);

        $data['tax'] = $tax;
        $data['rate'] = $taxCategoryRow['taxrule_rate'];
        $data['optionsSum'] = $tax;
        $optionsSum = 0;
        $data['taxCode'] = $taxCategoryRow['taxcat_code'];

        $srch = TaxRuleCombined::getSearchObject();
        $srch->joinTable(TaxStructure::DB_TBL, 'INNER JOIN', 'taxruledet_taxstr_id = taxstr_id');
        $srch->joinTable(TaxStructure::DB_TBL_LANG, 'LEFT JOIN', 'taxruledet_taxstr_id = taxstrlang_taxstr_id and taxstrlang_lang_id = ' . $langId);
        $srch->addCondition('taxruledet_taxrule_id', '=', $taxCategoryRow['taxrule_id']);
        $srch->addMultipleFields(array('taxstr_id', 'taxruledet_id', 'taxruledet_rate', 'IFNULL(taxstr_name, taxstr_identifier) as taxstr_name'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $combinedData = FatApp::getDb()->fetchAll($srch->getResultSet());

        if (!empty($combinedData)) {
            foreach ($combinedData as $comData) {
                $taxval = round((($prodPrice * $qty) * $comData['taxruledet_rate']) / 100, 2);
                $optionsSum += $taxval;
                $data['options'][$comData['taxruledet_id']]['taxstr_id'] = $comData['taxstr_id'];
                $data['options'][$comData['taxruledet_id']]['name'] = isset($comData['taxstr_name']) ? $comData['taxstr_name'] : $defaultTaxName;
                $data['options'][$comData['taxruledet_id']]['percentageValue'] = $comData['taxruledet_rate'];
                $data['options'][$comData['taxruledet_id']]['inPercentage'] = 1;
                $data['options'][$comData['taxruledet_id']]['value'] = $taxval;
            }
        }
        $data['optionsSum'] = $optionsSum;
        $data['status'] = true;
        return $data;
    }

    /**
     * createInvoice
     *
     * @param  array $childOrderInfo
     * @return bool
     */
    public function createInvoice(array $childOrderInfo): bool
    {
        $activatedTaxServiceId = static::getActivatedServiceId();
        if (!$activatedTaxServiceId) {
            return true;
        }
        $langId = $childOrderInfo['oplang_lang_id'];
        $pluginKey = Plugin::getAttributesById($activatedTaxServiceId, 'plugin_code');

        $error = '';
        if (false === PluginHelper::includePlugin($pluginKey, Plugin::getDirectory(Plugin::TYPE_TAX_SERVICES), $error, $langId)) {
            $this->error = $error;
            return false;
        }

        if (0 < $childOrderInfo['opshipping_by_seller_user_id']) {
            /* @todo check to get with seller_address */
            $fields = array('shop_postalcode', 'shop_address_line_1', 'shop_address_line_2', 'shop_city', 'state_name', 'state_code', 'country_code');
            $address = Shop::getShopAddress($childOrderInfo['op_shop_id'], true, $langId, $fields);
            $fromAddress = $this->formatAddress($address, 'shop');
        } else {
            $fromAddress = Admin::getAddress($langId);
        }

        $orderObj = new Orders();
        $addresses = $orderObj->getOrderAddresses($childOrderInfo['order_id']);

        $toAddress = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : $addresses[Orders::BILLING_ADDRESS_TYPE];
        $toAddress = $this->formatAddress($toAddress, 'order');

        $couponDiscount = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]) ? $childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount'] : 0;
        $volumeDiscount = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]) ? $childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount'] : 0;
        $rewardPointDiscount = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]) ? $childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount'] : 0;

        $discount = abs($couponDiscount) + abs($rewardPointDiscount) + abs($volumeDiscount);
        if (0 < $childOrderInfo['op_refund_qty']) {
            $discountPerQauntity = $discount / $childOrderInfo['op_qty'];
            $discount = $discountPerQauntity * ($childOrderInfo['op_qty'] - $childOrderInfo['op_refund_qty']);
        }

        $quantity = $childOrderInfo['op_qty'] - $childOrderInfo['op_refund_qty'];

        $salesTax = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_TAX]) ? $childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount'] : 0;
        if (0 < $childOrderInfo['op_refund_qty']) {
            $salesTaxPerQuantity = $salesTax / $childOrderInfo['op_qty'];
            $salesTax = $salesTaxPerQuantity * $quantity;
        }

        $shippingAmount = CommonHelper::orderProductAmount($childOrderInfo, 'SHIPPING') - $childOrderInfo['op_refund_shipping'];

        $itemsArr = [];
        $item = [
            'amount' => $childOrderInfo['op_unit_price'],
            'quantity' => $quantity,
            'productName' => $childOrderInfo['op_selprod_title'],
            'description' => $childOrderInfo['op_product_name'],
            'itemCode' => $childOrderInfo['op_id'],
            'taxCode' => $childOrderInfo['op_tax_code'],
            'salesTax' => $salesTax,
            'discount' => abs($discount)
        ];
        array_push($itemsArr, $item);

        $shippingItems = [];
        $shippingItem = [
            'amount' => $shippingAmount,
            'quantity' => 1,
            'itemCode' => 'S-' . $childOrderInfo['op_id'],
            'taxCode' => $childOrderInfo['op_tax_code'],
        ];
        array_push($shippingItems, $shippingItem);

        $taxApi = new $pluginKey($langId, $fromAddress, $toAddress);
        if (false === $taxApi->init()) {
            $this->error = $taxApi->getError();
            return false;
        }

        $taxRates = $taxApi->createInvoice($itemsArr, $shippingItems, $childOrderInfo['op_selprod_user_id'], $childOrderInfo['order_date_added'], $childOrderInfo['op_invoice_number']);

        if (false == $taxRates['status']) {
            $this->error = $taxRates['msg'];
            return false;
        }

        return true;
    }

    /**
     * getTaxCatByProductId
     *
     * @param  int $productId
     * @param  int $userId
     * @param  int $langId
     * @param  array $fields
     * @return array
     */
    public static function getTaxCatByProductId(int $productId, int $userId = 0, int $langId = 0, array $fields = array()): array
    {
        $taxData = array();
        $taxObj = static::getTaxCatObjByProductId($productId, $langId);
        $taxObj->addCondition('ptt_seller_user_id', '=', $userId);
        if ($fields) {
            $taxObj->addMultipleFields($fields);
        }
        $taxObj->doNotCalculateRecords();
        $taxObj->doNotLimitRecords();
        $res = $taxObj->getResultSet();
        $taxData = FatApp::getDb()->fetch($res);
        if (!$taxData) {
            return array();
        }
        return $taxData;
    }

    /**
     * removeTaxSetByAdmin
     *
     * @param  int $productId
     * @return bool
     */
    public function removeTaxSetByAdmin(int $productId): bool
    {
        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_PRODUCT_TO_TAX, array('smt' => 'ptt_seller_user_id = ? and ptt_product_id = ?', 'vals' => array(0, $productId)))) {
            return false;
        }
        return true;
    }

    /**
     * getActivatedServiceId
     *
     * @return int
     */
    public static function getActivatedServiceId(): int
    {
        $pluginObj = new Plugin();
        return (int) $pluginObj->getDefaultPluginData(Plugin::TYPE_TAX_SERVICES, 'plugin_id');
    }

    /**
     * getAttributesByCode
     *
     * @param  string $code
     * @param  array $attr
     * @param  int $plugInId
     * @return array
     */
    public static function getAttributesByCode(string $code, array $attr = null, int $plugInId = 0): array
    {
        $code = FatUtility::convertToType($code, FatUtility::VAR_STRING);
        $plugInId = FatUtility::convertToType($plugInId, FatUtility::VAR_INT);
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(static::tblFld('code'), '=', $code);
        $srch->addCondition(static::tblFld('plugin_id'), '=', $plugInId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $res = $srch->getResultSet();
        $row = $db->fetch($res);

        if (!is_array($row)) {
            return array();
        }

        if (is_string($attr)) {
            return $row[$attr];
        }

        return $row;
    }
}
