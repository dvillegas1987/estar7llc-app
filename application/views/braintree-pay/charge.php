<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<section class="payment-section">
    <div class="payable-amount">
        <div class="payable-amount__head">
            <div class="payable-amount--header">              
                <?php $this->includeTemplate('_partial/paymentPageLogo.php', array('siteLangId' => $siteLangId)); ?>
            </div>
            <div class="payable-amount--decription">
                <h2><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?></h2>
                <p><?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?></p>
                <p><?php echo Labels::getLabel('LBL_Order_Invoice', $siteLangId); ?>:<?php echo $orderInfo["invoice"]; ?></p>
            </div>
        </div>
        <div class="payable-amount__body payment-from">
            <?php
            if (!isset($error)) {
                ?>
                <?php echo $frm->getFormTag(); ?>
                <div class="payable-form__body">
                    <div class="waiting_message">
                        <?php echo Labels::getLabel('LBL_LOADING_PAYMENT_OPTIONS...', $siteLangId); ?>
                        <p>
                            <a onclick="loadLibrary();"><?php echo Labels::getLabel('LBL_Click_here', $siteLangId); ?></a>
                            <?php echo Labels::getLabel('LBL_IF_LOADING_IS_TAKING_MORE_THAN_15_SECONDS', $siteLangId); ?>
                        </p>
                    </div>
                    <div id="dropin-container"></div>
                    <?php echo $frm->getExternalJs(); ?>                       
                </div>                   
                <div class="payable-form__footer">
                    <div class="row">
                        <div class="col-md-6">                                    
                            <?php
                            $btn = $frm->getField('btn_submit');
                            $btn->addFieldTagAttribute('class', 'btn btn-secondary');
                            $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                            echo $frm->getFieldHtml('btn_submit');
                            ?> 
                        </div>
                        <div class="col-md-6 d-md-block d-none">
                            <?php if (FatUtility::isAjaxCall()) { ?>
                                <a href="javascript:void(0);" onclick="loadPaymentSummary()" class="btn btn-outline-brand">
                                    <?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?>
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo $cancelBtnUrl; ?>" class="btn btn-outline-gray"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?></a>
                            <?php } ?>                        
                        </div>
                    </div>  
                </div> 
                </form>
            <?php } else { ?>
                <div class="alert alert--danger"><?php echo $error ?></div>
            <?php } ?>
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
            <?php } ?>
        </div>
    </div>
</section>
<?php
if (isset($clientToken)) {
    if (!FatUtility::isAjaxCall()) {
        ?>
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

                    }, function (createErr, instance) {
                        if (createErr) {
                            // console.error(createErr);
                            $.mbsmessage(createErr.name + " : " + createErr.message, false, 'alert--danger');
                            return;
                        }
                        $(".waiting_message").remove();
                        $("#submit-button").removeAttr('disabled');
                        button.addEventListener('click', function () {
                            instance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
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
        }

        $(document).ready(function () {
            var paymentAmount = "<?php echo $paymentAmount; ?>";
            var currencyCode = "<?php echo $currencyCode; ?>";
            var clientToken = "<?php echo $clientToken; ?>";

            loadLibrary(clientToken, paymentAmount, currencyCode);
        });
    </script>
<?php } ?>