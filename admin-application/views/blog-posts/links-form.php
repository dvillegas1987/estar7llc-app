<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmLinks->setFormTagAttribute('class', 'web_form form_horizontal');
$frmLinks->setFormTagAttribute('onsubmit', 'setupPostCategories(this); return(false);');
$frmLinks->developerTags['colClassPrefix'] = 'col-md-';
$frmLinks->developerTags['fld_default_col'] = 12;

$fld_div = $frmLinks->getField('categories');
$fld_div->fieldWrapper = array('<div class="box--scroller">','</div>');
?>

<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Link_Blog_Post_To_Categories',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="blogPostForm(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li><a class="active" href="javascript:void(0);" onclick="linksForm(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_Link_Category',$adminLangId); ?></a></li>
			<li class="<?php echo ($post_id == 0) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo ($post_id) ? "onclick='langForm(" . $post_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
			<li><a href="javascript:void(0);" onclick="postImages(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_Post_Images',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmLinks->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
</div>
</div>
</section>