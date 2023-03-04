<?php

class PaymentMethodBase extends PluginBase
{
    public $userData = [];
    public $userMeta;
    public $userInfoColumns = [];

    private $commonColumns = [
        'user_name',
        'credential_email',
        'country_code',
        'country_code_alpha3',
        'state_code',
        'IFNULL(country_name, country_code) as country_name',
        'IFNULL(state_name, state_identifier) as state_name',
    ];

    private $buyerInfoColumns = [
        'addr_title',
        'addr_name',
        'addr_address1',
        'addr_address2',
        'addr_city',
        'addr_phone',
        'addr_zip'
    ];

    private $sellerInfoColumns = [
        'shop_phone',
        'shop_phone_dcode',
        'shop_id',
        'shop_postalcode',
        'IFNULL(shop_name, shop_identifier) as shop_name',
        'shop_description',
        'shop_address_line_1',
        'shop_address_line_2',
        'shop_city',
    ];
    
    /**
     * loadLoggedUserInfo
     *
     * @param int $userId
     * @return bool
     */
    public function loadLoggedUserInfo(int $userId): bool
    {
        if (is_array($this->userData) && 0 < count($this->userData)) {
            return true;
        }

        $this->userId = $userId;
        $this->userId = FatUtility::int($this->userId);
        if (1 > $this->userId) {
            $this->error = Labels::getLabel('MSG_INVALID_USER', $this->langId);
            return false;
        }
        $this->userInfoColumns = array_merge($this->commonColumns, $this->buyerInfoColumns);
        $srch = User::getSearchObject();
        $srch->joinTable(Address::DB_TBL, 'LEFT JOIN', 'ad.addr_record_id = u.user_id AND ad.addr_is_default = 1', 'ad');
        $srch->joinTable(Countries::DB_TBL, 'LEFT JOIN', 'ad.addr_country_id = c.country_id', 'c');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT JOIN', 'c.country_id = c_l.countrylang_country_id AND countrylang_lang_id = ' . $this->langId, 'c_l');
        $srch->joinTable(States::DB_TBL, 'LEFT JOIN', 'ad.addr_state_id = s.state_id', 's');
        $srch->joinTable(States::DB_TBL_LANG, 'LEFT JOIN', 's.state_id = s_l.statelang_state_id AND statelang_lang_id = ' . $this->langId, 's_l');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT JOIN', 'uc.' . User::DB_TBL_CRED_PREFIX . 'user_id = u.user_id', 'uc');
        $srch->addMultipleFields($this->userInfoColumns);
        $srch->addCondition('user_id', '=', $this->userId);
        $rs = $srch->getResultSet();
        $this->formatUserData((array) FatApp::getDb()->fetch($rs));
        return true;
    }
    
    /**
     * loadSellerInfo
     *
     * @param  int $userId
     * @return bool
     */
    public function loadSellerInfo(int $userId): bool
    {
        if (is_array($this->userData) && 0 < count($this->userData)) {
            return true;
        }

        $this->userId = $userId;
        $this->userId = FatUtility::int($this->userId);
        if (1 > $this->userId) {
            $this->error = Labels::getLabel('MSG_INVALID_USER', $this->langId);
            return false;
        }
        $this->userInfoColumns = array_merge($this->commonColumns, $this->sellerInfoColumns);
        $srch = User::getSearchObject();
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sh.shop_user_id', 'sh');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sh.shop_id = sh_l.shoplang_shop_id AND shoplang_lang_id = ' . $this->langId, 'sh_l');
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'sh.shop_country_id = c.country_id', 'c');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c.country_id = c_l.countrylang_country_id AND countrylang_lang_id = ' . $this->langId, 'c_l');
        $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'sh.shop_state_id = s.state_id', 's');
        $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 's.state_id = s_l.statelang_state_id AND statelang_lang_id = ' . $this->langId, 's_l');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.' . User::DB_TBL_CRED_PREFIX . 'user_id = u.user_id', 'uc');
        $srch->addMultipleFields($this->userInfoColumns);
        $srch->addCondition('user_id', '=', $this->userId);
        $rs = $srch->getResultSet();
        $this->formatUserData((array) FatApp::getDb()->fetch($rs));
        return true;
    }
    
    /**
     * formatUserData
     *
     * @param  array $data
     * @return void
     */
    private function formatUserData(array $data): void
    {
        if (empty($this->userData) || false === $this->userData) {
            /* Delete all values from an array while keeping thier keys. */
            $this->userData = array_map(function() {}, array_flip($this->userInfoColumns));
        }
        $this->userData = $data;
    }

    /**
     * loadBaseCurrencyCode
     *
     * @return bool
     */
    protected function loadBaseCurrencyCode(): bool
    {
        $currency = Currency::getDefault();
        if (empty($currency)) {
            $this->error = Labels::getLabel('MSG_DEFAULT_CURRENCY_NOT_SET', $this->langId);
            return false;
        }
        $this->systemCurrencyCode = strtoupper($currency['currency_code']);
        return true;
    }
    
    /**
     * updateUserMeta
     *
     * @param  string $key
     * @param  string $value
     * @return bool
     */
    protected function updateUserMeta(string $key, string $value): bool
    {
        $user = new User($this->userId);
        if (false === $user->updateUserMeta($key, $value)) {
            $this->error = $user->getError();
            return false;
        }
        return true;
    }
    
    /**
     * getUserMeta
     *
     * @param  string $key
     * @return void
     */
    protected function getUserMeta(string $key = '')
    {
        $resp = User::getUserMeta($this->userId, $key);
        return !empty($key) ? (string) $resp : $resp;
    }
}
