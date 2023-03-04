<?php

class Plugin extends MyAppModel
{
    public const DB_TBL = 'tbl_plugins';
    public const DB_TBL_PREFIX = 'plugin_';

    public const DB_TBL_LANG = 'tbl_plugins_lang';
    public const DB_TBL_LANG_PREFIX = 'pluginlang_';

    public const RETURN_FALSE  = 0;
    public const RETURN_TRUE  = 1;

    public const ENV_SANDBOX = 0;
    public const ENV_PRODUCTION = 1;

    public const ACTIVE  = 1;
    public const INACTIVE  = 0;

    public const TYPE_CURRENCY_CONVERTER = 1;
    public const TYPE_SOCIAL_LOGIN = 2;
    public const TYPE_PUSH_NOTIFICATION = 3;
    public const TYPE_PAYOUTS = 4;
    public const TYPE_ADVERTISEMENT_FEED = 5;
    public const TYPE_SMS_NOTIFICATION = 6;
    public const TYPE_FULL_TEXT_SEARCH = 7;
    public const TYPE_SHIPPING_SERVICES = 8;
    public const TYPE_TAX_SERVICES  = 10;
    public const TYPE_SPLIT_PAYMENT_METHOD  = 11;
    public const TYPE_REGULAR_PAYMENT_METHOD  = 13;
    public const TYPE_SHIPMENT_TRACKING = 14;

    /* Define here :  if system can activate only one plugin from any group.*/
    public const EITHER_GROUP_TYPE = [
        [
            self::TYPE_SPLIT_PAYMENT_METHOD,
            self::TYPE_REGULAR_PAYMENT_METHOD
        ],
    ];

    /* Define here :  if system can not activate multiple plugins for a same feature.*/
    public const HAVING_KINGPIN = [
        self::TYPE_CURRENCY_CONVERTER,
        self::TYPE_PUSH_NOTIFICATION,
        self::TYPE_ADVERTISEMENT_FEED,
        self::TYPE_SMS_NOTIFICATION,
        self::TYPE_TAX_SERVICES,
        // self::TYPE_FULL_TEXT_SEARCH, /* NOT IN USE */
        self::TYPE_SPLIT_PAYMENT_METHOD,
        self::TYPE_SHIPPING_SERVICES,
        self::TYPE_SHIPMENT_TRACKING,
    ];

    /* Payment Gateways Applicable For Pay Later. */
    public const PAY_LATER = [
        'CashOnDelivery',
        'PayAtStore'
    ];

    public const HAVING_SEPARATE_ICON = [
        self::TYPE_SPLIT_PAYMENT_METHOD,
        self::TYPE_REGULAR_PAYMENT_METHOD
    ];

    public const HAVING_DESCRIPTION = [
        self::TYPE_ADVERTISEMENT_FEED
    ];

