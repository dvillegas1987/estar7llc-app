<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$user_is_buyer = 0;
if (UserAuthentication::isUserLogged()) {
    $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
}
if ($user_is_buyer > 0 || (!UserAuthentication::isUserLogged())) { ?>
    <a href="javascript:void(0)" data-trigger-cart="side-cart">
        <span class="icn">
        <svg class="svg">
         <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#main-cart" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#main-cart"></use>
        </svg></span>
		<span class="cartQuantity"><?php echo (Cart::CART_MAX_DISPLAY_QTY < $totalCartItems) ? Cart::CART_MAX_DISPLAY_QTY . '+' : $totalCartItems; ?></span>
        <span class="icn-txt"><strong><?php echo Labels::getLabel("LBL_Cart", $siteLangId); ?></strong>
            <?php /* if (0 < $cartSummary['cartTotal']) { */ ?>
                <span class="cartValue"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
            <?php /* } */ ?>
        </span>
    </a>
    <div class="side-cart" id="side-cart" data-close-on-click-outside-cart="side-cart">
        <a href="javascript:void(0)" class="close-layer" data-target-close-cart="side-cart"></a>
        <?php if ($totalCartItems>0) { ?>
        <div class="cartdetail__body" data-simplebar="init" data-simplebar-auto-hide="false" >
            <div class="short-detail">
                <ul class="list-group list-cart cart-summary">                   
                        <?php
                        if (count($products)) {
                            foreach ($products as $product) {
                                $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                                $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));
                                $imageUrl =  UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "EXTRA-SMALL", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?> 
                                
                                <li class="list-group-item <?php echo (!$product['in_stock']) ? 'disabled' : '';
                                echo ($product['is_digital_product'])?'digital_product_tab-js':'physical_product_tab-js'; ?>">
                            
                            
                                <div class="item">
                            <div class="item__pic"><a href="<?php echo $productUrl; ?>"><img src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>"></a></div>
                                <div class="item__description">
                                    <div class="item__category"><a href="<?php echo $shopUrl; ?>"><?php echo $product['shop_name']; ?> </a></div>
                                    <div class="item__title"><a title="<?php echo $product['product_name']; ?>" href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a></div>
                                    <div class="item__specification"> <?php
                                    if (isset($product['options']) && count($product['options'])) {
                                        $count = 0;
                                        foreach ($product['options'] as $option) {
                                            ?> <?php echo ($count > 0) ? ' | ' : '' ;
                                            echo $option['option_name'].':'; ?> <?php echo $option['optionvalue_name']; ?> <?php $count++;
                                        }
                                    } ?> | <?php echo Labels::getLabel('LBL_Quantity:', $siteLangId) ?> <?php echo $product['quantity']; ?> </div>
                                </div>
                                </div>
                             
                            
                                <div class="product_price"><span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']*$product['quantity']); ?> </span>
                                    <?php if ($product['special_price_found']) { ?>
                                        <span class="text--normal text--normal-secondary text-nowrap"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                                    <?php } ?>
                                </div>
                             
                             <div class="product-action">
                                 <ul class="list-actions">
                                     <li>
                                        <a href="javascript:void(0)" class="" onclick="cart.remove('<?php echo md5($product['key']); ?>')" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                            <svg class="svg" width="24px" height="24px" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                                </use>
                                            </svg>
                                        </a>
                                     </li>
                                 </ul>
                                
                                </div>
                             
                        </li>
                         <?php
                            }
                        } else {
                            echo Labels::getLabel('LBL_Your_cart_is_empty', $siteLangId);
                        } ?>  
                </ul>
            </div>
        </div>
        <div class="cartdetail__footer cart-total">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                <span class="label"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></span>
                <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
                </li>
                <?php if (0 < $cartSummary['cartVolumeDiscount']) { ?>
                    <li class="list-group-item">
                    <span class="label"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></span>
                    <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></span>
                    </li>
                <?php } ?>
                <?php ?> 
                <?php $netChargeAmt = $cartSummary['cartTotal'] - ((0 < $cartSummary['cartVolumeDiscount'])?$cartSummary['cartVolumeDiscount']:0); ?>
                <li class="list-group-item">
                    <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
                    <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($netChargeAmt); ?></span>
                </li>
                <li class="list-group-item">                    
                    <div class="buttons-group">
                        <a class="btn btn-brand" href="<?php echo UrlHelper::generateUrl('cart'); ?>"><?php echo Labels::getLabel('LBL_Proceed_To_Pay', $siteLangId); ?></a>
                        <a href="javascript:void(0);" onclick="cart.clear();" class="btn btn-outline-brand"><?php echo Labels::getLabel('LBL_CLEAR_CART', $siteLangId); ?> </a>
                    </div>
                </li>
             </ul>
        </div>
        <?php } else { ?>
            <div class="block--empty m-auto text-center"> <img class="block__img" src="<?php echo CONF_WEBROOT_URL; ?>images/retina/empty_cart.svg" alt="<?php echo Labels::getLabel('LBL_No_Record_Found', $siteLangId); ?>" width="80">
                <h4><?php echo Labels::getLabel('LBL_Your_Shopping_Bag_is_Empty', $siteLangId); ?></h4>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<script>
$(document).ready(function () {
    
    $('body').find('*[data-trigger-cart]').click(function () {
        var targetElmId = $(this).data('trigger-cart');
        var elmToggleClass = targetElmId + '--on';
        if ($('body').hasClass(elmToggleClass)) {
            $('body').removeClass(elmToggleClass);
        } else {
            $('body').addClass(elmToggleClass);
        }
    });

    $('body').find('*[data-target-close-cart]').click(function () {
        var targetElmId = $(this).data('target-close-cart');
        $('body').toggleClass(targetElmId + '--on');
    });

    $('body').mouseup(function (event) {
        if ($(event.target).data('triggerCart') != '' && typeof $(event.target).data('triggerCart') !== typeof undefined) {
            event.preventDefault();
            return;
        }

        $('body').find('*[data-close-on-click-outside-cart]').each(function (idx, elm) {
            var slctr = $(elm);
            if (!slctr.is(event.target) && !$.contains(slctr[0], event.target)) {
                $('body').removeClass(slctr.data('close-on-click-outside-cart') + '--on');
            }
        });
    });

});
</script>
 


