<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($scollection_id) && $scollection_id > 0) {
    $scollection_id = $scollection_id;
} else {
    $scollection_id = 0;
} ?>
<ul class="tabs_nav tabs_nav--internal">
    <li>
        <a onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>);" href="javascript:void(0)">
            <?php echo Labels::getLabel('TXT_GENERAL', $adminLangId); ?>
        </a>
    </li>
    <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
        <a href="javascript:void(0);" <?php echo (0 < $scollection_id) ? "onclick='editShopCollectionLangForm(" . $shop_id . "," . $scollection_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
            <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
        </a>
    </li>
    <li>
        <a class="active" onclick="sellerCollectionProducts(<?php echo $scollection_id; ?>,<?php echo $shop_id; ?>);" href="javascript:void(0);">
            <?php echo Labels::getLabel('TXT_LINK', $adminLangId); ?>
        </a>
    </li>
    <li>
        <a onclick="collectionMediaForm(<?php echo $shop_id ?>, <?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId); ?> </a>
    </li>
</ul>
<div class="tabs_panel_wrap">
    <div class="form__subcontent">
        <?php
        $sellerCollectionproductLinkFrm->setFormTagAttribute('onsubmit', 'setUpSellerCollectionProductLinks(this); return(false);');
        $sellerCollectionproductLinkFrm->setFormTagAttribute('class', 'form form_horizontal web_form');
        $sellerCollectionproductLinkFrm->developerTags['colClassPrefix'] = 'col-md-';
        $sellerCollectionproductLinkFrm->developerTags['fld_default_col'] = 12;
        $sellerCollectionproductLinkFrm->addHiddenField('', 'shop_id', $shop_id);

        $fld = $sellerCollectionproductLinkFrm->getField('scp_selprod_id');
        $fld->setWrapperAttribute('class', 'ui-front');

        echo $sellerCollectionproductLinkFrm->getFormHtml(); ?>
    </div>
</div>
<script type="text/javascript">
    $("document").ready(function() {
        $('#selprod-products ul').on('click', '.remove_buyTogether', function() {
            /* $('#selprod-products ul').delegate('.remove_buyTogether', 'click', function() { */
            $(this).parent().remove();
        });
    });
    <?php
    if (isset($products) && !empty($products)) {
        foreach ($products as $key => $val) {
            $options = SellerProduct::getSellerProductOptions($val['selprod_id'], true, $adminLangId);
            $variantsStr = '';
            array_walk($options, function ($item, $key) use (&$variantsStr) {
                $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
            });
            $userName = isset($val["credential_username"]) ? " | " . $val["credential_username"] : '';
            $productName = strip_tags(html_entity_decode(($val['product_name'] != '') ? $val['product_name'] : $val['product_identifier'], ENT_QUOTES, 'UTF-8'));
            $productName .=  $variantsStr . $userName; ?>
            $('#selprod-products ul').append("<li id=\"selprod-products<?php echo $val['selprod_id']; ?>\"><i class=\" remove_buyTogether icon ion-close\"></i><?php echo $productName; ?> [<?php echo $val['product_identifier']; ?>]<input type=\"hidden\" name=\"product_ids[]\" value=\"<?php echo $val['selprod_id']; ?>\" /></li>");
    <?php }
    } ?>
</script>