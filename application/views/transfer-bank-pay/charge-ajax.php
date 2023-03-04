<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php 
    $frm->setFormTagAttribute('action', UrlHelper::generateUrl('TransferBankPay', 'send', array($orderInfo['id'])));
    $frm->setFormTagAttribute('class', 'form');
    $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-12 col-xs-';
    $frm->developerTags['fld_default_col'] = 12;
?>
<ul class="transfer-payment-detail">
    <li>
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bussiness-name" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bussiness-name"></use>
            </svg>
        </i>
        <div class="lable">
            <h6><?php echo Labels::getLabel('LBL_BUSSINESS_NAME', $siteLangId); ?></h6>
            <?php echo $settings['business_name']; ?>
        </div>

    </li>
    <li>
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-name" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-name"></use>
            </svg>
        </i>
        <div class="lable">
            <h6><?php echo Labels::getLabel('LBL_BANK_NAME', $siteLangId); ?></h6>
            <?php echo $settings['bank_name']; ?>
        </div>

    </li>
    <li>
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-branch" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-branch"></use>
            </svg>
        </i>
        <div class="lable">
            <h6><?php echo Labels::getLabel('LBL_BANK_BRANCH', $siteLangId); ?></h6>
            <?php echo $settings['bank_branch']; ?>
        </div>

    </li>
    <li>
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#account"></use>
            </svg>
        </i>
        <div class="lable">
            <h6><?php echo Labels::getLabel('LBL_ACCOUNT_#', $siteLangId); ?></h6>
            <?php echo $settings['account_number']; ?>
        </div>

    </li>
    <li>
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#ifsc" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#ifsc"></use>
            </svg>
        </i>
        <div class="lable">
            <h6><?php echo Labels::getLabel('LBL_IFSC_/_MICR', $siteLangId); ?></h6>
            <?php echo $settings['ifsc']; ?>
        </div>

    </li>
    <?php if (!empty($settings['routing'])) { ?>
    <li>
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#routing" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#routing"></use>
            </svg>
        </i>
        <div class="lable">
            <h6><?php echo Labels::getLabel('LBL_ROUTING_#', $siteLangId); ?></h6>
            <?php echo $settings['routing']; ?>
        </div>

    </li>
    <?php } ?>
    <?php if (!empty($settings['bank_notes'])) { ?>
        <li class="notes">
            <i class="icn">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-notes" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-notes"></use>
                </svg>
            </i>
            <div class="lable">
                <h6><?php echo Labels::getLabel('LBL_OTHER_NOTES', $siteLangId); ?></h6>
                <?php echo $settings['bank_notes']; ?>
            </div>
        </li>
    <?php } ?>
</ul>

<?php if (!isset($error)) :
    $frm->setFormTagAttribute('onsubmit', 'confirmPayment(this); return(false);');
    $btn = $frm->getField('btn_submit'); 
    $btn->addFieldTagAttribute('class', 'btn btn-secondary');
    $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
    $btn->developerTags['noCaptionTag'] = true;
    echo $frm->getFormHtml();
else : ?>
    <div class="alert alert--danger"><?php echo $error ?></div>
<?php endif; ?>
<div id="ajax_message"></div>
<script>
    var confirmPayment = function(frm) {
        var me = $(frm);
        if (me.data('requestRunning')) {
            return;
        }
        if (!me.validate()) return;
        $("input[type='submit']").val(langLbl.processing);
        var data = fcom.frmData(frm);
        var action = me.attr('action');
        fcom.ajax(action, data, function(t) {
            try {
                var json = $.parseJSON(t);
                var el = $('#ajax_message');
                if (json['error']) {
                    el.html('<div class="alert alert--danger">' + json['error'] + '<div>');
                }
                if (json['redirect']) {
                    $(location).attr("href", json['redirect']);
                }
            } catch (exc) {
                console.log(t);
            }
        });
    };
</script>