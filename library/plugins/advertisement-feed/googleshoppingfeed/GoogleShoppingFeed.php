<?php

class GoogleShoppingFeed extends AdvertisementFeedBase
{
    public const KEY_NAME = __CLASS__;
    public const PAGE_SIZE = 10;

    private $merchantId;

    public $requiredKeys = [
        'client_id',
        'client_secret',
        'developer_key',
    ];

    /**
     * __construct
     *
     * @param  int $langId
     * @param  int $userId
     * @return void
     */
    public function __construct(int $langId, int $userId = 0)
    {
        $this->langId = $langId;
        $this->userId = $userId;
        $this->merchantId = $this->getUserMeta(self::KEY_NAME . '_merchantId');
    }

    /**
     * ageGroup
     *
     * @param  mixed $langId
     * @return array
     */
    public static function ageGroup(int $langId): array
    {
        return [
            'newborn' => Labels::getLabel('LBL_UP_TO_3_MONTHS_OLD', $langId) . ' - ' . Labels::getLabel('LBL_NEWBORN', $langId),
            'infant' => Labels::getLabel('LBL_BETWEEN_3-12_MONTHS_OLD', $langId) . ' - ' . Labels::getLabel('LBL_INFANT', $langId),
            'toddler' => Labels::getLabel('LBL_BETWEEN_1-5_YEARS_OLD', $langId) . ' - ' . Labels::getLabel('LBL_TODDLER', $langId),
            'kids' => Labels::getLabel('LBL_BETWEEN_5-13_YEARS_OLD', $langId) . ' - ' . Labels::getLabel('LBL_KIDS', $langId),
            'adult' => Labels::getLabel('LBL_TYPICALLY_TEENS_OR_OLDER', $langId) . ' - ' . Labels::getLabel('LBL_ADULT', $langId),
        ];
    }

    /**
     * doRequest
     *
     * @param  array $data
     * @return void
     */
    private function doRequest(array $data)
    {
        if (false === $this->merchantId || 1 > $this->merchantId) {
            $this->error = Labels::getLabel('LBL_INVALID_MERCHANT', $this->langId);
            return false;
        }

        if (empty($data) || !array_key_exists('data', $data)) {
            $this->error = Labels::getLabel('LBL_PLEASE_PASS_REQUIRED_PRODUCT_DATA', $this->langId);
            return false;
        }

        $client = new Google_Client();
        $serviceAccountDetail = $this->getUserMeta('service_account');
        if (empty($serviceAccountDetail)) {
            $this->error = Labels::getLabel('LBL_SERVICE_ACCOUNT_DETAIL_NOT_FOUND', $this->langId);
            return false;
        }

        if (!array_key_exists('currency_code', $data)) {
            $this->error = Labels::getLabel('LBL_INVALID_CURRENCY', $this->langId);
            return false;
        }

        $serviceAccountDetail = json_decode($serviceAccountDetail, true);
        $client->setAuthConfig($serviceAccountDetail);
        $client->setScopes(Google_Service_ShoppingContent::CONTENT);
        $client->useApplicationDefaultCredentials();
        $client->setUseBatch(true);

        $service = new Google_Service_ShoppingContent($client);
        $batch = $service->createBatch();

        /* $channel = $this->getSettings('channel');
        if (false === $channel) {
            return false;
        } */

        $request = [];
        foreach ($data['data'] as $prodDetail) {
            $colorOption = array_filter($prodDetail['optionsData'], function ($v) {
                return array_key_exists('option_is_color', $v) && 1 == $v['option_is_color'];
            });
            $color = !empty($colorOption) ? array_shift($colorOption)['optionvalue_identifier'] : '';

            $product = new Google_Service_ShoppingContent_Product();
            $product->setId($prodDetail['selprod_id']);
            $product->setOfferId($prodDetail['selprod_id']);
            $product->setTitle($prodDetail['selprod_title']);
            $product->setDescription($prodDetail['product_description']);
            $product->setColor($color);
            $product->setItemGroupId($prodDetail['abprod_item_group_identifier']);
            $product->setBrand(ucfirst($prodDetail['brand_name']));
            $product->setLink(UrlHelper::generateFullUrl('Products', 'View', array($prodDetail['selprod_id'])));
            $product->setImageLink(UrlHelper::generateFullUrl('image', 'product', array($prodDetail['product_id'], "MEDIUM", $prodDetail['selprod_id'], 0, $this->langId)));
            $product->setContentLanguage(strtolower($prodDetail['language_code']));
            $product->setTargetCountry(strtoupper($prodDetail['country_code']));
            $product->setChannel('online');
            $product->setAvailability($prodDetail['selprod_stock']);
            $product->setAvailabilityDate(date('Y-m-d', strtotime($prodDetail['selprod_available_from'])));

            $timestamp = strtotime($prodDetail['adsbatch_expired_on']);
            if (0 < $timestamp) {
                $product->setExpirationDate(date('Y-m-d', $timestamp));
            }
            $product->setCondition($prodDetail['selprod_condition']);
            $product->setGoogleProductCategory($prodDetail['abprod_cat_id']);
            $product->setGtin($prodDetail['product_upc']);

            $price = new Google_Service_ShoppingContent_Price();
            $price->setValue($prodDetail['selprod_price']);

            $currencyCode = array_key_exists('currency_code', $data) ? $data['currency_code'] : '';
            if ('' != $currencyCode) {
                $price->setCurrency($data['currency_code']);
            }
            $product->setPrice($price);

            $request = $service->products->insert($this->merchantId, $product);
            $batch->add($request, $product->getOfferId());
        }
        if (empty($request)) {
            $this->error = Labels::getLabel('LBL_INVALID_PRODUCT_REQUEST', $this->langId);
            return false;
        }

        return $batch->execute();
    }

