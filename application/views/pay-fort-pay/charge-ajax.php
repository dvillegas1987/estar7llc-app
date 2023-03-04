<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
$frm->setFormTagAttribute('id', 'paymentForm-js');

$btn = $frm->getField('btn_submit');
$btn->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
$btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
$cancelBtn = $frm->getField('btn_cancel');
$cancelBtn->setWrapperAttribute('class', 'd-none'); // Not Required On ajax page

if (!isset($error)) { ?>
    <div class="text-center">
        <p class='redirectingText-js'><?php echo Labels::getLabel('LBL_PROCEED_TO_PAYMENT_?', $siteLangId); ?></p>
        <?php echo  $frm->getFormHtml(); ?>
    </div>
<?php } else { ?>
    <div class="alert alert--danger"> <?php echo $error; ?></div>
<?php } ?>
<script>
    function cancel() {
        location.href = "<?php echo $cancelBtnUrl; ?>";
    }

    function confirmOrder(frm) {
        $("form#paymentForm-js").removeAttr('onsubmit');
        var submitBtn = $("form#paymentForm-js input[type='submit']");
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
        submitBtn.attr('disabled', 'disabled');
        $('.redirectingText-js').text('<?php echo Labels::getLabel('LBL_REDIRECTING_TO_PAYMENT_PAGE...', $siteLangId); ?>');
        $("form#paymentForm-js").submit();
    }
</script>