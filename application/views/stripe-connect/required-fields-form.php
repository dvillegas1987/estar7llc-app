<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setupRequiredFields(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 4;

$btnFld = $frm->getField('btn_submit');
if (null != $btnFld) {
    $btnFld->addFieldTagAttribute('class', 'btn btn-brand btn-block');
    $btnFld->developerTags['col'] = 2;
    $btnFld->setWrapperAttribute('class', 'col-6 col-lg-2');
    // $btnFld->developerTags['noCaptionTag'] = true;
}

$btnFld = $frm->getField('btn_clear');
if (null != $btnFld) {
    $btnFld->addFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
    $btnFld->addFieldTagAttribute('onClick', 'clearForm();');
    $btnFld->developerTags['col'] = 2;
    $btnFld->setWrapperAttribute('class', 'col-6 col-lg-2');
    // $btnFld->developerTags['noCaptionTag'] = true;
}

$termFld = $frm->getField('tos_acceptance');
if (null != $termFld) {
    $termFld->addFieldTagAttribute('class', 'tosCheckbox-js');
    $link = '<a href="' . $termAndConditionsUrl . '" target="_blank" class="tosLink-js">' . Labels::getLabel('LBL_TERMS_OF_SERVICE', $siteLangId) . '</a>';

    $agree = Labels::getLabel('LBL_I_AGREE_TO_THE_{TERMS-OF-SERVICE}', $siteLangId);
    $termFld->htmlAfterField = CommonHelper::replaceStringData($agree, ['{TERMS-OF-SERVICE}' => $link]);
    $termFld->developerTags['noCaptionTag'] = true;
}

$tosFld = $frm->getField('tos_acceptance');
if (null != $tosFld) {
    $tosFld->developerTags['col'] = 12;
} ?>

<hr>
<div class="section__body">
    <?php $this->includeTemplate('stripe-connect/fieldsErrors.php', ['errors' => $errors]); ?>
    <?php echo $frm->getFormHtml(); ?>
</div>
<script language="javascript">
    $(document).ready(function() {
        if (0 < $(".state").length) {
            getStatesByCountryCode($(".state").data('country'), '0', '.state', 'state_code');
        }

        if (0 < $(".country").length) {
            $(".country").change();
        }

        if (0 < $(".tosLink-js").length && 0 < $(".tosCheckbox-js").length) {
            var parent = $(".tosLink-js").parent();
            var label = parent.children('label');
            label.remove();
            var html = parent.html();
            parent.html(label);
            label.children('span').append(html);
        }
    });
</script>