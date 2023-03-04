<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($productsData)) {
	echo '<div class="box--scroller mt-4"> <ul>';
	foreach ($productsData as $product) {
?>
		<li class="d-flex align-items-center py-4 border-bottom">
			<div class="col">
				<img class="mr-2 product-profile-img" src="<?php echo UrlHelper::generateUrl('Image', 'product', array($product['product_id'], 'SMALL', 0, 0, 1)) ?>" alt="" width="50"> <span><?php echo $product['product_name'] ?></span>
			</div>
			<?php if (isset($profileData['shipprofile_default']) && $profileData['shipprofile_default'] != 1) { ?>
				<div class="col-auto">
					<a href="javascript:void(0);" class="btn-clean btn-sm btn-icon btn-secondary" title="<?php echo Labels::getLabel('LBL_Remove_Product_from_profile', $adminLangId); ?>" onclick="removeProductFromProfile('<?php echo $product['product_id']; ?>')"><i class="fas fa-trash"></i></a>
				</div>
			<?php } ?>
		</li>
<?php
	}
	echo '</ul></div>';
}else{ 
	$this->includeTemplate('_partial/no-record-found.php', array('adminLangId'=>$adminLangId));
} 

$frm = new Form('frmProductListing', array('id' => 'frmProductListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadListProduct); return(false);');
echo $frm->getFormTag(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
<style>
	.product-profile-img {
		display: inline;
	}
</style>