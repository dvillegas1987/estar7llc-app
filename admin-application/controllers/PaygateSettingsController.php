<?php

class PaygateSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmPaygate');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_PAYGATE_ID', $langId), 'paygate_id');
        $fld1 = new FormFieldRequirement('paygate_id', Labels::getLabel('LBL_PAYGATE_ID', $langId));
        $fld1->setRequired(false);
        $reqFld1 = new FormFieldRequirement('paygate_id', Labels::getLabel('LBL_PAYGATE_ID', $langId));
        $reqFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_ENCRYPTION_KEY', $langId), 'encryption_key');
        $fld2 = new FormFieldRequirement('encryption_key', Labels::getLabel('LBL_ENCRYPTION_KEY', $langId));
        $fld2->setRequired(false);
        $reqFld2 = new FormFieldRequirement('encryption_key', Labels::getLabel('LBL_ENCRYPTION_KEY', $langId));
        $reqFld2->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_PAYGATE_ID', $langId), 'live_paygate_id');
        $liveFld1 = new FormFieldRequirement('live_paygate_id', Labels::getLabel('LBL_PAYGATE_ID', $langId));
        $liveFld1->setRequired(false);
        $reqLiveFld1 = new FormFieldRequirement('live_paygate_id', Labels::getLabel('LBL_PAYGATE_ID', $langId));
        $reqLiveFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_ENCRYPTION_KEY', $langId), 'live_encryption_key');
        $liveFld2 = new FormFieldRequirement('live_encryption_key', Labels::getLabel('LBL_ENCRYPTION_KEY', $langId));
        $liveFld2->setRequired(false);
        $reqLiveFld2 = new FormFieldRequirement('live_encryption_key', Labels::getLabel('LBL_ENCRYPTION_KEY', $langId));
        $reqLiveFld2->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'paygate_id', $reqFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'encryption_key', $reqFld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_paygate_id', $liveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_encryption_key', $liveFld2);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'paygate_id', $fld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'encryption_key', $fld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_paygate_id', $reqLiveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_encryption_key', $reqLiveFld2);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
