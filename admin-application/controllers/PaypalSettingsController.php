<?php

class PaypalSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmPayPal');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addRequiredField(Labels::getLabel('LBL_PAYEE_EMAIL', $langId), 'payee_email');

        $frm->addTextBox(Labels::getLabel('LBL_CLIENT_ID', $langId), 'client_id');
        $clientIdFld = new FormFieldRequirement('client_id', Labels::getLabel('LBL_CLIENT_ID', $langId));
        $clientIdFld->setRequired(false);
        $reqClientIdFld = new FormFieldRequirement('client_id', Labels::getLabel('LBL_CLIENT_ID', $langId));
        $reqClientIdFld->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SECRET_KEY', $langId), 'secret_key');
        $secretKeyFld = new FormFieldRequirement('secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $secretKeyFld->setRequired(false);
        $reqSecretKeyFld = new FormFieldRequirement('secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $reqSecretKeyFld->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_CLIENT_ID', $langId), 'live_client_id');
        $liveClientIdFld = new FormFieldRequirement('live_client_id', Labels::getLabel('LBL_CLIENT_ID', $langId));
        $liveClientIdFld->setRequired(false);
        $reqLiveClientIdFld = new FormFieldRequirement('live_client_id', Labels::getLabel('LBL_CLIENT_ID', $langId));
        $reqLiveClientIdFld->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_SECRET_KEY', $langId), 'live_secret_key');
        $liveSecretKeyFld = new FormFieldRequirement('live_secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $liveSecretKeyFld->setRequired(false);
        $reqLiveSecretKeyFld = new FormFieldRequirement('live_secret_key', Labels::getLabel('LBL_SECRET_KEY', $langId));
        $reqLiveSecretKeyFld->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'client_id', $reqClientIdFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'secret_key', $reqSecretKeyFld);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_client_id', $liveClientIdFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_secret_key', $liveSecretKeyFld);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'client_id', $clientIdFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'secret_key', $secretKeyFld);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_client_id', $reqLiveClientIdFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_secret_key', $reqLiveSecretKeyFld);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
