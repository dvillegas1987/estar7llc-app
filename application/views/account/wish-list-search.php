<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="account-fav-listing">
<div class="row">
    <?php if ($wishLists) {
    foreach ($wishLists as $wishlist) {
        if (count($wishlist['products']) > 0 || FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::YES) { ?>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="wishlists">
            <div class="wishlists__head">
                <span class="item__title">
                    <?php echo (isset($wishlist['uwlist_type']) && $wishlist['uwlist_type'] == UserWishList::TYPE_DEFAULT_WISHLIST) ? Labels::getLabel('LBL_Default_list', $siteLangId) : $wishlist['uwlist_title']; ?></span>
                <?php if ((!isset($wishlist['uwlist_type']) || (isset($wishlist['uwlist_type']) && $wishlist['uwlist_type'] != UserWishList::TYPE_FAVOURITE)) && $wishlist['uwlist_type'] != UserWishList::TYPE_DEFAULT_WISHLIST) { ?>
                <a href="javascript:void(0)"
                    onclick="deleteWishList(<?php echo $wishlist['uwlist_id']; ?>);"
                    class="icons-wrapper"><i class="icn shop"><svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"
                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin">
                            </use>
                        </svg>
                    </i>
                </a>
                <?php
            } ?>
            </div>
            <div class="wishlists__body">
                <?php if ($wishlist['products']) {
                ?>
                <ul class="media-wishlist">
                    
                        <?php foreach ($wishlist['products'] as $product) {
                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>
                        <li
                            class="item <?php echo (!$product['in_stock']) ? 'item--sold' : ''; ?>">
                           
                          
                                <?php if (!$product['in_stock']) {
                        ?>
                                <span class="tag--soldout tag--soldout-small"><?php echo Labels::getLabel('LBL_Sold_Out', $siteLangId); ?></span>
                                <?php
                    } ?>
                                <a href="<?php echo $productUrl; ?>"
                                    >
                                    <img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId ), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                        title="<?php echo $product['product_name']; ?>"
                                        alt="<?php echo $product['product_name']; ?>">
                                </a>
                             
                        </li>
                        <?php
                } ?>
                   
                </ul>
                <?php
            } else {
                $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>Labels::getLabel('LBL_No_items_added_to_this_wishlist.', $siteLangId)));
            }
            if (!isset($wishlist['uwlist_type']) || (isset($wishlist['uwlist_type']) && $wishlist['uwlist_type']!=UserWishList::TYPE_FAVOURITE)) {
                $functionName = 'viewWishListItems';
            } else {
                $functionName = 'viewFavouriteItems';
            } ?>

            </div>
            <?php
                    if ($wishlist['totalProducts']>0) {
                        ?>

            <div class="wishlists__foot">
                <div class="text-center">
                <a onClick="<?php echo $functionName; ?>(<?php echo $wishlist['uwlist_id']; ?>);"
                    href="javascript:void(0)" class="btn btn-outline-brand btn-sm">
                    <?php echo str_replace('{n}', $wishlist['totalProducts'], Labels::getLabel('LBL_View_{n}_items', $siteLangId)); ?>
                    <i class="fa fa-eye"></i>
                </a>
                </div>
            </div> <?php
                    } ?>
        </div>
    </div>
    <?php
        } else {
            $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false);
        }
    }
}
    if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::YES) {
        ?>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="wishlists">
            <div class="wishlists__body">
                <div class="form">
                    <h6><?php echo Labels::getLabel('LBL_Create_new_list', $siteLangId); ?>
                    </h6> <?php
                        $frm->setFormTagAttribute('onsubmit', 'setupWishList2(this,event); return(false);');
                        $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                        $frm->developerTags['fld_default_col'] = 12;
                        $titleFld = $frm->getField('uwlist_title');
                        $titleFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Enter_List_Name', $siteLangId));
                        $titleFld->setFieldTagAttribute('title', Labels::getLabel('LBL_List_Name', $siteLangId));

                        $btnSubmitFld = $frm->getField('btn_submit');
                        $btnSubmitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
                        $btnSubmitFld->value = Labels::getLabel('LBL_Create', $siteLangId);
                        $btnSubmitFld->developerTags['noCaptionTag'] = true;

                        echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    } ?>
</div>
</div>