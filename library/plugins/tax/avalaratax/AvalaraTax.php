<?php

class AvalaraTax extends TaxBase
{
    public const KEY_NAME = __CLASS__;

    public $langId = 0;
    private $client;
    private $companyCode;
    private $fromAddress = [];
    private $toAddress = [];
    private $products = [];
    private $productsShipping = [];
    private $customerCode;
    private $invoiceId;
    private $response;
    private $invoiceDate;

    public $requiredKeys = [
        'account_number',
        'company_code',       
        'license_key'
    ];

    /**
     * __construct
     *
     * @param  int $langId
     * @param  array $fromAddress
     * @param  array $toAddress
     */
    public function __construct(int $langId, array $fromAddress = [], array $toAddress = [])
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();

        if (!empty($fromAddress)) {
            $this->setFromAddress($fromAddress);
        }

        if (!empty($toAddress)) {
            $this->setToAddress($toAddress);
        }
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        if (false == $this->validateSettings()) {
            return false;
        }

        $websiteName = FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->langId, FatUtility::VAR_STRING, 'Yo!Kart');
        if (empty($websiteName) || empty(CONF_WEB_APP_VERSION)) {
            $this->error = Labels::getLabel('MSG_SITE_NAME_AND_SITE_VERSION_MUST_BE_DEFINED', $this->langId);
            return false;
        }

        $environment = FatUtility::int($this->settings['environment']) == 1 ? 'production' : 'sandbox';

        $this->client = new Avalara\AvaTaxClient($websiteName, CONF_WEB_APP_VERSION, $_SERVER['HTTP_HOST'], $environment);
        $this->client->withLicenseKey($this->settings['account_number'], $this->settings['license_key']);
        $this->client->withCatchExceptions(false);
        $this->companyCode = $this->settings['company_code'];
        return true;
    }

    public function checkCredentials($account_number, $license_key)
    {
        $this->client->withLicenseKey($account_number, $license_key);
        $p = $this->client->ping();
        return $p->authenticated;
    }

    /**
     * getRates
     *
     * @param  array $itemsArr
     * @param  array $shippingItem
     * @param  int $userId
     * @return array
     */
    public function getRates($itemsArr, $shippingItem, $userId)
    {
        try {
            $this->setProducts($itemsArr)
                ->setProductsShipping($shippingItem)
                ->setCustomerCode($userId);

            $taxes = $this->calculateTaxes();
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => Labels::getLabel($e->getMessage(), $this->langId),
            ];
        }
        //CommonHelper::printArray($taxes); exit;
        return [
            'status' => true,
            'msg' => Labels::getLabel("MSG_SUCCESS", $this->langId),
            'data' => $this->formatTaxes($taxes)
        ];
    }

    /**
     * createInvoice
     *
     * @param  array $itemsArr
     * @param  array $shippingItem
     * @param  int $userId
     * @param  string $invoiceDate
     * @param  string $InvoiceNo
     * @return array
     */
    public function createInvoice($itemsArr, $shippingItem, $userId, $invoiceDate, $InvoiceNo)
    {
        try {
            $this->setProducts($itemsArr)
                ->setProductsShipping($shippingItem)
                ->setCustomerCode($userId)
                ->setInvoiceId($InvoiceNo)
                ->setInvoiceDate($invoiceDate);

            $taxes = $this->calculateTaxes(true);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return [
            'status' => true,
            'referenceId' => $taxes->id,
            'data' => $this->formatTaxes($taxes)
        ];
    }

    /*
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/).
     * @param int $pageSize If nonzero, return no more than this number of results. Used with `$pageNumber` to provide pagination for large datasets. Unless otherwise specified, the maximum number of records that can be returned from an API call is 1,000 records.
     * @param int $pageNumber If nonzero, skip this number of results before returning data. Used with `$pageSize` to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     */

    public function getCodes($pageSize = null, $pageNumber = null, $filter = null, $orderBy = null, $formatted = true)
    {

        if (!empty($filter)) {
            $filter = "description contains '$filter' or taxCode contains '$filter'";
        }

        try {
            $codesArr = $this->client->listTaxCodes($filter, $pageSize, $pageNumber, $orderBy);
            if (false == $formatted) {
                return $codesArr;
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage()
            ];
        }

        $formatedCodesArr = [];
        if (!empty($codesArr)) {
            foreach ($codesArr->value as $code) {
                $formatedCodesArr[$code->id] = array(
                    'taxCode' => $code->taxCode,
                    'description' => $code->description,
                    'parentTaxCode' => $code->parentTaxCode ?? null,
                );
            }
        }
        return [
            'status' => true,
            'data' => $formatedCodesArr
        ];
    }

    public function getTaxApiActualResponse()
    {
        return $this->response;
    }

    private function setFromAddress($address)
    {
        if (!$this->validateAddressArrKeys($address)) {
            throw new Exception("E_Avalara_Error:_Invalid_From_Address_keys");
        }
        $this->fromAddress = $address;
        return $this;
    }

    private function setToAddress($address)
    {
        if (!$this->validateAddressArrKeys($address)) {
            throw new Exception("E_Avalara_Error:_Invalid_To_Address_keys");
        }

        $this->toAddress = $address;

        return $this;
    }

    private function setProducts($products)
    {
        if (!$this->validateitemArrKeys(current($products))) {
            throw new Exception("E_Avalara_Error:_Invalid_To_Product_Array_Keys");
        }

        $this->products = $products;

        return $this;
    }

    private function setProductsShipping($productsShipping)
    {
        if (!$this->validateitemArrKeys(current($productsShipping))) {
            throw new Exception("E_Avalara_Error:_Invalid_To_Product_Shipping_Array_keys");
        }

        foreach ($productsShipping as &$Shipping) {
            if (empty($Shipping['taxCode'])) {
                $Shipping['taxCode'] = 'FR000000';
            }
        }
        $this->productsShipping = $productsShipping;

        return $this;
    }

    private function setCustomerCode($customerCode)
    {
        /**
         *      * @param string        $customerCode  The customer code for this transaction
         */
        $this->customerCode = $customerCode;
        return $this;
    }

    private function setInvoiceId($invoiceId)
    {
        if (empty($invoiceId)) {
            throw new Exception("E_Avalara_Error:_Invoice_Id_Empty");
        }
        $this->invoiceId = $invoiceId;
        return $this;
    }

    private function setInvoiceDate($invoiceDate)
    {
        if (empty($invoiceDate)) {
            throw new Exception("E_Avalara_Error:_Invoice_Date_Empty");
        }
        $this->invoiceDate = date(DATE_W3C, strtotime($invoiceDate));
        return $this;
    }

    /**

     * @param string        $createTxn    If true is a permanent document and is recorded in AvaTax
     * @param string|null   $txnDate      The datetime of the transaction, defaults to current time when null (Format: Y-m-d)
     */
    private function calculateTaxes($createTxn = false)
    {
        $invoiceType = Avalara\DocumentType::C_SALESORDER;

        if ($createTxn) {
            $invoiceType = Avalara\DocumentType::C_SALESINVOICE;
        }

        if (empty($this->customerCode)) {
            throw new Exception('E_Avalara_Error:customerCode_Is_Not_Set');
        }

        if (1 > count($this->products)) {
            throw new Exception('E_Avalara_Error:_To_Items_Is_Not_Set');
        }

        if (1 > count($this->fromAddress)) {
            throw new Exception('E_Avalara_Error:_From_Address_Is_Not_Set');
        }

        if (1 > count($this->toAddress)) {
            throw new Exception('E_Avalara_Error:_To_Address_Is_Not_Set');
        }

        try {
            $tb = new Avalara\TransactionBuilder($this->client, $this->companyCode, $invoiceType, $this->customerCode, $this->invoiceDate);
            if (FatUtility::int($this->settings['commit_transaction']) == 1) {
                $tb->withCommit();
            }

            foreach ($this->products as $itemKey => $item) {
                $tb->withLine($item['amount'] * $item['quantity'], $item['quantity'], $item['itemCode'], $item['taxCode']);
                $tb->withLineDescription(Labels::getLabel('LBL_Product', $this->langId));

                $fromAddress = $this->fromAddress;
                $toAddress = $this->toAddress;

                $tb->withLineAddress(Avalara\TransactionAddressType::C_SHIPFROM, $fromAddress['line1'], $fromAddress['line2'], null, $fromAddress['city'], $fromAddress['stateCode'], $fromAddress['postalCode'], $fromAddress['countryCode'])
                    ->withLineAddress(Avalara\TransactionAddressType::C_SHIPTO, $toAddress['line1'], $toAddress['line2'], null, $toAddress['city'], $toAddress['stateCode'], $toAddress['postalCode'], $toAddress['countryCode']);
            }


            foreach ($this->productsShipping as $itemKey => $item) {
                $tb->withLine($item['amount'], $item['quantity'], $item['itemCode'], $item['taxCode']);
                $tb->withLineDescription(Labels::getLabel('LBL_Shipping', $this->langId));

                $fromAddress = $this->fromAddress;
                $toAddress = $this->toAddress;

                $tb->withLineAddress(Avalara\TransactionAddressType::C_SHIPFROM, $fromAddress['line1'], $fromAddress['line2'], null, $fromAddress['city'], $fromAddress['state'], $fromAddress['postalCode'], $fromAddress['country'])
                    ->withLineAddress(Avalara\TransactionAddressType::C_SHIPTO, $toAddress['line1'], $toAddress['line2'], null, $toAddress['city'], $toAddress['state'], $toAddress['postalCode'], $toAddress['country']);
            }

            if (!empty($this->invoiceId)) {
                $tb->withTransactionCode($this->invoiceId);
            }

            $this->response = $tb->create();

            return $this->response;
        } catch (\Exception $e) {
            //$errorMsgObj = json_decode($e->getResponse()->getBody()->getContents());
            //throw new Exception('E_Avalara_Error:_' . str_replace(" ", "_", $errorMsgObj->error->message));
            throw new Exception('E_Avalara_Error:_' . str_replace(" ", "_", $e->getMessage()));
        }
    }

    private function withLicenseKey($accountId, $licenseKey)
    {
        if (empty($accountId) || empty($licenseKey)) {
            throw new Exception('E_Avalara_Error:_AccountId_and_licenseKey_are_mandatory_fields!');
        }
        $this->client->withLicenseKey($accountId, $licenseKey);
        return $this;
    }

    private function withSecurity($username, $password)
    {
        if (empty($username) || empty($password)) {
            throw new Exception('E_Avalara_Error:_Username_and-password_are_mandatory_fields!');
        }
        $this->client->withSecurity($username, $password);
        return $this;
    }

    private function validateAddressArrKeys($address)
    {
        if (!is_array($address)) {
            return false;
        }

        $requiredKeys = ['line1', 'line2', 'city', 'state', 'postalCode', 'country', 'stateCode', 'country', 'countryCode'];
        return !array_diff($requiredKeys, array_keys($address));
    }

    private function validateitemArrKeys($item)
    {
        if (!is_array($item)) {
            return false;
        }
        $requiredKeys = ['amount', 'quantity', 'itemCode', 'taxCode'];

        return !array_diff($requiredKeys, array_keys($item));
    }

    private function formatTaxes($taxes)
    {
        //CommonHelper::printArray($taxes);
        $formatedTax = [];
        $rate = 0;
        foreach ($taxes->lines as $line) {
            $taxDetails = [];
            foreach ($line->details as $lineTaxdetail) {
                $taxName = $lineTaxdetail->taxName;
                $rate += $lineTaxdetail->rate;
                if (isset($taxDetails[$taxName])) {
                    $taxDetails[$taxName]['value'] += $lineTaxdetail->tax;
                    $taxDetails[$taxName]['rate'] += $lineTaxdetail->rate;
                } else {
                    $taxDetails[$taxName]['value'] = $lineTaxdetail->tax;
                    $taxDetails[$taxName]['name'] = $taxName;
                    $taxDetails[$taxName]['rate'] = $lineTaxdetail->rate;
                }
            }
            $formatedTax[$line->itemCode] = array(
                'tax' => $line->tax,
                'rate' => $rate,
                'taxDetails' => $taxDetails,
            );
        }

        return $formatedTax;
    }
}
