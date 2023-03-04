<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        <div class="">
            <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId); ?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="col-lg-12 col-md-12">
            <div class="">
                <div class="tabs tabs-sm tabs--scroll clearfix">
                    <ul>
                        <li><a onclick="getShopCollectionGeneralForm(<?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Basic', $siteLangId); ?></a></li>
                        <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $scollection_id) ? "onclick='editShopCollectionLangForm(" . $scollection_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                            </a>
                        </li>
                        <li class="is-active">
                            <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_LINK', $siteLangId); ?> </a>
                        </li>
                        <li class=""><a <?php if ($scollection_id > 0) { ?> onclick="collectionMediaForm(this, <?php echo $scollection_id; ?>);" <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_Media', $siteLangId); ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="row form__subcontent">
                <div class="col-lg-6 col-md-6">
                    <?php
                    $collectionLinkFrm->setFormTagAttribute('onsubmit', 'setUpSellerCollectionProductLinks(this); return(false);');
                    $collectionLinkFrm->setFormTagAttribute('class', 'form form--horizontal');
                    $collectionLinkFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md- ';
                    $collectionLinkFrm->developerTags['fld_default_col'] = 12;

                    $submitFld = $collectionLinkFrm->getField('btn_submit');
                    $submitFld->setFieldTagAttribute('class', "btn btn-brand btn-wide");
                    echo $collectionLinkFrm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("document").ready(function() {
        $('#selprod-products').on('click', '.remove_link', function() {
            $(this).parent().remove();
        });
    });

    <?php
    if (isset($products) && !empty($products)) {
        foreach ($products as $key => $val) {
            $options = SellerProduct::getSellerProductOptions($val['selprod_id'], true, $siteLangId);
            $variantsStr = '';
            array_walk($options, function ($item, $key) use (&$variantsStr) {
                $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
            });
            $productName = strip_tags(html_entity_decode(($val['product_name'] != '') ? $val['product_name'] : $val['product_identifier'], ENT_QUOTES, 'UTF-8'));
            $productName .=  $variantsStr; ?>
            $('#selprod-products ul').append("<li id=\"selprod-products<?php echo $val['selprod_id']; ?>\"> <?php echo $productName; ?>[<?php echo $val['product_identifier']; ?>] <i class=\"fa fa-times remove_param remove_link\"></i> <input type=\"hidden\"  name=\"product_ids[]\" value=\"<?php echo $val['selprod_id']; ?>\" /></li>");
    <?php }
    } ?>
</script>