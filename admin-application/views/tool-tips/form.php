<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
//$tooltipFrm->setFormTagAttribute('id', 'prodBrand');
$tooltipFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$tooltipFrm->setFormTagAttribute('onsubmit', 'setupTooltip(this); return(false);');
$tooltipFrm->developerTags['colClassPrefix'] = 'col-md-';
$tooltipFrm->developerTags['fld_default_col'] = 12;
if ($tooltipId > 0) {
$fld_tooltip_key = $tooltipFrm->getField('tooltip_key');
$fld_tooltip_key->setFieldTagAttribute('disabled','disabled');
}
?>
<section class="section">
<div class="sectionhead">  
    <h4><?php echo Labels::getLabel('LBL_Tooltip_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="tooltipForm(<?php echo $tooltipId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a>
			</li>
            <li class="<?php echo (0 == $tooltipId) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" onclick='tooltipLangForm(<?php echo $tooltipId; ?> , <?php echo FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1); ?>, "add")'>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $tooltipFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div></div></section>