<div class="featured">
    <?php $i = 0; 
    foreach ($collection['shops'] as $shop) { ?>    
        <div class="featured-item">
            <div class="featured-item__body">
                <div class="featured_logo">
                    <img loading='lazy'
                        src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopLogo', array($shop['shopData']['shop_id'], $siteLangId, "THUMB", 0, false), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                        alt="<?php echo $shop['shopData']['shop_name']; ?>"></div>
                <div class="featured_detail">
                    <div class="featured_name"><a
                            href="<?php echo (!isset($shop['shopData']['promotion_id']) ? UrlHelper::generateUrl('shops', 'view', array($shop['shopData']['shop_id'])) : UrlHelper::generateUrl('shops', 'track', array($shop['shopData']['promotion_record_id'], Promotion::REDIRECT_SHOP, $shop['shopData']['promotion_record_id']))); ?>"><?php echo $shop['shopData']['shop_name']; ?></a>
                    </div>
                    <div class="featured_location">
                        <?php echo $shop['shopData']['state_name']; ?><?php echo ($shop['shopData']['country_name'] && $shop['shopData']['state_name']) ? ', ' : ''; ?><?php echo $shop['shopData']['country_name']; ?>
                    </div>
                </div>
            </div>
            <div class="featured-item__foot">                
                    <?php if (round($collection['rating'][$shop['shopData']['shop_id']]) > 0) { ?>
                    <div class="products__rating"> <i class="icn"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                            </svg></i> <span
                            class="rate"><?php echo  round($collection['rating'][$shop['shopData']['shop_id']], 1); ?><span></span></span>
                    </div>
                    <?php } ?>
                    <a href="<?php echo (!isset($shop['shopData']['promotion_id']) ? UrlHelper::generateUrl('shops', 'view', array($shop['shopData']['shop_id'])) : UrlHelper::generateUrl('shops', 'track', array($shop['shopData']['promotion_record_id'], Promotion::REDIRECT_SHOP, $shop['shopData']['promotion_record_id']))); ?>"
                        class="btn btn-brand btn-sm"><?php echo Labels::getLabel('LBL_Shop_Now', $siteLangId); ?></a>
            </div>

        </div>
    
    <?php $i++;
        isset($shop['shopData']['promotion_id']) ? Promotion::updateImpressionData($shop['shopData']['promotion_id']) : '';
        if ($i == Collections::LIMIT_SHOP_LAYOUT1) break;
    } ?>
</div>