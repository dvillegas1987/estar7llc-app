<?php

class PaystackSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmPaystack');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SECRET_KEY', $langId), 'secret_key');
        $fld1 = new FormFieldRequirement('secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $fld1->setRequired(false);
        $reqFld1 = new FormFieldRequirement('secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $reqFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_PUBLIC_KEY', $langId), 'public_key');
        $fld2 = new FormFieldRequirement('public_key', Labels::getLabel('LBL_PUBLIC_KEY', $langId));
        $fld2->setRequired(false);
        $reqFld2 = new FormFieldRequirement('public_key', Labels::getLabel('LBL_PUBLIC_KEY', $langId));
        $reqFld2->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_SECRET_KEY', $langId), 'live_secret_key');
        $liveFld1 = new FormFieldRequirement('live_secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $liveFld1->setRequired(false);
        $reqLiveFld1 = new FormFieldRequirement('live_secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $reqLiveFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_PUBLIC_KEY', $langId), 'live_public_key');
        $liveFld2 = new FormFieldRequirement('live_public_key', Labels::getLabel('LBL_PUBLIC_KEY', $langId));
        $liveFld2->setRequired(false);
        $reqLiveFld2 = new FormFieldRequirement('live_public_key', Labels::getLabel('LBL_PUBLIC_KEY', $langId));
        $reqLiveFld2->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'secret_key', $reqFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'public_key', $reqFld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_secret_key', $liveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_public_key', $liveFld2);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'secret_key', $fld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'public_key', $fld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_secret_key', $reqLiveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_public_key', $reqLiveFld2);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
