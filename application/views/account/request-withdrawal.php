<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card-header">
    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Request_Withdrawal', $siteLangId);?></h5>
</div>
<div class="card-body ">
    <?php $frm->setFormTagAttribute('class', 'form');
    $frm->developerTags['colClassPrefix'] = 'col-md-';
    if (User::isAffiliate()) {
        $frm->developerTags['fld_default_col'] = 12;
    } else {
        $frm->developerTags['fld_default_col'] = 6;
    }

    $frm->setFormTagAttribute('onsubmit', 'setupWithdrawalReq(this); return(false);');

    $ifscFld = $frm->getField('ub_ifsc_swift_code');
    $ifscFld->setWrapperAttribute('class', 'col-sm-12');
    $ifscFld->developerTags['col'] = 12;

    if (User::isAffiliate()) {
        $paymentMethodFld = $frm->getField('uextra_payment_method');
        $paymentMethodFld->setOptionListTagAttribute('class', 'links--inline');

        $checkPayeeNameFld = $frm->getField('uextra_cheque_payee_name');
        $checkPayeeNameFld->setWrapperAttribute('class', 'cheque_payment_method_fld');

        $bankNameFld = $frm->getField('ub_bank_name');
        $bankNameFld->setWrapperAttribute('class', 'bank_payment_method_fld');

        $bankAccountNameFld = $frm->getField('ub_account_holder_name');
        $bankAccountNameFld->setWrapperAttribute('class', 'bank_payment_method_fld');

        $bankAccountNumberFld = $frm->getField('ub_account_number');
        $bankAccountNumberFld->setWrapperAttribute('class', 'bank_payment_method_fld');

        $bankSwiftCodeFld = $frm->getField('ub_ifsc_swift_code');
        $bankSwiftCodeFld->setWrapperAttribute('class', 'bank_payment_method_fld');

        $bankAddressFld = $frm->getField('ub_bank_address');
        $bankAddressFld->setWrapperAttribute('class', 'bank_payment_method_fld');

        $PayPalEmailIdFld = $frm->getField('uextra_paypal_email_id');
        $PayPalEmailIdFld->setWrapperAttribute('class', 'paypal_payment_method_fld');
    }

    $submitBtnFld = $frm->getField('btn_submit');
    $submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
    $submitBtnFld->setWrapperAttribute('class', 'col-6 col-lg-2');
    $submitBtnFld->developerTags['col'] = 3;

    $cancelBtnFld = $frm->getField('btn_cancel');
    $cancelBtnFld->setFieldTagAttribute('onClick', 'closeForm()');
    $cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
    $cancelBtnFld->setWrapperAttribute('class', 'col-6 col-lg-2');
    $cancelBtnFld->developerTags['col'] = 3;
    //$submitBtnFld->attachField($cancelBtnFld);

    echo $frm->getFormHtml();?> </div>
    <?php if (User::isAffiliate()) { ?>
        <script type="text/javascript">
            $("document").ready(function() {
                var AFFILIATE_PAYMENT_METHOD_CHEQUE = '<?php echo User::AFFILIATE_PAYMENT_METHOD_CHEQUE; ?>';
                var AFFILIATE_PAYMENT_METHOD_BANK = '<?php echo User::AFFILIATE_PAYMENT_METHOD_BANK; ?>';
                var AFFILIATE_PAYMENT_METHOD_PAYPAL = '<?php echo User::AFFILIATE_PAYMENT_METHOD_PAYPAL; ?>';
                var uextra_payment_method = '<?php echo $uextra_payment_method ?>';
                $("input[name='uextra_payment_method']").change(function() {
                    if ($(this).val() == AFFILIATE_PAYMENT_METHOD_CHEQUE) {
                        callChequePaymentMethod();
                    }
                    if ($(this).val() == AFFILIATE_PAYMENT_METHOD_BANK) {
                        callBankPaymentMethod();
                    }
                    if ($(this).val() == AFFILIATE_PAYMENT_METHOD_PAYPAL) {
                        callPayPalPaymentMethod();
                    }
                });
                if (uextra_payment_method == AFFILIATE_PAYMENT_METHOD_CHEQUE) {
                    callChequePaymentMethod();
                }
                if (uextra_payment_method == AFFILIATE_PAYMENT_METHOD_BANK) {
                    callBankPaymentMethod();
                }
                if (uextra_payment_method == AFFILIATE_PAYMENT_METHOD_PAYPAL) {
                    callPayPalPaymentMethod();
                }
            });

            function callChequePaymentMethod() {
                $(".cheque_payment_method_fld").show();
                $(".bank_payment_method_fld").hide();
                $(".paypal_payment_method_fld").hide();
            }

            function callBankPaymentMethod() {
                $(".cheque_payment_method_fld").hide();
                $(".bank_payment_method_fld").show();
                $(".paypal_payment_method_fld").hide();
            }

            function callPayPalPaymentMethod() {
                $(".cheque_payment_method_fld").hide();
                $(".bank_payment_method_fld").hide();
                $(".paypal_payment_method_fld").show();
            }
        </script>
    <?php } ?>
