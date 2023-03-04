<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupPackage(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Manage_Package', $adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
			<div class="col-sm-12">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</section>