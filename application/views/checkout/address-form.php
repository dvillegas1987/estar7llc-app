<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$addressFrm->setCustomRendererClass('FormRendererBS');
//$addressFrm->developerTags['rowClassNameDefault'] = 'rowClassName';
//$addressFrm->developerTags['colClassBeforeWidthDefault'] = 'colClassBeforeWidth';
//$addressFrm->developerTags['colClassAfterWidthDefault'] = 'colClassAfterWidth';
$addressFrm->developerTags['colWidthClassesDefault'] = [null, 'col-md-', null, null];
$addressFrm->developerTags['colWidthValuesDefault'] = [null, '6', null, null];
$addressFrm->developerTags['fldWidthClassesDefault'] = [null, null, null, null];
$addressFrm->developerTags['fldWidthValuesDefault'] = [null, null, null, null];
$addressFrm->developerTags['labelWidthClassesDefault'] = [null, null, null, null];
$addressFrm->developerTags['labelWidthValuesDefault'] = [null, null, null, null];
$addressFrm->developerTags['fieldWrapperRowExtraClassDefault'] = 'form-group';

$addressFrm->setFormTagAttribute('class', 'form');
$addressFrm->setFormTagAttribute('onsubmit', 'setUpAddress(this, ' . $addressType . '); return(false);');

$phoneFld = $addressFrm->getField('addr_address1');
$phoneFld->developerTags['colWidthClasses'] = [null, 'col-md-', null, null];
$phoneFld->developerTags['colWidthValues'] = [null, '12', null, null];

$phoneFld = $addressFrm->getField('addr_address2');
$phoneFld->developerTags['colWidthClasses'] = [null, 'col-md-', null, null];
$phoneFld->developerTags['colWidthValues'] = [null, '12', null, null];

$phoneFld = $addressFrm->getField('addr_city');
$phoneFld->developerTags['colWidthClasses'] = [null, 'col-md-', null, null];
$phoneFld->developerTags['colWidthValues'] = [null, '12', null, null];

$countryFld = $addressFrm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value, 0 ,\'#addr_state_id\')');

$stateFld = $addressFrm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'addr_state_id');

$submitFld = $addressFrm->getField('btn_submit');
$submitFld->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
$submitFld->developerTags['colClassBeforeWidth'] = 'col-auto';
$submitFld->developerTags['colWidthClasses'] = [null, null, null, null];
$submitFld->developerTags['colWidthValues'] = [null, null, null, null];
//$submitFld->developerTags['fieldWrapperRowExtraClass'] = 'form-group-btn';
$cancelFld = $addressFrm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-wide');
$cancelFld->developerTags['colClassBeforeWidth'] = 'col';
$cancelFld->developerTags['colWidthClasses'] = [null, null, null, null];
$cancelFld->developerTags['colWidthValues'] = [null, null, null, null];
$cancelFld->setFieldTagAttribute('onclick', 'resetAddress(' . $addressType . ')');
//$cancelFld->developerTags['fieldWrapperRowExtraClass'] = 'form-group-btn'; */
?>
<div class="step active">
    <div class="step__section">
        <div class="step__section__head">
            <h5 class="step__section__head__title"><?php echo Labels::getLabel('LBL_ADDRESS_DETAILS', $siteLangId); ?></h5>
        </div>
    </div>
    <?php echo $addressFrm->getFormHtml(); ?>
</div>
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#addr_country_id").val(), <?php echo $stateId; ?>, '#addr_state_id');
    });
</script>