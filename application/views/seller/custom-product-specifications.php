<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<div class="tabs ">
    <?php require_once(CONF_THEME_PATH.'seller/sellerCustomProductTop.php');?>
</div>
<div class="card">
    <div class="card-body ">
        <div class="tabs__content">
            <div class="row justify-content-between">
                <div class="col-md-auto">
                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Product_Specifications', $siteLangId); ?></h5>
                </div>
                <div class="col-md-auto">
                    <div class="action">
                        <div class="">
                            <?php if (is_array($prodSpec) && !empty($prodSpec)) { ?>
                            <a onclick="addProdSpec(<?php echo $product_id;?>)" href="javascript:void(0)" class="btn btn-brand btn-sm"><?php echo Labels::getLabel('LBL_Add_Specification', $siteLangId);?></a>
                            <?php }?>
                            <a href="<?php echo UrlHelper::generateUrl('Seller', 'sellerProductForm', array($product_id))?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Add_to_Store', $siteLangId);?></a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class=" " id="product_specifications_list"> </div>
                </div>
            </div>
        </div>
    </div>
</div>
