<?php if ($recommendedProducts) { ?>
<div class="container">
    <div class="section-head section--white--head section--head--center">
        <div class="section__heading">
            <h2><?php echo Labels::getLabel('LBL_Recommended_Products', $siteLangId); ?>
            </h2>
        </div>
    </div>
    <div class="js-collection-corner collection-corner product-listing"
        dir="<?php echo CommonHelper::getLayoutDirection();?>">
        <?php foreach ($recommendedProducts as $rProduct) {
            $productUrl = UrlHelper::generateUrl('Products', 'View', array($rProduct['selprod_id'])); ?>
                <!--product tile-->
                <div class="products">
                    <div class="products__quickview">
                        <a onClick='quickDetail(<?php echo $rProduct['selprod_id']; ?>)'
                            class="modaal-inline-content">
                            <span class="svg-icon">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view"
                                        href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view">
                                    </use>
                                </svg>
                            </span><?php echo Labels::getLabel('LBL_Quick_View', $siteLangId); ?>
                        </a>
                    </div>
                    <div class="products__body">
                        <?php $this->includeTemplate('_partial/collection-ui.php', array('product'=>$rProduct,'siteLangId'=>$siteLangId), false); ?>
                        <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
                        <div class="products__img">
                            <a title="<?php echo $rProduct['selprod_title']; ?>"
                                href="<?php echo !isset($rProduct['promotion_id'])?UrlHelper::generateUrl('Products', 'View', array($rProduct['selprod_id'])):UrlHelper::generateUrl('Products', 'track', array($rProduct['promotion_record_id'])); ?>">
                                <?php $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_PRODUCT_IMAGE, $rProduct['product_id']); ?>
                                <img data-ratio="1:1 (500x500)"
                                    src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($rProduct['product_id'], "CLAYOUT3", $rProduct['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                    alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $rProduct['prodcat_name']; ?>"
                                    title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $rProduct['prodcat_name']; ?>">
                            </a>
                        </div>
                    </div>
                    <div class="products__footer">
                        <div class="products__category">
                            <a
                                href="<?php echo UrlHelper::generateUrl('Category', 'View', array($rProduct['prodcat_id'])); ?>">
                                <?php echo $rProduct['prodcat_name']; ?>
                            </a>
                        </div>
                        <div class="products__title"><a
                                title="<?php echo $rProduct['selprod_title']; ?>"
                                href="<?php echo UrlHelper::generateUrl('Products', 'View', array($rProduct['selprod_id'])); ?>"><?php echo (mb_strlen($rProduct['selprod_title']) > 50) ? mb_substr($rProduct['selprod_title'], 0, 50)."..." : $rProduct['selprod_title']; ?>
                            </a></div>
                        <?php $this->includeTemplate('_partial/collection-product-price.php', array('product'=>$rProduct,'siteLangId'=>$siteLangId), false); ?>
                    </div>
                </div>
                <!--/product tile-->
                <?php
        } ?>
    </div>
</div>
<div class="gap"></div>
<?php }
