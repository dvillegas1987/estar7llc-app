<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!isset($error)) : ?>
    <div class="waiting_message">
        <?php echo Labels::getLabel('LBL_LOADING_PAYMENT_OPTIONS...', $siteLangId); ?>
        <p>
            <a onclick="loadLibrary();"><?php echo Labels::getLabel('LBL_Click_here', $siteLangId); ?></a>
            <?php echo Labels::getLabel('LBL_IF_LOADING_IS_TAKING_MORE_THAN_15_SECONDS', $siteLangId); ?>
        </p>
    </div>

    <div id="dropin-container"></div>
    <?php echo $frm->getFormTag(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php
                        $btn = $frm->getField('btn_submit');
                        $btn->addFieldTagAttribute('class', 'btn btn-secondary');
                        $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                        echo $frm->getFieldHtml('btn_submit'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>

    <?php echo $frm->getExternalJs(); ?>

<?php else : ?>
    <div class="alert alert--danger"><?php echo $error ?></div>
<?php endif; ?>
<div id="ajax_message"></div>
<?php if (isset($clientToken)) {
    if (!FatUtility::isAjaxCall()) { ?>
        <script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>
    <?php } ?>
    <script type="text/javascript">
        function loadLibrary(clientToken, paymentAmount, currencyCode) {
            try {
                if (typeof clientToken != typeof undefined) {
                    var button = document.querySelector('#submit-button');

                    braintree.dropin.create({
                        authorization: clientToken,
                        container: '#dropin-container',
                        venmo: {
                            allowNewBrowserTab: false
                        },
                        googlePay: {
                            environment: 'TEST',
                            transactionInfo: {
                                totalPriceStatus: 'FINAL',
                                totalPrice: paymentAmount,
                                currencyCode: currencyCode
                            },
                            cardRequirements: {
                                billingAddressRequired: true
                            }
                        },
                        paypal: {
                            flow: 'vault',
                            amount: paymentAmount,
                            currency: currencyCode
                        },
                        applePay: {
                            displayName: 'My Store',
                            paymentRequest: {
                                total: {
                                    amount: paymentAmount
                                },
                                // We recommend collecting billing address information, at minimum
                                // billing postal code, and passing that billing postal code with all
                                // Google Pay transactions as a best practice.
                                requiredBillingContactFields: ["postalAddress"]
                            }
                        }

                    }, function(createErr, instance) {
                        if (createErr) {
                            // console.error(createErr);
                            $.mbsmessage(createErr.name + " : " + createErr.message, false, 'alert--danger');
                            return;
                        }
                        $(".waiting_message").remove();
                        $("#submit-button").removeAttr('disabled');
                        button.addEventListener('click', function() {
                            instance.requestPaymentMethod(function(requestPaymentMethodErr, payload) {
                                // Submit payload.nonce to your server
                                var form = $("#frmPaymentForm");
                                var nonce = payload.nonce;
                                // insert the token into the form so it gets submitted to the server
                                form.append("<input type='hidden' name='paymentMethodNonce' value='" + nonce + "' />");
                                form.append("<input type='hidden' name='amount' value='" + paymentAmount + "' />");
                                form.get(0).submit();
                                $("#cancelLink").remove();
                                $("#submit-button").val('Processing..');
                                $("#submit-button").attr('disabled', 'disabled');
                            });
                        });
                    });

                }
            } catch (e) {
                console.log('Execution Error!!');
                console.log(e.message);
            }
        };

        $(document).ready(function() {
            var paymentAmount = "<?php echo $paymentAmount; ?>";
            var currencyCode = "<?php echo $currencyCode; ?>";
            var clientToken = "<?php echo $clientToken; ?>";

            loadLibrary(clientToken, paymentAmount, currencyCode);
        });
    </script>
<?php } ?>