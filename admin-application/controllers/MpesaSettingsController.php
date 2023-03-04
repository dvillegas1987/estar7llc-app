<?php

class MpesaSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmMpesa');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addRequiredField(Labels::getLabel('LBL_CONSUMER_KEY', $langId), 'consumer_key');
        $frm->addRequiredField(Labels::getLabel('LBL_CONSUMER_SECRET', $langId), 'consumer_secret');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_ACCOUNT_REFERENCE', $langId), 'account_reference');
        $fld->htmlAfterField = '<span class="form-text text-muted">' . Labels::getLabel("LBL_MPESA_ACCOUNT_REFERENCE_DESCRIPTION", $langId) . '</span>';

        $frm->addTextBox(Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_SHORTCODE', $langId), 'shortcode');
        $shortCodeFld = new FormFieldRequirement('shortcode', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_SHORTCODE', $langId));
        $shortCodeFld->setRequired(false);
        $reqShortCodeFld = new FormFieldRequirement('shortcode', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_SHORTCODE', $langId));
        $reqShortCodeFld->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_PASSKEY', $langId), 'passkey');
        $passKeyFld = new FormFieldRequirement('passkey', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_PASSKEY', $langId));
        $passKeyFld->setRequired(false);
        $reqPassKeyFld = new FormFieldRequirement('passkey', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_PASSKEY', $langId));
        $reqPassKeyFld->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_SHORTCODE', $langId), 'live_shortcode');
        $liveShortCodeFld = new FormFieldRequirement('live_shortcode', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_SHORTCODE', $langId));
        $liveShortCodeFld->setRequired(false);
        $reqLiveShortCodeFld = new FormFieldRequirement('live_shortcode', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_SHORTCODE', $langId));
        $reqLiveShortCodeFld->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_PASSKEY', $langId), 'live_passkey');
        $livePassKeyFld = new FormFieldRequirement('live_passkey', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_PASSKEY', $langId));
        $livePassKeyFld->setRequired(false);
        $reqLivePassKeyFld = new FormFieldRequirement('live_passkey', Labels::getLabel('LBL_LIPA_NA_MPESA_ONLINE_PASSKEY', $langId));
        $reqLivePassKeyFld->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'shortcode', $reqShortCodeFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'passkey', $reqPassKeyFld);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_shortcode', $liveShortCodeFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_passkey', $livePassKeyFld);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'shortcode', $shortCodeFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'passkey', $passKeyFld);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_shortcode', $reqLiveShortCodeFld);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_passkey', $reqLivePassKeyFld);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
