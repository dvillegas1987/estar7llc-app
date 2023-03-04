<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute("class","form form--horizontal");
?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Manage_Batch_Products_Media', $siteLangId); ?></h2>
	<ul class="tabs tabs--small    -js clearfix setactive-js">
		<li ><a href="javascript:void(0)" onclick="batchForm()"><?php echo Labels::getLabel( 'LBL_General', $siteLangId ); ?></a></li>
        <li class="<?php echo (0 == $prodgroup_id) ? 'fat-inactive' : ''; ?>">
            <a href="javascript:void(0);" <?php echo (0 < $prodgroup_id) ? "onclick='productLangForm(" . $prodgroup_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
            </a>
        </li>
		<li class="is-active"><a href="javascript:void(0)" <?php if( $prodgroup_id >0){ ?> onClick="batchMediaForm(<?php echo $prodgroup_id; ?>)" <?php } ?>><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
	</ul>

	<div class="col-md-12">
		<?php echo $mediaFrm->getFormHtml(); ?>
	</div>

	<?php if($batchImgArr){
	//CommonHelper::printArray($batchImgArr);
	?>
	<div class="col-md-12">
		<ul class="image-listing">
			<?php foreach( $batchImgArr as $batchImage ){ ?>
			<li><?php echo $language[$batchImage['afile_lang_id']]?>
				<div class="uploaded--image"><img src="<?php echo UrlHelper::generateUrl('Image', 'BatchProduct', array($batchImage['afile_record_id'],$batchImage['afile_lang_id'], 'THUMB') ); ?>"></div>
				<div class="btngroup--fix">
					<a class="btn btn-brand btn-sm" href="javascript:void(0);" onclick="removeBatchImage(<?php echo $prodgroup_id; ?>, <?php echo $batchImage['afile_lang_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?></a>
				</div>
			</li>
			<?php } ?>
		</ul>
		<?php ?>
	</div>
	<?php } ?>
</div>
