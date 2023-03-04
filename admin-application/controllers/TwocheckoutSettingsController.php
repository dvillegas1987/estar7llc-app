<?php

class TwocheckoutSettingsController extends PaymentMethodSettingsController
{
    public const KEY_NAME = 'Twocheckout';
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', [], Labels::getLabel('LBL_Select', $langId));
        $envFld->requirement->setRequired(true);

        /* $paymentTypesArr = array(
            'HOSTED' => 'Hosted Checkout',
            'API' => 'Payment API'
        );
        $frm->addRadioButtons(Labels::getLabel('LBL_Payment_Type', $langId), 'payment_type', $paymentTypesArr, 'HOSTED', array('class' => 'list-inline')); */
        $frm->addRequiredField(Labels::getLabel('LBL_MERCHANT_CODE', $langId), 'sellerId');
        $frm->addRequiredField(Labels::getLabel('LBL_Publishable_Key', $langId), 'publishableKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Private_Key', $langId), 'privateKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Word', $langId), 'hashSecretWord');

        $frm->addHTML(
            'Remember',
            '&nbsp;',
            '<div class="row justify-content-center">
                <div class="col-md-8">
                    <p class="bg-gray p-4">
                        In case of <strong>Hosted Checkout</strong>, Admin must set <strong>Redirect URL</strong> in which :<br>
                        <strong>Return method : Header Redirect</strong><br>  
                        <strong>Approved URL : ' . UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', 'callback', [], CONF_WEBROOT_FRONT_URL)  . '</strong><br>
                        Under <strong><a href="https://secure.2checkout.com/cpanel/webhooks_api.php" target="_blank">Integration > Webhooks & API</a></strong> tab find "Redirect URL" section.<br/><br/>
                    </p>
                </div>
            </div>'
        );

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}