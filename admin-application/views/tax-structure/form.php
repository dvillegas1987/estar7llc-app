<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'setupTaxStructure(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$fld = $frm->getField('taxstr_is_combined');
$fld->setOptionListTagAttribute('class', 'list-inline-checkboxes'); 
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';

?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Tax_Structure_Setup', $adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="tabs_nav_container responsive flat">
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<div class="row justify-content-center">
						<div class="col-md-8">
							<?php echo $frm->getFormTag(); ?>
							<div class="row">
								<div class="col-md-12">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label">
											<?php $fld = $frm->getField('taxstr_name['.$siteDefaultLangId.']');
												echo $fld->getCaption(); ?>
											<span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
											<?php echo $frm->getFieldHtml('taxstr_name['.$siteDefaultLangId.']'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="field-set">
										<div class="field-wraper">
											<div class="field_cover">
											<?php echo $frm->getFieldHtml('taxstr_is_combined'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php $combTaxCount = 0; ?>
							<?php if (array_key_exists('taxstr_is_combined', $taxStrData) && $taxStrData['taxstr_is_combined']) { ?>
								<div id="combinedTax-js" <?php echo (!array_key_exists('taxstr_is_combined', $taxStrData) || (!$taxStrData['taxstr_is_combined'])) ? 'style="display:none"' : '';?>>
									<div class="row">
										<div class="col-md-12">
											<h6><?php
												$fld = $frm->getField('taxstr_component_name['.$combTaxCount.']['.$siteDefaultLangId.']');
												echo $fld->getCaption();
											?></h6>
										</div>	
									</div>
									<div class="row combined-tax--js">
										<?php foreach ($combinedTaxes as $combTaxCount => $combinedTax) { ?>
										<div class="col-md-12 combined-tax-row<?php echo $combTaxCount; ?>">
											<div class="field-set">
												<div class="field-wraper">
													<div class="field_cover d-flex">
													<input type="text" name="taxstr_component_name[<?php echo $combTaxCount; ?>][<?php echo $siteDefaultLangId; ?>]" value="<?php echo isset($combinedTax[$siteDefaultLangId]) ? $combinedTax[$siteDefaultLangId] : '';?>">
													<button type="button" data-id="<?php echo $combTaxCount; ?>" class="btn btn--secondary ripplelink remove-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId); ?>"><i class="ion-minus-round"></i></button>
													<button type="button" class="btn btn--secondary ripplelink add-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Add', $adminLangId); ?>"><i class="ion-plus-round"></i></button>
													</div>
												</div>
											</div>
										</div>
										<?php } ?>
									</div>
								</div>
							<?php } else { ?>
								<div id="combinedTax-js" <?php echo (!array_key_exists('taxstr_is_combined', $taxStrData) || (!$taxStrData['taxstr_is_combined'])) ? 'style="display:none"' : '';?>>
									<div class="row">
										<div class="col-md-12">
											<h6><?php
												$fld = $frm->getField('taxstr_component_name['.$combTaxCount.']['.$siteDefaultLangId.']');
												echo $fld->getCaption();
											?></h6>
										</div>	
									</div>
									<div class="row combined-tax--js">
										<div class="col-md-12 combined-tax-row<?php echo $combTaxCount; ?>">
											<div class="field-set">
												<div class="field-wraper">
													<div class="field_cover d-flex">
													<?php echo $frm->getFieldHtml('taxstr_component_name['.$combTaxCount.']['.$siteDefaultLangId.']'); ?>
													<button type="button" data-id="<?php echo $combTaxCount; ?>" class="btn btn--secondary ripplelink remove-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId); ?>"><i class="ion-minus-round"></i></button>
													<button type="button" class="btn btn--secondary ripplelink add-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Add', $adminLangId); ?>"><i class="ion-plus-round"></i></button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php } ?>
							<?php /* $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
								if(!empty($translatorSubscriptionKey) && count($otherLangData) > 0) { ?>
								<div class="col-md-12">
									<div class="field-set">
										<div class="field-wraper">
											<div class="field_cover"> 
											<?php echo $frm->getFieldHtml('auto_update_other_langs_data'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php } */ ?>
						<?php if(!empty($otherLangData)){
							foreach($otherLangData as $langId => $data) { ?>
							<div class="accordians_container accordians_container-categories" defaultLang= "<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
								<div class="accordian_panel">
									<span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
									<?php echo $data." "; echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
									</span>
									<div class="accordian_body accordiancontent p-0" style="display: none;">
										<div class="row">
											<div class="col-md-12">
												<div class="field-set">
													<div class="caption-wraper">
														<label class="field_label">
														<?php  $fld = $frm->getField('taxstr_name['.$langId.']');
															echo $fld->getCaption(); ?>
														</label>
													</div>
													<div class="field-wraper">
														<div class="field_cover">
														<?php echo $frm->getFieldHtml('taxstr_name['.$langId.']'); ?>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?php $combTaxCount = 0; ?>
										<?php if (array_key_exists('taxstr_is_combined', $taxStrData) && $taxStrData['taxstr_is_combined']) { ?>
											<div id="combinedTaxLang-js" <?php echo (!array_key_exists('taxstr_is_combined', $taxStrData) || (!$taxStrData['taxstr_is_combined'])) ? 'style="display: none;"' : ''; ?>>
												<div class="row">
													<div class="col-md-12">
														<h6><?php
															$fld = $frm->getField('taxstr_component_name['.$combTaxCount.']['.$langId.']');
															echo $fld->getCaption();
														?></h6>
													</div>	
												</div>
												<div class="row combined-tax-lang--js<?php echo $langId; ?>">
												<?php foreach ($combinedTaxes as $combTaxCount => $combinedTax) { ?>
													<div class="col-md-12 combined-tax-row<?php echo $combTaxCount; ?>">
														<div class="field-set">
															<div class="field-wraper">
																<div class="field_cover">
																<input type="text" name="taxstr_component_name[<?php echo $combTaxCount; ?>][<?php echo $langId; ?>]" value="<?php echo isset($combinedTax[$langId]) ? $combinedTax[$langId] : '';?>">
																</div>
															</div>
														</div>
													</div>
												<?php } ?>
												</div>
											</div>
										<?php } else { ?>
										<div id="combinedTaxLang-js" <?php echo (!array_key_exists('taxstr_is_combined', $taxStrData) || (!$taxStrData['taxstr_is_combined'])) ? 'style="display: none;"' : ''; ?>>
											<div class="row">
												<div class="col-md-12">
													<h6><?php
														$fld = $frm->getField('taxstr_component_name['.$combTaxCount.']['.$langId.']');
														echo $fld->getCaption();
													?></h6>
												</div>	
											</div>
											<div class="row combined-tax-lang--js<?php echo $langId; ?>">
												<div class="col-md-12 combined-tax-row<?php echo $combTaxCount; ?>">
													<div class="field-set">
														<div class="field-wraper">
															<div class="field_cover">
															<?php echo $frm->getFieldHtml('taxstr_component_name['.$combTaxCount.']['.$langId.']'); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php } 
							} ?>
							<div class="row">
								<div class="col-md-12">
									<div class="field-set d-flex align-items-center">
										<div class="field-wraper w-auto">
											<div class="field_cover">
												<?php echo $frm->getFieldHtml('btn_submit'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php  echo $frm->getFieldHtml('taxstr_id'); ?>
							</form>
							<?php echo $frm->getExternalJS(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
    $(document).ready(function(){
        var combTaxCount = <?php echo $combTaxCount; ?>;
        $('body').on('click', '.add-combined-form--js', function( event ){
			event.stopImmediatePropagation();
			combTaxCount++;
			var rowHtml = '<div class="col-md-12 combined-tax-row'+combTaxCount+'"><div class="field-set"><div class="field-wraper"><div class="field_cover d-flex"><input type="text" name="taxstr_component_name['+ combTaxCount +'][<?php echo $siteDefaultLangId; ?>]" value=""><button type="button" data-id="'+combTaxCount+'" class="btn btn--secondary ripplelink remove-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId); ?>"><i class="ion-minus-round"></i></button><button type="button" class="btn btn--secondary ripplelink add-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Add', $adminLangId); ?>"><i class="ion-plus-round"></i></button></div></div></div></div>';
            $('.combined-tax--js').append(rowHtml);

            <?php foreach ($otherLangData as $langId => $data) { ?>
                var langRowHtml = '<div class="col-md-12 combined-tax-row'+combTaxCount+'"><div class="field-set"><div class="field-wraper"><div class="field_cover"><input type="text" name="taxstr_component_name['+ combTaxCount +'][<?php echo $langId; ?>]" value=""></div></div></div></div>';
                $('.combined-tax-lang--js'+<?php echo $langId; ?>).append(langRowHtml);
            <?php } ?>
			fcom.resetFaceboxHeight();
        });
		
		
		
		/* $( ".add-combined-form--js" ).click(function( event ) {
			event.preventDefault();
			combTaxCount++;
			var rowHtml = '<div class="col-md-12 combined-tax-row'+combTaxCount+'"><div class="field-set"><div class="field-wraper"><div class="field_cover d-flex"><input type="text" name="taxstr_component_name['+ combTaxCount +'][<?php echo $siteDefaultLangId; ?>]" value=""><button type="button" data-id="'+combTaxCount+'" class="btn btn--secondary ripplelink remove-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId); ?>"><i class="ion-minus-round"></i></button><button type="button" class="btn btn--secondary ripplelink add-combined-form--js ml-2" title="<?php echo Labels::getLabel('LBL_Add', $adminLangId); ?>"><i class="ion-plus-round"></i></button></div></div></div></div>';
            $('.combined-tax--js').append(rowHtml);

            <?php foreach ($otherLangData as $langId => $data) { ?>
                var langRowHtml = '<div class="col-md-12 combined-tax-row'+combTaxCount+'"><div class="field-set"><div class="field-wraper"><div class="field_cover"><input type="text" name="taxstr_component_name['+ combTaxCount +'][<?php echo $langId; ?>]" value=""></div></div></div></div>';
                $('.combined-tax-lang--js'+<?php echo $langId; ?>).append(langRowHtml);
            <?php } ?>
			fcom.resetFaceboxHeight();
		}); */

        // Find and remove selected table rows
        $('body').on('click', '.remove-combined-form--js', function(){
            var rowCount = $(this).data("id");
            if (rowCount > 0) {
                var className = $('.combined-tax-row'+rowCount).remove();
            }
			fcom.resetFaceboxHeight();
        });
    });
</script>