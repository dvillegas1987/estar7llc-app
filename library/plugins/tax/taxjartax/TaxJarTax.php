<?php

class TaxJarTax extends TaxBase
{
    public const KEY_NAME = __CLASS__;
       
    public $error;

    public $langId = 0;
    private $fromAddress = [];
    private $toAddress = [];
    private $client;
    private $params = [];

    private const RATE_TYPE_STATE = 1;
    private const RATE_TYPE_COUNTY = 2;
    private const RATE_TYPE_CITY = 3;
    private const RATE_TYPE_QST = 4;
    private const RATE_TYPE_PST = 5;
    private const RATE_TYPE_GST = 6;
    private const RATE_TYPE_SPECIAL = 7;


    private const TAX_RATE_STATE = 1;
    private const TAX_RATE_COUNTY = 2;
    private const TAX_RATE_CITY = 3;
    private const TAX_RATE_QST = 4;
    private const TAX_RATE_PST = 5;
    private const TAX_RATE_GST = 6;
    private const TAX_RATE_SPECIAL = 7;

    public $requiredKeys = [
        'sandbox_key'
    ];

    /**
     * __construct
     *
     * @param  int $langId
     * @param  array $fromAddress
     * @param  array $toAddress
     */
    public function __construct(int $langId, array $fromAddress = array(), array $toAddress = array())
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();

        if (!empty($fromAddress)) {
            $this->setFromAddress($fromAddress);
        }

