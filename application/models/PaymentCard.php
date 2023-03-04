<?php

class PaymentCard
{       
    private $paymentPlugin;
    private $userId = 0;
    private $langId = 0;
    private $response = [];
    private $error = '';

    /**
     * __construct
     *
     * @param  int $userId
     * @param  int $langId
     */
    public function __construct( int $langId, int $userId = 0)
    {
        $this->userId = $userId;
        $this->langId = $langId;

        if (1 > $this->langId) {
            $msg = Labels::getLabel("MSG_INVALID_REQUEST", $this->langId);
            trigger_error($msg, E_USER_ERROR);
        }

        $pluginObj = new Plugin();
        $keyName = $pluginObj->getDefaultPluginKeyName(Plugin::TYPE_SPLIT_PAYMENT_METHOD);
        if (false === $keyName) {
            trigger_error($pluginObj->getError(), E_USER_ERROR);
        }

        $this->paymentPlugin = PluginHelper::callPlugin($keyName, [$this->langId], $error, $this->langId);
        if (false === $this->paymentPlugin) {
            trigger_error($error, E_USER_ERROR);
        }

        if (false === $this->paymentPlugin->init($this->userId)) {
            trigger_error($this->paymentPlugin->getError(), E_USER_ERROR);
        }
    }
        
    /**
     * create
     *
     * @param  array $cardData
     * @return bool
     */
    public function create(array $cardData): bool
    {
        if (1 > $this->userId) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        if (empty($this->getCustomerId())) {
            if (false === $this->bindCustomer()) {
                return false;
            }
        }

        /* It will generate card temp token. */
        if (false === $this->paymentPlugin->generateCardToken($cardData)) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }
        $cardTempTokenResponse = $this->paymentPlugin->getResponse();

        if (false === $this->paymentPlugin->addCard(['source' => $cardTempTokenResponse->id])) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }

        $this->response = $this->paymentPlugin->getResponse();
        return true;
    }
    
    /**
     * delete
     *
     * @param  string $cardId
     * @return bool
     */
    public function delete(string $cardId): bool
    {
        if (1 > $this->userId) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        if (false === $this->paymentPlugin->removeCard(['cardId' => $cardId])) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }
        return true;
    }
    
    /**
     * markAsDefault
     *
     * @param  string $cardId
     * @return bool
     */
    public function markAsDefault(string $cardId): bool
    {
        if (false === $this->paymentPlugin->updateCustomerInfo(['default_source' => $cardId])) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }
        return true;
    }
    
    /**
     * fetchAll
     *
     * @return bool
     */
    public function fetchAll(): bool
    {
        if (empty($this->getCustomerId())) {
            if (false === $this->bindCustomer()) {
                return false;
            }
        }

        if (false === $this->paymentPlugin->fetchCards()) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }
        $this->response = json_decode(json_encode($this->paymentPlugin->getResponse()), true);
        return true;
    }

    /**
     * getDefault
     *
     * @return bool
     */
    public function getDefault(): bool
    {
        if (false === $this->paymentPlugin->getDefaultCard()) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }
        $this->response = $this->paymentPlugin->getResponse();
        return true;
    }

    /**
     * getCardForm
     *
     * @return object
     */
    public static function getCardForm(int $langId): object
    {
        $frm = new Form('frmCardForm');
        $frm->addRequiredField(Labels::getLabel('LBL_ENTER_CARD_NUMBER', $langId), 'number');
        $frm->addRequiredField(Labels::getLabel('LBL_CARD_HOLDER_FULL_NAME', $langId), 'name');
        $data['months'] = applicationConstants::getMonthsArr($langId);
        $today = getdate();
        $data['year_expire'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
        }
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_MONTH', $langId), 'exp_month', $data['months'], '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_YEAR', $langId), 'exp_year', $data['year_expire'], '', array(), '');
        $frm->addPasswordField(Labels::getLabel('LBL_CVV_SECURITY_CODE', $langId), 'cvc')->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $langId));

        return $frm;
    }

    /**
     * bindCustomer - To whom cards belongs
     *
     * @return void
     */
    public function bindCustomer()
    {
        if (1 > $this->userId) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        if (!empty($this->getCustomerId())) {
            $this->error = Labels::getLabel('MSG_ALREADY_BOUND', $this->langId);
            return false;
        }

        $userObj = new User($this->userId);
        $userData = $userObj->getUserInfo();
        $requestParam = [
            'email' => $userData['credential_email'],
            'name' => $userData['user_name'],
            'phone' => ValidateElement::formatDialCode($userData['user_phone_dcode']) . $userData['user_phone']
        ];

        if (false === $this->paymentPlugin->bindCustomer($requestParam)) {
            $this->error = $this->paymentPlugin->getError();
            return false;
        }
        $this->response = $this->paymentPlugin->getResponse();
        return true;
    }

    /**
     * bindCustomer - To whom cards belongs
     *
     * @return void
     */
    public function getCustomerId()
    {
        return $this->paymentPlugin->getCustomerId();
    }

    /**
     * getResponse
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * getError
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
