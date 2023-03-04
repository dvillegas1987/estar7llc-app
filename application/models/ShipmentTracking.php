<?php
class ShipmentTracking
{
    private $keyName;
    private $shipmentTracking;
    private $langId;
    private $response;
    private $error;
        
    /**
     * init
     *
     * @param  int $langId
     * @return void
     */
    public function init(int $langId)
    {
        $this->langId = $langId;
        
        $plugin = new Plugin();
        $this->keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPMENT_TRACKING);
        if (false === $this->keyName) {
            $this->error = $plugin->getError();
            return false;
        }

        $this->shipmentTracking = PluginHelper::callPlugin($this->keyName, [$this->langId], $this->error, $this->langId, false);
        if (false === $this->shipmentTracking) {
            return false;
        }

        if (false === $this->shipmentTracking->init()) {
            $this->error = $this->shipmentTracking->getError();
            return false;
        }

        return true;
    }
    
    /**
     * validateRequest
     *
     * @return void
     */
    private function validateRequest()
    {
        if (NULL === $this->shipmentTracking) {
            trigger_error(Labels::getLabel('LBL_MISSING_INITIAL_STEPS', $this->langId), E_USER_ERROR);
        }
    }
    
    /**
     * getTrackingInfo
     *
     * @param  string $trackingNumber
     * @param  string $courierCode
     * @return bool
     */
    public function getTrackingInfo(string $trackingNumber, string $courierCode): bool
    {
        $this->validateRequest();

        if (false === $this->shipmentTracking->getTrackingInfo($trackingNumber, $courierCode)) {
            $this->error = $this->shipmentTracking->getError();
            return false;
        }
        $this->response = $this->shipmentTracking->getResponse();
        return true;
    }
    
    /**
     * getTrackingCouriers
     *
     * @return bool
     */
    public function getTrackingCouriers(): bool
    {
        $this->validateRequest();

        if (false === $this->shipmentTracking->getTrackingCouriers()) {
            $this->error = $this->shipmentTracking->getError();
            return false;
        }
        $this->response = $this->shipmentTracking->getResponse();
        return true;
    }
    
    /**
     * createTracking
     *
     * @param  string $trackingNumber
     * @param  string $courierCode
     * @param  string $orderId
     * @return bool
     */
    public function createTracking(string $trackingNumber, string $courierCode, string $orderId): bool
    {
        $this->validateRequest();

        if (false === $this->shipmentTracking->createTracking($trackingNumber, $courierCode, $orderId)) {
            $this->error = $this->shipmentTracking->getError();
            return false;
        }
        $this->response = $this->shipmentTracking->getResponse();
        return true;
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
     * @return void
     */
    public function getError()
    {
        return $this->error;
    }
}
