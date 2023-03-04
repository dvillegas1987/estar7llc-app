<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setCustomRendererClass('FormRendererBS');

$langFrm->developerTags['colWidthClassesDefault'] = [null, 'col-md-', null, null];
$langFrm->developerTags['colWidthValuesDefault'] = [null, '12', null, null];
$langFrm->developerTags['fldWidthClassesDefault'] = [null, null, null, null];
$langFrm->developerTags['fldWidthValuesDefault'] = [null, null, null, null];
$langFrm->developerTags['labelWidthClassesDefault'] = [null, null, null, null];
$langFrm->developerTags['labelWidthValuesDefault'] = [null, null, null, null];
$langFrm->developerTags['fieldWrapperRowExtraClassDefault'] = 'form-group';

$langFrm->setFormTagAttribute('class', 'form');
$langFrm->setFormTagAttribute('onsubmit', 'setupLangRate(this); return(false);');


//$langFrm->developerTags['colClassPrefix'] = 'col-sm-4 col-md-';
//$langFrm->developerTags['fld_default_col'] = 12;
/*
$cancelFld = $langFrm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('onClick', 'clearForm(); return false;');
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand');
$cancelFld->developerTags['noCaptionTag'] = true;
$cancelFld->developerTags['colClassBeforeWidth'] = 'col-auto';
$cancelFld->developerTags['colWidthClasses'] = [null, null, null, null];
$cancelFld->developerTags['colWidthValues'] = [null, null, null, null];
 * 
 */

$btnSubmit = $langFrm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
$btnSubmit->developerTags['noCaptionTag'] = true;
$btnSubmit->developerTags['colClassBeforeWidth'] = 'col';
$btnSubmit->developerTags['colWidthClasses'] = [null, null, null, null];
$btnSubmit->developerTags['colWidthValues'] = [null, null, null, null];


?>
<div >
	<div class="card-header">
		<h5 class="card-title"><?php echo Labels::getLabel('LBL_Manage_Rates', $siteLangId); ?></h5>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-sm-12">
				<div class="tabs">
					<ul class="tabs_nav-js">
						<li>
							<a href="javascript:void(0);" onclick="addEditShipRates(<?php echo $zoneId ?>, <?php echo $rateId ?>);"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a>
						</li>
						<?php
						foreach ($languages as $key => $langName) {
							$class = ($langId == $key) ? 'is-active' : ''; ?>
							<li class="<?php echo $class; ?>">
								<a href="javascript:void(0);" <?php if ($rateId > 0) { ?> onclick="editRateLangForm(<?php echo $zoneId ?>, <?php echo $rateId ?>, <?php echo $key; ?>);" <?php } ?>><?php echo $langName; ?></a>
							</li>
						<?php
						} ?>
					</ul>
				</div>
				<div class="tabs__content" dir="<?php echo $formLayout; ?>">
					<?php echo $langFrm->getFormHtml(); ?>
				</div>
			</div>
		</div>
	</div>
</div>