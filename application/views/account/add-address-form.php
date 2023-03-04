<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$addressFrm->setFormTagAttribute('id', 'addressFrm');
$addressFrm->setFormTagAttribute('class', 'form');
$addressFrm->developerTags['colClassPrefix'] = 'col-sm-4 col-md-';
$addressFrm->developerTags['fld_default_col'] = 4;
$addressFrm->setFormTagAttribute('onsubmit', 'setupAddress(this); return(false);');

$countryFld = $addressFrm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,'.$stateId.',\'#addr_state_id\')');

$stateFld = $addressFrm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'addr_state_id');
$cancelFld = $addressFrm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('onclick', 'searchAddresses()');
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelFld->developerTags['col'] = 2;
$cancelFld->developerTags['noCaptionTag'] = true;

$submitFld = $addressFrm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
$submitFld->developerTags['col'] = 2;
$submitFld->developerTags['noCaptionTag'] = true;
?>
<!-- <div class="tabs">
    <ul>
        <li>
            <a href="javascript:void(0);" onClick="searchAddresses()"><?php echo Labels::getLabel('LBL_My_Addresses', $siteLangId);?></a>
        </li>
<?php //if ($addr_id > 0) { ?>
        <li class="is-active">
            <a href="javascript:void(0);" onClick="addAddressForm(<?php echo $addr_id; ?>)">
            <?php echo Labels::getLabel('LBL_Update_Address', $siteLangId); ?>
            </a>
        </li>
<?php //} else { ?>
        <li class="is-active">
            <a href="javascript:void(0);" onClick="addAddressForm(0)">
                <?php echo Labels::getLabel('LBL_Add_new_address', $siteLangId); ?>
            </a>
        </li>
<?php //} ?>
    </ul>
</div> -->
<div class="container--addresses"> <?php echo $addressFrm->getFormHtml();?> </div>
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#addr_country_id").val(), <?php echo $stateId ;?>, '#addr_state_id');
    });
</script>
