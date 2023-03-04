<?php

class TaxJarTaxSettingsController extends TaxSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmPayPal');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SANDBOX_KEY/TOKEN', $langId), 'sandbox_key');
        $sandBoxFld = new FormFieldRequirement('sandbox_key', Labels::getLabel('LBL_SANDBOX_KEY/TOKEN', $langId));
        $sandBoxFld->setRequired(false);
        $reqSandBoxFld = new FormFieldRequirement('sandbox_key', Labels::getLabel('LBL_SANDBOX_KEY/TOKEN', $langId));
        $reqSandBoxFld->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_LIVE_KEY/TOKEN', $langId), 'live_key');
        $liveKeyFld = new FormFieldRequirement('live_key', Labels::getLabel('LBL_LIVE_KEY/TOKEN', $langId));
        $liveKeyFld->setRequired(false);
        $reqLiveKeyFld = new FormFieldRequirement('live_key', Labels::getLabel('LBL_LIVE_KEY/TOKEN', $langId));
        $reqLiveKeyFld->setRequired(true);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'sandbox_key', $reqSandBoxFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_key', $liveKeyFld);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'sandbox_key', $sandBoxFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_key', $reqLiveKeyFld);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
