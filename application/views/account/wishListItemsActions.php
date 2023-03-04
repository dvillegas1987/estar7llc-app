<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); 

$displayActions = true;
if (isset($wishListRow['products']) && 1 > count($wishListRow['products'])) {
    $displayActions = false;
}

if (true === $displayActions) {
    if (true == $isWishList) {
        $function = 'removeSelectedFromWishlist(' . $wishListRow['uwlist_id'] . ', event)';
    } else {
        $function = 'removeSelectedFromFavtlist(event)';
    }
    ?>
    <div class="col-auto">
        <div class="action action--favs btn-group-scroll">
            <label class="btn btn-outline-brand btn-sm checkbox checkbox-inline select-all">
                <input type="checkbox" class='selectAll-js' onclick="selectAll($(this));"><i
                    class="input-helper"></i>Select all
            </label>
            <div class="btn-group">
            <?php if (true == $isWishList) { ?>
                <a title='<?php echo Labels::getLabel('LBL_Move_to_other_wishlist', $siteLangId); ?>'
                    class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css"
                    onclick="viewWishList(0,this,event, <?php echo !empty($wishListRow['uwlist_id']) ? $wishListRow['uwlist_id'] : 0; ?>);"
                    href="javascript:void(0)">
                    <i class="fa fa-heart"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Move', $siteLangId); ?>
                </a>
            <?php } ?>
            <a title='<?php echo Labels::getLabel('LBL_Move_to_cart', $siteLangId); ?>'
                class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css" onClick="addSelectedToCart(event, <?php echo ($isWishList ? 1 : 0); ?>);"
                href="javascript:void(0)">
                <i class="fa fa-shopping-cart"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Cart', $siteLangId); ?>
            </a>
            <a title='<?php echo Labels::getLabel('LBL_Move_to_trash', $siteLangId); ?>'
                class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css"
                onClick="<?php echo $function; ?>"
                href="javascript:void(0)">
                <i class="fa fa-trash"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?>
            </a>
            <?php if (true == $isWishList) { ?>
                <a class="btn btn-brand btn-sm" onClick="searchWishList();" href="javascript:void(0)">
                <i class="fa fa-backward"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>
                </a>
            <?php } ?>
        </div>
        </div>
    </div>
<?php } ?>