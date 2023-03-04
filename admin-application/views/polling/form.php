<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$pollingFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$pollingFrm->setFormTagAttribute('onsubmit', 'setupPolling(this); return(false);');
if(!empty($polling_type)){
	$polling_type_text ='';
	if($polling_type == Polling::POLLING_TYPE_PRODUCTS){
		$polling_type_text = 'Products';
	} else if($polling_type == Polling::POLLING_TYPE_CATEGORY){
		$polling_type_text = 'Categories';
	}
}
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Polling_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="pollingForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo ($polling_id == 0) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo ($polling_id) ? "onclick='pollingLangForm(" . $polling_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
			<?php 
            if(!empty($polling_type) && !empty($polling_type_text)){ ?>
			<li><a href="javascript:void(0)" <?php if($polling_id>0){?> onclick="linksForm(<?php echo $polling_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $pollingFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
