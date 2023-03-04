<?php
if (!empty($allShops)) {
    $i = 0;
    foreach ($allShops as $shop) { /* CommonHelper::printArray($shop); die; */ ?>

        <div class="ftshops row <?php echo ($i % 2 != 0) ? 'ftshops-rtl' : ''; ?>">
            <div class="col-md-12 mb-4">
                <div class="ftshops_item">
                    <div class="shop-detail-side">
                        <div class="shop-detail-inner">
                            <div class="ftshops_item_head_left">
                                <div class="ftshops_logo">
                                    <?php
                                    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_LOGO, $shop['shop_id'], 0, 0, false);
                                    $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
                                    ?>
                                    <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopLogo', array($shop['shop_id'], $siteLangId, "THUMB", 0, false), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $shop['shop_name']; ?>">
                                </div>
                                <div class="ftshops_detail">
                                    <div class="ftshops_name"><a href="<?php echo UrlHelper::generateUrl('shops', 'view', array($shop['shop_id'])); ?>"><?php echo $shop['shop_name']; ?></a></div>
                                    <div class="ftshops_location"><?php echo $shop['state_name']; ?><?php echo ($shop['country_name'] && $shop['state_name']) ? ', ' : ''; ?><?php echo $shop['country_name']; ?></div>
                                </div>
                            </div>
                            <div class="ftshops_item_head_right">
                                <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && round($shop['shopRating']) > 0) { ?>
                                    <div class="products__rating"> <i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                            </svg></i> <span class="rate"><?php echo  round($shop['shopRating'], 1); ?><span></span></span>
                                    </div>
                                <?php } ?>
                                <a href="<?php echo UrlHelper::generateUrl('shops', 'view', array($shop['shop_id']), '', null, false, false, true, true); ?>" class="btn btn-brand btn-sm ripplelink" tabindex="0"><?php echo Labels::getLabel('LBL_View_Shop', $siteLangId); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="product-wrapper">
                        <div class="row">
                            <?php
                            $displayProductNotAvailableLable = false;
                            if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
                                $displayProductNotAvailableLable = true;
                            }
                            foreach ($shop['products'] as $product) { ?>
                                <div class="col-6 col-lg-3 mb-3 mb-md-0">
                                    <?php include(CONF_THEME_PATH . '_partial/collection/product-layout-1-list.php'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php $i++;
    }
} else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false);
}

$postedData['page'] = (isset($page)) ? $page : 1;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmSearchShopsPaging'
));