    /**
     * getProductCategory
     *
     * @param  string $keyword
     * @param  bool $returnFullArray
     * @return array
     */
    public function getProductCategory(string $keyword = '', bool $returnFullArray = false): array
    {
        $arr = [];
        if ($fh = fopen(__DIR__ . '/googleProductCategory.txt', 'r')) {
            $rowIndex = 1;
            while (!feof($fh)) {
                $line = fgets($fh);
                if ($returnFullArray || false !== stripos($line, $keyword)) {
                    $lineContentArr = explode('-', $line, 2);
                    if (!empty($lineContentArr) && 1 < count($lineContentArr)) {
                        $arr[trim($lineContentArr[0])] = trim($lineContentArr[1]);
                    }
                    $rowIndex++;
                }

                if (false === $returnFullArray && $rowIndex == self::PAGE_SIZE) {
                    break;
                }
            }
            fclose($fh);
        }
        ksort($arr);
        return $arr;
    }

    /**
     * publishBatch
     *
     * @param  mixed $data
     * @return array
     */
    public function publishBatch(array $data): array
    {
        $status = empty($data) ? Plugin::RETURN_FALSE : Plugin::RETURN_TRUE;
        $msg = Labels::getLabel('MSG_PUBLISHED_SUCESSFULLY', $this->langId);
        $data = ($status == Plugin::RETURN_TRUE ? $this->doRequest($data) : '');
        if (false === $data) {
            $status = Plugin::RETURN_FALSE;
        }

        if (is_array($data) && !empty($data) && method_exists((current($data)), 'getErrors')) {
            $errors = (current($data))->getErrors();
            $this->error = '';
            foreach ($errors as $error) {
                $this->error .= $error['message'] . '. ';
            }
            $status = Plugin::RETURN_FALSE;
        }

        $errorMsg = '' == $this->getError() ? Labels::getLabel('MSG_INVALID_REQUEST', $this->langId) : $this->getError();
        $msg = ($status == Plugin::RETURN_FALSE ? $errorMsg : $msg);
        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
