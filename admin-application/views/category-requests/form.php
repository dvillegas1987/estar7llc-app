<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmCategoryReq->setFormTagAttribute('class', 'web_form form_horizontal');
$frmCategoryReq->setFormTagAttribute('onsubmit', 'setupCategoryReq(this); return(false);');
$frmCategoryReq->setValidatorJsObjectName('brandRequestFormValidator');

$fld = $frmCategoryReq->getField('status');
$fld->setFieldTagAttribute('onChange','showHideCommentBox(this.value)');

$fldBl = $frmCategoryReq->getField('comments');
$fldBl->htmlBeforeField = '<span id="div_comments_box" class="hide">Reason for Cancellation';
$fldBl->htmlAfterField = '</span>';
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Category_Request_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addCategoryReqForm(<?php echo $categoryReqId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo (0 == $categoryReqId) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo (0 < $categoryReqId) ? "onclick='addCategoryReqLangForm(" . $categoryReqId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmCategoryReq->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
