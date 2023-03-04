<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$pmethodName = $paymentMethod["plugin_name"];
$pmethodCode = $paymentMethod["plugin_code"];

$frm->setFormTagAttribute('class', 'form form--normal');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
$submitFld = $frm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', "btn btn-brand");
?>
<div class="text-center paymentForm-js d-none">
    <?php if (!isset($error)) {
        echo $frm->getFormHtml();
    }
    ?>
</div>
<script type="text/javascript">
    $("document").ready(function() {
        <?php if (isset($error)) { ?>
            $.systemMessage(<?php echo $error; ?>);
        <?php } ?>
    });


    function confirmOrder(frm) {

        var data = fcom.frmData(frm);
        var action = $(frm).attr('action')
        var getExternalLibraryUrl = $(frm).data('external');
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'confirmOrder'), data, function(res) {
            if ('undefined' != typeof getExternalLibraryUrl) {
                fcom.ajax(getExternalLibraryUrl, '', function(t) {
                    var json = $.parseJSON(t);
                    if (1 > json.status) {
                        $("#tabs-container form input[type='submit']").val(langLbl.confirmPayment);
                        $.mbsmessage(json.msg, true, 'alert--danger');
                        return;
                    }

                    if (0 < (json.libraries).length) {
                        $.each(json.libraries, function(key, src) {
                            loadScript(src, loadChargeForm, [action]);
                        });
                    } else {
                        loadChargeForm(action);
                    }
                });
            } else {
                loadChargeForm(action);
            }
        });
    }

    function loadChargeForm(action) {
        fcom.ajax(action, '', function(t) {
            $.mbsmessage.close();
            try {
                var ans = $.parseJSON(t);
                if (1 > ans.status) {
                    $.mbsmessage(ans.msg, true, 'alert--danger');
                    return false;
                } else if ('undefined' != typeof ans.redirect) {
                    location.href = ans.redirect;
                } else {
                    $('#tabs-container').html(ans.html);
                    <?php if ('stripeconnect' == strtolower($pmethodCode)) { ?>
                        $('#tabs-container').addClass('p-0');
                    <?php } ?>
                }
            } catch (e) {
                // console.log(e);
            }
        });
    }

    /*
    function confirmOrder(frm) {
        var data = fcom.frmData(frm);
        var action = $(frm).attr('action')
        fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'confirmOrder'), data, function(ans) {
            $(location).attr("href", action);
        });
    }
 * 
     */
</script>
<?php
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
if (!empty($siteKey) && !empty($secretKey) && in_array(strtolower($paymentMethod['plugin_code']), ['cashondelivery', 'payatstore'])) { ?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
<?php } ?>