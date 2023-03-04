<?php
class TrackingCodeRelationController extends AdminBaseController
{
    
    public function __construct($action)
    {
        parent::__construct($action);  
        if(!$this->objPrivilege->canViewTrackingRelationCode()){
            Message::addErrorMessage(Labels::getLabel('LBL_Please_activate_ship_station_and_tracking_plugins', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Plugins'));
        }
    }
    
    public function index()
    {
        $this->_template->render();
    }
    
    public function search()
    {
        $shipmentTracking = new ShipmentTracking(); 
		if (false === $shipmentTracking->init($this->adminLangId)) {
			Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
		}
		
        if(false === $shipmentTracking->getTrackingCouriers()) {
			Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
		}
		
        $trackingCourier = $shipmentTracking->getResponse();
        if($trackingCourier == false){
            Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $plugin = new Plugin();
        $shipApiPluginData = $plugin->getDefaultPluginData(Plugin::TYPE_SHIPPING_SERVICES);
        $shipApi = PluginHelper::callPlugin($shipApiPluginData['plugin_code'], [$this->adminLangId], $error, $this->adminLangId);
        if($shipApi->init() === false){              
            Message::addErrorMessage($shipApi->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $carriers = $shipApi->getCarriers();
        if(empty($carriers)){
            Message::addErrorMessage($shipApi->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
       
        $trackingApiPluginId = $plugin->getDefaultPluginData(Plugin::TYPE_SHIPMENT_TRACKING, 'plugin_id');
        $trackingRelation = new TrackingCourierCodeRelation();
        $records = $trackingRelation->getDefaultShipAndTrackingRecords($shipApiPluginData['plugin_id'], $trackingApiPluginId);
        
        $this->set('trackingCourier', $trackingCourier);
        $this->set('carriers', $carriers); 
        $this->set('records', $records); 
        $this->_template->render(false, false);
    }
    
    public function setUpCourierRelation()
    {
        $trackingApiCode = FatApp::getPostedData('trackingApiCode', FatUtility::VAR_STRING, '');
        $shipApiCode = FatApp::getPostedData('shipApiCode', FatUtility::VAR_STRING, '');
        if(empty($trackingApiCode) || empty($shipApiCode)){
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $plugin = new Plugin();
        $trackingApiPluginId = $plugin->getDefaultPluginData(Plugin::TYPE_SHIPMENT_TRACKING, 'plugin_id');
        $shipApiPluginId = $plugin->getDefaultPluginData(Plugin::TYPE_SHIPPING_SERVICES, 'plugin_id');
        if($trackingApiPluginId < 1 || $shipApiPluginId < 1){
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $data = array(
            'tccr_shipapi_plugin_id' => $shipApiPluginId,
            'tccr_shipapi_courier_code' => $shipApiCode,
            'tccr_tracking_plugin_id' => $trackingApiPluginId,
            'tccr_tracking_courier_code' => $trackingApiCode
            
        );
        if (!FatApp::getDb()->insertFromArray(TrackingCourierCodeRelation::DB_TBL, $data, false, array(), $data)) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
 
}
