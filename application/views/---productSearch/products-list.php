<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($products) {
	
    $showActionBtns = !empty($showActionBtns) ? $showActionBtns : false;
    foreach ($products as $product) { 
		
        $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['_source']['general']['selprod_id'])); ?> <div class="items">
    <!--product tile-->
    <div class="products">
        <div class="products__quickview">
            <a onClick='quickDetail(<?php echo $product['_source']['general']['selprod_id']; ?>)' class="modaal-inline-content">
            <span class="svg-icon">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view"></use>
                </svg>
            </span><?php echo Labels::getLabel('LBL_Quick_View', $siteLangId);?></a>
        </div>
        <?php /*if($product['in_stock'] == 0) { */ ?>
            <!--<span class="tag--soldout"><?php /*echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId); */?></span>-->
        <?php /* }*/ ?>
        <div class="products__body"> 
            <?php $this->includeTemplate('_partial/collection-ui.php', array('product'=>$product,'siteLangId'=>$siteLangId, 'showActionBtns'=> $showActionBtns), false); ?> 
        <div class="products__img">
                <?php $uploadedTime = AttachedFile::setTimeParam($product['_source']['general']['product_updated_on']);?> <a title="<?php echo $product['_source']['inventories'][0]['selprod_title']; ?>"
                    href="<?php echo !isset($product['promotion_id'])? UrlHelper::generateUrl('Products', 'View', array($product['_source']['general']['selprod_id'])):UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id']))?>">
                    <?php $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['_source']['general']['product_id']);?>
					<img data-ratio="1:1 (500x500)"
                        src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['_source']['general']['product_id'], "CLAYOUT3", $product['_source']['general']['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                        alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $product['_source']['categories'][0]['prodcat_name']; ?>" title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $product['_source']['categories'][0]['prodcat_name']; ?>">
                </a>
            </div>
        </div>
        <div class="products__footer"> <?php /* if(round($product['prod_rating'])>0 && FatApp::getConfig("CONF_ALLOW_REVIEWS",FatUtility::VAR_INT,0)){ ?> <div class="products__rating">
                <i class="icn"><svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                    </svg></i> <span class="rate"><?php echo round($product['prod_rating'],1);?></span> <?php if(round($product['prod_rating'])==0 ){  ?> <span class="be-first"> <a
                        href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Be_the_first_to_review_this_product', $siteLangId); ?> </a> </span> <?php } ?> </div> <?php } */ ?>
                        <div class="products__category">
                            <a href="<?php echo UrlHelper::generateUrl('Category', 'View', array($product['prodcat_id'])); ?>"><?php echo html_entity_decode($product['_source']['categories'][0]['prodcat_name'], ENT_QUOTES, 'UTF-8'); ?> </a>
                        </div>
            <div class="products__title"><a title="<?php echo $product['_source']['general']['product_name']; ?>"
                    href="<?php echo UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>"><?php echo (mb_strlen($product['_source']['inventories'][0]['selprod_title']) > 50) ? mb_substr($product['_source']['general']['product_name'], 0, 50)."..." : $product['_source']['general']['product_name']; ?>
                </a></div> <?php $this->includeTemplate('productSearch/collection-product-price.php', array('product'=> $product, 'siteLangId'=>$siteLangId), false); ?>
        </div>
    </div>    
    <!--/product tile-->
</div> <?php
    }

    $searchFunction ='goToProductListingSearchPage';
    if (isset($pagingFunc)) {
        $searchFunction =  $pagingFunc;
    }

    $postedData['page'] = (isset($page)) ? $page:1;
    $postedData['recordDisplayCount'] = $recordCount;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging','id' => 'frmProductSearchPaging'));
    $pagingArr = array('pageCount'=>$pageCount,'page'=> $postedData['page'],'recordCount' => $recordCount, 'callBackJsFunc' => $searchFunction);
    $this->includeTemplate('productSearch/pagination.php', $pagingArr, false);
} else {
    $arr['recordDisplayCount'] = $recordCount;
    echo FatUtility::createHiddenFormFromData($arr, array('name' => 'frmProductSearchPaging','id' => 'frmProductSearchPaging'));
    echo Labels::getLabel('LBL_No_record_found!', $siteLangId);
}
