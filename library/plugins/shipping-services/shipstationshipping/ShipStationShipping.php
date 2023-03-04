<?php

require_once dirname(__FILE__) . '/ShipStationFunctions.php';

class ShipStationShipping extends ShippingServicesBase
{
    use ShipStationFunctions;

    public const KEY_NAME = __CLASS__;
    public const HOST = 'ssapi.shipstation.com';
    public const PRODUCTION_URL = 'https://' . self::HOST . '/';

    private const REQUEST_CARRIER_LIST = 1;
    private const REQUEST_SHIPPING_RATES = 2;
    private const REQUEST_CREATE_ORDER = 3;
    private const REQUEST_CREATE_LABEL = 4;
    private const REQUEST_FULFILLMENTS = 5;
    private const REQUEST_GET_ORDER = 6;
    private const REQUEST_MARK_AS_SHIPPED = 7;

    private $resp;
    private $endpoint = '';

    public $requiredKeys = [
        'api_key',
        'api_secret_key'
    ];

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = FatUtility::int($langId);
        if (1 > $this->langId) {
            $this->langId = CommonHelper::getLangId();
        }
    }

    /**
     * init
     *
     * @return bool
     */
    public function init(): bool
    {
        return $this->validateSettings($this->langId);
    }

    /**
     * getCarriers
     *
     * @return array
     */
    public function getCarriers(): array
    {
        if (Plugin::INACTIVE == $this->settings['plugin_active'] || false === $this->doRequest(self::REQUEST_CARRIER_LIST)) {
            return [];
        }
        return $this->getResponse();
    }

    /**
     * getRates
     *
     * @param  string $carrierCode
     * @param  string $shipFromPostalCode
     * @param  int $langId
     * @return array
     */
    public function getRates(string $carrierCode, string $shipFromPostalCode): array
    {
        if (Plugin::INACTIVE == $this->settings['plugin_active'] || empty($this->address)) {
            return [];
        }

        $pkgDetail = [
            'carrierCode' => $carrierCode,
            'serviceCode' => null,
            'packageCode' => null,
            'fromPostalCode' => $shipFromPostalCode,
            'toState' => $this->address['state'],
            'toCountry' => $this->address['country'],
            'toPostalCode' => $this->address['postalCode'],
            'toCity' => $this->address['city'],
            'weight' => $this->getWeight(),
            'dimensions' => $this->getDimensions()
        ];

        if (false === $this->doRequest(self::REQUEST_SHIPPING_RATES, $pkgDetail)) {
            return [];
        }
        return $this->getResponse();
    }

    /**
     * addOrder
     *
     * @param  int $opId
     * @return bool
     */
    public function addOrder(int $opId): bool
    {
        $orderDetail = $this->getSystemOrder($opId);
        if (empty($orderDetail)) {
            return false;
        }

        $orderTimestamp = strtotime($orderDetail['order_date_added']);
        $orderDate = date('Y-m-d', $orderTimestamp) . 'T' . date('H:i:s', $orderTimestamp) . '.0000000';

        $taxCharged = 0;
        $orderInvoiceNumber = 0;

        $taxOptions = json_decode($orderDetail['op_product_tax_options'], true);

        $shippingTotal = CommonHelper::orderProductAmount($orderDetail, 'shipping');
        if (!empty($taxOptions)) {
            foreach ($taxOptions as $key => $val) {
                $taxCharged += $val['value'];
            }
        }
        $orderInvoiceNumber = $orderDetail['op_invoice_number'];

        $orderObj = new Orders($orderDetail['order_id']);
        $addresses = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $billingAddress = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $shippingAddress = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $this->order = [];
        $this->order['orderNumber'] = $orderInvoiceNumber;
        $this->order['orderKey'] = $orderInvoiceNumber; // if specified, the method becomes idempotent and the existing Order with that key will be updated
        $this->order['orderDate'] = $orderDate;
        $this->order['paymentDate'] = $orderDate;
        $this->order['orderStatus'] = "awaiting_shipment"; // {awaiting_shipment, on_hold, shipped, cancelled}
        $this->order['customerUsername'] = $orderDetail['buyer_user_name'];
        $this->order['customerEmail'] = $orderDetail['buyer_email'];
        $this->order['amountPaid'] = $orderDetail['order_net_amount'];
        $this->order['taxAmount'] = (1 > $taxCharged ? $orderDetail['order_tax_charged'] : $taxCharged);
        $this->order['shippingAmount'] = $shippingTotal;
        /* $this->order['customerNotes'] = null;
        $this->order['internalNotes'] = "Express Shipping Please"; */
        $this->order['paymentMethod'] = $orderDetail['plugin_name'];
        $this->order['carrierCode'] = $orderDetail['opshipping_carrier_code'];
        $this->order['serviceCode'] = $orderDetail['opshipping_service_code'];
        $this->order['packageCode'] = "package";
        /* $this->order['confirmation'] = null;
        $this->order['shipDate'] = null; */


        $this->setAddress($billingAddress['oua_name'], $billingAddress['oua_address1'], $billingAddress['oua_address2'], $billingAddress['oua_city'], $billingAddress['oua_state'], $billingAddress['oua_zip'], $billingAddress['oua_country_code'], $billingAddress['oua_phone']);
        $this->order['billTo'] = $this->getAddress();

        $this->setAddress($shippingAddress['oua_name'], $shippingAddress['oua_address1'], $shippingAddress['oua_address2'], $shippingAddress['oua_city'], $shippingAddress['oua_state'], $shippingAddress['oua_zip'], $shippingAddress['oua_country_code'], $shippingAddress['oua_phone']);
        $this->order['shipTo'] = $this->getAddress();

        $weightUnitsArr = applicationConstants::getWeightUnitsArr($this->langId, true);
        $weightUnitName = ($orderDetail['op_product_weight_unit']) ? $weightUnitsArr[$orderDetail['op_product_weight_unit']] : '';
        $productWeightInOunce = Shipping::convertWeightInOunce($orderDetail['op_product_weight'], $weightUnitName);

        $this->setWeight($productWeightInOunce);
        $this->order['weight'] = $this->getWeight();

        $lengthUnitsArr = applicationConstants::getLengthUnitsArr($this->langId, true);
        $dimUnitName = ($orderDetail['op_product_dimension_unit']) ? $lengthUnitsArr[$orderDetail['op_product_dimension_unit']] : '';

        $lengthInCenti = Shipping::convertLengthInCenti($orderDetail['op_product_length'], $dimUnitName);
        $widthInCenti = Shipping::convertLengthInCenti($orderDetail['op_product_width'], $dimUnitName);
        $heightInCenti = Shipping::convertLengthInCenti($orderDetail['op_product_height'], $dimUnitName);

        $this->setDimensions($lengthInCenti, $widthInCenti, $heightInCenti);
        $this->order['dimensions'] = $this->getDimensions();

        $this->setItem($orderDetail);
        $this->order['items'] = [$this->getItem()];
        return $this->doRequest(self::REQUEST_CREATE_ORDER, $this->order); //Return bool
    }

    /**
     * bindLabel - This function should be called after addOrder
     *
     * @return bool
     */
    public function bindLabel(array $requestParam): bool
    {
        return $this->doRequest(self::REQUEST_CREATE_LABEL, $requestParam); //Return bool
    }

    /**
     * downloadLabel
     *
     * @param  string $labelData
     * @param  string $filename
     * @return void
     */
    public function downloadLabel(string $labelData, string $filename = "label.pdf", bool $preview = false)
    {
        $disposition = (true === $preview ? 'inline' : 'attachment');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/pdf");
        header("Content-Disposition: " . $disposition . "; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");

        echo base64_decode($labelData);
        die;
    }

    /**
     * setAddress
     *
     * @param  string $name
     * @param  string $stt1
     * @param  string $stt2
     * @param  string $city
     * @param  string $state
     * @param  string $zip
     * @param  string $countryCode
     * @param  string $phone
     * @return bool
     */
    public function setAddress(string $name, string $stt1, string $stt2, string $city, string $state, string $zip, string $countryCode, string $phone): bool
    {
        $this->address = [];

        $this->address['name'] = $name; // This has to be a String... If you put NULL the API cries...
        $this->address['company'] = $name;
        $this->address['street1'] = $stt1;
        $this->address['street2'] = $stt2;
        $this->address['city'] = $city;
        $this->address['state'] = $state;
        $this->address['postalCode'] = $zip;
        $this->address['country'] = $countryCode;
        $this->address['phone'] = $phone;
        return true;
    }

    /**
     * getAddress
     *
     * @return array
     */
    public function getAddress(): array
    {
        return empty($this->address) ? [] : $this->address;
    }

    /**
     * setWeight
     *
     * @param  float $weight
     * @param  string $unit
     * @return bool
     */
    public function setWeight($weight, $unit = 'ounces'): bool
    {
        $this->weight = [];
        $this->weight['value'] = floatval($weight);
        $this->weight['units'] = trim($unit);

        return true;
    }

    /**
     * getWeight
     *
     * @return array
     */
    public function getWeight(): array
    {
        return empty($this->weight) ? [] : $this->weight;
    }

    /**
     * setDimensions
     *
     * @param  int $length
     * @param  int $width
     * @param  int $height
     * @param  string $unit
     * @return bool
     */
    public function setDimensions($length, $width, $height, $unit = 'centimeters'): bool
    {
        $this->dimensions = [];

        $this->dimensions['units'] = $unit;
        $this->dimensions['length'] = $length;
        $this->dimensions['width'] = $width;
        $this->dimensions['height'] = $height;

        return true;
    }

    /**
     * getDimensions
     *
     * @return array
     */
    public function getDimensions(): array
    {
        return empty($this->dimensions) ? [] : $this->dimensions;
    }

    /**
     * setItem
     *
     * @param  array $op
     * @return bool
     */
    public function setItem(array $op): bool
    {
        $this->item = [];

        $this->item['lineItemKey'] = $op['op_product_name'];
        $this->item['sku'] = $op['op_selprod_sku'];
        $this->item['name'] = $op['op_selprod_title'];
        $this->item['imageUrl'] = UrlHelper::generateFullUrl('image', 'product', array($op['selprod_product_id'], "THUMB", $op['op_selprod_id'], 0, $this->langId));
        $this->item['weight'] = $this->order['weight'];
        $this->item['quantity'] = $op['op_qty'];
        $this->item['unitPrice'] = $op['op_unit_price'];
        return true;
    }

    /**
     * getItem
     *
     * @return array
     */
    public function getItem(): array
    {
        return empty($this->item) ? [] : $this->item;
    }

    /**
     * getFulfillments - This function return order shipment detail
     *
     * @param  mixed $requestParam
     * @return bool
     */
    public function getFulfillments(array $requestParam): bool
    {
        return $this->doRequest(self::REQUEST_FULFILLMENTS, $requestParam);
    }

    /**
     * loadOrder
     *
     * @param  string $orderId
     * @return bool
     */
    public function loadOrder(string $orderId): bool
    {
        return $this->doRequest(self::REQUEST_GET_ORDER, [$orderId]);
    }

    /**
     * proceedToShipment
     *
     * @param  array $requestParam
     * @return bool
     */
    public function proceedToShipment(array $requestParam): bool
    {
        return $this->doRequest(self::REQUEST_MARK_AS_SHIPPED, $requestParam);
    }

    /**
     * doRequest
     *
     * @param  int $requestType
     * @param  mixed $requestParam
     * @param  bool $formatError
     * @return bool
     */
    private function doRequest(int $requestType, $requestParam = [], bool $formatError = true): bool
    {
        try {
            switch ($requestType) {
                case self::REQUEST_CARRIER_LIST:
                    $this->carrierList();
                    break;
                case self::REQUEST_SHIPPING_RATES:
                    $this->shippingRates($requestParam);
                    break;
                case self::REQUEST_CREATE_ORDER:
                    $this->createOrder($requestParam);
                    break;
                case self::REQUEST_CREATE_LABEL:
                    $this->createLabel($requestParam);
                    break;
                case self::REQUEST_FULFILLMENTS:
                    $this->fulfillments($requestParam);
                    break;
                case self::REQUEST_GET_ORDER:
                    $this->getOrder($requestParam);
                    break;
                case self::REQUEST_MARK_AS_SHIPPED:
                    $this->markAsShipped($requestParam);
                    break;
            }

            if (array_key_exists('Message', (array)$this->getResponse(true))) {
                $this->error = (true === $formatError) ? $this->getResponse(true) : $this->resp;
                if (true === $formatError) {
                    $this->error = $this->formatError();
                }
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        } catch (Error $e) {
            $this->error = $e->getMessage();
        }

        $this->error =  (true === $formatError ? $this->formatError() : $this->error);
        return false;
    }
}
