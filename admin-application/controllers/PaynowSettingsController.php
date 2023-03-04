<?php

class PaynowSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmPaynow');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_API_ACCESS_KEY', $langId), 'api_access_key');
        $fld1 = new FormFieldRequirement('api_access_key', Labels::getLabel('LBL_API_ACCESS_KEY', $langId));
        $fld1->setRequired(false);
        $reqFld1 = new FormFieldRequirement('api_access_key', Labels::getLabel('LBL_API_ACCESS_KEY', $langId));
        $reqFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SIGNATURE_CALCULATION_KEY', $langId), 'signature_calculation_key');
        $fld2 = new FormFieldRequirement('signature_calculation_key', Labels::getLabel('LBL_SIGNATURE_CALCULATION_KEY', $langId));
        $fld2->setRequired(false);
        $reqFld2 = new FormFieldRequirement('signature_calculation_key', Labels::getLabel('LBL_SIGNATURE_CALCULATION_KEY', $langId));
        $reqFld2->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_API_ACCESS_KEY', $langId), 'live_api_access_key');
        $liveFld1 = new FormFieldRequirement('live_api_access_key', Labels::getLabel('LBL_API_ACCESS_KEY', $langId));
        $liveFld1->setRequired(false);
        $reqLiveFld1 = new FormFieldRequirement('live_api_access_key', Labels::getLabel('LBL_API_ACCESS_KEY', $langId));
        $reqLiveFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SIGNATURE_CALCULATION_KEY', $langId), 'live_signature_calculation_key');
        $liveFld2 = new FormFieldRequirement('live_signature_calculation_key', Labels::getLabel('LBL_SIGNATURE_CALCULATION_KEY', $langId));
        $liveFld2->setRequired(false);
        $reqLiveFld2 = new FormFieldRequirement('live_signature_calculation_key', Labels::getLabel('LBL_SIGNATURE_CALCULATION_KEY', $langId));
        $reqLiveFld2->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'api_access_key', $reqFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'signature_calculation_key', $reqFld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_api_access_key', $liveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_signature_calculation_key', $liveFld2);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'api_access_key', $fld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'signature_calculation_key', $fld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_api_access_key', $reqLiveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_signature_calculation_key', $reqLiveFld2);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
