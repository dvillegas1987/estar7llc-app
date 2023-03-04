<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$filterGroupsFrm->setFormTagAttribute('id', 'frmFilterGroups');
$filterGroupsFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$filterGroupsFrm->setFormTagAttribute('onsubmit', 'setupFilterGroup(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Filter_Group_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="filterGroupForm(<?php echo $filtergroup_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo ($filtergroup_id == 0) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo ($filtergroup_id) ? "onclick='filterGroupLangForm(" . $filtergroup_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $filterGroupsFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
