<?php

class PaytmSettingsController extends PaymentMethodSettingsController
{

    public static function form(int $langId)
    {
        $frm = new Form('frmPaymentMethods');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_Merchant_ID', $langId), 'merchant_id');
        $merIdReqObj = new FormFieldRequirement('merchant_id', Labels::getLabel('LBL_Merchant_ID', $langId));
        $merIdReqObj->setRequired(true);
        $merIdUnReqObj = new FormFieldRequirement('merchant_id', Labels::getLabel('LBL_Merchant_ID', $langId));
        $merIdUnReqObj->setRequired(false);

        $frm->addTextBox(Labels::getLabel('LBL_Merchant_Key', $langId), 'merchant_key');
        $merKeyReqObj = new FormFieldRequirement('merchant_key', Labels::getLabel('LBL_Merchant_Key', $langId));
        $merKeyReqObj->setRequired(true);
        $merKeyUnReqObj = new FormFieldRequirement('merchant_key', Labels::getLabel('LBL_Merchant_Key', $langId));
        $merKeyUnReqObj->setRequired(false);

        $frm->addTextBox(Labels::getLabel('LBL_Website', $langId), 'merchant_website');
        $merWebReqObj = new FormFieldRequirement('merchant_website', Labels::getLabel('LBL_Website', $langId));
        $merWebReqObj->setRequired(true);
        $merWebUnReqObj = new FormFieldRequirement('merchant_website', Labels::getLabel('LBL_Website', $langId));
        $merWebUnReqObj->setRequired(false);

        $frm->addTextBox(Labels::getLabel('LBL_Merchant_ID', $langId), 'live_merchant_id');
        $merIdLiveReqObj = new FormFieldRequirement('live_merchant_id', Labels::getLabel('LBL_Merchant_ID', $langId));
        $merIdLiveReqObj->setRequired(true);
        $merIdLiveUnReqObj = new FormFieldRequirement('live_merchant_id', Labels::getLabel('LBL_Merchant_ID', $langId));
        $merIdLiveUnReqObj->setRequired(false);

        $frm->addTextBox(Labels::getLabel('LBL_Merchant_Key', $langId), 'live_merchant_key');
        $merKeyLiveReqObj = new FormFieldRequirement('live_merchant_key', Labels::getLabel('LBL_Merchant_Key', $langId));
        $merKeyLiveReqObj->setRequired(true);
        $merKeyLiveUnReqObj = new FormFieldRequirement('live_merchant_key', Labels::getLabel('LBL_Merchant_Key', $langId));
        $merKeyLiveUnReqObj->setRequired(false);

        $frm->addTextBox(Labels::getLabel('LBL_Website', $langId), 'live_merchant_website');
        $merWebLiveReqObj = new FormFieldRequirement('live_merchant_website', Labels::getLabel('LBL_Website', $langId));
        $merWebLiveReqObj->setRequired(true);
        $merWebLiveUnReqObj = new FormFieldRequirement('live_merchant_website', Labels::getLabel('LBL_Website', $langId));
        $merWebLiveUnReqObj->setRequired(false);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'merchant_id', $merIdReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'merchant_key', $merKeyReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'merchant_website', $merWebReqObj);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_merchant_id', $merIdLiveUnReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_merchant_key', $merKeyLiveUnReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_merchant_website', $merWebLiveUnReqObj);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_merchant_id', $merIdLiveReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_merchant_key', $merKeyLiveReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_merchant_website', $merWebLiveReqObj);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'merchant_id', $merIdUnReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'merchant_key', $merKeyUnReqObj);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'merchant_website', $merWebUnReqObj);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

}
