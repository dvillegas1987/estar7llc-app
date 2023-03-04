<?php

class ShippingServicesController extends AdminBaseController
{    
    use ShippingServices;
    
    /**
     * __construct
     *
     * @param  string $action
     * @return void
     */
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->objPrivilege->canViewShippingManagement($this->admin_id);
        $this->canEdit = $this->objPrivilege->canEditShippingManagement($this->admin_id, true);
        $this->set("canEdit", $this->canEdit);
        $this->langId = $this->adminLangId;
        $this->init();
    }
}
