<?php
require_once dirname(__FILE__) . '/StripeConnectFunctions.php';

class StripeConnect extends PaymentMethodBase
{
    use StripeConnectFunctions;

    public const KEY_NAME = __CLASS__;

    private $stripe;
    private $stripeAccountId = '';
    private $stripeAccountType;
    private $requiredFields = [];
    private $userInfoObj;
    private $resp = [];
    private $liveMode = '';
    private $sessionId = '';
    private $priceId = '';
    private $customerId = '';
    private $loginUrl = '';
    private $connectedAccounts = [];
    private $customerInfo = [];
    public $userId = 0;

    private $payoutScheduleInterval = 'daily';
    private $payoutScheduleDelayDays = '';
    private $payoutScheduleWeekly = '';
    private $payoutScheduleMonthly = '';

    public $requiredKeys = [
        'env',
        'client_id',
        'publishable_key',
        'secret_key'
    ];

    private const CONNECT_URI = "https://connect.stripe.com/oauth";
    public const TERMS_AND_SERVICES_URI = "https://stripe.com/en-in/connect-account/legal";

    public const REQUEST_CREATE_ACCOUNT = 1;
    public const REQUEST_RETRIEVE_ACCOUNT = 2;
    public const REQUEST_UPDATE_ACCOUNT = 3;
    public const REQUEST_PERSON_TOKEN = 4;
    public const REQUEST_ADD_BANK_ACCOUNT = 5;
    public const REQUEST_CREATE_PERSON = 6;
    public const REQUEST_UPDATE_PERSON = 7;
    public const REQUEST_UPLOAD_VERIFICATION_FILE = 8;
    public const REQUEST_DELETE_ACCOUNT = 9;
    public const REQUEST_CREATE_SESSION = 10;
    public const REQUEST_CREATE_PRICE = 11;
    public const REQUEST_CREATE_CUSTOMER = 12;
    public const REQUEST_RETRIEVE_CUSTOMER = 13;
    public const REQUEST_UPDATE_CUSTOMER = 14;
    public const REQUEST_CREATE_LOGIN_LINK = 15;
    public const REQUEST_ALL_CONNECT_ACCOUNTS = 16;
    public const REQUEST_INITIATE_REFUND = 17;
    public const REQUEST_TRANSFER_AMOUNT = 18;
    public const REQUEST_REVERSE_TRANSFER = 19;
    public const REQUEST_ADD_CARD = 20;
    public const REQUEST_REMOVE_CARD = 21;
    public const REQUEST_LIST_ALL_CARDS = 22;
    public const REQUEST_CREATE_CARD_TOKEN = 23;
    public const REQUEST_CHARGE = 24;
    public const REQUEST_PAYMENT_INTENT = 25;
    public const REQUEST_RETRIEVE_PAYMENT_INTENT = 26;
    public const REQUEST_CREATE_PAYMENT_METHOD = 27;
    public const REQUEST_CAPTURE_PAYMENT = 28;
    public const REQUEST_CREATE_ACCOUNT_LINKS = 29;

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();
        $this->requiredKeys();
    }

    /**
     * requiredKeys
     *
     * @return void
     */
    public function requiredKeys()
    {
        $this->env = FatUtility::int($this->getKey('env'));
        if (0 < $this->env) {
            $this->liveMode = "live_";
            $this->requiredKeys = [
                'env',
                $this->liveMode . 'client_id',
                $this->liveMode . 'publishable_key',
                $this->liveMode . 'secret_key'
            ];
        }
    }

    /**
     * init
     *
     * @param int $userId
     * @return void
     */
    public function init(int $userId = 0, $isSeller = false)
    {
        if (false == $this->validateSettings()) {
            return false;
        }

        if (0 < $userId) {
            if (false === $isSeller && false === $this->loadLoggedUserInfo($userId)) {
                return false;
            } else if (false === $this->loadSellerInfo($userId)) {
                return false;
            }
        }

        // For Some functions this line is also required to initiate API secret key
        \Stripe\Stripe::setApiKey($this->settings[$this->liveMode . 'secret_key']);

        $weekdays = TimeSlot::getDaysArr(1);

        $this->stripe = new \Stripe\StripeClient($this->settings[$this->liveMode . 'secret_key']);

        $this->payoutScheduleInterval = isset($this->settings['payouts_schedule_interval']) ? $this->settings['payouts_schedule_interval'] : 'daily';
        $this->payoutScheduleDelayDays = isset($this->settings['payouts_schedule_delay_days']) ? $this->settings['payouts_schedule_delay_days'] : '';

        $scheduleWeekly = isset($this->settings['payouts_schedule_delay_days']) ? FatUtility::int($this->settings['payouts_schedule_weekly_anchor']) : 0;
        $this->payoutScheduleWeekly = (0 < $scheduleWeekly && isset($weekdays[$scheduleWeekly])  ? $weekdays[$scheduleWeekly] : '');

        $this->payoutScheduleMonthly = isset($this->settings['payouts_schedule_monthly_anchor']) ? $this->settings['payouts_schedule_monthly_anchor'] : '';
        return true;
    }
    
    /**
     * isMandatoryForSeller : Check if stripe connect configuration is mandatory for seller.
     *
     * @return int
     */
    public function isMandatoryForSeller(): int
    {
        return (int) $this->getKey('stripe_connect_mandatory');
    }

    /**
     * getRedirectUri
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return self::CONNECT_URI . "/authorize?response_type=code&client_id=" . $this->settings[$this->liveMode . 'client_id'] . "&scope=read_write&redirect_uri=" . UrlHelper::generateFullUrl(self::KEY_NAME, 'callback', [], '', false);
    }

    /**
     * connect
     *
     * @return bool
     */
    private function connect(): bool
    {
        $params = [
            'clientId'                => $this->settings[$this->liveMode . 'client_id'],
            'clientSecret'            => $this->settings[$this->liveMode . 'secret_key'],
            'redirectUri'             => $this->getRedirectUri(),
            'urlAuthorize'            => self::CONNECT_URI . '/authorize',
            'urlAccessToken'          => self::CONNECT_URI . '/token',
            'urlResourceOwnerDetails' => 'https://api.stripe.com/v1/account'
        ];
        $this->stripe = new \League\OAuth2\Client\Provider\GenericProvider($params);
        return true;
    }

    /**
     * getResponse
     *
     * @return mixed
     */
    public function getResponse()
    {
        return empty($this->resp) ? (object) array() : $this->resp;
    }

    /**
     * register
     *
     * @return bool
     */
    public function register(): bool
    {
        if (empty($this->getAccountId())) {
            if (false === $this->doRequest(self::REQUEST_CREATE_ACCOUNT)) {
                return false;
            }
        } 

        return true;
    }
    
    /**
     * requestAccountLinks
     *
     * @return bool
     */
    public function requestAccountLinks(): bool
    {
        $stripeConnectForm = UrlHelper::generateFullUrl('Seller', 'shop', [self::KEY_NAME]);
        $requestParam = [
            'account' => $this->getAccountId(),
            'refresh_url' => $stripeConnectForm,
            'return_url' => $stripeConnectForm,
            'type' => 'account_onboarding',
            'collect' => 'eventually_due'
        ];
        
        $this->resp = $this->doRequest(self::REQUEST_CREATE_ACCOUNT_LINKS, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * accessAccountId
     *
     * @param string $code
     * @return bool
     */
    public function accessAccountId(string $code): bool
    {
        try {
            $this->connect();
            $accessToken = $this->stripe->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            $this->stripeAccountId = $this->stripe->getResourceOwner($accessToken)->getId();
            if ($this->updateUserMeta('stripe_account_id', $this->stripeAccountId)) {
                $this->updateUserMeta('stripe_form_submitted', 1);
            }
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * isUserAccountRejected
     *
     * @return bool
     */
    public function isUserAccountRejected(): bool
    {
        if (false === $this->loadRemoteUserInfo()) {
            return false;
        }

        $this->userInfoObj = $this->getResponse();
        $requirements = $this->userInfoObj->requirements;
        if (isset($requirements->disabled_reason) && false !== strpos($requirements->disabled_reason, "rejected")) {
            $this->unsetUserAccountElements();
            $msg = Labels::getLabel('MSG_YOUR_ACCOUNT_HAS_BEEN_', $this->langId);
            $this->error = $msg . ucwords(str_replace(".", " - ", $requirements->disabled_reason));
            return true;
        }
        return false;
    }

    /**
     * checkUserAccountIsIncomplete
     *
     * @return bool
     */
    public function checkUserAccountIsIncomplete(): bool
    {
        if (false === $this->loadRemoteUserInfo()) {
            return false;
        }

        $this->userInfoObj = $this->getResponse();
        $requirements = $this->userInfoObj->requirements;
        if (empty($requirements) || !isset($requirements->errors) || empty($requirements->errors) || !is_array($requirements->errors)) {
            return false;
        }

        $this->error = Labels::getLabel('MSG_YOUR_ACCOUNT_HAS_BEEN_INCOMPLETE_/_RESTRICTED', $this->langId);
        foreach ($requirements->errors as $error) {
            $this->error = ' ' . $error['reason'];
        }
        return true;
    }

    /**
     * getCurrentlyDueFields
     *
     * @return array
     */
    public function getCurrentlyDueFields(): array
    {
        if (false === $this->loadRemoteUserInfo()) {
            return [];
        }

        $this->userInfoObj = $this->getResponse();
        if (!isset($this->userInfoObj->requirements) || !isset($this->userInfoObj->requirements->currently_due)) {
            return [];
        }

        return (array) $this->userInfoObj->requirements->currently_due;
    }

    /**
     * userAccountIsValid
     *
     * @return bool
     */
    public function userAccountIsValid(): bool
    {
        return (!empty($this->getAccountId()) && empty($this->getCurrentlyDueFields()) && false === $this->checkUserAccountIsIncomplete() && false === $this->isUserAccountRejected());
    }

    /**
     * unsetUserAccountElements
     *
     * @return type
     */
    private function unsetUserAccountElements(): bool
    {
        $db = FatApp::getDb();
        if (false == $db->deleteRecords(User::DB_TBL_META, ['smt' => 'usermeta_user_id = ? AND usermeta_key LIKE ? ', 'vals' => [$this->userId, 'stripe_%']])) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    /**
     * initialFieldsValue
     *
     * @return array
     */
    public function initialFieldsValue(): array
    {
        return [
            'email' => $this->userData['credential_email'],
            'business_profile' => [
                'name' => $this->userData['shop_name'],
                'url' => UrlHelper::generateFullUrl('shops', 'view', [$this->userData['shop_id']]),
                'support_url' => UrlHelper::generateFullUrl('shops', 'view', [$this->userData['shop_id']]),
                'support_phone' => ValidateElement::formatDialCode($this->userData['shop_phone_dcode']) . $this->userData['shop_phone'],
                'support_email' => $this->userData['credential_email'],
                'support_address' => [
                    'city' => $this->userData['shop_city'],
                    'country' => strtoupper($this->userData['country_code']),
                    'line1' => $this->userData['shop_address_line_1'],
                    'line2' => $this->userData['shop_address_line_2'],
                    'postal_code' => $this->userData['shop_postalcode'],
                    'state' => $this->userData['state_code'],
                ],
                'product_description' => $this->userData['shop_description'],
            ]
        ];
    }

    /**
     * getPayoutSettingsArr
     *
     * @return array
     */
    public function getPayoutSettingsArr(): array
    {
        $settings = [
            'payouts' => [
                'schedule' => [
                    'interval' => $this->payoutScheduleInterval
                ]
            ]
        ];

        if ('daily' == $this->payoutScheduleInterval) {
            $settings['payouts']['schedule']['delay_days'] = empty($this->payoutScheduleDelayDays) ? 'minimum' : $this->payoutScheduleDelayDays;
        }

        if ('weekly' == $this->payoutScheduleInterval && !empty($this->payoutScheduleWeekly)) {
            $settings['payouts']['schedule']['weekly_anchor'] = strtolower($this->payoutScheduleWeekly);
        }

        if ('monthly' == $this->payoutScheduleInterval && !empty($this->payoutScheduleMonthly)) {
            $settings['payouts']['schedule']['monthly_anchor'] = $this->payoutScheduleMonthly;
        }

        return $settings;
    }

    /**
     * createAccount
     *
     * Can follow: https://stripe.com/docs/api OR https://medium.com/@Keithweaver_/creating-your-own-marketplace-with-stripe-connect-php-like-shopify-or-uber-6eadbb08993f for help.
     *
     * @return bool
     */
    public function createAccount(): bool
    {
        $data = [
            'type' => 'custom',
            'country' => strtoupper($this->userData['country_code']),
            'email' => $this->userData['credential_email'],
            'requested_capabilities' => [
                'card_payments',
                'transfers',
            ],
            'settings' => $this->getPayoutSettingsArr()
        ];

        $data['default_currency'] = Currency::getAttributesById(CommonHelper::getCurrencyId(), 'currency_code');

        $this->resp = $this->create($data);
        if (false === $this->resp) {
            return false;
        }
        $this->stripeAccountId = $this->resp->id;
        $this->updateUserMeta('stripe_account_type', 'custom');
        return $this->updateUserMeta('stripe_account_id', $this->resp->id);
    }


    /**
     * getPersonToken
     *
     * @return string
     */
    public function getPersonToken(): string
    {
        $personId = $this->getUserMeta('stripe_person_token');
        if (empty($personId)) {
            if (false === $this->createPersonToken()) {
                return false;
            }
            $personId = $this->getUserMeta('stripe_person_token');
        }
        return (string)$personId;
    }

    /**
     * createPersonToken
     *
     * @return string
     */
    private function createPersonToken(): bool
    {
        $this->resp = $this->createToken();
        if (false === $this->resp) {
            return false;
        }
        return $this->updateUserMeta('stripe_person_token', $this->resp->id);
    }

    /**
     * getAccountId
     *
     * @return string
     */
    public function getAccountId(): string
    {
        if (!empty($this->stripeAccountId)) {
            return $this->stripeAccountId;
        }

        return $this->getUserMeta('stripe_account_id');
    }

    /**
     * getAccountType
     *
     * @return string
     */
    public function getAccountType(): string
    {
        if (!empty($this->stripeAccountType)) {
            return $this->stripeAccountType;
        }

        $this->stripeAccountType = $this->getUserMeta('stripe_account_type');
        if (empty($this->stripeAccountType)) {
            $this->accessAccountType();
            $this->updateUserMeta('stripe_account_type', $this->stripeAccountType);
        }
        return $this->stripeAccountType;
    }

    /**
     * accessAccountType
     *
     * @return bool
     */
    private function accessAccountType(): bool
    {
        if (false === $this->loadRemoteUserInfo()) {
            return false;
        }
        $this->userInfoObj = $this->getResponse();
        $this->stripeAccountType = $this->userInfoObj->type;
        return true;
    }

    /**
     * getRelationshipPersonId
     *
     * @return string
     */
    public function getRelationshipPersonId(): string
    {
        return (string) $this->getUserMeta('stripe_person_id');
    }

    /**
     * loadRemoteUserInfo
     *
     * @return bool
     */
    public function loadRemoteUserInfo(): bool
    {
        if (!empty($this->userInfoObj)) {
            $this->resp = $this->userInfoObj;
            return true;
        }

        $this->resp = $this->doRequest(self::REQUEST_RETRIEVE_ACCOUNT);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * getBusinessTypeFields
     *
     * @param  string $businessType
     * @return array
     */
    public function getBusinessTypeFields(): array
    {
        return [
            'external_account.account_holder_name' => [
                'title' => Labels::getLabel("MSG_BANK_ACCOUNT_HOLDER_NAME", $this->langId),
                'description' => Labels::getLabel('API_THE_NAME_OF_THE_PERSON_OR_BUSINESS_THAT_OWNS_THE_BANK_ACCOUNT', $this->langId),
                'required' => true
            ],
            'external_account.account_number' => [
                'title' => Labels::getLabel("MSG_BANK_ACCOUNT_NUMBER", $this->langId),
                'description' => Labels::getLabel('API_THE_BANK_ACCOUNT_NUMBER', $this->langId),
                'required' => true
            ],
            'external_account.routing_number' => [
                'title' => Labels::getLabel("MSG_BANK_ROUTING_NUMBER", $this->langId),
                'description' => Labels::getLabel('API_THE_ROUTING_NUMBER', $this->langId),
                'required' => false
            ],
        ];
    }
    
    /**
     * initialFormSubmitted
     *
     * @return bool
     */
    public function initialFormSubmitted(): bool
    {
        return (empty($this->getUserMeta('stripe_form_submitted')));
    }

    /**
     * getRequiredFields
     *
     * @return array
     */
    public function getRequiredFields(): array
    {
        if (empty($this->getAccountId()) || false === $this->loadRemoteUserInfo()) {
            return [];
        }
        $formSubmittedFlag = $this->getUserMeta('stripe_form_submitted');

        if (empty($formSubmittedFlag)) {
            $this->requiredFields = $this->getBusinessTypeFields();
        }

        return $this->requiredFields;
    }

    /**
     * cleanRequest
     *
     * @param  array $array
     * @return array
     */
    private function cleanRequest(array $array): array
    {
        foreach ($array as $key => &$value) {
            if ('' == $value) {
                unset($array[$key]);
            } else {
                if (is_array($value)) {
                    $value = $this->cleanRequest($value);
                    if ('' == $value) {
                        unset($array[$key]);
                    }
                }
            }
        }
        return $array;
    }

    /**
     * updateRequiredFields
     *
     * @param array $requestParam
     * @param int $updateSubmittedFormFlag
     * @return bool
     */
    public function updateRequiredFields(array $requestParam): bool
    {
        if (array_key_exists('external_account', $requestParam)) {
            $this->loadBaseCurrencyCode();
            $requestParam['external_account']['object'] = 'bank_account';
            $requestParam['external_account']['country'] = strtoupper($this->userData['country_code']);
            $requestParam['external_account']['currency'] = Currency::getAttributesById(CommonHelper::getCurrencyId(), 'currency_code');
        }

        $requestParam = $this->cleanRequest($requestParam);

        if (array_key_exists('relationship', $requestParam)) {
            $requestParam['company']['directors_provided'] = true;
            $requestParam['company']['executives_provided'] = true;
            $requestParam['company']['owners_provided'] = true;
            $requestParam['relationship']['representative'] = true;
        }

        return $this->doRequest(self::REQUEST_UPDATE_ACCOUNT, $requestParam);
    }

    /**
     * addFinancialInfo
     *
     * @param array $requestParam
     * @return bool
     */
    private function addFinancialInfo(array $requestParam): bool
    {
        $businessType = $this->getUserMeta('stripe_business_type');

        $this->loadBaseCurrencyCode();
        $requestParam = [
            'external_account' => [
                'object' => 'bank_account',
                'account_holder_name' => $requestParam['account_holder_name'],
                'account_number' => $requestParam['account_number'],
                'account_holder_type' => $businessType,
                'country' => strtoupper($this->userData['country_code']),
                'currency' => Currency::getAttributesById(CommonHelper::getCurrencyId(), 'currency_code'),
                'routing_number' => $requestParam['routing_number'],
            ]
        ];

        $this->resp = $this->createExternalAccount($requestParam);
        if (false === $this->resp) {
            return false;
        }
        return $this->updateUserMeta('stripe_bank_account_id', $this->resp->id);
    }

    /**
     * updateAccount
     *
     * @param array $requestParam
     * @return bool
     */
    private function updateAccount(array $requestParam): bool
    {
        $relationship = [];
        $person = [];
        $personData = [];

        $personId = $this->getRelationshipPersonId();

        $person = isset($requestParam['relationship_person']) ? $requestParam['relationship_person'] : [];
        if (!empty($person)) {
            unset($requestParam['relationship_person']);
        }

        if (array_key_exists('relationship', $requestParam)) {
            $relationship = $requestParam['relationship'];
            unset($requestParam['relationship']);
        }

        if (!empty($personId) && array_key_exists($personId, $requestParam)) {
            $personData = $requestParam[$personId];
            unset($requestParam[$personId]);
        }

        if (array_key_exists('tos_acceptance', $requestParam)) {
            $requestParam['tos_acceptance'] =  [
                'date' => time(),
                'ip' => CommonHelper::getClientIp(),
                'user_agent' => CommonHelper::userAgent(),
            ];
        }

        if (!empty($requestParam) && false === $this->update($requestParam)) {
            return false;
        }

        if (array_key_exists('business_type', $requestParam)) {
            $this->updateUserMeta('stripe_business_type', $requestParam['business_type']);
        }

        if (!empty($requestParam)) {
            $this->resp = $this->update($requestParam);
        }

        if (empty($personId) && !empty($relationship)) {
            $personDataToUpdate = array_merge($person, ['relationship' => $relationship]);
            $this->resp = $this->doRequest(self::REQUEST_CREATE_PERSON, $personDataToUpdate);
            if (false === $this->resp) {
                return false;
            }
            $this->updateUserMeta('stripe_person_id', $this->resp->id);
        } elseif (!empty($personId) && (!empty($relationship) || !empty($personData))) {
            $relationship = !empty($relationship) ? ['relationship' => $relationship] : [];
            $requestParam = array_merge($relationship, $personData);
            $this->resp = $this->doRequest(self::REQUEST_UPDATE_PERSON, $requestParam);
            if (false === $this->resp) {
                return false;
            }
        }

        $formSubmittedFlag = $this->getUserMeta('stripe_form_submitted');
        if (empty($formSubmittedFlag)) {
            $this->updateUserMeta('stripe_form_submitted', 1);
        }

        return true;
    }

    /**
     * uploadVerificationFile
     *
     * @param string $path
     * @return bool
     */
    public function uploadVerificationFile(string $path): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_UPLOAD_VERIFICATION_FILE, [$path]);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * updatePersonVerificationDocument
     *
     * @param string $side - Front/Back of uploaded document
     * @return bool
     */
    public function updatePersonVerificationDocument(string $side, string $name = 'document'): bool
    {
        if (empty($this->resp)) {
            $this->error = Labels::getLabel('LBL_INVALID_REQUEST', $this->langId);
            return false;
        }

        $requestParam = [
            'verification' => [
                $name => [
                    $side => $this->resp
                ]
            ]
        ];

        $personId = $this->getRelationshipPersonId();
        if (empty($personId)) {
            $this->resp = $this->doRequest(self::REQUEST_CREATE_PERSON, $requestParam);
            if (false === $this->resp) {
                return false;
            }
            $this->updateUserMeta('stripe_person_id', $this->resp->id);
            return true;
        } else {
            $this->resp = $this->doRequest(self::REQUEST_UPDATE_PERSON, $requestParam);
            return (false === $this->resp) ? false : true;
        }
    }

    /**
     * getErrorWhileUpdate
     *
     * @return array
     */
    public function getErrorWhileUpdate(): array
    {
        return (false === $this->loadRemoteUserInfo()) ? [] : ($this->getResponse()->toArray())['requirements']['errors'];
    }

    /**
     * deleteAccount
     *
     * @return bool
     */
    public function deleteAccount(): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_DELETE_ACCOUNT);

        if (false === $this->resp) {
            return false;
        }

        $this->resp = $this->resp->toArray();
        if (array_key_exists('deleted', $this->resp) && true == $this->resp['deleted']) {
            $this->unsetUserAccountElements();
            return true;
        }

        $this->error = Labels::getLabel('MSG_UNABLE_TO_DELETE_THIS_ACCOUNT', $this->langId);
        return false;
    }

    /**
     * unlinkAccount
     *
     * @return bool
     */
    public function unlinkAccount(): bool
    {
        return (bool) $this->unsetUserAccountElements();
    }

    /**
     * initiateSession
     *
     * @param array $requestParam
     * @return bool
     */
    public function initiateSession(array $requestParam): bool
    {
        if (empty($requestParam)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $this->resp = $this->doRequest(self::REQUEST_CREATE_SESSION, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        $this->sessionId = $this->resp->id;
        return true;
    }

    /**
     * getSessionId
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * createPriceObject
     *
     * @param array $requestParam
     * @return bool
     */
    public function createPriceObject(array $requestParam): bool
    {
        if (empty($requestParam)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $this->resp = $this->doRequest(self::REQUEST_CREATE_PRICE, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        $this->priceId = $this->resp->id;
        return true;
    }

    /**
     * getPriceId
     *
     * @return string
     */
    public function getPriceId(): string
    {
        return $this->priceId;
    }

    /**
     * updateCustomerInfo
     *
     * @param array $requestParam
     * @return bool
     */
    public function updateCustomerInfo(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_UPDATE_CUSTOMER, $requestParam);
        return (false === $this->resp) ? false : true;
    }

    /**
     * bindCustomer
     *
     * @param array $requestParam
     * @return bool
     */
    public function bindCustomer(array $requestParam): bool
    {
        if (empty($requestParam)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        if (!empty($this->getCustomerId())) {
            $this->resp = $this->updateCustomerInfo($requestParam);
        } else {
            $this->resp = $this->doRequest(self::REQUEST_CREATE_CUSTOMER, $requestParam);
        }

        if (false === $this->resp) {
            return false;
        }

        $this->customerId = empty($this->getCustomerId()) ? $this->resp->id : $this->getCustomerId();

        return $this->updateUserMeta('stripe_customer_id', $this->customerId);
    }

    /**
     * loadCustomer
     *
     * @return bool
     */
    public function loadCustomer(): bool
    {
        if (!empty($this->customerInfo)) {
            $this->resp = $this->customerInfo;
            return true;
        }

        $this->resp = $this->customerInfo = $this->doRequest(self::REQUEST_RETRIEVE_CUSTOMER);
        return (false === $this->resp) ? false : true;
    }

    /**
     * getCustomerId
     *
     * @return string
     */
    public function getCustomerId(): string
    {
        return empty($this->customerId) ? $this->getUserMeta('stripe_customer_id') : $this->customerId;
    }

    /**
     * formatCustomerDataFromOrder
     * @param array $orderInfo
     * @return array
     */
    public function formatCustomerDataFromOrder(array $orderInfo): array
    {
        if (empty($orderInfo)) {
            return [];
        }

        $orderData = [
            'address' => [
                'line1' => $orderInfo['customer_billing_address_1'],
                'line2' => $orderInfo['customer_billing_address_2'],
                'city' => $orderInfo['customer_billing_city'],
                'state' => $orderInfo['customer_billing_state'],
                'country' => $orderInfo['customer_billing_country'],
                'postal_code' => $orderInfo['customer_billing_postcode']
            ],
            'shipping' => [
                'address' => [
                    'line1' => $orderInfo['customer_shipping_address_1'],
                    'line2' => $orderInfo['customer_shipping_address_2'],
                    'city' => $orderInfo['customer_shipping_city'],
                    'state' => $orderInfo['customer_shipping_state'],
                    'country' => $orderInfo['customer_shipping_country'],
                    'postal_code' => $orderInfo['customer_shipping_postcode']
                ],
                'name' => $orderInfo['customer_shipping_name'],
                'phone' => $orderInfo['customer_shipping_phone']
            ],
            'email' => $orderInfo['customer_email'],
            'name' => $orderInfo['customer_billing_name'],
            'phone' => $orderInfo['customer_billing_phone']
        ];
        return $orderData;
    }

    /**
     * createLoginLink
     *
     * @return bool
     */
    public function createLoginLink(): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CREATE_LOGIN_LINK);
        if (false === $this->resp) {
            return false;
        }
        $this->loginUrl = $this->resp->url;
        return true;
    }

    /**
     * getLoginUrl
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    /**
     * loadAllAccounts
     *
     * @param $requestParam - Used for pagination
     * Detail : https://stripe.com/docs/api/accounts/list?lang=php
     * @return bool
     */
    public function loadAllAccounts(array $requestParam = ['limit' => 10]): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_ALL_CONNECT_ACCOUNTS, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        $this->connectedAccounts = $this->resp->toArray();
        return true;
    }

    /**
     * getAllAccounts
     *
     * @return array
     */
    public function getAllAccounts(): array
    {
        return $this->connectedAccounts;
    }

    /**
     * initiateRefund
     *
     * @param $requestParam
     * Follow : https://stripe.com/docs/api/refunds/create
     * @return bool
     */
    public function initiateRefund(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_INITIATE_REFUND, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * doTransfer
     *
     * @param array $requestParam : [
     *         'amount' => 7000,
     *         'currency' => 'inr',
     *         'destination' => '{{CONNECTED_STRIPE_ACCOUNT_ID}}',
     *         'transfer_group' => '{ORDER10}',
     *       ]
     * @return bool
     */
    public function doTransfer(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_TRANSFER_AMOUNT, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * revertTransfer
     *
     * @param array $requestParam : [
     *         'transferId' => 'tr_1XXXXXXXXXXXX,
     *         'data' => [
     *              'amount' => 1000, // In Paisa
     *              'description' => '',
     *              'metadata' => [
     *                  'xyz' => 'abc' // Set of key-value pairs that you can attach to an object.
     *              ],
     *          ],
     *       ]
     * @return bool
     */
    public function revertTransfer(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_REVERSE_TRANSFER, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * addCard
     *
     * @return bool
     */
    public function addCard(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_ADD_CARD, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * removeCard
     *
     * @param array $requestParam : ['cardId' => 'card_xxxxx']
     * @return bool
     */
    public function removeCard(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_REMOVE_CARD, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * loadSavedCards - Retrieve All Saved Cards
     *
     * @return bool
     */
    public function loadSavedCards(): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_LIST_ALL_CARDS);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * generateCardToken
     *
     * @param array $requestParam : [
     *           'number' => '4242424242424242',
     *           'exp_month' => 6,
     *           'exp_year' => 2021,
     *           'cvc' => '314',
     *       ]
     * @return bool
     */
    public function generateCardToken(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CREATE_CARD_TOKEN, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * doCharge
     *
     * @param array $requestParam
     * @return bool
     */
    public function doCharge(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CHARGE, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * createPaymentIntent
     *
     * @param array $requestParam
     * @return bool
     */
    public function createPaymentIntent(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_PAYMENT_INTENT, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * loadPaymentIntent
     *
     * @param string $paymentIntentId
     * @return bool
     */
    public function loadPaymentIntent(string $paymentIntentId): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_RETRIEVE_PAYMENT_INTENT, [$paymentIntentId]);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * addPaymentMethod
     *
     * @param array $requestParam
     * @return bool
     */
    public function addPaymentMethod(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CREATE_PAYMENT_METHOD, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * fetchCards
     *
     * @return bool
     */
    public function fetchCards(): bool
    {
        if (false === $this->loadCustomer()) {
            return false;
        }
        $customerInfo = $this->getResponse()->toArray();
        $this->resp = (array) $customerInfo['sources']['data'];
        return true;
    }

    /**
     * getDefaultCard
     *
     * @return bool
     */
    public function getDefaultCard(): bool
    {
        if (false === $this->loadCustomer()) {
            return false;
        }
        $customerInfo = $this->getResponse()->toArray();
        $this->resp = $customerInfo['default_source'];
        return true;
    }

    /**
     * captureDetainedAmount
     * @param array $requestParam : [
     *      'paymentIntentId' => 'pi_JRXXXXXXXXXXXXX',
     *      'amount_to_capture' => 750,
     *      'statement_descriptor' => 'TEXT' // Description that appears on your customers’ statements. Length at least one letter, maximum 22 characters.
     *   ]
     * @return bool
     */
    public function captureDetainedAmount(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CAPTURE_PAYMENT, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * getMerchantCategory
     *
     * @return array
     */
    public function getMerchantCategory(): array
    {
        $json = FatCache::get('merchantCategoryCode' . $this->langId, CONF_DEF_CACHE_TIME, '.txt');
        if (!empty($json)) {
            return json_decode($json, true);
        }

        include(__DIR__ . '/MerchantCategoryCode.php');
        FatCache::set('merchantCategoryCode' . $this->langId, FatUtility::convertToJson($arr), '.txt');
        return $arr;
    }

    /**
     * getPayoutInterval
     *
     * @return void
     */
    public function getPayoutInterval()
    {
        $json = FatCache::get('stripePayoutInterval' . $this->langId, CONF_DEF_CACHE_TIME, '.txt');
        if (!empty($json)) {
            return json_decode($json, true);
        }

        $interval = [
            'daily' => Labels::getLabel('LBL_DAILY', $this->langId),
            'manual' => Labels::getLabel('LBL_MANUAL', $this->langId),
            'weekly' => Labels::getLabel('LBL_WEEKLY', $this->langId),
            'monthly' => Labels::getLabel('LBL_MONTHLY', $this->langId),
        ];
        FatCache::set('stripePayoutInterval' . $this->langId, FatUtility::convertToJson($interval), '.txt');
        return $interval;
    }

    /**
     * getPayoutDays
     *
     * @return void
     */
    public function getPayoutDays()
    {
        $json = FatCache::get('stripePayoutDays' . $this->langId, CONF_DEF_CACHE_TIME, '.txt');
        if (!empty($json)) {
            return json_decode($json, true);
        }

        $days = range(0, 31);
        unset($days[0]);
        FatCache::set('stripePayoutDays' . $this->langId, FatUtility::convertToJson($days), '.txt');
        return $days;
    }

    /**
     * doRequest
     *
     * @param  mixed $requestType
     * @return mixed
     */
    public function doRequest(int $requestType, array $requestParam = [])
    {
        try {
            switch ($requestType) {
                case self::REQUEST_CREATE_ACCOUNT:
                    return $this->createAccount();
                    break;
                case self::REQUEST_RETRIEVE_ACCOUNT:
                    return $this->retrieve();
                    break;
                case self::REQUEST_UPDATE_ACCOUNT:
                    return $this->updateAccount($requestParam);
                    break;
                case self::REQUEST_PERSON_TOKEN:
                    return $this->getPersonToken();
                    break;
                case self::REQUEST_ADD_BANK_ACCOUNT:
                    return $this->addFinancialInfo($requestParam);
                    break;
                case self::REQUEST_CREATE_PERSON:
                    return $this->createPerson($requestParam);
                    break;
                case self::REQUEST_UPDATE_PERSON:
                    return $this->updatePerson($requestParam);
                    break;
                case self::REQUEST_UPLOAD_VERIFICATION_FILE:
                    return $this->createFile(reset($requestParam));
                    break;
                case self::REQUEST_DELETE_ACCOUNT:
                    return $this->delete();
                    break;
                case self::REQUEST_CREATE_SESSION:
                    return $this->createSession($requestParam);
                    break;
                case self::REQUEST_CREATE_PRICE:
                    return $this->createPrice($requestParam);
                    break;
                case self::REQUEST_CREATE_CUSTOMER:
                    return $this->createCustomer($requestParam);
                    break;
                case self::REQUEST_RETRIEVE_CUSTOMER:
                    return $this->retrieveCustomer();
                    break;
                case self::REQUEST_UPDATE_CUSTOMER:
                    return $this->updateCustomer($requestParam);
                    break;
                case self::REQUEST_CREATE_LOGIN_LINK:
                    return $this->loginLink();
                    break;
                case self::REQUEST_ALL_CONNECT_ACCOUNTS:
                    return $this->connectedAccounts($requestParam);
                    break;
                case self::REQUEST_INITIATE_REFUND:
                    return $this->requestRefund($requestParam);
                    break;
                case self::REQUEST_TRANSFER_AMOUNT:
                    return $this->transferAmount($requestParam);
                    break;
                case self::REQUEST_REVERSE_TRANSFER:
                    return $this->reverseTransfer($requestParam);
                    break;
                case self::REQUEST_ADD_CARD:
                    return $this->createSource($requestParam);
                    break;
                case self::REQUEST_REMOVE_CARD:
                    return $this->deleteSource($requestParam);
                    break;
                case self::REQUEST_LIST_ALL_CARDS:
                    return $this->listAllCards();
                    break;
                case self::REQUEST_CREATE_CARD_TOKEN:
                    return $this->createCardToken($requestParam);
                    break;
                case self::REQUEST_CHARGE:
                    return $this->charge($requestParam);
                    break;
                case self::REQUEST_PAYMENT_INTENT:
                    return $this->paymentIntent($requestParam);
                    break;
                case self::REQUEST_RETRIEVE_PAYMENT_INTENT:
                    return $this->retrievePaymentIntent($requestParam);
                    break;
                case self::REQUEST_CREATE_PAYMENT_METHOD:
                    return $this->createPaymentMethod($requestParam);
                    break;
                case self::REQUEST_CAPTURE_PAYMENT:
                    return $this->capturePayment($requestParam);
                    break;
                case self::REQUEST_CREATE_ACCOUNT_LINKS:
                    return $this->createAccountLinks($requestParam);
                    break;
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            $this->error = $e->getMessage();
            // (maybe you changed API keys recently)
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (\UnexpectedValueException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $this->error = $e->getMessage();
        } catch (Error $e) {
            // Handle error
            $this->error = $e->getMessage();
        }
        return false;
    }
}
