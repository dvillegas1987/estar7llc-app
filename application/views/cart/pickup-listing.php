<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="cart-blocks">
    <?php
    $productsCount = count($products);
    if ($productsCount) {
        uasort($products, function ($a, $b) {
            return  $b['fulfillment_type'] - $a['fulfillment_type'];
        });
    ?>
        <ul class="list-group list-cart <?php echo (count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]) != $productsCount) ? '' : 'list-cart-page'; ?>">
            <?php
            //if (count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]) > 0 && count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]) != $productsCount) {
            if (count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]) != $productsCount) {
            ?>
                <li class="list-group-item">
                    <div class="info">
                        <span> <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                                </use>
                            </svg><?php echo Labels::getLabel('MSG_SOME_ITEMS_NOT_AVAILABLE_FOR_PICKUP', $siteLangId); ?>
                            <?php if (count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]) == $productsCount) { ?>
                                <a href="javascript:void(0);" onClick="listCartProducts(<?php echo Shipping::FULFILMENT_SHIP; ?>);" class="link"><?php echo Labels::getLabel('LBL_Ship_Entire_Order', $siteLangId); ?></a>
                            <?php } ?>
                        </span>
                        <ul class="list-actions">
                            <li>
                                <a href="javascript:void(0);" onClick="removeShippedOnlyProducts();"><svg class="svg" width="24px" height="24px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                        </use>
                                    </svg>
                                </a></li>
                        </ul>
                    </div>
                </li>
                <?php foreach ($products as $key => $product) {
                    if ($product['fulfillment_type'] != Shipping::FULFILMENT_SHIP) {
                        continue;
                    }
                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                    $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));
                    $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                    $productTitle =  ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];
                ?>
                    <li class="list-group-item <?php echo md5($product['key']); ?> <?php echo (!$product['in_stock']) ? 'disabled' : ''; ?> list-saved-later">
                        <div class="product-profile">
                            <div class="product-profile__thumbnail">
                                <a href="<?php echo $productUrl; ?>">
                                    <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                                </a></div>
                            <div class="product-profile__data">
                                <div class="title"><a class="" href="<?php echo $productUrl; ?>"><?php echo $productTitle; ?></a> </div>
                                <div class="options">
                                    <p class=""> <?php
                                                    if (isset($product['options']) && count($product['options'])) {
                                                        foreach ($product['options'] as $key => $option) {
                                                            if (0 < $key) {
                                                                echo ' | ';
                                                            }
                                                            echo $option['option_name'] . ':'; ?> <span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
                                        <?php }
                                                    } ?></p>
                                </div>
                                <p class="txt-brand pt-2"><?php echo Labels::getLabel('LBL_NOT_AVAILABLE_FOR_PICKUP', $siteLangId); ?></p>
                            </div>
                        </div>
                        <button class="btn btn-outline-brand btn-sm" type="button" onClick="moveToSaveForLater( '<?php echo md5($product['key']); ?>',<?php echo $product['selprod_id']; ?>, <?php echo Shipping::FULFILMENT_PICKUP; ?> );"> <?php echo Labels::getLabel('LBL_Save_For_later', $siteLangId); ?></button>
                    </li>
                <?php } ?>
        </ul>
        <ul class="list-group list-cart list-cart-page">
        <?php } ?>
        <?php foreach ($products as $product) {

            if ($product['fulfillment_type'] == Shipping::FULFILMENT_SHIP) {
                continue;
            }

            $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
            $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));
            $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $productTitle =  ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];

        ?>

            <li class="list-group-item <?php echo md5($product['key']); ?> <?php echo (!$product['in_stock']) ? 'disabled' : ''; ?>">
                <div class="product-profile">
                    <div class="product-profile__thumbnail">
                        <a href="<?php echo $productUrl; ?>">
                            <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                        </a></div>
                    <div class="product-profile__data">
                        <div class="title"><a class="" href="<?php echo $productUrl; ?>"><?php echo $productTitle; ?></a> </div>
                        <div class="options">
                            <p class=""> <?php
                                            if (isset($product['options']) && count($product['options'])) {
                                                foreach ($product['options'] as $key => $option) {
                                                    if (0 < $key) {
                                                        echo ' | ';
                                                    }
                                                    echo $option['option_name'] . ':'; ?> <span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
                                <?php }
                                            } ?></p>
                        </div>
                        <p class="save-later">
                            <?php
                            $showAddToFavorite = true;
                            if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
                                $showAddToFavorite = false;
                            }
                            if ($showAddToFavorite) { ?>

                                <?php if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
                                    if (empty($product['ufp_id'])) {  ?>
                                        <a href="javascript:void(0)" class="" onClick="addToFavourite( '<?php echo md5($product['key']); ?>',<?php echo $product['selprod_id']; ?> );" title="<?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_Move_to_favourites', $siteLangId); ?></a>
                                    <?php } else {
                                        echo Labels::getLabel('LBL_Already_marked_as_favourites.', $siteLangId);
                                    }
                                } else {
                                    if (empty($product['is_in_any_wishlist'])) { ?>
                                        <a href="javascript:void(0)" class="" onClick="moveToWishlist( <?php echo $product['selprod_id']; ?>, event, '<?php echo md5($product['key']); ?>' );" title="<?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?></a>
                            <?php  } else {

                                        echo Labels::getLabel('LBL_Already_added_to_your_wishlist.', $siteLangId);
                                    }
                                }
                            } ?>
                            / <a href="javascript:void(0)" class="" onClick="moveToSaveForLater( '<?php echo md5($product['key']); ?>',<?php echo $product['selprod_id']; ?>, <?php echo Shipping::FULFILMENT_PICKUP; ?> );" title="<?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_Save_For_later', $siteLangId); ?></a>
                        </p>
                    </div>
                </div>
                <div class="wrap-qty-price">
                    <div class="product-quantity">
                        <div class="quantity quantity-2" data-stock="<?php echo $product['selprod_stock']; ?>">
                            <span class="decrease decrease-js shipProductsCount <?php echo ($product['quantity'] <= $product['selprod_min_order_qty']) ? 'not-allowed' : ''; ?>"><i class="fas fa-minus"></i></span>
                            <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                                <input name="qty_<?php echo md5($product['key']); ?>" data-key="<?php echo md5($product['key']); ?>" class="qty-input cartQtyTextBox productQty-js" value="<?php echo $product['quantity']; ?>" type="text" />
                            </div>
                            <span class="increase increase-js <?php echo ($product['selprod_stock'] <= $product['quantity']) ? 'not-allowed' : ''; ?>"><i class="fas fa-plus"></i></span>
                        </div>
                    </div>
                    <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?></div>
                </div>
                <div class="product-action">
                    <ul class="list-actions">
                        <li>
                            <a href="javascript:void(0)" onclick="cart.remove('<?php echo md5($product['key']); ?>','cart')"><svg class="svg" width="24px" height="24px" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                    </use>
                                </svg>
                            </a></li>
                    </ul>
                </div>
            </li>
        <?php } ?>

        </ul>
    <?php } ?>
    <?php if (0 < count($saveForLaterProducts)) { ?>
        <h5 class="cart-title"><?php echo Labels::getLabel('LBL_Save_For_later', $siteLangId); ?> (<?php echo count($saveForLaterProducts); ?>)</h5>
        <ul class="list-group list-cart list-cart-page">
            <?php foreach ($saveForLaterProducts as $product) {
                $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $productTitle =  ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];
            ?>
                <li class="list-group-item <?php echo md5($product['key']); ?> <?php echo (!$product['in_stock']) ? 'disabled' : ''; ?>">
                    <div class="product-profile">
                        <div class="product-profile__thumbnail">
                            <a href="<?php echo $productUrl; ?>">
                                <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                            </a></div>
                        <div class="product-profile__data">
                            <div class="title"><a class="" href="<?php echo $productUrl; ?>"><?php echo $productTitle; ?></a></div>
                            <div class="options">
                                <p class=""> <?php
                                                if (isset($product['options']) && count($product['options'])) {
                                                    foreach ($product['options'] as $key => $option) {
                                                        if (0 < $key) {
                                                            echo ' | ';
                                                        }
                                                        echo $option['option_name'] . ':'; ?> <span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
                                    <?php }
                                                } ?></p>
                            </div>
                            <button class="btn btn-outline-brand btn-sm product-profile__btn" type="button" onclick="moveToCart(<?php echo $product['selprod_id']; ?>, <?php echo $product['uwlp_uwlist_id']; ?>, event, <?php echo Shipping::FULFILMENT_PICKUP; ?>)"><?php echo Labels::getLabel('LBL_Move_To_Bag', $siteLangId); ?></button>
                        </div>
                    </div>
                    <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?></div>
                    <div class="product-action">
                        <ul class="list-actions">
                            <li>
                                <a href="javascript:void(0)" onclick="removeFromWishlist(<?php echo $product['selprod_id']; ?>, <?php echo $product['uwlp_uwlist_id']; ?>, event)"><svg class="svg" width="24px" height="24px" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                        </use>
                                    </svg>
                                </a></li>
                        </ul>
                    </div>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</div>