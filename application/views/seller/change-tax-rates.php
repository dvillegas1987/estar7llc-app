<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="popup__body">
	<div class="pop-up-title"><?php echo Labels::getLabel('LBL_Customize_Tax_Rates',$siteLangId);?></div>
	<?php
	$frm->setFormTagAttribute('onsubmit','setUpTaxRates(this); return(false);');
	$frm->setFormTagAttribute('class','form');
	$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-6 col-sm-';
	$frm->developerTags['fld_default_col'] = 6;
	echo $frm->getFormHtml(); ?>
</div>