        if (!empty($toAddress)) {
            $this->setToAddress($toAddress);
        }
        $this->requiredKeys();
    }

    /**
     * requiredKeys
     *
     * @return void
     */
    public function requiredKeys()
    {
        $envoirment = FatUtility::int($this->getKey('env'));
        if (0 < $envoirment) {
            $this->requiredKeys = [
                'live_key'
            ];
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
        
        $env = $this->settings['env'];
        $apiToken = Plugin::ENV_SANDBOX == $env ? $this->settings['sandbox_key'] : $this->settings['live_key'];

        $this->client = TaxJar\Client::withApiKey($apiToken);
        if (Plugin::ENV_SANDBOX == $env) {
            $this->client->setApiConfig('api_url', TaxJar\Client::SANDBOX_API_URL);
        }
        return true;
    }

    /**
     * getRates
     *
     * @param  array $itemsArr
     * @param  array $shippingItem
     * @param  int $userId
     * @return array
     */
    public function getRates(array $itemsArr, array $shippingItem, int $userId)
    {
        if ($this->error != '') {
            return [
                'status' => false,
                'msg' => $this->error,
            ];
        }

        $this->setItems($itemsArr, $shippingItem, $userId);

        try {
            $taxes = $this->client->taxForOrder($this->params);
        } catch (exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
            ];
        }
        //  CommonHelper::printArray($taxes, true);

        // if (!isset($taxes->breakdown)) {
        //     return [
        //         'status' => false,
        //         'msg' => Labels::getLabel("LBL_Tax_could_not_be_calculated_from_TaxJar", $this->langId),
        //     ];
        // }
        //CommonHelper::printArray($taxes, true);
        return [
            'status' => true,
            'msg' => Labels::getLabel("MSG_SUCCESS", $this->langId),
            'data' => $this->formatTaxes($taxes)
        ];
    }

    /**
     * formatTaxes
     *
     * @param  array $taxes
     * @return array
     */
    private function formatTaxes($taxes)
    {
        $formatedTax = [];
        $types = $this->getRateTypesNames();
        $rateTypes = $this->getRateTypesKeys();
        $taxRates = $this->getTaxRatesKeys();
        $rate = 0;
        if (isset($taxes->breakdown->line_items)) {
            foreach ($taxes->breakdown->line_items as $item) {
                $taxDetails = [];
                foreach ($rateTypes as $key => $name) {
                    if (isset($item->$name)) {
                        $taxDetails[$types[$key]]['name'] = $types[$key];
                        $taxDetails[$types[$key]]['rate'] = $types[$key];
                        $taxDetails[$types[$key]]['value'] = $item->$name;
                    }
                }
                foreach ($taxRates as $key => $name) {
                    if (isset($item->$name)) {
                        $rate = $rate + $item->$name;
                        $taxDetails[$types[$key]]['rate'] = $item->$name;
                    }
                }
                $formatedTax[$item->id] = array(
                    'tax' => $taxes->breakdown->tax_collectable,
                    'rate' => $rate,
                    'taxDetails' => $taxDetails,
                );
            }
        } else {
            $taxDetails = [];
            $taxDetails[Labels::getLabel('LBL_TAX', $this->langId)]['name'] = Labels::getLabel('LBL_TAX', $this->langId);
            $taxDetails[Labels::getLabel('LBL_TAX', $this->langId)]['value'] = $taxes->amount_to_collect;
            $formatedTax[0] = array(
                'tax' => $taxes->amount_to_collect,
                'rate' => $rate,
                'taxDetails' => $taxDetails,
            );
        }
        if (isset($taxes->breakdown->shipping)) {
            $itemId = $taxes->breakdown->line_items[0]->id;
            foreach ($rateTypes as $key => $name) {
                if (isset($taxes->breakdown->shipping->$name)) {
                    if (isset($formatedTax[$itemId]['taxDetails'][$types[$key]]['value'])) {
                        $formatedTax[$itemId]['taxDetails'][$types[$key]]['value'] = $formatedTax[$itemId]['taxDetails'][$types[$key]]['value'] + $taxes->breakdown->shipping->$name;
                    } else {
                        $formatedTax[$itemId]['taxDetails'][$types[$key]]['name'] = $types[$key];
                        $formatedTax[$itemId]['taxDetails'][$types[$key]]['value'] = $taxes->breakdown->shipping->$name;
                    }
                }
            }
        }
        return $formatedTax;
    }

    /**
     * getCodes
     *
     * @param  int $pageSize
     * @param  int $pageNumber
     * @param  string $filter
     * @param  array $orderBy
     * @param  bool $formatted
     * @return array
     */
    public function getCodes(int $pageSize = null, int $pageNumber = null, string $filter = null, array $orderBy = null, bool $formatted = true)
    {
        try {
            $codesArr = $this->client->categories();
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
            foreach ($codesArr as $code) {
                $formatedCodesArr[$code->product_tax_code] = array(
                    'taxCode' => $code->product_tax_code,
                    'name' => $code->name,
                    'description' => $code->description,
                    'parentTaxCode' => null,
                );
            }
        }

        return [
            'status' => true,
            'data' => $formatedCodesArr
        ];
    }

    /**
     * createInvoice
     *
     * @param  array $itemsArr
     * @param  array $shippingItem
     * @param  int $userId
     * @param  string $orderDate
     * @param  string $invoiceNumber
     * @return array
     */
    public function createInvoice(array $itemsArr, array $shippingItem, int $userId, string $txnDateTime, string $invoiceNumber)
    {
        $this->params['transaction_id'] = $invoiceNumber;
        $this->params['transaction_date'] = $this->formatDateTime($txnDateTime);
        $this->setItemsForOrder($itemsArr, $shippingItem);

        try {
            $order = $this->client->createOrder($this->params);
        } catch (exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return [
            'status' => true,
            'referenceId' => $order->transaction_id,
            'data' => $order
        ];
    }

    private function formatDateTime($txnDateTime)
    {
        return date("Y-m-d\TH:i:s", strtotime($txnDateTime));
    }

    private function setItemsForOrder($itemsArr, $shippingItem)
    {
        $lineItems = [];
        $salesTax = 0;
        $totalDiscount = 0;
        $totalAmount = 0;
        //$netAmount = ($childOrderInfo['op_unit_price'] * $quantity) - abs($discount)  + $shippingAmount;
        //348 - 86.12 - 0.1 + 30 =
        foreach ($itemsArr as $item) {
            $arr = [
                'id' => $item['itemCode'],
                'quantity' => $item['quantity'],
                'product_identifier' => $item['productName'],
                'description' => $item['description'],
                'unit_price' => $item['amount'],
                'discount' => $item['discount'],
                'sales_tax' => 0 + $item['salesTax'],
            ];

            $totalAmount = $totalAmount + ($item['amount'] * $item['quantity']);
            $salesTax = $salesTax + $item['salesTax'];
            $totalDiscount = $item['discount'];
            array_push($lineItems, $arr);
        }

        $shipAmount = 0;
        foreach ($shippingItem as $item) {
            $shipAmount = $shipAmount + $item['amount'];
        }

        $this->params['line_items'] = $lineItems;
        $this->params['shipping'] = $shipAmount;
        $this->params['sales_tax'] = $salesTax;
        $this->params['amount'] = $totalAmount - $totalDiscount + $shipAmount;
    }

    private function setItems($itemsArr, $shippingItem, $userId)
    {
        $totalAmount = 0;
        $lineItems = [];

        foreach ($itemsArr as $item) {
            $arr = [
                'id' => $item['itemCode'],
                'quantity' => $item['quantity'],
                'product_tax_code' => $item['taxCode'],
                'unit_price' => $item['amount'],
                'discount' => 0
            ];
            $totalAmount = $totalAmount + ($item['amount'] * $item['quantity']);
            array_push($lineItems, $arr);
        }

        $shipAmount = 0;
        foreach ($shippingItem as $item) {
            $shipAmount = $shipAmount + $item['amount'];
        }

        $this->params['line_items'] = $lineItems;
        $this->params['shipping'] = $shipAmount;
        $this->params['amount'] = $totalAmount;
    }

    private function setFromAddress(array $address)
    {
        if (!$this->validateAddress($address)) {
            return false;
        }
        $this->params['from_country'] = $address['countryCode'];
        $this->params['from_zip'] = $address['postalCode'];
        $this->params['from_state'] = $address['stateCode'];
        $this->params['from_city'] = $address['city'];
        $this->params['from_street'] = $address['line1'] . " " . $address['line2'];
        return $this;
    }


    private function setToAddress(array $address)
    {
        if (!$this->validateAddress($address)) {
            return false;
        }

        /*if ($this->params['from_state'] != $this->params['to_state']) {
            $regions = $this->client->nexusRegions();
            if (empty($regions) || count($regions['regions']) == 0) {
                $this->error = "Invalid_nexus_regions";
                return false;
            }

            foreach($regions as $region) {
                if ($region->region_code == $address['stateCode']) {
                    $this->params['nexus_addresses'] = [
                        'country' => $region->country_code,
                        'state' => $region->region_code,
                    ];
                }
            }
        }*/

        $this->params['to_country'] = $address['countryCode'];
        $this->params['to_zip'] = $address['postalCode'];
        $this->params['to_state'] = $address['stateCode'];
        $this->params['to_city'] = $address['city'];
        $this->params['to_street'] = $address['line1'] . " " . $address['line2'];
        return $this;
    }

    private function validateAddress(array $address)
    {
        if (!is_array($address)) {
            return false;
        }

        // if (0 < $this->settings['env']) {
        //     try {
        //         $addresses = $this->client->validateAddress([
        //             'country' => $address['countryCode'],
        //             'state' => $address['stateCode'],
        //             'zip' => $address['postalCode'],
        //             'city' => $address['city'],
        //             'street' => $address['line1'] . ' ' . $address['line2']
        //         ]);
        //     } catch(exception $e){
        //         $this->error = $e->getMessage();
        //         return false;
        //     }
        //     return true;
        // }

        $requiredKeys = ['line1', 'line2', 'city', 'state', 'postalCode', 'country', 'stateCode', 'countryCode'];
        return !array_diff($requiredKeys, array_keys($address));
    }

    private function getRateTypesNames()
    {
        return array(
            static::RATE_TYPE_STATE => Labels::getLabel('LBL_STATE_TAX', $this->langId),
            static::RATE_TYPE_COUNTY => Labels::getLabel('LBL_COUNTRY_TAX', $this->langId),
            static::RATE_TYPE_CITY => Labels::getLabel('LBL_CITY_TAX', $this->langId),
            static::RATE_TYPE_QST => Labels::getLabel('LBL_QST_TAX', $this->langId),
            static::RATE_TYPE_PST => Labels::getLabel('LBL_PST_TAX', $this->langId),
            static::RATE_TYPE_GST => Labels::getLabel('LBL_GST_TAX', $this->langId),
            static::RATE_TYPE_SPECIAL => Labels::getLabel('LBL_SPECIAL_TAX', $this->langId),
        );
    }

    private function getRateTypesKeys()
    {
        return array(
            static::RATE_TYPE_STATE => 'state_amount',
            static::RATE_TYPE_COUNTY => 'county_amount',
            static::RATE_TYPE_CITY => 'city_amount',
            static::RATE_TYPE_QST => 'qst',
            static::RATE_TYPE_PST => 'pst',
            static::RATE_TYPE_GST => 'gst',
            static::RATE_TYPE_SPECIAL => 'special_district_amount',
        );
    }

    private function getTaxRatesKeys()
    {
        return array(
            static::TAX_RATE_STATE => 'state_sales_tax_rate',
            static::TAX_RATE_COUNTY => 'county_tax_rate',
            static::TAX_RATE_CITY => 'city_tax_rate',
            static::TAX_RATE_QST => 'qst',
            static::TAX_RATE_PST => 'pst',
            static::TAX_RATE_GST => 'gst',
            static::TAX_RATE_SPECIAL => 'special_tax_rate',
        );
    }
}
