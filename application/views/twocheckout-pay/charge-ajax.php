<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
if (false === $processRequest) {
    $frm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
}
$frm->setFormTagAttribute('id', 'paymentForm-js');

$btn = $frm->getField('btn_submit');
if (null != $btn) {
    $btn->developerTags['noCaptionTag'] = true;
    $btn->setFieldTagAttribute('class', "btn btn-brand btn-wide");
}
?>
<?php if ($paymentType == 'HOSTED') {  /* Hosted Checkout */ ?>
    <div class="text-center" id="paymentFormElement-js">
        <?php if (false === $processRequest) { ?>
            <h6><?php echo Labels::getLabel('LBL_PROCEED_TO_PAYMENT_?', $siteLangId); ?></h6>
        <?php } else { ?>
            <h6><?php echo Labels::getLabel('LBL_REDIRECTING_TO_PAYMENT_PAGE...', $siteLangId); ?></h6>
        <?php } ?>
        <?php echo $frm->getFormHtml(); ?>
    </div>
    <script>
        function confirmOrder(frm) {
            var data = fcom.frmData(frm);
            var action = $(frm).attr('action');
            var submitBtn = $("form#paymentForm-js input[type='submit']");
            $.mbsmessage(langLbl.processing, false, 'alert--process alert');
            submitBtn.attr('disabled', 'disabled');
            fcom.ajax(action, data, function(res) {
                var json = $.parseJSON(res);
                if (1 > json.status) {
                    submitBtn.removeAttr('disabled');
                    $.mbsmessage(json.msg, true, 'alert--danger');
                    return false;
                }
                $("#paymentFormElement-js").replaceWith(json.html);
                $("form#paymentForm-js").submit();
            });
        }
    </script>
<?php } else {
    /* API Checkout */
    if (!isset($error)) {
        $frm->getField('ccNo')->setFieldTagAttribute('class', 'p-cards');
        $frm->getField('ccNo')->setFieldTagAttribute('id', 'ccNo');

        $frm->getField('cvv')->addFieldTagAttribute('id', 'cvv');
        $frm->getField('expMonth')->addFieldTagAttribute('id', 'expMonth');
        $frm->getField('expYear')->addFieldTagAttribute('id', 'expYear');
        echo $frm->getFormTag(); ?>
        <?php echo $frm->getFieldHtml('token'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $siteLangId); ?></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover"> <?php echo $frm->getFieldHtml('ccNo'); ?> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="caption-wraper">
                    <label class="field_label"> <?php echo Labels::getLabel('LBL_CREDIT_CARD_EXPIRY', $siteLangId); ?> </label>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php
                                    $fld = $frm->getField('expMonth');
                                    echo $fld->getHtml();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php
                                    $fld = $frm->getField('expYear');
                                    echo $fld->getHtml();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_CVV_SECURITY_CODE', $siteLangId); ?></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover"> <?php echo $frm->getFieldHtml('cvv'); ?> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="total-pay"><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?>
            <small>(<?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?>)</small>
        </div>
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
                            echo $frm->getFieldHtml('btn_submit');
                            ?>
                            <a href="<?php echo $cancelBtnUrl; ?>" class="btn btn-outline-brand"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            </form>
            <?php echo $frm->getExternalJs(); ?>
    <?php } else { ?>
        <div class="alert alert--danger"><?php echo $error; ?></div>
    <?php } ?>
    <div id="ajax_message"></div>
    <?php if (!FatUtility::isAjaxCall()) { ?>
        <script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
    <?php } ?>
    <script type="text/javascript">
        var sandbox = "<?php echo Plugin::ENV_SANDBOX == $envoirment ? 1 : 0; ?>";
        $("#ccNo").keydown(function() {
            var obj = $(this);
            var cc = obj.val();
            obj.attr('class', 'p-cards');
            if (cc != '') {
                var data = "cc=" + cc;
                fcom.ajax(fcom.makeUrl('AuthorizeAimPay', 'checkCardType'), data, function(t) {
                    var ans = $.parseJSON(t);
                    var card_type = ans.card_type.toLowerCase();
                    obj.addClass('type-bg p-cards ' + card_type);
                });
            }
        });

        var frmApiCheckout = '';

        // Called when token created successfully.
        var successCallback = function(cdata) {
            var myForm = document.getElementById('twocheckout');
            // Set the token as the value for the token input
            myForm.token.value = cdata.response.token.token;
            // IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.

            var data = fcom.frmData(myForm);
            data += '&outmode=json&is_ajax_request=yes';

            var action = $(myForm).attr('action');
            fcom.ajax(action, data, function(response) {
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
        var errorCallback = function(data) {
            // Retry the token request if ajax call fails
            if (data.errorCode === 200) {
                // This error code indicates that the ajax call failed. We recommend that you retry the token request.
                tokenRequest();
            } else {
                frmApiCheckout.data('requestRunning', false);
                $('#ajax_message').html('<div class="alert alert--danger">' + data.errorMsg + '</div>');
            }
        };

        var tokenRequest = function() {
            // Setup token request arguments
            var args = {
                sellerId: "<?php echo $sellerId; ?>",
                privateKey: "<?php echo $privateKey; ?>",
                ccNo: $("#ccNo").val(),
                cvv: $("#cvv").val(),
                expMonth: $("#expMonth").val(),
                expYear: $("#expYear").val(),
                demo: 0 < sandbox ? true : false
            };
            console.log(args);
            // Make the token request
            TCO.requestToken(successCallback, errorCallback, args);
        };

        $(function() {
            // Pull in the public encryption key for our environment
            TCO.loadPubKey("<?php echo $transaction_mode; ?>");

            $(document).on("submit", "#paymentForm-js", function(event) {
                event.preventDefault();
                $(this).data('requestRunning', false);
                tokenRequest();
                return false;
            });
        });
    </script>
<?php } ?>