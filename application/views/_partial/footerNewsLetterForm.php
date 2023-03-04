<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0)) {
    if (FatApp::getConfig('CONF_NEWSLETTER_SYSTEM') == applicationConstants::NEWS_LETTER_SYSTEM_MAILCHIMP) {
        $class = (isset($blogPage)) ? 'form form-subscribe' : 'sub-form';
        $frm->setFormTagAttribute('class', $class);
        if (isset($formId) && $formId != '') {
            $frm->setFormTagAttribute('id', $formId);
        }

        $frm->setFormTagAttribute('onSubmit', 'setUpNewsLetter(this); return false;');
        $emailFld = $frm->getField('email');
        $emailFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Enter_Your_Email_Address', $siteLangId));
        $emailFld->setFieldTagAttribute('class', "no--focus"); ?>
        <?php echo $frm->getFormTag(); ?>
        <?php echo $frm->getFieldHtml('email');
        /* echo $frm->getFieldHtml('btnSubmit'); */ ?>
        </form>
        <?php echo $frm->getExternalJS(); ?>
    <?php } elseif (FatApp::getConfig('CONF_NEWSLETTER_SYSTEM') == applicationConstants::NEWS_LETTER_SYSTEM_AWEBER) { ?>
        <span class='d-none aweber-js'><?php echo FatApp::getConfig('CONF_AWEBER_SIGNUP_CODE'); ?></span>
        <a href="javascript:void(0)" class="btn btn-brand" onclick="awebersignup();">
            <?php echo Labels::getLabel('LBL_NEWSLETTER_SIGNUP_AWEBER', $siteLangId); ?>
        </a>
    <?php }
} else { ?>
    <div class="gap"></div>
<?php } ?>
<script>
    (function() {
        setUpNewsLetter = function(frm) {
            if (!$(frm).validate()) return;
            events.newsLetterSubscription();
            var data = fcom.frmData(frm);
            fcom.updateWithAjax(fcom.makeUrl('MyApp', 'setUpNewsLetter'), data, function(t) {
                if (t.status) {
                    frm.reset();
                }
            });
        };
    })();
</script>