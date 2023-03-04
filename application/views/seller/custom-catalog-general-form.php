<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'form form--horizontal');
$productFrm->setFormTagAttribute('onsubmit', 'setupcustomCatalogProduct(this); return(false);');

$autoUpdateFld = $productFrm->getField('auto_update_other_langs_data');
if (null != $autoUpdateFld) {
    $autoUpdateFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $autoUpdateFld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
}

$btnSubmit = $productFrm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");

$btnDiscardFld = $productFrm->getField('btn_discard');
$btnDiscardFld->addFieldTagAttribute('onClick', 'goToCatalogRequest();');
$btnDiscardFld->value = Labels::getLabel('LBL_Discard', $siteLangId);
$btnDiscardFld->setFieldTagAttribute('class', "btn btn-outline-brand");
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $productFrm->getFormTag(); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_identifier');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_identifier'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_type');
                            echo $fld->getCaption();
                            ?>
                        </label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_type'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper d-flex justify-content-between">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('brand_name');
                            echo $fld->getCaption();
                            ?>
                            <?php if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) { ?>
                            <span class="spn_must_field">*</span>
                            <?php } ?>
                        </label>
                        <small><a class="form-text text-muted" href="javascript:void(0)" onClick="addBrandReqForm(0);"><?php echo (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Request_For_Brand', $siteLangId) : Labels::getLabel('LBL_Add_Brand', $siteLangId) ?></a></small>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('brand_name'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper d-flex justify-content-between">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('category_name');
                            echo $fld->getCaption();
                            ?>
                            <span class="spn_must_field">*</span>
                        </label>
                        <small><a class="form-text text-muted" href="javascript:void(0)"
                                onClick="addCategoryReqForm(0);"><?php echo (FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Request_For_Category', $siteLangId) : Labels::getLabel('LBL_Add_Category', $siteLangId) ?></a></small>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('category_name'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
					<div class="caption-wraper d-flex justify-content-between">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('taxcat_name');
                            echo $fld->getCaption();
                            ?>
                            <span class="spn_must_field">*</span>
                        </label>
                        <small><a class="form-text text-muted" target="_blank" href="<?php echo UrlHelper::generateUrl('seller', 'taxCategories'); ?>"><?php echo Labels::getLabel('LBL_Tax_Categories', $siteLangId) ; ?></a></small>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('taxcat_name'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_min_selling_price');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_min_selling_price'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_active');
                            echo $fld->getCaption();
                            ?>
                        </label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_active'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $divLayout = Language::getLayoutDirection($siteDefaultLangId); ?>
        <div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $divLayout; ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $productFrm->getField('product_name[' . $siteDefaultLangId . ']');
                                echo $fld->getCaption();
                                ?>
                                <span class="spn_must_field">*</span>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('product_name[' . $siteDefaultLangId . ']'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $productFrm->getField('product_youtube_video[' . $siteDefaultLangId . ']');
                                echo $fld->getCaption();
                                ?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('product_youtube_video[' . $siteDefaultLangId . ']'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set mb-0">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $productFrm->getField('product_description_'.$siteDefaultLangId);
                                echo $fld->getCaption();
                                ?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('product_description_'.$siteDefaultLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            if (!empty($translatorSubscriptionKey) && count($otherLanguages) > 0) {
            ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set mb-0">
                        <div class="caption-wraper"></div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('auto_update_other_langs_data'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <?php
        if (!empty($otherLanguages)) {
            foreach ($otherLanguages as $langId => $data) {
                $layout = Language::getLayoutDirection($langId);
        ?>
        <div class="accordion my-4" id="specification-accordion-<?php echo $langId; ?>">
            <h6 class="dropdown-toggle" data-toggle="collapse" data-target="#collapse-<?php echo $langId; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $langId; ?>"><span onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')">
                    <?php echo $data . " ";
        echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                </span>
            </h6>
            <div id="collapse-<?php echo $langId; ?>" class="collapse collapse-js-<?php echo $langId; ?>" aria-labelledby="headingOne" data-parent="#specification-accordion-<?php echo $langId; ?>">
                <div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $layout; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $productFrm->getField('product_name[' . $langId . ']');
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $productFrm->getFieldHtml('product_name[' . $langId . ']'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $productFrm->getField('product_youtube_video[' . $langId . ']');
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $productFrm->getFieldHtml('product_youtube_video[' . $langId . ']'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $productFrm->getField('product_description_'.$langId);
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $productFrm->getFieldHtml('product_description_'.$langId); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
            }
        }
        ?>
        <div class="row">
            <div class="col-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('btn_discard');?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 text-right">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php
                            echo $productFrm->getFieldHtml('product_id');
                            echo $productFrm->getFieldHtml('preq_id');
                            echo $productFrm->getFieldHtml('product_brand_id');
                            echo $productFrm->getFieldHtml('ptc_prodcat_id');
                            echo $productFrm->getFieldHtml('ptt_taxcat_id');
                            echo $productFrm->getFieldHtml('btn_submit');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php echo $productFrm->getExternalJS(); ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('input[name=\'brand_name\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('brands', 'autoComplete'),
                    data: {
                        keyword: request['term'],
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(event, ui) {
                $('input[name=\'product_brand_id\']').val(ui.item.id);
            }
        });

        $('input[name=\'brand_name\']').change(function() {
            if ($(this).val() == '') {
                $("input[name='product_brand_id']").val(0);
            }
        });

        $('input[name=\'category_name\']').autocomplete({
            minLength: 2,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'linksAutocomplete'),
                    data: {
                        keyword: request['term'],
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(event, ui) {
                $('input[name=\'ptc_prodcat_id\']').val(ui.item.id);
            }
        });

        $('input[name=\'category_name\']').change(function() {
            if ($(this).val() == '') {
                $("input[name='ptc_prodcat_id']").val(0);
            }
        });

        $('input[name=\'taxcat_name\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function(request, response) {
                    $.ajax({
                            url: fcom.makeUrl('products', 'autoCompleteTaxCategories'),
                            data: {keyword: request['term'],fIsAjax:1},
                            dataType: 'json',
                            type: 'post',
                            success: function(json) {
                                    response($.map(json, function(item) {
                                            return { label: item['name'], value: item['name'], id: item['id'] };
                                    }));
                            },
                    });
            },
            select: function(event, ui) {
                    $('input[name=\'ptt_taxcat_id\']').val(ui.item.id);
            }
        });
    });
</script>
