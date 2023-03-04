<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$filterFrm->setFormTagAttribute('id', 'filterFrm');
$filterFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$filterFrm->setFormTagAttribute('onsubmit', 'setUp(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Filter_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addForm(<?php echo $filtergroup_id;?>,<?php echo $filter_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo ($filter_id == 0) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo ($filter_id) ? "onclick='langForm(" . $filter_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $filterFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
