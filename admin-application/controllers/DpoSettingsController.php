<?php

class DpoSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmDpo');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_COMPANY_TOKEN', $langId), 'company_token');
        $fld1 = new FormFieldRequirement('company_token', Labels::getLabel('LBL_COMPANY_TOKEN', $langId));
        $fld1->setRequired(false);
        $reqFld1 = new FormFieldRequirement('company_token', Labels::getLabel('LBL_COMPANY_TOKEN', $langId));
        $reqFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SERVICE_TYPE', $langId), 'service_type');
        $fld2 = new FormFieldRequirement('service_type', Labels::getLabel('LBL_SERVICE_TYPE', $langId));
        $fld2->setRequired(false);
        $reqFld2 = new FormFieldRequirement('service_type', Labels::getLabel('LBL_SERVICE_TYPE', $langId));
        $reqFld2->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_COMPANY_TOKEN', $langId), 'live_company_token');
        $liveFld1 = new FormFieldRequirement('live_company_token', Labels::getLabel('LBL_COMPANY_TOKEN', $langId));
        $liveFld1->setRequired(false);
        $reqLiveFld1 = new FormFieldRequirement('live_company_token', Labels::getLabel('LBL_COMPANY_TOKEN', $langId));
        $reqLiveFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SERVICE_TYPE', $langId), 'live_service_type');
        $liveFld2 = new FormFieldRequirement('live_service_type', Labels::getLabel('LBL_SERVICE_TYPE', $langId));
        $liveFld2->setRequired(false);
        $reqLiveFld2 = new FormFieldRequirement('live_service_type', Labels::getLabel('LBL_SERVICE_TYPE', $langId));
        $reqLiveFld2->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'company_token', $reqFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'service_type', $reqFld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_company_token', $liveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_service_type', $liveFld2);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'company_token', $fld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'service_type', $fld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_company_token', $reqLiveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_service_type', $reqLiveFld2);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
