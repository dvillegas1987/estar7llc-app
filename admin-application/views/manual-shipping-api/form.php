<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupManualShippingApi(this); return(false);');

$fld = $frm->getField('mshipapi_volume_upto');
$fld->htmlAfterField = '<span class="txt--small">'.Labels::getLabel('MSG_Volume_in_cc',$adminLangId).'</span>';

$fld = $frm->getField('mshipapi_weight_upto');
$fld->htmlAfterField = '<span class="txt--small">'.Labels::getLabel('MSG_Weight_in_gm',$adminLangId).'</span>';

$countryFld = $frm->getField('mshipapi_country_id');
$countryFld->setFieldTagAttribute('id','mshipapi_country_id');
$countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value,'.$stateId.',\'#mshipapi_state_id\')');

$stateFld = $frm->getField('mshipapi_state_id');
$stateFld->setFieldTagAttribute('id','mshipapi_state_id');

?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Manual_Shipping_Cost_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="manualShippingForm(<?php echo $mshipapi_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo ($mshipapi_id == 0) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo ($mshipapi_id) ? "onclick='manualShippingLangForm(" . $mshipapi_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
<script language="javascript">
	$(document).ready(function(){
		getCountryStates($( "#mshipapi_country_id" ).val(),<?php echo $stateId ;?>,'#mshipapi_state_id');
	});	
</script>