    public const ATTRS = [
        self::DB_TBL_PREFIX . 'id',
        self::DB_TBL_PREFIX . 'code',
        self::DB_TBL_PREFIX . 'type',
        self::DB_TBL_PREFIX . 'description',
        'COALESCE(plg_l.' . self::DB_TBL_PREFIX . 'name, plg.' . self::DB_TBL_PREFIX . 'identifier) as plugin_name',
        self::DB_TBL_PREFIX . 'active',
    ];

    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->objMainTableRecord->setSensitiveFields(
            array('plugin_code')
        );
    }

    /**
     * getTypeArr - Used to get plugin type
     *
     * @param  mixed $langId
     * @return void
     */
    public static function getTypeArr($langId)
    {
        return [
            self::TYPE_CURRENCY_CONVERTER => Labels::getLabel('LBL_CURRENCY_CONVERTER', $langId),
            self::TYPE_SOCIAL_LOGIN => Labels::getLabel('LBL_SOCIAL_LOGIN', $langId),
            self::TYPE_PUSH_NOTIFICATION => Labels::getLabel('LBL_PUSH_NOTIFICATION', $langId),
            self::TYPE_PAYOUTS => Labels::getLabel('LBL_PAYOUT', $langId),
            self::TYPE_ADVERTISEMENT_FEED => Labels::getLabel('LBL_ADVERTISEMENT_FEED', $langId),
            self::TYPE_SMS_NOTIFICATION => Labels::getLabel('LBL_SMS_NOTIFICATION', $langId),
            self::TYPE_TAX_SERVICES => Labels::getLabel('LBL_TAX_SERVICES', $langId),
            // self::TYPE_FULL_TEXT_SEARCH => Labels::getLabel('LBL_FULL_TEXT_SEARCH', $langId), /* NOT IN USE */
            self::TYPE_SPLIT_PAYMENT_METHOD => Labels::getLabel('LBL_SPLIT_PAYMENT_METHODS', $langId),
            self::TYPE_REGULAR_PAYMENT_METHOD => Labels::getLabel('LBL_REGULAR_PAYMENT_METHODS', $langId),
            self::TYPE_SHIPPING_SERVICES => Labels::getLabel('LBL_SHIPPING_SERVICES', $langId),
            self::TYPE_SHIPMENT_TRACKING => Labels::getLabel('LBL_SHIPMENT_TRACKING', $langId),
        ];
    }

    /**
     * getDirectory - Used to get plugin directory
     *
     * @param  mixed $pluginType
     * @return mixed
     */
    public static function getDirectory(int $pluginType)
    {
        $pluginDir = [
            self::TYPE_CURRENCY_CONVERTER => "currency-converter",
            self::TYPE_SOCIAL_LOGIN => "social-login",
            self::TYPE_PUSH_NOTIFICATION => "push-notification",
            self::TYPE_ADVERTISEMENT_FEED => "advertisement-feed",
            self::TYPE_SMS_NOTIFICATION => "sms-notification",
            // self::TYPE_FULL_TEXT_SEARCH => "full-text-search", /* NOT IN USE */
            self::TYPE_TAX_SERVICES => "tax",
            self::TYPE_SPLIT_PAYMENT_METHOD => "payment-methods",
            self::TYPE_REGULAR_PAYMENT_METHOD => "payment-methods",
            self::TYPE_SHIPPING_SERVICES => "shipping-services",
            self::TYPE_SHIPMENT_TRACKING => "shipment-tracking",
        ];

        if (array_key_exists($pluginType, $pluginDir)) {
            return $pluginDir[$pluginType];
        }
        return false;
    }

    /**
     * getActivatationLimit
     *
     * @param  int $typeId
     * @return void
     */
    public static function getActivatationLimit(int $typeId): int
    {
        if (false === static::getDirectory($typeId)) {
            return -1;
        }

        $pluginTypeArr = [
            self::TYPE_REGULAR_PAYMENT_METHOD => 4,
        ];
        return array_key_exists($typeId, $pluginTypeArr) ? $pluginTypeArr[$typeId] : 0;
    }

    /**
     * getGroupType
     *
     * @param  mixed $pluginType
     * @return array
     */
    public static function getGroupType(int $pluginType): array
    {
        $groupArr = [];
        try {
            $eitherGroupTypes = Plugin::EITHER_GROUP_TYPE;
            array_walk($eitherGroupTypes, function ($group, $index) use ($pluginType, &$groupArr) {
                if (in_array($pluginType, $group)) {
                    $groupArr = $group;
                    throw new Exception();
                }
            });
        } catch (Exception $e) {
            // Do Nothing. Used Just to break array_walk.
        }
        return $groupArr;
    }

    /**
     * getEnvArr
     *
     * @param  mixed $langId
     * @return array
     */
    public static function getEnvArr(int $langId): array
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = CommonHelper::getLangId();
        }

        return [
            self::ENV_SANDBOX => Labels::getLabel('LBL_SANDBOX', $langId),
            self::ENV_PRODUCTION => Labels::getLabel('LBL_PRODUCTION', $langId),
        ];
    }

    /**
     * getSearchObject
     *
     * @param  int $langId
     * @param  bool $isActive
     * @param  bool $joinSettings
     * @return object
     */
    public static function getSearchObject(int $langId = 0, bool $isActive = true, bool $joinSettings = false): object
    {
        $srch = new SearchBase(static::DB_TBL, 'plg');
        if ($isActive == true) {
            $srch->addCondition('plg.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }
        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'plg_l.pluginlang_' . static::DB_TBL_PREFIX . 'id = plg.' . static::DB_TBL_PREFIX . 'id and plg_l.pluginlang_lang_id = ' . $langId,
                'plg_l'
            );
        }

        if (true === $joinSettings) {
            $srch->joinTable(
                PluginSetting::DB_TBL,
                'LEFT OUTER JOIN',
                'plgs.' . PluginSetting::DB_TBL_PREFIX . static::DB_TBL_PREFIX . 'id = plg.' . static::DB_TBL_PREFIX . 'id',
                'plgs'
            );
        }
        return $srch;
    }

    /**
     * isActive
     *
     * @param  string $code - Keyname
     * @return bool
     */
    public static function isActive(string $code): bool
    {
        return (0 < static::getAttributesByCode($code, self::DB_TBL_PREFIX . 'active') ? true : false);
    }

    /**
     * isActive
     *
     * @param  string $code - Keyname
     * @return bool
     */
    public static function isActiveByType(string $type): bool
    {
        $srch = new SearchBase(static::DB_TBL, 'plg');
        $srch->addCondition('plg.' . static::DB_TBL_PREFIX . 'type', '=', $type);
        $srch->addCondition('plg.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    /**
     * getAttributesByCode
     *
     * @param  string $code
     * @param  mixed $attr
     * @param  int $langId
     * @return mixed
     */
    public static function getAttributesByCode(string $code, $attr = '', int $langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'plg');
        $srch->addCondition('plg.' . static::DB_TBL_PREFIX . 'code', '=', $code);

        if (0 < $langId) {
            $srch->joinTable(self::DB_TBL_LANG, 'LEFT JOIN', self::DB_TBL_LANG_PREFIX . static::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_PREFIX . 'id and ' . self::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'plg_l');
        }

        if ('' != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (empty($row) || !is_array($row)) {
            return false;
        }

        if (!empty($attr) && is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    /**
     * pluginTypeSrchObj
     *
     * @param  int $typeId
     * @param  int $langId
     * @param  bool $customCols
     * @param  bool $active
     * @return object
     */
    private static function pluginTypeSrchObj(int $typeId, int $langId, bool $customCols = true, bool $active = false)
    {
        $srch = static::getSearchObject($langId, $active);
        if (false === $customCols) {
            $srch->addMultipleFields(self::ATTRS);
        }

        $srch->addCondition('plg.' . static::DB_TBL_PREFIX . 'type', '=', $typeId);
        return $srch;
    }

    /**
     * getDataByType
     *
     * @param  int $typeId
     * @param  int $langId
     * @param  bool $assoc
     * @param  bool $active
     * @return mixed
     */
    public static function getDataByType(int $typeId, int $langId = 0, bool $assoc = false, bool $active = true)
    {
        $typeId = FatUtility::int($typeId);
        if (1 > $typeId) {
            return false;
        }

        if (in_array($typeId, self::HAVING_KINGPIN) && empty((new self())->getDefaultPluginKeyName($typeId))) {
            return [];
        }

        $srch = static::pluginTypeSrchObj($typeId, $langId, $assoc, $active);

        if (true == $assoc) {
            $srch->addMultipleFields(
                [
                    'plg.' . static::DB_TBL_PREFIX . 'id',
                    'COALESCE(plg_l.' . static::DB_TBL_PREFIX . 'name, plg.' . static::DB_TBL_PREFIX . 'identifier) as plugin_name'
                ]
            );
        }
        $srch->addOrder('plugin_display_order', 'ASC');
        $rs = $srch->getResultSet();

        $db = FatApp::getDb();
        if (true == $assoc) {
            return $db->fetchAllAssoc($rs);
        }

        return $db->fetchAll($rs, static::DB_TBL_PREFIX . 'id');
    }

    /**
     * getNamesByType
     *
     * @param  int $typeId
     * @param  int $langId
     * @return mixed
     */
    public static function getNamesByType(int $typeId, int $langId)
    {
        $typeId = FatUtility::int($typeId);
        $langId = FatUtility::int($langId);
        if (1 > $typeId && 1 > $langId) {
            return false;
        }
        return static::getDataByType($typeId, $langId, true);
    }

    /**
     * getNamesWithCode
     *
     * @param  int $typeId
     * @param  int $langId
     * @return mixed
     */
    public static function getNamesWithCode(int $typeId, int $langId)
    {
        $typeId = FatUtility::int($typeId);
        $langId = FatUtility::int($langId);
        if (1 > $typeId && 1 > $langId) {
            return false;
        }
        $arr = [];
        $pluginsTypeArr = static::getDataByType($typeId, $langId);
        array_walk($pluginsTypeArr, function (&$value, &$key) use (&$arr) {
            $arr[$value['plugin_code']] = $value['plugin_name'];
        });
        return $arr;
    }

    /**
     * getSocialLoginPluginsStatus
     *
     * @param  int $langId
     * @return void
     */
    public static function getSocialLoginPluginsStatus(int $langId)
    {
        $srch = static::pluginTypeSrchObj(static::TYPE_SOCIAL_LOGIN, $langId);
        $srch->addMultipleFields(
            [
                'plg.' . static::DB_TBL_PREFIX . 'code',
                'plg.' . static::DB_TBL_PREFIX . 'active'
            ]
        );
        $rs = $srch->getResultSet();

        return FatApp::getDb()->fetchAllAssoc($rs);
    }

    /**
     * getDefaultPluginKeyName - Used for Kingpin plugins only
     *
     * @param  int $typeId
     * @return mixed
     */
    public function getDefaultPluginKeyName(int $typeId)
    {
        return $this->getDefaultPluginData($typeId, 'plugin_code');
    }

    /**
     * getDefaultPluginData - Used for Kingpin plugins only
     *
     * @param  int $typeId
     * @param  mixed $attr
     * @param  int $langId
     * @return mixed
     */
    public function getDefaultPluginData(int $typeId, $attr = null, int $langId = 0)
    {
        if (!in_array($typeId, self::HAVING_KINGPIN)) {
            $this->error = Labels::getLabel('MSG_INVALID_PLUGIN_TYPE', CommonHelper::getLangId());
            return false;
        }
        $kingPin = FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . $typeId, FatUtility::VAR_INT, 0);
        if (1 > $kingPin) {
            $this->error = Labels::getLabel('MSG_PLUGIN_NOT_FOUND', CommonHelper::getLangId());
            return false;
        }

        if (0 < $langId) {
            $customCols = !empty($attr) ? true : false;
            $srch = static::pluginTypeSrchObj($typeId, $langId, $customCols, true);

            if (!empty($attr)) {
                switch ($attr) {
                    case is_string($attr):
                        if ('plugin_name' == $attr) {
                            $attr = 'COALESCE(plg_l.' . static::DB_TBL_PREFIX . 'name, plg.' . static::DB_TBL_PREFIX . 'identifier) as plugin_name';
                        }
                        $srch->addFld($attr);
                        break;

                    default:
                        $srch->addMultipleFields($attr);
                        break;
                }
            }

            $rs = $srch->getResultSet();
            $result = FatApp::getDb()->fetch($rs);
            if (is_string($attr)) {
                return $result[$attr];
            }
            return $result;
        }
        return Plugin::getAttributesById($kingPin, $attr);
    }

    /**
     * canSendSms
     *
     * @param  string $tpl
     * @return bool
     */
    public static function canSendSms(string $tpl = ''): bool
    {
        $active = (new self())->getDefaultPluginData(Plugin::TYPE_SMS_NOTIFICATION, 'plugin_active');
        $status = empty($tpl) ? 1 : SmsTemplate::getTpl($tpl, 0, 'stpl_status');
        return (false != $active && !empty($active) && 0 < $status);
    }

    /**
     * updateStatus
     *
     * @param  int $typeId
     * @param  int $status
     * @param  int $id
     * @param  mixed $error
     * @return bool
     */
    public static function updateStatus(int $typeId, int $status, int $id = null, &$error = ''): bool
    {
        $db = FatApp::getDb();
        $langId = CommonHelper::getLangId();
        $pluginKey = Plugin::getAttributesById($id, 'plugin_code');
        $pluginTypesArr = static::getTypeArr($langId);
        $plugins = static::getDataByType($typeId, $langId);
        $activationLimit = static::getActivatationLimit($typeId);
        $payLater = self::PAY_LATER;

        $msg = '';
        if (self::TYPE_REGULAR_PAYMENT_METHOD == $typeId) {
            $msg = ' ' . Labels::getLabel('MSG_EXCEPT_PAY_LATER_PLUGINS.', $langId);
            $plugins = !$plugins ? [] : $plugins;
            $activatedPayLaterPlugins = 0;
            array_walk($plugins, function ($plugin) use (&$activationLimit, $pluginKey, $payLater, &$activatedPayLaterPlugins) {
                if (in_array($plugin['plugin_code'], $payLater)) {
                    $activatedPayLaterPlugins++;
                }

                if (in_array($pluginKey, $payLater) && in_array($plugin['plugin_code'], $payLater)) {
                    $activationLimit++;
                    return;
                }
            });

            if (!in_array($pluginKey, $payLater) && $activationLimit == count($plugins) && $activatedPayLaterPlugins == count($payLater)) {
                $activationLimit++;
            }
        }

        if (0 < $activationLimit && $activationLimit <= count($plugins) && self::ACTIVE == $status) {
            $msg = Labels::getLabel("MSG_MAXIMUM_OF_{LIMIT}_{PLUGIN-TYPE}_CAN_BE_ACTIVATED_SIMULTANEOUSLY.", $langId) . $msg;
            $error = CommonHelper::replaceStringData($msg, ['{LIMIT}' => $activationLimit, '{PLUGIN-TYPE}' => $pluginTypesArr[$typeId]]);
            return false;
        }

        $max = in_array($typeId, self::HAVING_KINGPIN) && applicationConstants::ACTIVE == $status ? 2 : 1;

        for ($i = 0; $i < $max; $i++) {
            $condition = ['smt' => self::DB_TBL_PREFIX . 'type = ?', 'vals' => [$typeId]];
            if (null != $id) {
                $operator = (0 < $i ? '!=' : '=');
                $condition = ['smt' => self::DB_TBL_PREFIX . 'type = ? AND ' . self::DB_TBL_PREFIX . 'id ' . $operator . ' ?', 'vals' => [$typeId, $id]];
            }
            if (!$db->updateFromArray(self::DB_TBL, [self::DB_TBL_PREFIX . 'active' => (0 < $i ? self::INACTIVE : $status)], $condition)) {
                $error = $db->getError();
                return false;
            }
        }

        if (in_array($typeId, self::HAVING_KINGPIN)) {
            $kingPin = (self::INACTIVE == $status) ? self::INACTIVE : $id;
            
            $assignValues = [
                'conf_name' => 'CONF_DEFAULT_PLUGIN_' . $typeId,
                'conf_val' => $kingPin
            ];
            if (false === $db->insertFromArray(
                'tbl_configurations',
                $assignValues,
                false,
                array(),
                $assignValues
            )) {
                $error = $db->getError();
                return false;
            }
        }
        return true;
    }
}
