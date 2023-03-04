<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$prodBrandFrm->setFormTagAttribute('id', 'prodBrand');
$prodBrandFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$prodBrandFrm->setFormTagAttribute('onsubmit', 'setupBrand(this); return(false);');
$prodBrandFrm->developerTags['colClassPrefix'] = 'col-md-';
$prodBrandFrm->developerTags['fld_default_col'] = 12;

$identiFierFld = $prodBrandFrm->getField('brand_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup', "Slugify(this.value,'urlrewrite_custom','brand_id');
getSlugUrl($(\"#urlrewrite_custom\"),$(\"#urlrewrite_custom\").val())");
$IDFld = $prodBrandFrm->getField('brand_id');
$IDFld->setFieldTagAttribute('id', "brand_id");
$urlFld = $prodBrandFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
$urlFld->htmlAfterField = "<small class='text--small'>" . UrlHelper::generateFullUrl('Brands', 'View', array($brand_id), CONF_WEBROOT_FRONT_URL) . '</small>';
$urlFld->setFieldTagAttribute('onKeyup', "getSlugUrl(this,this.value)");
?>
<section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Product_Brand_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a class="active" href="javascript:void(0)"
                                onclick="brandForm(<?php echo $brand_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId);?></a>
                        </li>
                        <li class="<?php echo (0 == $brand_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $brand_id) ? "onclick='brandLangForm(" . $brand_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <?php
                        $inactive = ($brand_id == 0) ? 'fat-inactive' : '';?>
                        <li class="<?php echo $inactive;?>">
                            <a href="javascript:void(0);" 
                                <?php if ($brand_id > 0) { ?>
                                    onclick="brandMediaForm(<?php echo $brand_id ?>);" 
                                <?php } ?>>
                                <?php echo Labels::getLabel('LBL_Media', $adminLangId);?>
                            </a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php echo $prodBrandFrm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>