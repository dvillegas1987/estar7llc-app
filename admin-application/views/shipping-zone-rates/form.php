<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupRate(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$nameFld = $frm->getField('shiprate_identifier');
$nameFld->htmlAfterField = "<span class='form-text text-muted'>".Labels::getLabel("LBL_Customers_will_see_this_at_checkout.", $adminLangId)."</span>";

$costFld = $frm->getField('shiprate_cost');
$costFld->htmlAfterField = "<div class='gap'></div><p class='add-condition--js'><a href='javascript:void(0);' onclick='modifyRateFields(1);'>".Labels::getLabel("LBL_Add_Condition", $adminLangId)."</a></p> <p class='remove-condition--js' style='display : none;'><a href='javascript:void(0);' onclick='modifyRateFields(0);'>".Labels::getLabel("LBL_Remove_Condition", $adminLangId)."</a></p>";
$extraClass = 'hide-extra-fields';
if (!empty($rateData) && $rateData['shiprate_condition_type'] > 0) {
    $extraClass = '';
}

$cndFld = $frm->getField('shiprate_condition_type');
$cndFld->setWrapperAttribute('class', 'condition-field--js '. $extraClass);

$minFld = $frm->getField('shiprate_min_val');
$minFld->setWrapperAttribute('class', 'condition-field--js '. $extraClass);

$maxFld = $frm->getField('shiprate_max_val');
$maxFld->setWrapperAttribute('class', 'condition-field--js '. $extraClass);
/*
$cancelFld = $frm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('onClick', 'searchProductsSection($(\'input[name="profile_id"]\').val()); return false;');
 * 
 */
?>
<div class="portlet">
	<div class="portlet__head">
		<div class="portlet__head-label">
			<h3 class="portlet__head-title"><?php echo Labels::getLabel('LBL_Manage_Rates', $adminLangId); ?>
			</h3>
		</div>
	</div>
	<div class="portlet__body">
		<div class="row">
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li>
							<a class="active" href="javascript:void(0)"
								onclick="addEditShipRates(<?php echo $zoneId ?>, <?php echo $rateId ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
						</li>
						<?php
                        $inactive = ($rateId == 0)?'fat-inactive':'';
                        foreach ($languages as $langId => $langName) { ?>
						<li class="<?php echo $inactive;?>">
							<a href="javascript:void(0);" <?php if ($rateId > 0) { ?>
								onclick="editRateLangForm(<?php echo $zoneId ?>, <?php echo $rateId ?>, <?php echo $langId;?>);" <?php } ?>><?php echo Labels::getLabel('LBL_'. $langName, $adminLangId);?></a>
						</li>
						<?php } ?>
					</ul>
					<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<?php echo $frm->getFormHtml(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
if (!empty($rateData) && $rateData['shiprate_condition_type'] > 0) { ?>
<script>
	$(document).ready(function() {
		$('.add-condition--js').hide();
		$('.remove-condition--js').show();
	});
</script>
<?php }
