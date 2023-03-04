<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$frm->setFormTagAttribute('id', 'twocheckout');
?>
<section class="payment-section">
    <div class="payable-amount">            
        <div class="payable-amount__head">
            <div class="payable-amount--header">              
                <?php $this->includeTemplate('_partial/paymentPageLogo.php', array('siteLangId' => $siteLangId)); ?>
            </div>
            <div class="payable-amount--decription">
                <h2><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?></h2>
                <p><?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?></p>
                <p><?php echo Labels::getLabel('LBL_Order_Invoice', $siteLangId); ?>: <?php echo $orderInfo["invoice"]; ?></p>
            </div>
        </div>
        <div class="payable-amount__body payment-from">                
            <?php if ($paymentType == 'HOSTED') { /* Hosted Checkout */ ?>
                <div class="payable-form__body">
                    <?php if (!isset($error)) : ?>
                        <p><?php echo Labels::getLabel('LBL_REDIRECTING_TO_PAYMENT_PAGE..', $siteLangId) ?>:</p>
                        <?php
                        $btn = $frm->getField('btn_submit');
                        if(null != $btn){
                            $btn->setWrapperAttribute('class', 'd-none');
                        }                                                
                        echo $frm->getFormHtml(); ?>
                    <?php else : ?>
                        <div class="alert alert--danger"><?php echo $error; ?></div>
                    <?php endif; ?>              
                    <script type="text/javascript">
                        $(function () {
                            setTimeout(function () {
                                $('form#twocheckout').submit();
                            }, 200);
                        });
                    </script>
                </div>    
                <?php
            } else { /* API Checkout */
                if (!isset($error)) :
                    $frm->getField('ccNo')->setFieldTagAttribute('class', 'p-cards');
                    $frm->getField('ccNo')->setFieldTagAttribute('id', 'ccNo');
                    $frm->getField('cvv')->addFieldTagAttribute('id', 'cvv');
                    $frm->getField('expMonth')->addFieldTagAttribute('id', 'expMonth');
                    $frm->getField('expYear')->addFieldTagAttribute('id', 'expYear');
                    echo $frm->getFormTag();
                    echo $frm->getFieldHtml('token');
                    ?>
                    <?php echo $frm->getFormTag(); ?>
                    <div class="payable-form__body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $siteLangId); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('ccNo'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label"><?php echo Labels::getLabel('LBL_Expiry_Month', $siteLangId); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('expMonth'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label"><?php echo Labels::getLabel('LBL_Expiry_year', $siteLangId); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('expYear'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>                        
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label"><?php echo Labels::getLabel('LBL_CVV_SECURITY_CODE', $siteLangId); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('cvv'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div>
                        <?php echo $frm->getExternalJs(); ?>                       
                        <div id="ajax_message"></div>
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
                <?php else : ?>
                    <div class="alert alert--danger"><?php echo $error ?></div>
                <?php endif; ?>
            <?php } ?>
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
                <?php } ?>
        </div>
    </div>
</section>
<?php
if ($paymentType != 'HOSTED') {
    if (!FatUtility::isAjaxCall()) {
        ?>
        <script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
    <?php } ?>
    <script type="text/javascript">
        $("#ccNo").keydown(function () {
            var obj = $(this);
            var cc = obj.val();
            obj.attr('class', 'p-cards');
            if (cc != '') {
                var data = "cc=" + cc;
                fcom.ajax(fcom.makeUrl('AuthorizeAimPay', 'checkCardType'), data, function (t) {
                    var ans = $.parseJSON(t);
                    var card_type = ans.card_type.toLowerCase();
                    obj.addClass('type-bg p-cards ' + card_type);
                });
            }
        });

        var frmApiCheckout = '';

        // Called when token created successfully.
        var successCallback = function (cdata) {
            var myForm = document.getElementById('twocheckout');
            // Set the token as the value for the token input
            myForm.token.value = cdata.response.token.token;
            // IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.

            var data = fcom.frmData(myForm);
            data += '&outmode=json&is_ajax_request=yes';

            var action = $(myForm).attr('action');
            fcom.ajax(action, data, function (response) {
                try {
                    var json = $.parseJSON(response);

                    if (json['error']) {
                        $('#ajax_message').html('<div class="alert alert--danger">' + json['error'] + '</div>');
                    }
                    if (json['redirect']) {
                        $(location).attr("href", json['redirect']);
                    }
                } catch (exc) {
                    console.log(t);
                }
            });
        };

        // Called when token creation fails.
        var errorCallback = function (data) {
            // Retry the token request if ajax call fails
            if (data.errorCode === 200) {
                // This error code indicates that the ajax call failed. We recommend that you retry the token request.
                tokenRequest();
            } else {
                frmApiCheckout.data('requestRunning', false);
                $('#ajax_message').html('<div class="alert alert--danger">' + data.errorMsg + '</div>');
            }
        };

        var tokenRequest = function () {
            // Setup token request arguments
            var args = {
                sellerId: "<?php echo $sellerId; ?>",
                publishableKey: "<?php echo $publishableKey; ?>",
                ccNo: $("#ccNo").val(),
                cvv: $("#cvv").val(),
                expMonth: $("#expMonth").val(),
                expYear: $("#expYear").val()
            };
            // Make the token request
            TCO.requestToken(successCallback, errorCallback, args);
        };

        // Pull in the public encryption key for our environment
        TCO.loadPubKey("<?php echo $transaction_mode; ?>");

        $(document).on("submit", "#twocheckout", function (event) {
            event.preventDefault();
            $(this).data('requestRunning', false);
            tokenRequest();
        });
    </script>
<?php } ?>
  