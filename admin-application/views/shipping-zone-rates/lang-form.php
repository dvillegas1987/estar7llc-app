<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'. $formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangRate(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;
/*
$cancelFld = $langFrm->getField('btn_cancel');
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
							<a href="javascript:void(0);"
								onclick="addEditShipRates(<?php echo $zoneId ?>, <?php echo $rateId ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
						</li>
						<?php
                        if ($rateId > 0) {
                            foreach ($languages as $key => $langName) { ?>
						<li>
							<a class="<?php echo ($langId == $key) ? 'active' : ''?>"
								href="javascript:void(0);"
								onclick="editRateLangForm(<?php echo $zoneId ?>, <?php echo $rateId ?>, <?php echo $key;?>);"><?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?></a>
						</li>
						<?php }
                        }
                        ?>
					</ul>
					<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<?php echo $langFrm->getFormHtml(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>